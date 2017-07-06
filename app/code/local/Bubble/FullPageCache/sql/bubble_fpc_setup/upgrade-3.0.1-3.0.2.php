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

$tableBlock = $installer->getTable('bubble_fpc_block'); // AJAX blocks
$tableCache = $installer->getTable(defined('FPC_DB_TABLE_NAME') ? FPC_DB_TABLE_NAME : 'bubble_fpc_cache');

$installer->run("
DROP TABLE IF EXISTS `{$tableBlock}`;
CREATE TABLE `{$tableBlock}` (
    `block_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='Full Page Cache AJAX Blocks';

DROP TABLE IF EXISTS `{$tableCache}`;
CREATE TABLE `{$tableCache}` (
    `cache_id` VARCHAR(255) NOT NULL PRIMARY KEY,
    `data` MEDIUMBLOB NOT NULL,
    `lifetime` INT(11) UNSIGNED
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='Full Page Cache Data';
");

$installer->endSetup();
