<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('assignorder_history')};
CREATE TABLE {$this->getTable('assignorder_history')} (
 `assignorder_id` int(11) unsigned NOT NULL auto_increment,
 `order_id` int(11) NOT NULL default '0',
 `customer_id` int(11) NOT NULL default '0',
 `created_at` datetime NULL,
 `order_customer_firstname` varchar(255) NOT NULL default '',
 `order_customer_lastname` varchar(255) NOT NULL default '',
 `order_customer_email` varchar(255) NOT NULL default '',
 `order_customer_group_id` int(11) NOT NULL default '0',
 `notify_customer` int(11) NOT NULL default '0',
  PRIMARY KEY (`assignorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"); 
$installer->endSetup();