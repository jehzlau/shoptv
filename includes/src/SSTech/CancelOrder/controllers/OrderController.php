<?php
class SSTech_CancelOrder_OrderController
    extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /*
        @params for cancel the order along with email

    */
    public function cancelAction()
    {
        $order = Mage::getModel('sales/order')->load(
            $this->getRequest()->getParam('order_id')
        );

        if ($order->getId()) {
            if (Mage::helper('cancelorder/customer')->canCancel($order)) {
                try {
                    $order->cancel();

                    if ($status = Mage::helper('cancelorder/customer')->getCancelStatus($store)) {
                        $order->addStatusHistoryComment('', $status)
                              ->setIsCustomerNotified(1);
                    }

                    $order->save();
                     // If sending transactionnal email is enabled in system configuration, we send the email
                    //if(Mage::getStoreConfigFlag('sales/cancel/send_email')) {
                    $order->sendOrderUpdateEmail();
                    //}

                    Mage::getSingleton('catalog/session')
                        ->addSuccess($this->__('Your order has been canceled.'));
                } catch (Exception $e) {
                    Mage::getSingleton('catalog/session')
                        ->addException($e, $this->__('Cannot cancel your order.'));
                }
            } else {
                Mage::getSingleton('catalog/session')
                    ->addError($this->__('Cannot cancel your order.'));
            }

            $this->_redirect('sales/order/history');

            return;
        }

        $this->_forward('noRoute');
    }
}
