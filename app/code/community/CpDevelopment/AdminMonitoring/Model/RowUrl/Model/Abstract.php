<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:49 PM
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

abstract class CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Abstract
{
	/**
	 * Abstract method for retrieving the class name.
	 *
	 * @return string
	 */
	abstract protected function _getClassName();

	/**
	 * Abstract method for retrieving the route path.
	 *
	 * @return string
	 */
	abstract protected function _getRoutePath();

	/**
	 * Abstract method for retrieving the route params.
	 *
	 * @param  Mage_Core_Model_Abstract $model Object
	 * @return array
	 */
	abstract protected function _getRouteParams(Mage_Core_Model_Abstract $model);

	/**
	 * Sets the row url in the transport object for a cms_page model
	 *
	 * @param Varien_Event_Observer $observer Observer Instance
	 */
	public function setRowUrl(Varien_Event_Observer $observer)
	{
		/* @var $history CpDevelopment_AdminMonitoring_Model_History */
		$history = $observer->getHistory();
		$rowUrl = $this->_getRowUrl(
			$history,
			$this->_getClassName(),
			$this->_getRoutePath(),
			$this->_getRouteParams($history->getOriginalModel())
		);

		if ($rowUrl) {
			$observer->getTransport()->setRowUrl($rowUrl);
		}
	}

	/**
	 * Retrieve the row url with the given parameters.
	 *
	 * @param  CpDevelopment_AdminMonitoring_Model_History $history     History Model
	 * @param  string                                  $className   Class Name
	 * @param  string                                  $routePath   Route Path
	 * @param  array                                   $routeParams Route Params
	 * @return Mage_Adminhtml_Model_Url
	 */
	protected function _getRowUrl(CpDevelopment_AdminMonitoring_Model_History $history, $className, $routePath, $routeParams)
	{
		/* @var $history CpDevelopment_AdminMonitoring_Model_History */
		if (!$history->isDelete()) {
			$model = $history->getOriginalModel();
			if (is_a($model, $className) && $model->getId()) {
				return Mage::getModel('adminhtml/url')->getUrl($routePath, $routeParams);
			}
		}

		return false;
	}
}

// End of Abstracts.php 