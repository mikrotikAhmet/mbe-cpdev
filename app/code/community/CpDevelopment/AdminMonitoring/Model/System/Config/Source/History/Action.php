<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:55 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Action.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_System_Config_Source_History_Action
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
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_INSERT,
					'label' => 'INSERT',
				),
				array(
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_UPDATE,
					'label' => 'UPDATE',
				),
				array(
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_DELETE,
					'label' => 'DELETE',
				),
				array(
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_LOGIN,
					'label' => 'LOGIN',
				),
				array(
					'value' => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_LOGOUT,
					'label' => 'LOGOUT',
				)
			);
		}

		return $this->_options;
	}
}

// End of Action.php 