<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:37 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Data.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_History_Data
{
	/**
	 * @var Mage_Core_Model_Abstract
	 */
	protected $_savedModel;

	/**
	 * Init the saved model
	 *
	 * @param Mage_Core_Model_Abstract $savedModel Model which is to be saved
	 */
	public function __construct(Mage_Core_Model_Abstract $savedModel)
	{
		$this->_savedModel = $savedModel;
	}

	/**
	 * Retrieve the serialized content
	 *
	 * @return string Serialized Content
	 */
	public function getSerializedContent()
	{
		return json_encode($this->getContent());
	}

	/**
	 * Retrieve the content of the saved model
	 *
	 * @return array Content
	 */
	public function getContent()
	{
		// have to re-load the model as based on database datatypes the format of values changes
		$className = get_class($this->_savedModel);
		$model = new $className;

		// Add store id if given
		if ($storeId = $this->_savedModel->getStoreId()) {
			$model->setStoreId($storeId);
		}
		$model->load($this->_savedModel->getId());

		return $this->_filterObligatoryFields($model->getData());
	}

	/**
	 * Remove the obligatory fields from the data
	 *
	 * @param  array $data Data
	 * @return array Filtered Data
	 */
	protected function _filterObligatoryFields($data)
	{
		$fields = Mage::getSingleton('cpdevelopment_adminmonitoring/config')->getFieldExcludes();
		foreach ($fields as $field) {
			unset($data[$field]);
		}

		return $data;
	}

	/**
	 * Retrieve the original content of the saved model
	 *
	 * @return array Data
	 */
	public function getOrigContent()
	{
		$data = $this->_savedModel->getOrigData();

		return $this->_filterObligatoryFields($data);
	}

	/**
	 * Retrieve the object type of the saved model
	 *
	 * @return string Object Type
	 */
	public function getObjectType()
	{
		return get_class($this->_savedModel);
	}

	/**
	 * Retrieve the object id of the saved model
	 *
	 * @return int Object ID
	 */
	public function getObjectId()
	{
		return $this->_savedModel->getId();
	}
}

// End of Data.php 