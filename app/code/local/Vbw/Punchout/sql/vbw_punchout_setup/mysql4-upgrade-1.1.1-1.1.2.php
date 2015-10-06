<?php

/** @var $installer Mage_Tax_Model_Resource_Setup */
$installer = $this;

/**
 * Create table 'vbw_punchout/sales_quote_stash'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('vbw_punchout/sales_quote_stash'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'nullable'  => false,
    'primary'   => true,
        ), 'ID')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Quote ID')
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Item ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Customer ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Store ID')
    ->addColumn('request', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        ), 'Request')
    ->addColumn('stash', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        ), 'Stashed Data')
;
$installer->getConnection()->createTable($table);

