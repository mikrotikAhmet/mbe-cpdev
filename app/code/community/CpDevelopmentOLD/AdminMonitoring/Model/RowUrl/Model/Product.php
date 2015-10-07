<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:53 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Product.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Product
	extends CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Abstract
{
	/**
	 * Retrieve the class name for the current implementation.
	 *
	 * @return string Class Name
	 */
	protected function _getClassName()
	{
		return 'Mage_Catalog_Model_Product';
	}

	/**
	 * Retrieve the route path for the current implementation
	 *
	 * @return string Route Path
	 */
	protected function _getRoutePath()
	{
		return 'adminhtml/catalog_product/edit';
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
			'id'    => $model->getId(),
			'store' => $model->getStoreId(),
		);
	}
}

// End of Product.php 