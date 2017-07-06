<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Generate_Auto extends Mage_Adminhtml_Block_Template
{
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('bubble_fpc/general/enable_auto_generation');
    }

    public function getGenerateUrl()
    {
        return $this->getUrl('*/fpc/generateUrl');
    }

    public function getUrls()
    {
        $urls = Mage::getSingleton('core/session')->getAutoGenerateUrls();
        if ($urls) {
            $urls = array_values((array) $urls);
        }

        return $urls;
    }
}