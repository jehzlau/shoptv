<?php
/**
 * @author Ecommerce Approch Team
 * @copyright Copyright (c) 2015 Ecommerce Approch (http://www.ecommerceapproach.com/)
 * @package Ecommerceapp_Cashdelivery
 */
 
class Ecommerceapp_Cashdelivery_Model_System_Config_Source_Mode
{
    /**
     * Retrieve all options array
     *
     * @return array
    */
    public function toOptionArray()
    {
        $options = array(
			array(
				"label" => Mage::helper("cashdelivery")->__("Include Mode"),
				"value" =>  1
			),
			array(
				"label" => Mage::helper("cashdelivery")->__("Exclude Mode"),
				"value" =>  2
			)
        );
        return $options;
    }
}