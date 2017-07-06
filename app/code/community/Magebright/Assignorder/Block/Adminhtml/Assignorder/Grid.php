<?php

class Magebright_Assignorder_Block_Adminhtml_Assignorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
    {
        parent::__construct();
        $this->setId('assignorderGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        
        /*Get Customer Id from Order Id Start*/
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        
        if(!$order_id)
        {            
            $order_id = Mage::getSingleton('core/session')->getAssignOrderIds();
            $explode_order_ids = explode(',',$order_id);
            $count_order_ids = count($explode_order_ids);
            if($count_order_ids ==1)
            {                
                $order_id = $explode_order_ids[0];
            }                       
        }
        
        $order = Mage::getModel('sales/order')->load($order_id);
        
        $storeId = $order->getStoreId();
        $website_id = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        //echo "<pre>";print_r($website_id);exit;
        $customer_email = $order['customer_email'];
        
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId($website_id);
        $customer->loadByEmail($customer_email);
        
        $customer_data = $customer->getData();
        $customer_id = '';
        if(!empty($customer_data))
        {            
            $customer_id = $customer_data['entity_id'];
        }
        /*Get Customer Id from Order Id End*/
        
        $collection = Mage::getResourceModel('customer/customer_collection');        	
             $collection->addNameToSelect()
            ->addAttributeToSelect('email')            
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');            
        
        $collection->addAttributeToFilter('entity_id',array('neq'=>$customer_id));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));

        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
       return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {        
        return 'javascript: assignorderRowClick();';
    }
    
    protected function _afterToHtml($html) {
        return parent::_afterToHtml($html) . $this->_appendHtml();
    }
    private function _appendHtml() {	
        
        $form_key = Mage::getSingleton("core/session")->getFormKey(); 
        $form_action_url = $this->getUrl('assignorder/adminhtml_assignorder/customerSelect');
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $increment_id = "Order: #".Mage::getModel('sales/order')->load($order_id)->getIncrementId();
        if(!$order_id)
        {            
            $order_id = Mage::getSingleton('core/session')->getAssignOrderIds();
            $explode_order_ids = explode(',',$order_id);
            $count_order_ids = count($explode_order_ids);
            if($count_order_ids ==1)
            {
                $increment_id = "Order: #".Mage::getModel('sales/order')->load($explode_order_ids[0])->getIncrementId();
            }
            if($count_order_ids > 1)
            {
                $increment_id = $count_order_ids.' Orders';
            }            
        }
        
        $html = '<script type="text/javascript">
    
    if(typeof String.prototype.trim !== "function") {
        String.prototype.trim = function() {
            return this.replace(/^\s+|\s+$/g, "");
        }
    }
    
    var assignorderRowClick = function () {
        var tds = $$("#assignorderGrid tr.on-mouse td");

        var customerId = tds[0].innerHTML.toString();
        var customerName = tds[1].innerHTML.toString();
        var customerEmail = tds[2].innerHTML.toString();
        
        var templatePattern =  "<div class='."assignorder-dialog".'>"+
            "<div class='."assignorder-message".'>"+
            "<div class='."order".'><span title='."'$increment_id'".'>'.$increment_id.'</span></div>"+
            "<div class='."arrow".'>&nbsp;</div>"+
            "<div class='."customer".'><span class='."left-hover".'></span><span class='."name".' title="+customerName.trim()+">"+customerName.trim()+"</span><br/><span class='."email".' title="+customerEmail.trim()+">"+customerEmail.trim()+"</span><span class='."right-hover".'></span></div>"+
            "<div class='."fixed".'></div>"+
            "</div>"+
            "<div class='."assignorder-checkboxes".'>"+
            "<form id='."assignorder-form".' action='."$form_action_url".' method='."POST".'>"+
            "<input type='."hidden".' class='."hidden".' name='."order_ids".' value='."$order_id".'>"+
            "<input type='."hidden".' class='."hidden".' name='."form_key".' value='."$form_key".'>"+
            "<input type='."hidden".' class='."hidden".' name='."customer_id".' value="+customerId.trim()+">"+
            "<div class='."line".'>"+
            "<input type='."checkbox".' class='."checkbox".' name='."overwrite_name".' id='."overwrite_name_checkbox".'>"+
            "<label for='."overwrite_name_checkbox".'>Overwrite Customer Name</label>"+
            "</div>"+
            "<div class='."line".'>"+
            "<input type='."checkbox".' class='."checkbox".' name='."send_email".' id='."send_email_checkbox".'>"+
            "<label for='."send_email_checkbox".'>Notify Customer</label>"+
            "</div>"+
            "</form>"+
            "</div>"+
            "</div>"
            ;
            
        var templateMessage = new Template(templatePattern, new RegExp("(^|.|\\r|\\n)({{\\s*(\\w+)\\s*}})", ""));

        var data = {
            "order_id": "11",
            "increment_id": "100000008",
            "customer_name": customerName.trim(),
            "customer_email": customerEmail.trim(),
            "customer_id": customerId.trim(),
            "send_email": false,
            "overwrite_name": true };
         
        var message = templateMessage.evaluate(data);        
        Dialog.confirm(message, {
            className: "magento",
            id: "mp_assignorder_dialog",
            width: 390,
            height: 165,
            title: "Confirmation",
            destroyOnClose: true,
            closable: true,
            draggable: true,
            okLabel: "Assign Order",
            cancelLabel: "Cancel",
            onShow: (function (event) {
                $("send_email_checkbox").checked = data.send_email;
                $("overwrite_name_checkbox").checked = data.overwrite_name;
            }).bind(data),
            onOk: function (event) {

                $$("#mp_assignorder_dialog_content button.ok_button, #mp_assignorder_dialog_content input.ok_button").each(function(el){
                    el.disabled = true;
                    el.addClassName("disabled");
                });
                $("assignorder-form").submit();
            }
        });
    };
</script>';
        return $html;
    }

}
