<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 5:00 PM
 */
/**
 * @package     Semite DOO magento.com
 * @version     upgrade-1.0.0-1.0.1.php 9/9/15 Ahmet GOUDENOGLU
 * @copyright   Copyright (c) 2015 Semite DOO .
 * @license     http://www.semitedoo.com/license/
 */
/**
 * Description of upgrade-1.0.0-1.0.1.php
 *
 * @author Ahmet GOUDENOGLU
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addIndex(
	$installer->getTable('cpdevelopment_adminmonitoring/history'),
	$installer->getConnection()->getIndexName(
		$installer->getTable('cpdevelopment_adminmonitoring/history'),
		array(
			'object_type', 'object_id'
		),
		Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
	),
	array(
		'object_type', 'object_id'
	),
	Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

$installer->getConnection()->changeColumn(
	$installer->getTable('cpdevelopment_adminmonitoring/history'),
	'data',
	'content',
	array(
		'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
		'size'    => null,
		'comment' => 'data of changed entity'
	)
);

$installer->getConnection()->addColumn(
	$installer->getTable('cpdevelopment_adminmonitoring/history'),
	'content_diff',
	array(
		'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
		'size'    => null,
		'comment' => 'changed data of entity'
	)
);

$installer->endSetup();


// End of upgrade-1.0.0-1.0.1.php 