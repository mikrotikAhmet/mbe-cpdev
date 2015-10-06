<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('autoexport/autoexportrecords')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exported_orders` text DEFAULT '' NOT NULL,
  `exported_customers` text DEFAULT '' NOT NULL,
  `last_exported_order_id` int(11),
  `last_exported_customer_id` int(11),
  `exported_at` datetime NOT NULL,
  `passed` tinyint(3) DEFAULT '0' NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
");
  
$installer->endSetup();
	 