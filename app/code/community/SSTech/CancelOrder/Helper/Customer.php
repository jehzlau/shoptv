<?php
class SSTech_CancelOrder_Helper_Customer
    extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CANCEL_NEW     = 'cancelorder/customer/cancel_new';
    const XML_PATH_CANCEL_PENDING = 'cancelorder/customer/cancel_pending';
    const XML_PATH_CANCEL_STATUS  = 'cancelorder/customer/cancel_status';

    public function canCancelNew($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CANCEL_NEW, $store);
    }

    public function canCancelPending($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CANCEL_PENDING, $store);
    }

    public function getCancelStatus($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CANCEL_STATUS, $store);
    }

    public function canCancel(Mage_Sales_Model_Order $order)
    {
        if ($order->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
            return false;
        }

        if (!in_array($order->getState(), Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates(), $strict = true)) {
            return false;
        }

        if (!$order->canCancel() || $order->hasInvoices() || $order->hasShipments()) {
            return false;
        }

        if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW && $this->canCancelNew($order->getStore())) {
            return true;
        }

        if ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT && $this->canCancelPending($order->getStore())) {
            return true;
        }

        return false;
    }
}
