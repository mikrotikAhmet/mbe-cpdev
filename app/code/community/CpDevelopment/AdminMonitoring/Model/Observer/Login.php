<?php


/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:39 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Login.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Observer_Login
	extends CpDevelopment_AdminMonitoring_Model_Observer_Log
{
	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function logSuccess(Varien_Event_Observer $observer)
	{
		/* @var $user Mage_Admin_Model_User */
		$user = $observer->getEvent()->getUser();

		$this->_saveLoginHistory($user);
	}

	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function logFailure(Varien_Event_Observer $observer)
	{
		/* @var $user Mage_Admin_Model_User */
		$username = $observer->getEvent()->getUserName();
		/* @var $exception Exception */
		$exception = $observer->getEvent()->getException();

		/* @var $user Mage_Admin_Model_User */
		$user = Mage::getModel('admin/user')->loadByUsername($username);
		if (!$user->getId()) {
			return;
		}

		$this->_saveLoginHistory($user, true, $exception->getMessage());
	}

	/**
	 * Save the login history item for the given user
	 *
	 * @param  Mage_Admin_Model_User $user    User
	 * @param  string                $message Message
	 * @throws Exception
	 */
	protected function _saveLoginHistory($user, $failure = false, $message = '')
	{
		/* @var $history CpDevelopment_AdminMonitoring_Model_History */
		$history = Mage::getModel('cpdevelopment_adminmonitoring/history');
		$history->setForcedLogging(true);
		$history->setData(array(
			'object_id'   => $user->getId(),
			'object_type' => get_class($user),
			'user_agent'  => $this->getUserAgent(),
			'ip'          => $this->getRemoteAddr(),
			'user_id'     => $user->getId(),
			'user_name'   => $user->getUsername(),
			'action'      => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_LOGIN,
			'created_at'  => now(),
		));

		// Add some error information when login failed
		if ($failure) {
			$history->setData('status', CpDevelopment_AdminMonitoring_Helper_Data::STATUS_FAILURE);
			$history->setData('history_message', $message);
		}

		$history->save();
	}
}

// End of Login.php 