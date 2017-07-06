<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Generate extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        if (!$this->_getData('store')) {
            $this->setStore(Mage::registry('fpc_store'));
        }
    }

    public function getGenerateUrl()
    {
        return $this->getUrl('*/*/generateUrl');
    }

    public function getUrls()
    {
        return Mage::helper('bubble_fpc')->getStoreUrls($this->getStore());
    }
}