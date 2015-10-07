<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:05 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    History.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Block_Adminhtml_History
	extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	/**
	 * Constructor of the grid container
	 */
	public function __construct()
	{
		$this->_blockGroup = 'cpdevelopment_adminmonitoring';
		$this->_controller = 'adminhtml_history';
		$this->_headerText = Mage::helper('cpdevelopment_adminmonitoring')->__('History');
		parent::__construct();
		$this->removeButton('add');
	}
}

// End of History.php 