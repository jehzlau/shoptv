<?php


class Galore_Extapi_Model_Api2_Invoice_Capture extends Mage_Api2_Model_Resource
{

    protected function _update() {
        /*
         Invoice states
            const STATE_OPEN       = 1;
            const STATE_PAID       = 2;
            const STATE_CANCELED   = 3;
        */
        $invoice_increment_id = $this->getRequest()->getParam('invoice_id');
        $invoice = Mage::getModel("sales/order_invoice")->loadByIncrementId($invoice_increment_id);

        if($invoice->canCapture()){
            try{
                $invoice->capture();
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            }catch (Exception $e){
                header("X-Message: Unable to capture.", true, 400);
            }
            header("X-Message: Capture Success");
            return "Capture Success";
        }else{
            header("X-Message: Unable to capture.", true, 400);
            return "Unable to capture invoice";
        }
    }
}