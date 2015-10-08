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
 * @version    Config.php.php 9/9/15 ahmet $
 * @author     Ahmet GOUDENOGLU
 */

class CpDevelopment_AdminMonitoring_Model_Config extends Varien_Simplexml_Config
{
	/**
	 * @var string
	 */
	const CACHE_ID = 'cpdevelopment_adminmonitoring';

	/**
	 * @var string
	 */
	const CACHE_TAG = 'CACHE';

	/**
	 * Class Constructor
	 *
	 * @param string|Varien_Simplexml_Element $sourceData Source Data
	 */
	public function __construct($sourceData = null)
	{
		$this->setCacheId(self::CACHE_ID);
		$this->setCacheTags(array(self::CACHE_TAG));

		parent::__construct($sourceData);
		$this->_construct();
	}

	/**
	 * Init the admin monitoring configuration
	 *
	 * @return Mage_Api_Model_Config
	 */
	protected function _construct()
	{
		if (Mage::app()->useCache(self::CACHE_ID)) {
			if ($this->loadCache()) {
				return $this;
			}
		}

		// Load all MDI adapters from the configuration files
		$config = Mage::getConfig()->loadModulesConfiguration('adminmonitoring.xml');
		$this->setXml($config->getNode('adminmonitoring'));

		if (Mage::app()->useCache(self::CACHE_ID)) {
			$this->saveCache();
		}

		return $this;
	}

	/**
	 * Retrieve all object type excludes
	 *
	 * @return array
	 */
	public function getObjectTypeExcludes()
	{
		$excludes = (array)$this->getNode('excludes/object_types');
		if (!$excludes) {
			return array();
		}

		return array_keys($excludes);
	}

	/**
	 * Retrieve global admin route excludes (routes with no object types as children)
	 *
	 * @return array
	 */
	public function getGlobalAdminRouteExcludes()
	{
		$adminRouteExludes = (array)$this->getNode('excludes/admin_routes');
		if (!$adminRouteExludes) {
			return array();
		}

		$excludes = array();
		foreach ($adminRouteExludes as $key => $exclude) {
			if (!$exclude->children()) {
				$excludes[] = $key;
			}
		}

		return $excludes;
	}

	/**
	 * Retrieve partial admin route excludes (routes with object types as children)
	 *
	 * @return array
	 */
	public function getPartialAdminRouteExcludes()
	{
		$adminRouteExludes = (array)$this->getNode('excludes/admin_routes');
		if (!$adminRouteExludes) {
			return array();
		}

		$excludes = array();
		foreach ($adminRouteExludes as $key => $exclude) {
			if ($exclude->children()) {
				$children = (array)$exclude->children();
				$excludes[$key] = array_keys($children);
			}
		}

		return $excludes;
	}

	/**
	 * Retrieve all field excludes
	 *
	 * @return array
	 */
	public function getFieldExcludes()
	{
		$excludes = (array)$this->getNode('excludes/fields');
		if (!$excludes) {
			return array();
		}

		return array_keys($excludes);
	}

	/**
	 * Retrieve cache model
	 *
	 * @return Varien_Simplexml_Config_Cache_Abstract|Zend_Cache_Core
	 */
	public function getCache()
	{
		return Mage::app()->getCache();
	}

	/**
	 * Load data from the cache
	 *
	 * @param  string $id Cache ID
	 * @return bool|mixed
	 */
	protected function _loadCache($id)
	{
		return Mage::app()->loadCache($id);
	}

	/**
	 * Save data in the cache
	 *
	 * @param  string $data     Cache Data
	 * @param  string $id       Cache ID
	 * @param  array  $tags     Cache Tags
	 * @param  bool   $lifetime Cache Lifetime
	 * @return bool|Mage_Core_Model_App
	 */
	protected function _saveCache($data, $id, $tags = array(), $lifetime = false)
	{
		return Mage::app()->saveCache($data, $id, $tags, $lifetime);
	}

	/**
	 * Remove date from the cache
	 *
	 * @param  string $id Cache ID
	 * @return bool|Mage_Core_Model_App
	 */
	protected function _removeCache($id)
	{
		return Mage::app()->removeCache($id);
	}
}

// End of Config.php 