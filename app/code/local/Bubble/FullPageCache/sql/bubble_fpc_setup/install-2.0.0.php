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

$tableBlock = $installer->getTable('bubble_fpc_block'); // Remove old table if v1 was installed
$tableAction = $installer->getTable('bubble_fpc_action');

$installer->run("
    DROP TABLE IF EXISTS `{$tableBlock}`;
    DROP TABLE IF EXISTS `{$tableAction}`;
    CREATE TABLE `{$tableAction}` (
        `action_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
        `is_deletable` TINYINT(1) UNSIGNED DEFAULT NULL DEFAULT '1'
    ) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='Full Page Cache Actions';

    INSERT INTO `{$tableAction}` (`name`, `is_deletable`) VALUES
        ('cms/index/index', 0),
        ('cms/page/view', 0),
        ('catalog/product/view', 0),
        ('catalog/category/view', 0),
        ('catalogsearch/result/index', 0),
        ('catalogsearch/advanced/index', 0),
        ('catalogsearch/term/popular', 0),
        ('catalog/seo_sitemap/category', 0),
        ('catalog/seo_sitemap/product', 0);
");

$installer->endSetup();
