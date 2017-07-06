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
class AdjustWare_Cartalert_Model_Cartalert_Cart extends AdjustWare_Cartalert_Model_Cartalert
{
    const CARTALERT_INSTANCE_TYPE = 'cart';

    public function getResource()
    {
        return Mage::getResourceSingleton('adjcartalert/cartalert_cart');
    }
}