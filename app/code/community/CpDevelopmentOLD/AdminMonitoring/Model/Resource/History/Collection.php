<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:48 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Collection.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Resource_History_Collection
	extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	/**
	 * Inits the model and resource model for the current collection
	 */
	protected function _construct()
	{
		$this->_init('cpdevelopment_adminmonitoring/history');
	}
}

// End of Collection.php 