<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:47 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    History.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Resource_History
	extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 * Inits the main table and the id field name
	 */
	protected function _construct()
	{
		$this->_init('cpdevelopment_adminmonitoring/history', 'history_id');
	}
}

// End of History.php 