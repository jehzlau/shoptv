<?php

class Magebright_Assignorder_Model_Observer {
    
    public function adminhtmlWidgetContainerHtmlBefore($event) {
        $block = $event->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View && Mage::getStoreConfig('assignorder/assignorder_settings/enabled')) {
            $order_id = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($order_id);
            if ($order->getCustomerIsGuest())
            {
                $message = Mage::helper('assignorder')->__('Are you sure you want to do this?');
                $block->addButton('ordertocustomer', array(
                    'label' => Mage::helper('assignorder')->__('Assign To Customer'),
                    'onclick'   => "confirmSetLocation('{$message}', '{$block->getUrl('assignorder/adminhtml_assignorder/index')}')",                    
                    'class' => 'assign-order'
                ));
            }
            else
            {
                $message = Mage::helper('assignorder')->__('Are you sure you want to do this?');
                $block->addButton('ordertocustomer', array(
                    'label' => Mage::helper('assignorder')->__('Assign To Other Customer'),
                    'onclick'   => "confirmSetLocation('{$message}', '{$block->getUrl('assignorder/adminhtml_assignorder/index')}')",                    
                    'class' => 'assign-order'
                ));
            }
        }
    }
    
    public function addAssignOrderToCustomerMassAction($observer) {       
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order'  && Mage::getStoreConfig('assignorder/assignorder_settings/enabled'))
        {
            $block->addItem('assigntocustomer', array(
                'label' => 'Assign To Customer',                
                'url' => $block->getUrl('assignorder/adminhtml_assignorder/massAssignToCustomer'),
            ));
        }
    }    
}
?>
