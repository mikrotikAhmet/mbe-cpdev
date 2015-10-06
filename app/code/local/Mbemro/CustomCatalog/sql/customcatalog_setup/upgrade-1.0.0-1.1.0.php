<?php

/*  @var $this Mage_Sales_Model_Mysql4_Setup  */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('customcatalog/category');

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
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            //'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Customer Id')
        ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => false,
        ), 'Category Id')
        ->addColumn('apply_subcategories', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
            'nullable'  => false,
            'primary'   => false,
            'default'   => false,
        ), 'Product Id')
        ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12, 4), array(
            'nullable'  => true,
        ), 'Amount of discount')
        ->setComment('MBE Custom Catalog Categories.');

    $installer->getConnection()->createTable($table);

    $sql = sprintf('
            create unique index unq_customcatalog_entity on %1$s (customer_id, category_id);
            create index idx_category on %1$s (category_id);
            create index idx_customer on %1$s (customer_id);
        ', $tableName);

    $installer->run( $sql );

}

$installer->endSetup();