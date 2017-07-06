<?php
$email = "jehzlau@ropoventures.com,anna@bily.com.ph,shirley.pedrera@bily.com.ph,maryann.sison@bily.com.ph,meann.manansala@bily.com.ph,billie.peralta@bily.com.ph";
$url = "http://www.cudsly.com/";
$path = "reports/";

//$today = date("Y-m-d");
//$time = mktime(0, 0, 0, 11, 10, 2015);
$time = time();
$today = date("Y-m-d", $time + 86400);
$yesterday = date("Y-m-d", $time - 86400);

require_once '../app/Mage.php';
Mage::app();
umask(0);
 
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');

$query = "
select 
sfo.created_at created_at,
sfo.customer_email,
sfo.increment_id order_no,
sfoi.sku,
sfoi.base_tax_refunded,
sfoi.product_type,
sfoi.qty_ordered,
sfoi.original_price,
sfoi.discount_amount order_discount,
sfoi.row_total order_subtotal,
sfi.increment_id invoice_no,
sfii.qty qty_invoiced,
sfoi.discount_invoiced invoiced_discount,
sfii.row_total invoiced_total,
sfop.method payment_method,
sfo.coupon_code,
sfoi.discount_percent,
sfo.status order_status,
sfo.shipping_amount,
sfoa.street,
sfoa.city,
sfoa.region,
sfoa.postcode,
sfo.shipping_description,
sfo.grand_total,
sfo.total_paid
#sfoh_ship.created_at date_shipped, 
#sfii.*
#sfoi.*
#sfo.*
from sales_flat_order_item sfoi 
left join sales_flat_order sfo on sfo.entity_id = sfoi.order_id
left join sales_flat_order_address sfoa on sfoa.parent_id = sfo.entity_id
left join sales_flat_order_payment sfop on sfop.parent_id = sfo.entity_id
left join sales_flat_invoice_item sfii on sfii.order_item_id = sfoi.item_id
left join sales_flat_invoice sfi on sfi.entity_id = sfii.parent_id
#inner join sales_flat_order_status_history sfoh_ship on sfo.entity_id = sfoh_ship.parent_id
where sfo.created_at >= '".$yesterday."' and sfo.created_at <= '".$today."' #and sfo.increment_id like 'CU400001439'
order by sfo.increment_id
limit 10000
";

$results = $readConnection->fetchAll($query);

$output = fopen('cudsly-dailysales-'.$today.'.csv', 'w');

fputcsv($output,array('created_at','customer_email','order_no','sku','base_tax_refunded','product_type','qty_ordered','original_price','order_discount','order_subtotal','invoice_no','qty_invoiced','invoiced_discount','invoiced_total','payment_method','coupon_code','discount_percent','order_status','shipping_amount','street','city','region','postcode','shipping_description','grand_total','total_paid'));

$rows = array();
foreach( $results as $result ){
    $values = array();
    
    $values[]           = $result['created_at'];
    $values[]           = $result['customer_email'];
    $values[]           = $result['order_no'];
    $values[]           = $result['sku'];
    $values[]           = $result['base_tax_refunded'];
    $values[]           = $result['product_type'];
    $values[]           = $result['qty_ordered'];
    $values[]           = $result['original_price'];
    $values[]           = $result['order_discount'];
    $values[]           = $result['order_subtotal'];
    $values[]           = $result['invoice_no'];
    $values[]           = $result['qty_invoiced'];
    $values[]           = $result['invoiced_discount'];
    $values[]           = $result['invoiced_total'];
    $values[]           = $result['payment_method'];
    $values[]           = $result['coupon_code'];
    $values[]           = $result['discount_percent'];
    $values[]           = $result['order_status'];
    $values[]           = $result['shipping_amount'];
    $values[]           = $result['street'];
    $values[]           = $result['city'];
    $values[]           = $result['region'];
    $values[]           = $result['postcode'];
    $values[]           = $result['shipping_description'];
    $values[]           = $result['grand_total'];
    $values[]           = $result['total_paid'];

    fputcsv($output, $values);
}
fclose($output);

try{
    $myfile = 'cudsly-dailysales-'.$today.'.csv';
    
    $message = "Click the link to download the Automatically Generated Daily Sales Report:".$url.$path.$myfile;
 
    @mail($email, "Cudsly Automated Daily Sales Report - ".$today, $message);
    
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>
