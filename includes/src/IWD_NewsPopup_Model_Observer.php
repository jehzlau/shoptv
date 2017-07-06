<?php
class IWD_NewsPopup_Model_Observer
{
	
	public function checkRequiredModules($observer){
		$cache = Mage::app()->getCache();
		if (Mage::getSingleton('admin/session')->isLoggedIn()) {
			if (!Mage::getConfig()->getModuleConfig('IWD_All')->is('active', 'true')){
				if ($cache->load("iwdpopup")===false){
					$message = 'Important: Please setup IWD_ALL in order to finish <strong>IWD Nesletter Pop-up</strong>  installation.<br />
Please download <a href="http://iwdextensions.com/media/modules/iwd_all.tgz" target="_blank">IWD_ALL</a> and setup it via Magento Connect.<br />
Please refer to <a href="https://docs.google.com/document/d/1k3_KuiHFoWX_T4F1yhJSlO2nKHD_03c3--u3L0PwTKw/edit?usp=sharing" target="_blank">installation guide</a>';
					Mage::getSingleton('adminhtml/session')->addNotice($message);
					$cache->save('true', 'iwdpopup', array("iwdpopup"), $lifeTime=5);
				}
			}
		}
	}
}