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
class AdjustWare_Cartalert_Model_System_Config_Source_Status
{
    public function toOptionArray()
    {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */
        
        $options = array();
        $optionsA = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach($optionsA as $k=>$v)
        {
            $options[] = array('label' => $v, 'value' => $k);
        }
       
        return $options;
    }
}