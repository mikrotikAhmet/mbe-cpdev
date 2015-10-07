<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:36 PM
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

class CpDevelopment_AdminMonitoring_Model_History extends Mage_Core_Model_Abstract
{
	/**
	 * @var bool
	 */
	protected $_forcedLogging = false;

	/**
	 * Inits the resource model and resource collection model
	 */
	protected function _construct()
	{
		$this->_init('cpdevelopment_adminmonitoring/history');
	}

	/**
	 * Processing object before save data
	 *
	 * @return CpDevelopment_AdminMonitoring_Model_History
	 */
	protected function _beforeSave()
	{
		if (Mage::helper('cpdevelopment_adminmonitoring')->isAdminUserIdExcluded($this->getData('user_id'))
			&& !$this->_forcedLogging
		) {
			$this->_dataSaveAllowed = false;
		}

		return parent::_beforeSave();
	}

	/**
	 * Set the forced logging value
	 *
	 * @param  bool $flag Flag
	 * @return CpDevelopment_AdminMonitoring_Model_History
	 */
	public function setForcedLogging($flag)
	{
		$this->_forcedLogging = $flag;

		return $this;
	}

	/**
	 * Retrieve the original model
	 *
	 * @return Mage_Core_Model_Abstract
	 */
	public function getOriginalModel()
	{
		$objectType = $this->getObjectType();

		/* @var Mage_Core_Model_Abstract $model */
		$model = new $objectType;
		$content = $this->getDecodedContent();
		if (isset($content['store_id'])) {
			$model->setStoreId($content['store_id']);
		}
		$model->load($this->getObjectId());

		return $model;
	}

	/**
	 * Retrieve the decoded content diff
	 *
	 * @return array Decoded Content Diff
	 */
	public function getDecodedContentDiff()
	{
		return json_decode($this->getContentDiff(), true);
	}

	/**
	 * Retrieve the decoded content
	 *
	 * @return array Decoded Content
	 */
	public function getDecodedContent()
	{
		return json_decode($this->getContent(), true);
	}

	/**
	 * Check if the history action is an update action.
	 *
	 * @return bool Result
	 */
	public function isInsert()
	{
		return ($this->getAction() == CpDevelopment_AdminMonitoring_Helper_Data::ACTION_INSERT);
	}

	/**
	 * Check if the history action is an update action.
	 *
	 * @return bool Result
	 */
	public function isUpdate()
	{
		return ($this->getAction() == CpDevelopment_AdminMonitoring_Helper_Data::ACTION_UPDATE);
	}

	/**
	 * Check if the history action is an delete action.
	 *
	 * @return bool
	 */
	public function isDelete()
	{
		return ($this->getAction() == CpDevelopment_AdminMonitoring_Helper_Data::ACTION_DELETE);
	}

	/**
	 * Check if the history action is an login action
	 *
	 * @return bool
	 */
	public function isLogin()
	{
		return ($this->getAction() == CpDevelopment_AdminMonitoring_Helper_Data::ACTION_LOGIN);
	}

	/**
	 * Check if the history action is an logout action
	 *
	 * @return bool
	 */
	public function isLogout()
	{
		return ($this->getAction() == CpDevelopment_AdminMonitoring_Helper_Data::ACTION_LOGOUT);
	}
}

// End of History.php 