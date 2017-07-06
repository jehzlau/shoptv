<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.6.2
 * @license:     HURXKDG7XXFyzvUh6YIaOzBxXgSru0OEdm0JvUmsP0
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
Class AdjustWare_Cartalert_Controller_Adminhtml_Abstract extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
             parent::preDispatch();
             if(Mage::helper('core')->isModuleEnabled('Aitoc_Common')) {
                 Mage::getSingleton('aitoc_common/cron')->validateCronDate()->showError();
             }
    }
}