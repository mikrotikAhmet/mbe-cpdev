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
 * @version    User.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_System_Config_Source_Admin_User
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
			$userCollection = Mage::getModel('admin/user')->getCollection();

			foreach ($userCollection as $user) {
				$this->_options[] = array(
					'value' => $user->getData('user_id'),
					'label' => $user->getData('username'),
				);
			}

			if ($withEmpty) {
				array_unshift($this->_options, array(
					'value' => '',
					'label' => $this->_getHelper()->__('No admin user')
				));
			}
		}

		return $this->_options;
	}
}

// End of User.php 