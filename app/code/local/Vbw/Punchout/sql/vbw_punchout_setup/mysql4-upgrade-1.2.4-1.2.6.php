<?php

/** @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();

$table = $this->getTable('vbw_punchout/sales_quote_stash');

$connection = $installer->getConnection();
$response = $connection->fetchAll("describe ". $table);

$issuesFields = array ('quote_id','item_id','customer_id');

foreach ($response AS $field) {
    if (in_array($field['Field'],$issuesFields)) {
        Mage::log('Checking '. $field['Field'] .' data type : '. $field['Type'],null,'vbw_punchout.updating.log',true);
        if ($field['Type'] == 'smallint(5)') {
            Mage::log('Updating  '. $field['Field'] .'',null,'vbw_punchout.updating.log',true);
            $connection->query('ALTER TABLE '. $table .' MODIFY '. $field['Field'] .' int(10) unsigned');
        } else {
            Mage::log('Ok  '. $field['Field'] .'',null,'vbw_punchout.updating.log',true);
        }
    }
}

/*
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('vbw_punchout/sales_quote_stash')};
CREATE TABLE {$this->getTable('vbw_punchout/sales_quote_stash')} (
  `id` int(11) NOT NULL auto_increment,
  `quote_id` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `customer_id` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `request` text NOT NULL,
  `stash` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
*/

$installer->endSetup();

