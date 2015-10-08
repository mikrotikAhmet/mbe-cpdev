<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:54 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    SourceAbstract.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

abstract class CpDevelopment_AdminMonitoring_Model_System_Config_Source_SourceAbstract
{
	/**
	 * @var array
	 */
	protected $_options = null;

	/**
	 * Retrieve the option array
	 *
	 * @param  bool $withEmpty Flag if empty value should be added
	 * @return array
	 */
	abstract public function toOptionArray($withEmpty = true);

	/**
	 * Retrieve the option hash
	 *
	 * @param  bool $withEmpty Flag if empty value should be added
	 * @return array
	 */
	public function toOptionHash($withEmpty = true)
	{
		$options = $this->toOptionArray($withEmpty);
		$optionHash = array();

		foreach ($options as $option) {
			$optionHash[$option['value']] = $option['label'];
		}

		return $optionHash;
	}

	/**
	 * Retrieve the helper instance
	 *
	 * @return CpDevelopment_AdminMonitoring_Helper_Data
	 */
	protected function _getHelper()
	{
		return Mage::helper('cpdevelopment_adminmonitoring');
	}
}

// End of SourceAbstract.php 