<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:37 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Diff.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_History_Diff
{
	/**
	 * @var CpDevelopment_AdminMonitoring_Model_History_Data
	 */
	protected $_dataModel;

	/**
	 * Init the data model
	 *
	 * @param CpDevelopment_AdminMonitoring_Model_History_Data $dataModel History Data Model
	 */
	public function __construct(CpDevelopment_AdminMonitoring_Model_History_Data $dataModel)
	{
		$this->_dataModel = $dataModel;
	}

	/**
	 * Check if the data has changed.
	 *
	 * @return bool Result
	 */
	public function hasChanged()
	{
		return ($this->_dataModel->getContent() != $this->_dataModel->getOrigContent());
	}

	/**
	 * Generate an object diff of the original content and the actual content.
	 *
	 * @return array Diff Array
	 */
	private function getObjectDiff()
	{
		$dataOld = $this->_dataModel->getOrigContent();
		if (is_array($dataOld)) {
			$dataNew = $this->_dataModel->getContent();
			$dataDiff = array();
			foreach ($dataOld as $key => $oldValue) {
				// compare objects serialized
				if (isset($dataNew[$key])
					&& (json_encode($oldValue) != json_encode($dataNew[$key]))
				) {
					$dataDiff[$key] = $oldValue;
				}
			}

			return $dataDiff;
		} else {
			return array();
		}
	}

	/**
	 * Retrieve the serialized diff for the current data model.
	 *
	 * @return string Serialized Diff
	 */
	public function getSerializedDiff()
	{
		$dataDiff = $this->getObjectDiff();

		return json_encode($dataDiff);
	}
}

// End of Diff.php 