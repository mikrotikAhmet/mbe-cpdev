<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:08 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    View.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Block_Adminhtml_History_View
	extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	/**
	 * Constructor of the grid container
	 */
	public function __construct()
	{
		/* @var $history CpDevelopment_AdminMonitoring_Model_History */
		$history = Mage::registry('current_history');

		$this->_blockGroup = 'cpdevelopment_adminmonitoring';
		$this->_controller = 'adminhtml_history_view';
		$this->_headerText = Mage::helper('cpdevelopment_adminmonitoring')->__('History Entry #%s', $history->getId());
		parent::__construct();
		$this->removeButton('add');

		// Add back to history button
		$this->_addBackButton();

		// Add revert button is possible
		if ($history->isUpdate() && $history->getDecodedContentDiff()) {
			$this->addButton('revert', array(
				'label'   => Mage::helper('cpdevelopment_adminmonitoring')->__('Revert Changes'),
				'onclick' => 'confirmSetLocation(\'' . Mage::helper('cpdevelopment_adminmonitoring')->__('Are you sure?') . '\', \'' . $this->getUrl('*/*/revert', array('id' => $history->getId())) . '\')',
				'class'   => 'delete',
			), 10);
		}
	}

	/**
	 * Call not the direct parent but the parent-parent class because we don't want to add
	 * an actual grid block here.
	 *
	 * @return CpDevelopment_AdminMonitoring_Block_Adminhtml_History_View
	 */
	protected function _prepareLayout()
	{
		return call_user_func(array(get_parent_class(get_parent_class($this)), '_prepareLayout'));
	}

	/**
	 * Retrieve the back url
	 *
	 * @return string
	 */
	public function getBackUrl()
	{
		return $this->getUrl('*/*');
	}
}

// End of View.php 