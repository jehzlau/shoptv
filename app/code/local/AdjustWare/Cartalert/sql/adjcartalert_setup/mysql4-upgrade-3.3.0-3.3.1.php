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
$installer = $this;
$fromDatePath = AdjustWare_Cartalert_Helper_Data::XML_PATH_FROM_DATE;
$orderFromDatePath = AdjustWare_Cartalert_Helper_Data::XML_PATH_ORDER_FROM_DATE;

$installer->startSetup();

$date = $installer->getConnection()->fetchOne("SELECT value FROM {$this->getTable('core/config_data')} WHERE `scope`='default' AND `path`='$fromDatePath' LIMIT 1");

$installer->run("
UPDATE {$this->getTable('core/config_data')} SET `value` = '$date' WHERE `scope`='default' AND `path`='$orderFromDatePath';
");

$installer->endSetup();