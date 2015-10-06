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
 * @version    Save.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Observer_Model_Save
	extends CpDevelopment_AdminMonitoring_Model_Observer_Model_Abstract
{
	/**
	 * @var string Object Hash
	 */
	protected $_currentHash;

	/**
	 * @var array
	 */
	protected $_beforeIds = array();

	/**
	 * Handle the model_save_after event.
	 *
	 * @param Varien_Event_Observer $observer Observer Instance
	 */
	public function modelAfter(Varien_Event_Observer $observer)
	{
		$this->setCurrentHash($observer->getObject());
		parent::modelAfter($observer);
	}

	/**
	 * Set the current hash of the given model.
	 *
	 * @param Mage_Core_Model_Abstract $model Object
	 */
	private function setCurrentHash(Mage_Core_Model_Abstract $model)
	{
		$this->_currentHash = $this->getObjectHash($model);
	}

	/**
	 * Retrieve the object hash for the given model.
	 *
	 * @param  object $object Object to hash
	 * @return string Hashed object
	 */
	private function getObjectHash($object)
	{
		return spl_object_hash($object);
	}

	/**
	 * Check if data has changed.
	 *
	 * @return bool Result
	 */
	protected function hasChanged()
	{
		return (!$this->isUpdate() || parent::hasChanged());
	}

	/**
	 * Check if the current action is an update.
	 *
	 * @return bool
	 */
	private function isUpdate()
	{
		return $this->getAction() == CpDevelopment_AdminMonitoring_Helper_Data::ACTION_UPDATE;
	}

	/**
	 * Handle the model_save_before event.
	 *
	 * @param Varien_Event_Observer $observer Observer Instance
	 */
	public function modelBefore(Varien_Event_Observer $observer)
	{
		/* @var $savedObject Mage_Core_Model_Abstract */
		$savedObject = $observer->getObject();
		$this->setCurrentHash($savedObject);
		$this->storeBeforeId($savedObject->getId());
	}

	/**
	 * Store the before id for the current hash.
	 *
	 * @param int $id Object ID
	 */
	private function storeBeforeId($id)
	{
		$this->_beforeIds[$this->_currentHash] = $id;
	}

	/**
	 * Retrieve the current monitoring action
	 *
	 * @return int Action ID
	 */
	protected function getAction()
	{
		if ($this->hadIdAtBefore() // for models which call model_save_before
			|| $this->hasOrigData() // for models with origData but without model_save_before like stock item
		) {
			return CpDevelopment_AdminMonitoring_Helper_Data::ACTION_UPDATE;
		} else {
			return CpDevelopment_AdminMonitoring_Helper_Data::ACTION_INSERT;
		}
	}

	/**
	 * Check if the id was there before.
	 *
	 * @return bool Result
	 */
	private function hadIdAtBefore()
	{
		return (isset($this->_beforeIds[$this->_currentHash]) && $this->_beforeIds[$this->_currentHash]);
	}

	/**
	 * Check if the saved model has original data.
	 *
	 * @return bool Result
	 */
	private function hasOrigData()
	{
		$data = $this->_savedModel->getOrigData();

		// unset website_ids as this is even on new entities set for catalog_product models
		unset($data['website_ids']);

		return (bool)$data;
	}
}

// End of Save.php 