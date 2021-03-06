<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Mobile3
 * @version    3.0.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


if (@class_exists('Mage_Checkout_Block_Cart_Item_Configure')) {
    class AW_Mobile3_Block_Checkout_Cart_Item_Configure_Parent extends Mage_Checkout_Block_Cart_Item_Configure {}
} else {
    class AW_Mobile3_Block_Checkout_Cart_Item_Configure_Parent extends Mage_Core_Block_Template {
        protected function _toHtml()
        {
            return '';
        }
    }
}
class AW_Mobile3_Block_Checkout_Cart_Item_Configure extends AW_Mobile3_Block_Checkout_Cart_Item_Configure_Parent {}