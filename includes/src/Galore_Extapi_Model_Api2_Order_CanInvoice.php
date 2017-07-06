<?php
class Galore_Extapi_Model_Api2_Order_CanInvoice extends Mage_Api2_Model_Resource
{


    protected function _retrieve()
    {
         $order_increment_id = $this->getRequest()->getParam('order_id');
         $order = Mage::getModel('sales/order')->loadByIncrementId($order_increment_id);

         if($order->canInvoice()) {
            return array("true");
         }else {
            return array("false");
        };
    }
}