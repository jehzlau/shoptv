<?php

class Galore_Extapi_Model_Api2_Invoice_Pdf extends Mage_Api2_Model_Resource
{
	protected function _retrieve() {
		$invoice_increment_id = $this->getRequest()->getParam('invoice_id');
		$invoice = Mage::getModel("sales/order_invoice")->loadByIncrementId($invoice_increment_id);
		$path = "tmp";
		$filename = md5(uniqid(rand(), true)) . ".pdf";
		
		$full_path = Mage::getBaseDir('media') . "/$path/$filename";
		$url = Mage::getBaseUrl('media') . "$path/$filename";
		
		$pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice))->render();
		
		//TODO: self destruct after a time
		
		if (file_put_contents("$full_path", $pdf)) {
			 //Don't tell me why it should be placed in an array.
			 //I also have no effing clue why Magento behaves this way.
			 return array($url);
		} else {
			header("X-Message: Unable to generate file.", true, 400);
			return "Unable to generate file";
		}
					
	}
	
}
	