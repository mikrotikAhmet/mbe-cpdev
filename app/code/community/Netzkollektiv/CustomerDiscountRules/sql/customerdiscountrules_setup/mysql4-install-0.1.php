<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('customerdiscountrules/rule')};
CREATE TABLE {$this->getTable('customerdiscountrules/rule')} (
  `customer_id` int(10) NOT NULL default 0,
  `rule_id` int(10) NOT NULL default 0,
  `discount_amount` decimal(12,4) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

ALTER TABLE {$this->getTable('customerdiscountrules/rule')} ADD PRIMARY KEY (  `customer_id` ,  `rule_id` );
");
$installer->endSetup();
