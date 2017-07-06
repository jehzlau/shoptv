<?php
/**
 * @author Ecommerce Approch Team
 * @copyright Copyright (c) 2015 Ecommerce Approch (http://www.ecommerceapproach.com/)
 * @package Ecommerceapp_Cashdelivery
 */
class Ecommerceapp_Cashdelivery_Model_Observer {

	public function allowCashOnDelivery(Varien_Event_Observer $observer) {
        $event           = $observer->getEvent();
        $method          = $event->getMethodInstance();
        $result          = $event->getResult();
        $isModuleEnable = Mage::getStoreConfig('cashdelivery/cashdelivery/enable');
		
		if($isModuleEnable) {
		
			if($method->getCode() == 'cashondelivery' ){
				
				$quote = Mage::getSingleton('checkout/cart')->getQuote();
				$add = $quote->getShippingAddress();
				$postcode = $add->getData('postcode');
				$CodRegion = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getRegion();
				
				$comparisonMode = Mage::getStoreConfig('cashdelivery/cashdelivery/mode');
				$zipCodes = Mage::getStoreConfig('cashdelivery/cashdelivery/zipcode');
				$isExist = false;
				
				if(trim($zipCodes) == '') {				
					$result->isAvailable = true;
				} else {	
				
					if(strpos($zipCodes, $CodRegion) !==  false) {
						$isExist = true;
					}
						 
					if($isExist != true) {
						
						$zipCodesArray = explode('<br />', nl2br($zipCodes));
						foreach($zipCodesArray as $codzipLine) {
							$elementLineArray = explode('-', $codzipLine);
							if(count($elementLineArray) == 2 && ( $postcode >= $elementLineArray[0] && $postcode <= $elementLineArray[1] )) {
								$isExist = true;
								break;
							} else if($postcode == $codzipLine) {
								$isExist = true;
								break;
							}
						}
					}
					
					if($isExist != true) {
						
						$zipCodesArray = explode(',', nl2br($zipCodes));
						if(count($elementLineArray) > 1) {
							foreach($zipCodesArray as $codzipLine) {
								$elementLineArray = explode('-', $codzipLine);
								if(count($elementLineArray) == 2 && ( $postcode >= $elementLineArray[0] && $postcode <= $elementLineArray[1] )) {
									$isExist = true;
									break;
								} else if($postcode == $codzipLine) {
									$isExist = true;
									break;
								}
							}
						}
					}
					
					$returnValue = '';
					if($comparisonMode == '1') { //Include Mode
						$returnValue = ($isExist)?true:false;
					} else if($comparisonMode == '2') {	//Exclude Mode
						$returnValue = ($isExist)?false:true;
					}
						
					$result->isAvailable = $returnValue;
				}	
			
			} 
		}	
    }
}
