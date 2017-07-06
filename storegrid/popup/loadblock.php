<?php
require_once('../../app/Mage.php'); 
umask(0);
Mage::app();

$_SERVER['REQUEST_URI_PATH'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', $_SERVER['REQUEST_URI_PATH']);

$block_identifier = "popup_".$segments[4];
if(Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($block_identifier)->toHtml()  !== '' ){
    $staticBlock = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($block_identifier);
}else{
    $staticBlock = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId("popup_default");
}

?>

<div class="ajax-text-and-image white-popup-block">
<?php echo $staticBlock->toHtml(); ?>
</div>