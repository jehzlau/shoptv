<?php

class Magebright_Assignorder_Adminhtml_AssignorderController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {

        $this->loadLayout()
                ->_setActiveMenu('assignorder/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {

        $this->_initAction()                
                ->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('assignorder/adminhtml_assignorder_grid')->toHtml()
        );
    }

    public function massAssignToCustomerAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        //echo "<pre>";print_r($orderIds);exit;
        if(!is_array($orderIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select order(s).'));
        } else {
            try {
                $comma_separated_orderIds = implode(',',$orderIds);
                Mage::getSingleton('core/session')->setAssignOrderIds($comma_separated_orderIds);                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('assignorder/adminhtml_assignorder/index');
    }
    
    public function customerSelectAction()
    {
        $params = $this->getRequest()->getParams();
        /*Assign Order To Customer Start*/
        $order_ids_array = explode(',',$params['order_ids']);        
        
        /*Notify Customer Start*/        
        if(isset($params['send_email']))
        {
            foreach($order_ids_array as $order_id)
            { 
                $customer_id = $params['customer_id'];
                $customer = Mage::getModel('customer/customer')->load($customer_id);
                $customer_email = $customer->getEmail();
                $customer_name = $customer->getFirstname().' '.$customer->getLastname();
                $order = Mage::getModel('sales/order')->load($order_id);
                $this->sendEmail($customer_name,$order,$customer_email);
            }
        }        
        /*Notify Customer End*/
        
        foreach($order_ids_array as $order_id)
        {            
            
            /*Assign Order Data To Customer Start*/
            $customer_id = $params['customer_id'];
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $customer_group_id = $customer->getGroupId();
            $customer_email = $customer->getEmail();
            $customer_firstname = $customer->getFirstname();
            $customer_lastname = $customer->getLastname();
			
			/*Downloadable Link Start*/
			$downlaod_check = Mage::getModel('downloadable/link_purchased')->getCollection()->addFieldToFilter('order_id',array('eq'=>$order_id));
			if(count($downlaod_check) > 0)
			{			
				$downloadable_grid = Mage::getModel('downloadable/link_purchased')->getCollection()->addFieldToFilter('order_id',array('eq'=>$order_id))->getFirstItem();
				$downloadable_grid->setCustomerId($customer_id);
				$downloadable_grid->save();
			}
			/*Downloadable Link End*/
            
            
            /*Send Email Copy To Recepient Start*/
            $customer_name = $customer->getFirstname().' '.$customer->getLastname();
            $order = Mage::getModel('sales/order')->load($order_id);            
            $recepients = Mage::getStoreConfig('assignorder/assignorder_settings/notification_to_email');
            $recepients = trim($recepients,",");                
            $recepients_array = explode(',',$recepients);
            if(count($recepients_array) > 0)
            {
                foreach($recepients_array as $recepient)
                {
                    $recepient = trim($recepient);
                    $this->sendEmail($customer_name,$order,$recepient);
                }
            }
            /*Send Email Copy To Recepient End*/
            
            /*Add Fisrt Entry in History Of Assignment Table Start*/
            $assignorder_history_collection = Mage::getModel('assignorder/history')->getCollection()->addFieldToFilter('order_id', array('eq'=>$order_id));
            if(count($assignorder_history_collection) == 0)
            {
                $order = Mage::getModel('sales/order')->load($order_id);
                $first_assignorder_history_customer_id = $order->getCustomerId();
                $first_assignorder_history_customer_firstname = $order->getCustomerFirstname();
                $first_assignorder_history_customer_lastname = $order->getCustomerLastname();
                $first_assignorder_history_customer_email = $order->getCustomerEmail();
                $first_assignorder_history_customer_group_id = $order->getCustomerGroupId();
                
                $assignorder_history = Mage::getModel('assignorder/history');
                $assignorder_history->setOrderId($order_id);
                $assignorder_history->setCustomerId($first_assignorder_history_customer_id);                        
                $assignorder_history->setCreatedAt(now());

                $assignorder_history->setOrderCustomerFirstname($first_assignorder_history_customer_firstname);
                $assignorder_history->setOrderCustomerLastname($first_assignorder_history_customer_lastname);
                $assignorder_history->setOrderCustomerEmail($first_assignorder_history_customer_email);
                $assignorder_history->setOrderCustomerGroupId($first_assignorder_history_customer_group_id);
                //echo "<pre>";print_r($assignorder_history->getData());exit;
                $assignorder_history->save();
            }
            /*Add Fisrt Entry in History Of Assignment Table End*/

            $order = Mage::getModel('sales/order')->load($order_id);
            $order->setCustomerId($customer_id);
            $order->setCustomerIsGuest(NULL);
            $order->setCustomerGroupId($customer_group_id);
            $order->setCustomerEmail($customer_email);            
            if(isset($params['overwrite_name']))
            {
                $order->setCustomerFirstname($customer_firstname);
                $order->setCustomerLastname($customer_lastname);
            }            
            $order->save();

            $order_grid = Mage::getResourceModel('sales/order_grid_collection')->addFieldToFilter('entity_id',array('eq'=>$order_id))->getFirstItem();
            $order_grid->setCustomerId($customer_id);
            $order_grid->save();
            /*Assign Order Data To Customer End*/
            
            /*Add Data in History Of Assignment Start*/
            $order = Mage::getModel('sales/order')->load($order_id);
            $order_customer_firstname = $order->getCustomerFirstname();
            $order_customer_lastname = $order->getCustomerLastname();
            $order_customer_email = $order->getCustomerEmail();
            $order_customer_group_id = $order->getCustomerGroupId();
            
            $assignorder_history = Mage::getModel('assignorder/history');
            $assignorder_history->setOrderId($order_id);
            $assignorder_history->setCustomerId($customer_id);                        
            $assignorder_history->setCreatedAt(now());
            
            $assignorder_history->setOrderCustomerFirstname($order_customer_firstname);
            $assignorder_history->setOrderCustomerLastname($order_customer_lastname);
            $assignorder_history->setOrderCustomerEmail($order_customer_email);
            $assignorder_history->setOrderCustomerGroupId($order_customer_group_id);            
            if(isset($params['send_email']))
            {
                $assignorder_history->setNotifyCustomer(1);
            }
            //echo "<pre>";print_r($assignorder_history->getData());exit;
            $assignorder_history->save();
            /*Add Data in History Of Assignment End*/
            
        }
        /*Assign Order To Customer End*/
        
        /*Redirect To Order List or View Page Start*/
        $count_order_ids = count($order_ids_array);
        if($count_order_ids ==1)
        {
            Mage::getSingleton('adminhtml/session')->addSuccess('Order was successfully assigned to customer.');
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$order_id)));
        }
        if($count_order_ids > 1)
        {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('%s orders were successfully assigned to the customer.', $count_order_ids)
            );
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));
        }
        /*Redirect To Order List or View Page End*/        
    }
    
    public function sendEmail($name,$order,$email) {

		$storeId = Mage::app()->getStore()->getId();
		$emailTemplate_assignorder = Mage::getStoreConfig('assignorder/assignorder_settings/email_notification_template', $storeId);
		if (!is_numeric($emailTemplate_assignorder)) {
			$emailTemplate_assignorder = 'assignorder_assignorder_settings_email_notification_template';
		}
	
        $emailTemplate  = Mage::getModel('core/email_template');
		$emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => Mage::app()->getStore()->getId()));
        //$emailTemplate->loadDefault(Mage::getStoreConfig('assignorder/assignorder_settings/email_notification_template'));
		$emailTemplate->loadDefault($emailTemplate_assignorder);
        
        $salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');
        $emailTemplate->setSenderName($salesData['name']);
        $emailTemplate->setSenderEmail($salesData['email']);
        
        $emailTemplateVariables = array();
        $storename = $order->getStoreName();
        list($website, $store, $storeview) = explode("\n", $storename);  
        $emailTemplateVariables['store_name'] = $store;   
        $emailTemplate->setTemplateSubject('Assign To Customer'); 
        $emailTemplateVariables['customer_name'] = $name; 
        $emailTemplateVariables['order'] = $order; 
		$emailTemplateVariables['logo_url'] = Mage::getSingleton('core/design_package')->getSkinBaseUrl(array('_area' => 'frontend')).'images/logo_email.gif';
        $emailTemplate->send($email, $order->getStoreName(), $emailTemplateVariables);

    }
    
    public function rollbackAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        /*Delete Last Item Start*/
        $assignorder_history_collection = Mage::getModel('assignorder/history')->getCollection()->addFieldToFilter('order_id', array('eq'=>$order_id))->getLastItem();
        $assignorder_history_collection->delete();
        /*Delete Last Item End*/
        
        $assignorder_history_collection_last = Mage::getModel('assignorder/history')->getCollection()->addFieldToFilter('order_id', array('eq'=>$order_id))->getLastItem();
        $customer_id = $assignorder_history_collection_last['customer_id'];
        $order_customer_firstname = $assignorder_history_collection_last['order_customer_firstname'];
        $order_customer_lastname = $assignorder_history_collection_last['order_customer_lastname'];
        $order_customer_email = $assignorder_history_collection_last['order_customer_email'];
        $order_customer_group_id = $assignorder_history_collection_last['order_customer_group_id'];
        
        /*Assign Order Data To Customer Start*/
        
        if($customer_id > 0)
        {
            $customer_is_guest = NULL;
        }
        else
        {
            $customer_id = NULL;
            $customer_is_guest = 1;
        }
        
        $order = Mage::getModel('sales/order')->load($order_id);
        $order->setCustomerId($customer_id);
        $order->setCustomerIsGuest($customer_is_guest);
        $order->setCustomerGroupId($order_customer_group_id);
        $order->setCustomerEmail($order_customer_email);                    
        $order->setCustomerFirstname($order_customer_firstname);
        $order->setCustomerLastname($order_customer_lastname);                            
        $order->save();

        $order_grid = Mage::getResourceModel('sales/order_grid_collection')->addFieldToFilter('entity_id',array('eq'=>$order_id))->getFirstItem();
        $order_grid->setCustomerId($customer_id);
        $order_grid->save();        
        /*Assign Order Data To Customer End*/
		
		/*Downloadable Link Start*/
		$downlaod_check = Mage::getModel('downloadable/link_purchased')->getCollection()->addFieldToFilter('order_id',array('eq'=>$order_id));
		if(count($downlaod_check) > 0)
		{			
			$downloadable_grid = Mage::getModel('downloadable/link_purchased')->getCollection()->addFieldToFilter('order_id',array('eq'=>$order_id))->getFirstItem();
			$downloadable_grid->setCustomerId($customer_id);
			$downloadable_grid->save();
		}
		/*Downloadable Link End*/
        
        Mage::getSingleton('adminhtml/session')->addSuccess('The assignment was successfully rolled back.');
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$order_id)));
        
    }
}