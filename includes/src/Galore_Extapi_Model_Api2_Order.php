<?php
class Galore_Extapi_Model_Api2_Order extends Mage_Api2_Model_Resource
{


    protected function _retrieve()
    {
         $order_increment_id = $this->getRequest()->getParam('order_id');
         $order = Mage::getModel('sales/order')->loadByIncrementId($order_increment_id);
		 return $order;
    }

    protected function _update()
    {
         $order_increment_id = $this->getRequest()->getParam('order_id');
		 $body_params = $this->getRequest()->getBodyParams();
		 
		 $status = $body_params['status'];
		 $notify_customer_by_email = $body_params['notify_customer_by_email'];
		 $comment = $body_params['comment'];
		 $force = $body_params["force"] || false;
		 
		 //var_dump($body_params);
         $order = Mage::getModel('sales/order')->loadByIncrementId($order_increment_id);
		 $old_status = $order->status;
		 
		 //Wash off of all flags if reverting back to new.
		 //This is debatable. Sounds like a bug.
		 /*
		 if ($status == Mage_Sales_Model_Order::STATE_NEW && $force) {
 
		 	$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_HOLD, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_EDIT, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CREDITMEMO, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_INVOICE, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_REORDER, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_SHIP, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_COMMENT, false);
			$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED, false);
			
			//delete all invoices
			$invoices = $order->getInvoiceCollection();
			foreach($invoices as $invoice) {
				 $invoice->delete();
			}
 			
			//Set invoiced quantity back to orderd
		    foreach ($order->getAllItems() as $item) {
		            $item->setData('qty_invoiced', 0);
		    }
			 			
		   }
		  * 
		  */ 
		  
	 	 $order->setStatus($status);
	 	 $order->addStatusHistoryComment("[Galore/Extapi]:" . $comment, $status);
		 
		 if ($notify_customer_by_email) {
		 	$order->sendOrderUpdateEmail(true, $comment);
		 }
		 
		 $order->save();
		 		 
		 header("X-Message: Setting status from '$old_status' to '$status'.");		 
    }
 
}