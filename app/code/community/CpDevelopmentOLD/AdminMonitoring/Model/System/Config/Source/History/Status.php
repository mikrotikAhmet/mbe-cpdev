<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:56 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Status.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_System_Config_Source_History_Status
	extends CpDevelopment_AdminMonitoring_Model_System_Config_Source_SourceAbstract
{
	/**
	 * Retrieve the option array
	 *
	 * @param  bool $withEmpty Flag if empty value should be added
	 * @return array
	 */
	public function toOptionArray($withEmpty = true)
	{
		if (null === $this->_options) {
			$this->_options = array(
				array(
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::STATUS_SUCCESS,
					'label' => $this->_getHelper()->__('Success'),
				),
				array(
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::STATUS_FAILURE,
					'label' => $this->_getHelper()->__('Failure'),
				)
			);
		}

		return $this->_options;
	}
}

// End of Status.php 