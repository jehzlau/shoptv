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

$installer->startSetup();

$date = Mage::getStoreConfig('catalog/adjcartalert/from_date');

$installer->run("
ALTER TABLE {$this->getTable('adjcartalert')} ADD `quote_is_active` tinyint(1) unsigned NOT NULL default '1';
ALTER TABLE {$this->getTable('adjcartalert_history')} ADD `quote_is_active` tinyint(1) unsigned NOT NULL default '1';

INSERT INTO {$this->getTable('core/config_data')} (`scope` , `scope_id` , `path` , `value` )
    VALUES ('default', '0', 'catalog/adjcartalert/order_from_date', '$date');
");

$installer->endSetup();