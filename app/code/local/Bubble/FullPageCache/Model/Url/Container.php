<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Model_Url_Container extends ArrayObject
{
    public function __construct()
    {
        Mage::getSingleton('core/session')->setAutoGenerateUrls($this);
    }
}