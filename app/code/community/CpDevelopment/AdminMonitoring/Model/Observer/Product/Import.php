<?php
	
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:45 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Import.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Observer_Product_Import
	extends CpDevelopment_AdminMonitoring_Model_Observer_Log
{
	const XML_PATH_ADMINMONITORING_LOG_PRODUCT_IMPORT = 'admin/cpdevelopment_adminmonitoring/product_import_logging';

	/**
	 * Observe the product import before
	 *
	 * @param  Varien_Event_Observer $observer Observer Instance
	 * @return void
	 */
	public function catalogProductImportFinishBefore(Varien_Event_Observer $observer)
	{
		if (!Mage::getStoreConfigFlag(self::XML_PATH_ADMINMONITORING_LOG_PRODUCT_IMPORT)) {
			return;
		}

		$productIds = $observer->getEvent()->getAdapter()->getAffectedEntityIds();

		$objectType = get_class(Mage::getModel('catalog/product'));
		$content = json_encode(array('updated_during_import' => ''));
		$userAgent = $this->getUserAgent();
		$ip = $this->getRemoteAddr();
		$userId = $this->getUserId();
		$userName = $this->getUserName();

		foreach ($productIds as $productId) {
			/* @var CpDevelopment_AdminMonitoring_Model_History $history */
			$history = Mage::getModel('cpdevelopment_adminmonitoring/history');
			$history->setData(array(
				'object_id'    => $productId,
				'object_type'  => $objectType,
				'content'      => $content,
				'content_diff' => '{}',
				'user_agent'   => $userAgent,
				'ip'           => $ip,
				'user_id'      => $userId,
				'user_name'    => $userName,
				'action'       => CpDevelopment_AdminMonitoring_Helper_Data::ACTION_UPDATE,
				'created_at'   => now(),
			));
			$history->save();
			$history->clearInstance();
		}
	}
}

// End of Import.php 