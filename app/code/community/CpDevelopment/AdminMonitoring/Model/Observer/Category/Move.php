<?php
	
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:40 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Move.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Observer_Category_Move
	extends CpDevelopment_AdminMonitoring_Model_Observer_Log
{

	/**
	 * Observe the category move
	 *
	 * @param  Varien_Event_Observer $observer Observer Instance
	 * @return void
	 */
	public function catalogCategoryMove(Varien_Event_Observer $observer)
	{
		$category = $observer->getCategory();

		$dataModel = Mage::getModel('cpdevelopment_adminmonitoring/history_data', $category);
		$diffModel = Mage::getModel('cpdevelopment_adminmonitoring/history_diff', $dataModel);

		$eventData = array(
			'object_id' => $dataModel->getObjectId(),
			'object_type' => $dataModel->getObjectType(),
			'content' => $dataModel->getSerializedContent(),
			'content_diff' => $diffModel->getSerializedDiff(),
			'action' => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_UPDATE,
		);

		Mage::dispatchEvent('cpdevelopment_adminmonitoring_log', $eventData);
	}
}

// End of Move.php 