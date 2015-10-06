<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 5:01 PM
 */
/**
 * @package     Semite DOO magento.com
 * @version     upgrade-1.0.2-1.0.3.php 9/9/15 Ahmet GOUDENOGLU
 * @copyright   Copyright (c) 2015 Semite DOO .
 * @license     http://www.semitedoo.com/license/
 */
/**
 * Description of upgrade-1.0.2-1.0.3.php
 *
 * @author Ahmet GOUDENOGLU
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('cpdevelopment_adminmonitoring/history'),
	'status',
	array(
		'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'size'    => 1,
		'default' => 1,
		'comment' => 'Status'
	)
);

$installer->endSetup();


// End of upgrade-1.0.2-1.0.3.php 