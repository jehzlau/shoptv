<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
/**
 * @var $this Mage_Eav_Model_Entity_Setup
 */
$installer = $this;
$installer->startSetup();

Mage::getConfig()->reinit(); // needed to access config data in helper methods
Mage::app()->reinitStores(); // needed to retrieve store list in helper methods

// Create fpc dir
if (defined('FPC_DIR') && !file_exists(FPC_DIR)) {
    @mkdir(FPC_DIR);
}

// Create default fpc dir anyway
@mkdir(Mage::getConfig()->getVarDir('fpc'), 0777);

/**
 * @var $helper Bubble_FullPageCache_Helper_Data
 */
$helper = Mage::helper('bubble_fpc');
$helper->disableCache();
$helper->saveGeneralSettings();
$helper->saveStorageSettings();
$helper->saveDesignExceptions();

$installer->endSetup();
