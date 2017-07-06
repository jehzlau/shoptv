<?php


class Galore_Extapi_Model_Api2_Invoice extends Mage_Api2_Model_Resource
{
    protected function _retrieveCollection()
    {

		 $order_increment_id = $this->getRequest()->getParam('order_id');
		 $order = Mage::getModel("sales/order")->loadByIncrementId($order_increment_id);
		 $invoices = $order->getInvoiceCollection();

		 foreach ($invoices as $invoice) {
		 	$return_invoices[] = $invoice->getIncrementId();
		 }

		 return $return_invoices;

    }

    protected function _retrieve() {
		$invoice_increment_id = $this->getRequest()->getParam('invoice_id');
		$invoice = Mage::getModel("sales/order_invoice")->loadByIncrementId($invoice_increment_id);
		return $invoice;
    }

	protected function _create() {
		 $order_increment_id = $this->getRequest()->getParam('order_id');

		 $body_params = $this->getRequest()->getBodyParams();

		 $comment = $body_params['comment'];
		 $order = Mage::getModel("sales/order")->loadByIncrementId($order_increment_id);

		 if($order->canInvoice()) {
            if(isset($body_params['order_items'])){
                $order_items = $body_params['order_items'];
                $items_array = array();
                foreach($order_items as $item){
                    $item_id = $item['item_id'];
                    $qty = $item['qty'];
                    $items_array[$item_id] = $qty;
                }
            }else{
                $items_array = null;
            }

		   //http://stackoverflow.com/questions/20799498/magento-create-partial-invoice-programmatically


         $comment = "[Updated by Galore Extapi]" . $comment;
         $do_send_email = false;
         $do_include_comment_in_mail = false;

//			 $items_array = array();
//			 foreach($order->getAllItems() as $item) {
//					$item_id = $item->getItemId(); 	//order_item_id
//					$qty = $item->getQtyOrdered();  //qty ordered for that item
//			 }

         $invoice_id = Mage::getModel('sales/order_invoice_api')
                    ->create($order->getIncrementId(), $items_array,
                    $comment,
                    $do_send_email,
                    $do_include_comment_in_mail);

            header("X-Message: $invoice_id", 200, true);
			return $invoice_id;
         }else{
            header("X-Message: unable to generate invoice", 400, true);
			return "unable to generate invoice";
        }
	}

	protected function _delete() {
		$invoice_increment_id = $this->getRequest()->getParam('invoice_id');
		$invoice = Mage::getModel("sales/order_invoice")->loadByIncrementId($invoice_increment_id);
		$invoice->delete();
	}

}