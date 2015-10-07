<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:07 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Grid.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Block_Adminhtml_History_Grid
	extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Grid constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('cpdevelopment_adminmonitoring_grid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	/**
	 * Retrieve helper class
	 *
	 * @return CpDevelopment_AdminMonitoring_Helper_Data Helper Instance
	 */
	public function getMonitoringHelper()
	{
		return Mage::helper('cpdevelopment_adminmonitoring');
	}

	/**
	 * Prepare the grid collection
	 *
	 * @return CpDevelopment_AdminMonitoring_Block_Adminhtml_History_Grid Self.
	 */
	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('cpdevelopment_adminmonitoring/history_collection');
		$collection->setOrder('created_at', 'DESC');
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	/**
	 * Prepare the grid columns
	 *
	 * @return CpDevelopment_AdminMonitoring_Block_Adminhtml_History_Grid Self.
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('created_at', array(
			'header' => $this->getMonitoringHelper()->__('Date/Time'),
			'index'  => 'created_at',
			'type'   => 'datetime',
			'width'  => 130
		));

		$this->addColumn('object_type', array(
			'header' => $this->getMonitoringHelper()->__('Object Type'),
			'index'  => 'object_type',
		));

		$this->addColumn('object_id', array(
			'header' => $this->getMonitoringHelper()->__('Object ID'),
			'index'  => 'object_id',
			'type'   => 'number',
		));

		/* @var $adminUsers CpDevelopment_AdminMonitoring_Model_System_Config_Source_History_Action */
		$adminUsers = Mage::getModel('cpdevelopment_adminmonitoring/system_config_source_history_action');
		$actionOptions = $adminUsers->toOptionHash();
		$this->addColumn('action', array(
			'header'  => $this->getMonitoringHelper()->__('Action'),
			'index'   => 'action',
			'type'    => 'options',
			'options' => $actionOptions
		));

		/* @var $adminUsers CpDevelopment_AdminMonitoring_Model_System_Config_Source_History_Status */
		$adminUsers = Mage::getModel('cpdevelopment_adminmonitoring/system_config_source_history_status');
		$statusOptions = $adminUsers->toOptionHash();
		$this->addColumn('status', array(
			'header'  => $this->getMonitoringHelper()->__('Status'),
			'index'   => 'status',
			'type'    => 'options',
			'options' => $statusOptions
		));

		/* @var $adminUsers CpDevelopment_AdminMonitoring_Model_System_Config_Source_Admin_User */
		$adminUsers = Mage::getModel('cpdevelopment_adminmonitoring/system_config_source_admin_user');
		$userOptions = $adminUsers->toOptionHash(false);
		$this->addColumn('user_id', array(
			'header'  => $this->getMonitoringHelper()->__('User'),
			'index'   => 'user_id',
			'type'    => 'options',
			'options' => $userOptions,
		));

		$this->addColumn('ip', array(
			'header' => $this->getMonitoringHelper()->__('Remote Address'),
			'index'  => 'ip',
			'width'  => 110
		));

		$this->addColumn('history_message', array(
			'header' => $this->getMonitoringHelper()->__('Message'),
			'index'  => 'history_message',
		));

		$this->addColumn('object_link', array(
			'header'   => Mage::helper('customer')->__('Link'),
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'cpdevelopment_adminmonitoring/adminhtml_history_grid_link',
		));

		$this->addColumn('row_action',
			array(
				'header'   => Mage::helper('catalog')->__('Action'),
				'width'    => 50,
				'type'     => 'action',
				'getter'   => 'getId',
				'actions'  => array(
					array(
						'caption' => Mage::helper('catalog')->__('View'),
						'url'     => array(
							'base' => '*/*/view',
						),
						'field'   => 'id'
					)
				),
				'filter'   => false,
				'sortable' => false,
				'index'    => 'stores',
			));

		return parent::_prepareColumns();
	}

	/**
	 * Retrieve the row url for the given history entry
	 *
	 * @param  CpDevelopment_AdminMonitoring_Model_History $row History Model
	 * @return bool|string
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('id' => $row->getId()));
	}

	/**
	 * Retrieve the grid url for the ajax calls in the grid
	 *
	 * @return string
	 */
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current' => true));
	}
}

// End of Grid.php 