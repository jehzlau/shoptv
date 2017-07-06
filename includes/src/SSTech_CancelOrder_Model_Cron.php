<?php
class SSTech_CancelOrder_Model_Cron
{
    const XML_PATH_CANCEL_PENDING = 'cancelorder/cron/cancel_pending';
    const XML_PATH_CANCEL_AFTER   = 'cancelorder/cron/cancel_after';
    const XML_PATH_CANCEL_STATUS  = 'cancelorder/cron/cancel_status';

    public function run()
    {
        $stores = Mage::getModel('core/store')
            ->getCollection()
            ->addFieldToFilter('store_id', array('neq' => Mage_Core_Model_App::ADMIN_STORE_ID));

        foreach ($stores as $store) {
            if (!Mage::getStoreConfigFlag(self::XML_PATH_CANCEL_PENDING, $store)) {
                continue;
            }

            if (!intval(Mage::getStoreConfig(self::XML_PATH_CANCEL_AFTER, $store))) {
                continue;
            }

            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('store_id', $store->getStoreId())
                ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                ->addFieldToFilter('created_at', array('lt' => new Zend_Db_Expr("DATE_ADD('" . now() . "', INTERVAL -'" . intval(Mage::getStoreConfig(self::XML_PATH_CANCEL_AFTER, $store)) . "' MINUTE)")))
                ->setCurPage(1)
                ->setPageSize(10);

            foreach ($orders as $order) {
                if (!$order->canCancel() || $order->hasInvoices() || $order->hasShipments()) {
                    continue;
                }

                $order->cancel();
                if ($status = Mage::getStoreConfig(self::XML_PATH_CANCEL_STATUS, $store)) {
                    $order->addStatusHistoryComment('', $status)
                          ->setIsCustomerNotified(1);
                }

                $order->save();
                $order->sendOrderUpdateEmail(); ///send cancel confirmation mail
            }
        }
    }
}
