<?php

/*  @var $this Mage_Sales_Model_Mysql4_Setup  */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('customcatalog/product');
// Check if the table already exists
if ($installer->getConnection()->isTableExists($tableName) != true) {
    $table = $installer->getConnection()
        ->newTable($tableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            //'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Store Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            //'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Customer Id')
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            //'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Product Id')
        ->addColumn('part_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
            'nullable'  => true,
        ), 'Part Number')
        ->addColumn('custom_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array(
            'nullable'  => true,
        ), 'name')
        ->addColumn('notes', Varien_Db_Ddl_Table::TYPE_VARCHAR, 250, array(
            'nullable'  => true,
        ), 'Notes')
        ->setComment('MBE Custom Catalog records.');

    $installer->getConnection()->createTable($table);

    $sql = sprintf('
            create unique index unq_customcatalog_entity on %1$s (store_id, customer_id, product_id);
            create index idx_product on %1$s (product_id);
            create index idx_customer on %1$s (customer_id, store_id);', $tableName);

    $installer->run( $sql );
}

$tableName = $installer->getTable('customcatalog/customer');
// Check if the table already exists
if ($installer->getConnection()->isTableExists($tableName) != true) {
    $table = $installer->getConnection()
        ->newTable($tableName)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            //'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Store Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            //'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Customer Id')
        ->setComment('MBE Custom Catalog customers.');

    $installer->getConnection()->createTable($table);

    $sql = sprintf('create unique index unq_customcatalog_customer on %s (store_id, customer_id);', $tableName);

    $installer->run($sql);
}


/*

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('<module>')};
CREATE TABLE {$this->getTable('<module>')} (
  `<module>_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`<module>_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
*/
$installer->endSetup();
