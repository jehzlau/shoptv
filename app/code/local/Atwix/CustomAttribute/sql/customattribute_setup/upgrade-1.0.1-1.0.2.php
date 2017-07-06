<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Atwix
 * @package     Atwix_CustomAttribute
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'brand', $options);
    $installer->addAttribute($entity, 'cudsly_category', $options);
    $installer->addAttribute($entity, 'cudsly_subcategory', $options);
    $installer->addAttribute($entity, 'with_expiry', $options);
    $installer->addAttribute($entity, 'expiration_date', $options);
    $installer->addAttribute($entity, 'actual_delivery_date', $options);
    $installer->addAttribute($entity, 'ship_type', $options);
    $installer->addAttribute($entity, 'cudsly_category', $options);
    $installer->addAttribute($entity, 'cudsly_subcategory', $options);
    $installer->addAttribute($entity, 'inventory_type', $options);
    $installer->addAttribute($entity, 'consignment', $options);
    $installer->addAttribute($entity, 'consignment_date', $options);
    $installer->addAttribute($entity, 'margin', $options);
    $installer->addAttribute($entity, 'margin_notes', $options);
    $installer->addAttribute($entity, 'trade_margin_usable', $options);
    $installer->addAttribute($entity, 'supplier', $options);
    $installer->addAttribute($entity, 'for_correction', $options);
    $installer->addAttribute($entity, 'notes', $options);
    $installer->addAttribute($entity, 'live_date', $options);
    $installer->addAttribute($entity, 'photoshoot_date', $options);
    $installer->addAttribute($entity, 'product_content_complete', $options);
    $installer->addAttribute($entity, 'received_qty', $options);
    $installer->addAttribute($entity, 'unit_cost', $options);
    $installer->addAttribute($entity, 'unit_srp', $options);
    $installer->addAttribute($entity, 'po_qty', $options);
    $installer->addAttribute($entity, 'po_number', $options);
    $installer->addAttribute($entity, 'payment_terms', $options);
    $installer->addAttribute($entity, 'special_arrangement', $options);
    $installer->addAttribute($entity, 'contract_email_renewal_date', $options);
    $installer->addAttribute($entity, 'renewal_contract_sign_date', $options);
    $installer->addAttribute($entity, 'company', $options);
    $installer->addAttribute($entity, 'contact_person', $options);
    $installer->addAttribute($entity, 'contact_person_email', $options);
    $installer->addAttribute($entity, 'business_address', $options);
    $installer->addAttribute($entity, 'remarks', $options);
}
$installer->endSetup();