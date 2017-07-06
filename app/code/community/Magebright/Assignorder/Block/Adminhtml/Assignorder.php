<?php
class Magebright_Assignorder_Block_Adminhtml_Assignorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        $redirect_url = $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$order_id));
        $increment_id = "Order #".Mage::getModel('sales/order')->load($order_id)->getIncrementId();
        if(!$order_id)
        {
            $redirect_url = $this->getUrl('adminhtml/sales_order/index');
            $order_id = Mage::getSingleton('core/session')->getAssignOrderIds();
            $explode_order_ids = explode(',',$order_id);
            $count_order_ids = count($explode_order_ids);
            if($count_order_ids ==1)
            {
                $increment_id = "Order #".Mage::getModel('sales/order')->load($explode_order_ids[0])->getIncrementId();
            }
            if($count_order_ids > 1)
            {
                $increment_id = $count_order_ids.' Selected Order(s)';
            }
        }

        $this->_controller = 'adminhtml_assignorder';
        $this->_blockGroup = 'assignorder';
        $this->_headerText = Mage::helper('assignorder')->__('Assign '.$increment_id.' To Customer');
        
        parent::__construct();
        $this->_removeButton('add');

        $this->_addButton("back", array(
                "label"     => Mage::helper("assignorder")->__("Back"),                
                "onclick"   => "setLocation('{$redirect_url}')",
                "class"     => "back",
        ));
    }
}