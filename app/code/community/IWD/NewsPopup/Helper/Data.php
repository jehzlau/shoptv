<?php

class IWD_NewsPopup_Helper_Data extends IWD_NewsPopup_Helper_Mobiledetects {
	
	public function getPopupWidth()
	{
		$width = trim(Mage::getStoreConfig('iwdpopup/general/newswidth', Mage::app()->getStore()));
		if($width!='')
			return $width;
		return 640;
	}
	
	public function getPopupTopMargin()
	{
		$top_margin = trim(Mage::getStoreConfig('iwdpopup/general/newstopspace', Mage::app()->getStore()));
	
		if($top_margin!='')
			return $top_margin;
		return 40;
	}
	
	public function isMobileStyle()
	{
		$width = trim(Mage::getStoreConfig('iwdpopup/general/newswidth', Mage::app()->getStore()));
		if($width<=380)
			return true;
		return false;
	}
	
	public function getPopupCss()
	{
		$button_bg = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsbuttonbg', Mage::app()->getStore())), '#');
		$button_hover = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsbuttonhover', Mage::app()->getStore())), '#');
		$button_text = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsbuttontext', Mage::app()->getStore())), '#');
		$icon_bg = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsiconbg', Mage::app()->getStore())), '#');
		$icon_hover = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsiconhover', Mage::app()->getStore())), '#');
		$icon_text = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsicontext', Mage::app()->getStore())), '#');
		$icon_grid = trim(trim(Mage::getStoreConfig('iwdpopup/general/newsicongrid', Mage::app()->getStore())), '#');
		
		
		$html = '<style type = "text/css">';
		$html.="#popup-newssubscribe #newsletter_submit{background:#".$button_bg." !important}";
		$html.="#popup-newssubscribe #newsletter_submit:hover{background:#".$button_hover." !important}";
		$html.="#popup-newssubscribe #newsletter_submit span{color:#".$button_text." !important}";
		$html.="#popup-newssubscribe .news_popup .social_links a span.fa{color:#".$icon_text." !important}";
		$html.="#popup-newssubscribe .news_popup .social_links a span.fa-circle{color:#".$icon_bg." !important}";
		$html.="#popup-newssubscribe .news_popup .social_links a:hover span.fa-circle{color:#".$icon_hover." !important}";
		
		switch ($icon_grid) {
			case "2":
				$html.="#popup-newssubscribe .news_popup .social_links a {width:50% !important}";
				$html.="#popup-newssubscribe .news_popup .social_links a:nth-child(2n+1){clear:left}";
				
				break;
			case "3":
				$html.="#popup-newssubscribe .news_popup .social_links a {width:33% !important}";
				$html.="#popup-newssubscribe .news_popup .social_links a:nth-child(3n+1){clear:left}";
				break;
			case "4":
				$html.="#popup-newssubscribe .news_popup .social_links a {width:25% !important}";
				$html.="#popup-newssubscribe .news_popup .social_links a:nth-child(4n+1){clear:left}";
				break;
			case "5":
				$html.="#popup-newssubscribe .news_popup .social_links a {width:20% !important}";
				$html.="#popup-newssubscribe .news_popup .social_links a:nth-child(5n+1){clear:left}";
				break;
			case "6":
				$html.="#popup-newssubscribe .news_popup .social_links a {width:16% !important}";
				$html.="#popup-newssubscribe .news_popup .social_links a:nth-child(6n+1){clear:left}";
				break;
			case "auto":
				$html.="#popup-newssubscribe .news_popup .social_links a {width:auto !important}";
				break;
		}
		
		$html.="</style>";
		
		return $html;
	}
	
	
}

