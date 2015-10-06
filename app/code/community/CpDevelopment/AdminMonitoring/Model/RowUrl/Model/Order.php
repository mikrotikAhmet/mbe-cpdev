<?php
	
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:51 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Order.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Order
	extends CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Abstract
{
	/**
	 * Retrieve the class name for the current implementation.
	 *
	 * @return string Class Name
	 */
	protected function _getClassName()
	{
		return 'Mage_Sales_Model_Order';
	}

	/**
	 * Retrieve the route path for the current implementation
	 *
	 * @return string Route Path
	 */
	protected function _getRoutePath()
	{
		return 'adminhtml/sales_order/view';
	}

	/**
	 * Retrieve the route params for the current implementation and given model
	 *
	 * @param  Mage_Core_Model_Abstract $model Model
	 * @return array Route Params
	 */
	protected function _getRouteParams(Mage_Core_Model_Abstract $model)
	{
		return array(
			'order_id' => $model->getId()
		);
	}
}

// End of Order.php 