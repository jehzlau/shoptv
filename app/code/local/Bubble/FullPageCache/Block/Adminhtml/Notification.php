<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Template
{
    public function checkFpcFile()
    {
        return class_exists('Bubble_FPC', false);
    }

    public function checkFpcDir()
    {
        return defined('FPC_DIR') && is_dir_writeable(FPC_DIR);
    }

    public function getFpcDir()
    {
        return defined('FPC_DIR') ? FPC_DIR : Mage::getConfig()->getVarDir('fpc');
    }
}