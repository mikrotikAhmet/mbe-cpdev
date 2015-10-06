<?php
	
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:52 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Page.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Page
	extends CpDevelopment_AdminMonitoring_Model_RowUrl_Model_Abstract
{
	/**
	 * Retrieve the class name for the current implementation.
	 *
	 * @return string Class Name
	 */
	protected function _getClassName()
	{
		return 'Mage_Cms_Model_Page';
	}

	/**
	 * Retrieve the route path for the current implementation
	 *
	 * @return string Route Path
	 */
	protected function _getRoutePath()
	{
		return 'adminhtml/cms_page/edit';
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
			'page_id' => $model->getId()
		);
	}
}

// End of Page.php 