<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:43 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Abstracts.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

abstract class CpDevelopment_AdminMonitoring_Model_Observer_Model_Abstract
{
	/**
	 * @var Mage_Core_Model_Abstract
	 */
	protected $_savedModel;

	/**
	 * @var CpDevelopment_AdminMonitoring_Model_History_Diff
	 */
	protected $_diffModel;

	/**
	 * @var CpDevelopment_AdminMonitoring_Model_History_Data
	 */
	protected $_dataModel;

	/**
	 * Abstract method for retrieving the history action.
	 *
	 * @return int
	 */
	abstract protected function getAction();

	/**
	 * Handle the model_save_after and model_delete_after events
	 *
	 * @param Varien_Event_Observer $observer Observer Instance
	 */
	public function modelAfter(Varien_Event_Observer $observer)
	{
		$this->storeByObserver($observer);
	}

	/**
	 * Check if the data has changed.
	 *
	 * @return bool
	 */
	protected function hasChanged()
	{
		return $this->_diffModel->hasChanged();
	}

	/**
	 * Check if the data has changed and create a history entry if there are changes.
	 *
	 * @param Varien_Event_Observer $observer Observer Instance
	 */
	protected function storeByObserver(Varien_Event_Observer $observer)
	{
		/* @var $savedModel Mage_Core_Model_Abstract */
		$savedModel = $observer->getObject();
		$this->_savedModel = $savedModel;

		if (!$this->isExcludedClass($savedModel)) {
			$this->_dataModel = Mage::getModel('cpdevelopment_adminmonitoring/history_data', $savedModel);
			$this->_diffModel = Mage::getModel('cpdevelopment_adminmonitoring/history_diff', $this->_dataModel);

			if ($this->hasChanged()) {
				$this->createHistoryForModelAction();
			}
		}
	}

	/**
	 * Dispatch event for creating a history entry
	 */
	private function createHistoryForModelAction()
	{
		$eventData = array(
			'object_id'    => $this->_dataModel->getObjectId(),
			'object_type'  => $this->_dataModel->getObjectType(),
			'content'      => $this->_dataModel->getSerializedContent(),
			'content_diff' => $this->_diffModel->getSerializedDiff(),
			'action'       => $this->getAction(),
		);

		Mage::dispatchEvent('cpdevelopment_adminmonitoring_log', $eventData);
	}

	/**
	 * Check if the dispatched model has to be excluded from the logging.
	 *
	 * @return bool Result
	 */
	private function isExcludedClass()
	{
		$savedModel = $this->_savedModel;

		$fullActionName = Mage::helper('cpdevelopment_adminmonitoring')->getFullActionName();

		// Check if full action name is restricted
		$globalAdminRouteExcludes = $this->getConfig()->getGlobalAdminRouteExcludes();
		if (in_array($fullActionName, $globalAdminRouteExcludes)) {
			return true;
		}

		// Fetch all object type excludes

		$objectTypeExcludes = $this->getConfig()->getObjectTypeExcludes();

		// Add all object type excludes from the partial admin route excludes
		$partialAdminRouteExcludes = $this->getConfig()->getPartialAdminRouteExcludes();
		if (isset($partialAdminRouteExcludes[$fullActionName])) {
			$objectTypeExcludes = array_merge($objectTypeExcludes, $partialAdminRouteExcludes[$fullActionName]);
		}

		$objectTypeExcludesFiltered = array_filter(
			$objectTypeExcludes,
			function ($className) use ($savedModel) {
//                if (is_a($savedModel, $className)){
//                    return is_a($savedModel, $className);
//                } else {
//                    return false;
//                }
                echo '<pre>';
//                print_r($savedModel);
                print_r($className);
//                print_r(is_a($savedModel, $className));
			}
		);

        echo '<pre>';
        print_r($objectTypeExcludes);
        die();

		return (count($objectTypeExcludesFiltered) > 0);

	}

	/**
	 * @return CpDevelopment_AdminMonitoring_Model_Config
	 */
	public function getConfig()
	{
		return Mage::getSingleton('cpdevelopment_adminmonitoring/config');
	}
}

// End of Abstracts.php 