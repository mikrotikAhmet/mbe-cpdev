<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:12 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    HistoryController.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Adminhtml_HistoryController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Inits the layout, the active menu tab and the breadcrumbs
	 *
	 * @return CpDevelopment_AdminMonitoring_Adminhtml_HistoryController
	 */
	protected function _initAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('cpdevelopment_adminmonitoring/history');
		$this->_addBreadcrumb(
			$this->getMonitoringHelper()->__('Admin Monitoring'),
			$this->getMonitoringHelper()->__('History')
		);

		$this->_title($this->getMonitoringHelper()->__('Admin Monitoring'))
			->_title($this->getMonitoringHelper()->__('History'));

		return $this;
	}

	/**
	 * Shows the history grid
	 */
	public function indexAction()
	{
		$this->_initAction();
		$this->renderLayout();
	}

	/**
	 * Reload the adminhtml history grid, for
	 */
	public function gridAction()
	{
		$block = $this->getLayout()->createBlock('cpdevelopment_adminmonitoring/adminhtml_history_grid');
		$this->getResponse()->setBody($block->toHtml());
	}

	/**
	 * View a single history grid
	 */
	public function viewAction()
	{
		/* @var $history CpDevelopment_AdminMonitoring_Model_History */
		$history = Mage::getModel('cpdevelopment_adminmonitoring/history')->load($this->getRequest()->getParam('id'));
		if (!$history->getId()) {
			$this->_redirect('*/*');

			return;
		}

		Mage::register('current_history', $history, true);

		$this->_initAction();
		$this->renderLayout();
	}

	/**
	 * Reverts a history entry
	 */
	public function revertAction()
	{
		/* @var $history CpDevelopment_AdminMonitoring_Model_History */
		$history = Mage::getModel('cpdevelopment_adminmonitoring/history')->load($this->getRequest()->getParam('id'));
		if ($history->getId()) {
			$model = $history->getOriginalModel();
			$model->addData($history->getDecodedContentDiff());
			$model->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(
				$this->getMonitoringHelper()->__(
					'Revert of %1$s with id %2$d successful',
					$history->getObjectType(),
					$history->getObjectId()
				)
			);
		}

		$this->_redirect('*/*');
	}

	/**
	 * Retrieve the adminmonitoring helper
	 *
	 * @return CpDevelopment_AdminMonitoring_Helper_Data
	 */
	public function getMonitoringHelper()
	{
		return Mage::helper('cpdevelopment_adminmonitoring');
	}

	/**
	 * Check is allowed access to action - needed afer security patch SUPEE-6285
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/history');
	}
}

// End of HistoryController.php 