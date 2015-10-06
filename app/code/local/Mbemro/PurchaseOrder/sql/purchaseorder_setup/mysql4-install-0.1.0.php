<?php


/*  @var $this Mage_Sales_Model_Mysql4_Setup  */
$this->startSetup();
$connection = $this->getConnection();

/**
 * Create the payment method dropdown field, because this field _may_ be
 * used for searching we will create an index for it.
 */

$connection->addColumn(
    $this->getTable('sales/order'),
    'purchase_order_number',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 30,
        'nullable' => true,
        'default' => null,
        'comment' => 'Purchase Order Number'
    )

);
$connection->addKey($this->getTable('sales/order'), 'purchase_order_number', 'purchase_order_number');

$this->endSetup();