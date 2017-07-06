<?php
$installer = $this;
$installer->startSetup();
$block = Mage::getModel('cms/block');
$page = Mage::getModel('cms/page');
$stores = array(0);

# INSERT STATIC BLOCKS 

//1. Top popup block
$dataBlock = array(
	'title' => 'IWD Newsletter Popup Top',
	'identifier' => 'iwd_newsletter_top',
	'stores' => $stores,
	'is_active' => 1,
	'content'	=> <<<EOB
		<h1 class="title">SIGN UP TO KEEP IN TOUCH!</h1>
		<p>Subscribe for the latest deals.</p>
EOB
);
$block->setData($dataBlock)->save();

//2.Bottom popup block
$dataBlock = array(
	'title' => 'IWD Newsletter Popup Bottom',
	'identifier' => 'iwd_newsletter_bottom',
	'stores' => $stores,
	'is_active' => 1,
	'content'	=> <<<EOB
		<p class="subtitle">More ways to stay connected:</p>
		<p>Join us on facebook or twitter for more exciting special deals and offers!</p>
		<div class="social_links"><a href="#"><span class="fa-stack fa-lg"> <span class="fa fa-circle fa-stack-2x">&nbsp;</span> <span class="fa fa-twitter fa-stack-1x">&nbsp;</span> </span>Follow us on Twitter </a><a  href="#"><span class="fa-stack fa-lg"> <span class="fa fa-circle fa-stack-2x">&nbsp;</span> <span class="fa fa-facebook fa-stack-1x">&nbsp;</span> </span>Like us on Facebook</a><a href="#"><span class="fa-stack fa-lg"> <span class="fa fa-circle fa-stack-2x">&nbsp;</span> <span class="fa fa-google-plus fa-stack-1x">&nbsp;</span> </span>Follow us on Google+</a></div>
EOB
);
$block->setData($dataBlock)->save();

$installer->endSetup();
