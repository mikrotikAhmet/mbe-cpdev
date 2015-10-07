<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:09 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Link.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Block_Adminhtml_History_Grid_Link
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
	 * Renders the given column
	 *
	 * @param  Varien_Object $row Column Object
	 * @throws Exception
	 * @return string Rendered column
	 */
	public function render(Varien_Object $row)
	{
		if ($row instanceof CpDevelopment_AdminMonitoring_Model_History) {
			/* @var $helper CpDevelopment_AdminMonitoring_Helper_Data */
			$helper = Mage::helper('cpdevelopment_adminmonitoring');

			$link = $helper->getRowUrl($row);
			if ($link) {
				return sprintf('<a href="%s">%s</a>', $link, $helper->__('Go To Object'));
			} else {
				return '-';
			}
		} else {
			throw new Exception('Block is only compatible to CpDevelopment_AdminMonitoring_Model_History');
		}
	}
}

// End of Link.php 