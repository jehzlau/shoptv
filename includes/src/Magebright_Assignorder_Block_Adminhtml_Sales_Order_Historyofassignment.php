<?php
class Magebright_Assignorder_Block_Adminhtml_Sales_Order_Historyofassignment
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface {
   
    public function _construct()
    {
        parent::_construct();
       $this->setTemplate('assignorder/sales/order/historyofassignment.phtml');
    }
   
    public function getTabLabel()
    {
        return $this->__('History of Assignment');
    }
   
    public function getTabTitle()
    {
        return $this->__('History of Assignment');
    }
  
    public function canShowTab()
    {
        return true;
    }
    
    public function isHidden()
    {
        return false;
    }
}