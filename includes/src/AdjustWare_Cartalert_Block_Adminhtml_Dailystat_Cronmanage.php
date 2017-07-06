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
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat_Cronmanage extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adjcartalert/cronmanage.phtml'); 
    }
    
    public function getIsBusy()
    {   
        return Mage::getModel('adjcartalert/cronstat')->isBusy();
    }
   
}