<?php
	
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:46 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Update.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Observer_Product_Attribute_Update
	extends CpDevelopment_AdminMonitoring_Model_Observer_Log
{
	const XML_PATH_ADMINMONITORING_PROD_ATTR_UPDATE = 'admin/cpdevelopment_adminmonitoring/product_mass_update_logging';

	/**
	 * Observe the catalog product attribute update before
	 *
	 * @param  Varien_Event_Observer $observer Observer Instance
	 * @return void
	 */
	public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $observer)
	{
		if (!Mage::getStoreConfigFlag(self::XML_PATH_ADMINMONITORING_PROD_ATTR_UPDATE)) {
			return;
		}

		$objectType = get_class(Mage::getModel('catalog/product'));
		$content = json_encode($observer->getEvent()->getAttributesData());
		$userAgent = $this->getUserAgent();
		$ip = $this->getRemoteAddr();
		$userId = $this->getUserId();
		$userName = $this->getUserName();

		foreach ($observer->getEvent()->getProductIds() as $productId) {
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

// End of Update.php 