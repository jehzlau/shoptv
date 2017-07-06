<?php
class Galore_Gcore_Model_Observer extends Varien_Event_Observer
{
   public function __construct()
   {
   }
   
   public function sales_order_save_before_sms_notification($observer)
   {
   	
		  $order = $observer->getEvent()->getOrder();		  
		  //order status before the change
		  $original_order = $order->getOrigData();
		  $original_status = $original_order['status'];
		  $current_status = $order->getStatus();
		  
		  //Proceed only if: $original_status is pending
		  //and $current_status is NOT 'cancelled', 'canceled',  
		  error_log("SMS: Original Status:" . $original_status);
		  error_log("SMS: Current Status:" . $current_status);			  
		  		  
		  if (!(
		  	$original_status == '' &&
		  	$current_status == 'pending'
		  )) {
		  	error_log("SMS: Not applicable to status. Exiting...");	
		  	return;
		  }
		  	
		  
   			
		  //Load data from configuration  
		  $app_id = Mage::getStoreConfig('gcore_config/basic/app_id');
		  $gcore_api_key = Mage::getStoreConfig('gcore_config/basic/api_key');
		  $gcore_api_secret = Mage::getStoreConfig('gcore_config/basic/api_secret');
		  
		  $site = $_SERVER['HTTP_HOST'];
		  
		  //Hardcoding Live GCore API for the SMS notification.
		  //QA is not setup on Globlabs SMS
		  $endpoint = "https://api.gcore.galoretv.com/systems/sms/$app_id/notifications";	
		  
		  if (!$app_id) {
		  	error_log("SMS Error: missing App ID on $site. Can't send SMS messages.");
		  }
		  
		  error_log("SMS app_id: $app_id");
		  
		  $increment_id = $order->getIncrementId();
		  $grand_total = $order->getGrandTotal();
		  $billing_address = $order->getBillingAddress();
		  $firstname = $billing_address->getFirstname();
		  $lastname = $billing_address->getLastname();
		  		  		  		  
		  $message =<<< EOF
New Order at $site. 
Order Number: $increment_id.
Total Amount: P$grand_total 
Ordered Items:

EOF;

		 $purchase_array = array();
		 $message_item = "";
		 $message_all_items = "";
		 #Display all items
		 foreach($order->getAllVisibleItems() as $item) {
		 	
			$item_sku = $item->getSku();
			$item_quantity = intval($item->getQtyOrdered());
			$piece = $item_quantity > 1 ? "pcs" : "pc";
		 	$message_item .=<<<EOF
$item_sku - $item_quantity$piece;

EOF;
			#Avoid duplicate message
			$is_duplicate = false;
			foreach($purchase_aray as $p) {
				if ($p == $item_sku) {
					$is_duplicate = true;
					break;
				}
			}
			
			if (!$is_duplicate) {
				error_log("SMS: Not Duplicate: $item_sku"); 
				$purchase_array[] = $item_sku;
				$message_all_items .= $message_item;
			}
		 }
		 
		 error_log("SMS: Purchase Array: " . json_encode($purchase_array));
		 $message .= $message_all_items;
		 
		 $message .=<<< EOF
Ordered by: $firstname $lastname
EOF;
		  
		  $parameters = array(
		  	"message" => $message
		  ); 
		  
		  
		  //Connect to GCore SMS
		  $curl = curl_init();
		  curl_setopt($curl, CURLOPT_URL,            $endpoint);		  
		  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($curl, CURLOPT_POST, true);
		  curl_setopt($curl, CURLOPT_RETURNTRANSFER,  true);
		  curl_setopt($curl, CURLOPT_HTTPHEADER,     array(
		  	'Content-Type: application/json',
		  	'Accept: application/json',
		  	"Authorization: $gcore_api_key:$gcore_api_secret"
		  ));
		  curl_setopt($curl, CURLOPT_POSTFIELDS,     json_encode($parameters));
		  error_log("SMS: Accessing $endpoint");  		  
		  $result=curl_exec ($curl);
		  error_log("SMS:" . json_encode($parameters));
		  error_log("SMS Result: $result");
 }

 public function sales_order_save_after_pull($observer) {
 	error_log('sales_order_save_after_pull');
	
	$order = $observer->getEvent()->getOrder();
	$order_id = $order->getIncrementId();
	
	if(!$order->getStatus()) {
		return;
	}
	
	//Load data from configuration  
	$gcore_endpoint = Mage::getStoreConfig('gcore_config/basic/gcore_endpoint');
	$store_code = Mage::getStoreConfig('gcore_config/basic/store_code');
	$gcore_api_key = Mage::getStoreConfig('gcore_config/basic/api_key');
	$gcore_api_secret = Mage::getStoreConfig('gcore_config/basic/api_secret');
	
	$endpoint = "$gcore_endpoint/stores/$store_code/sales_orders/$order_id/pull";
	error_log("POST $endpoint");
	
	$parameters = json_encode(array(
		"entity_id" => $order->getEntityId()
	));
	error_log($parameters);
	
	//Connect to GCore
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL,            $endpoint);		  
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,  true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,     array(
		'Content-Type: application/json',
		'Accept: application/json',
		"Authorization: $gcore_api_key:$gcore_api_secret"
	));
  	curl_setopt($curl, CURLOPT_POSTFIELDS,  $parameters); 
	$result=curl_exec($curl);
	//error_log(json_encode($parameters));
	$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	error_log("Result: $http_status");		
 }
   
}