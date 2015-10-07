<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/9/15
 * Time: 4:33 PM
 */
/**
 * CP Development
 *
 * @company    Semite DOO BEOGRAD
 * @package    Product Aggregation System
 * @copyright  Copyright 2009-2015 Semite DOO. Developments
 * @license    http://www.semitedoo.com/license/
 * @version    Clean.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Clean
{
	const XML_PATH_ADMINMONITORING_INTERVAL      = 'admin/cpdevelopment_adminmonitoring/interval';
	const XML_PATH_ADMINMONITORING_CLEAN_ENABLED = 'admin/cpdevelopment_adminmonitoring/enable_cleaning';

	/**
	 * Clean in chunks.
	 *
	 * CHUNK_SIZE determines the items cleared per chunk.
	 *
	 * CHUNK_RUNS determines the number of chunks cleaned per call to clean()
	 *
	 * I.e. per call of clean(), at most CHUNK_SIZE * CHUNK_RUNS items are cleaned.
	 */
	const CHUNK_SIZE = 1000;
	const CHUNK_RUNS = 250;

	/**
	 * Cronjob method for cleaning the database table.
	 *
	 * @return CpDevelopment_AdminMonitoring_Model_Clean
	 */
	public function scheduledCleanAdminMonitoring()
	{
		if (!Mage::getStoreConfigFlag(self::XML_PATH_ADMINMONITORING_CLEAN_ENABLED)) {
			return $this;
		}

		try {
			$this->clean();
		} catch (Exception $e) {
			Mage::logException($e);
		}

		return $this;
	}

	/**
	 * Clean the database table for the given interval.
	 *
	 * @return CpDevelopment_AdminMonitoring_Model_Clean
	 */
	public function clean()
	{
		if (!Mage::getStoreConfig(self::XML_PATH_ADMINMONITORING_CLEAN_ENABLED)
			|| !Mage::getStoreConfigFlag(self::XML_PATH_ADMINMONITORING_INTERVAL)
		) {
			return $this;
		}

		$this->cleanInChunks();

		return $this;
	}

	/**
	 * Clean the database table for the given interval, usink chunks to avoid memory over-usage.
	 *
	 * @return $this
	 */
	protected function cleanInChunks()
	{
		$numChunks = 0;
		do {
			$cleanedItems = $this->cleanChunk();
		} while ($cleanedItems == static::CHUNK_SIZE && $numChunks++ < static::CHUNK_RUNS);

		return $this;
	}

	/**
	 * Clean a chunk of the items in database table for the given interval.
	 *
	 * @return int Number of items deleted
	 */
	protected function cleanChunk()
	{
		$interval = Mage::getStoreConfig(self::XML_PATH_ADMINMONITORING_INTERVAL);

		/* @var $adminMonitoringCollection CpDevelopment_AdminMonitoring_Model_Resource_History_Collection */
		$adminMonitoringCollection = Mage::getModel('cpdevelopment_adminmonitoring/history')
			->getCollection()
			->setPageSize(static::CHUNK_SIZE)
			->addFieldToFilter(
				'created_at',
				array(
					'lt' => new Zend_Db_Expr("DATE_SUB('" . now() . "', INTERVAL " . (int)$interval . " DAY)")
				)
			);

		$count = 0;

		foreach ($adminMonitoringCollection as $history) {
			$history->delete();
			$count++;
		}

		return $count;
	}
}

// End of Clean.php 