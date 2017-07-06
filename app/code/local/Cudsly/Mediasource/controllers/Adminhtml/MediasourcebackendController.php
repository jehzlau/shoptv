<?php
class Cudsly_Mediasource_Adminhtml_MediasourcebackendController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('mediasource/mediasourcebackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Cudsly Media Source"));
	   $this->renderLayout();
    }
}