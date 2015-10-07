<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:44 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Delete.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Observer_Model_Delete
	extends CpDevelopment_AdminMonitoring_Model_Observer_Model_Abstract
{
	/**
	 * Retrieve the current action id
	 *
	 * @return int Action ID
	 */
	protected function getAction()
	{
		return CpDevelopment_AdminMonitoring_Helper_Data::ACTION_DELETE;
	}
}

// End of Delete.php 