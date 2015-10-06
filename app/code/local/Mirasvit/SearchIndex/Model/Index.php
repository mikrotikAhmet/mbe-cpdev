<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  675
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_SearchIndex_Model_Index extends Mage_Core_Model_Abstract
{
    protected $_matchedIds       = array();
    protected $_cache            = false;
    protected $_tmpTableCreated  = false;
    protected static $_instances = array();

    protected function _construct()
    {
        $this->_init('searchindex/index');
    }

    public function getBaseGroup()
    {
        return 'Others';
    }

    public function getBaseTitle()
    {
        return get_class($this);
    }

    public function getCode()
    {
        $arr = explode('_', get_class($this));
        return strtolower($arr[4].'_'.$arr[5].'_'.$arr[6]);
    }

    public function getIndexInstance()
    {
        if (!isset(self::$_instances[$this->getIndexCode()])) {
            $model = Mage::helper('searchindex/index')->getIndexModel($this->getIndexCode());
            if ($model) {
                $model->load($this->getId());
                self::$_instances[$this->getIndexCode()] = $model;
            } else {
                Mage::throwException("Can't find index instance for code ".$this->getIndexCode());
            }
        }

        return self::$_instances[$this->getIndexCode()];
    }

    public function getFieldsets()
    {
        return array();
    }

    public function canUse()
    {
        return true;
    }

    public function isLocked()
    {
        return false;
    }

    public function getIndexer()
    {
        $indexer = Mage::getSingleton('searchindex/index_'.$this->getCode().'_indexer');
        $indexer->setIndexModel($this);

        return $indexer;
    }

    public function reset()
    {
        $this->_tmpTableCreated = false;

        return $this;
    }

    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = unserialize($this->getAttributesSerialized());
            if (!is_array($attributes)) {
                $attributes = array();
            }

            $this->setData('attributes', $attributes);
        }

        return $this->getData('attributes');
    }

    public function getProperty($code)
    {
        if (!$this->hasData('properties')) {
            $properties = unserialize($this->getPropertiesSerialized());
            if (!is_array($properties)) {
                $properties = array();
            }

            $this->setData('properties', $properties);
        }

        return $this->getData('properties', $code);
    }

    public function reindexAll()
    {
        if (!Mage::helper('mstcore/code')->getStatus()) {
            return $this;
        }

        $uid = Mage::helper('mstcore/debug')->start();

        $this->getIndexInstance()->getIndexer()->reindexAll();
        $this->setStatus(1)
            ->save();

        Mage::helper('mstcore/debug')->end($uid);
    }

    public function getMatchedIds($queryText = null, $storeId = null)
    {
        if (!Mage::helper('mstcore/code')->getStatus()) {
            return array();
        }

        $uid = Mage::helper('mstcore/debug')->start();

        if ($queryText == null) {
            $query     = $this->getQuery();
            $queryText = $query->getQueryText();

            if ($query->getSynonymFor()) {
                $queryText = $query->getSynonymFor();
            }
        }

        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!isset($this->_matchedIds[$queryText])) {
            $this->_processSearch($queryText, $storeId);
        }

        Mage::helper('mstcore/debug')->end($uid, $this->_matchedIds);

        return $this->_matchedIds[$queryText];
    }

    public function setMatchedIds($queryText, $ids)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if (!is_array($ids)) {
            $ids = array();
        }

        $this->_matchedIds[$queryText] = $ids;

        Mage::helper('mstcore/debug')->dump($uid, array('$this->_matchedIds', $this->_matchedIds));
        Mage::helper('mstcore/debug')->end($uid);

        return $this;
    }

    public function getQuery()
    {
        return Mage::helper('catalogsearch')->getQuery();
    }

    protected function _processSearch($queryText, $storeId)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $ts = microtime(true);

        $engine  = Mage::helper('searchindex')->getSearchEngine();

        try {
            $result = $engine->query($queryText, $storeId, $this);
            $this->setMatchedIds($queryText, $result);
        } catch (Exception $e) {
            Mage::helper('mstcore/logger')->logException($this, $e, $e);

            // Ð°Ð»ÑÑÐµÑÐ½Ð°ÑÐ¸Ð²Ð½ÑÐ¹ Ð´Ð²Ð¸Ð¶Ð¾Ðº (fulltext)
            try {
                $engine = Mage::getModel('searchsphinx/engine_fulltext');
                $result = $engine->query($queryText, $storeId, $this);
                $this->setMatchedIds($queryText, $result);
            } catch(Exception $e) {
                Mage::helper('mstcore/debug')->dump($uid, $e);
                Mage::helper('mstcore/logger')->logException($this, $e, $e);
                $this->setMatchedIds($queryText, array());
            }
        }

        Mage::helper('mstcore/logger')->logPerformance($this, __FUNCTION__.' '.count($this->getMatchedIds()).' '.$queryText, round(microtime(true) - $ts, 4));

        Mage::helper('mstcore/debug')->end($uid);

        return $this;
    }

    public function getCountResults()
    {
        return $this->getCollection()->getSize();
    }

    public function joinMatched($collection, $mainTableKeyField = 'e.entity_id')
    {
        $matchedIds = $this->getMatchedIds();
        $this->_createTemporaryTable($matchedIds);

        $collection->getSelect()->joinLeft(
            array('tmp_table' => $this->_getTemporaryTableName()),
            '(tmp_table.entity_id='.$mainTableKeyField.')',
            array('relevance' => 'tmp_table.relevance')
        );
        if ($this->_cache) {
            $collection->getSelect()->where('tmp_table.query_id = '.$this->getQuery()->getId());
        }
        $collection->getSelect()->where('tmp_table.id IS NOT NULL');

        return $this;
    }

    public function getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    protected function _createTemporaryTable($matchedIds)
    {
        if ($this->_tmpTableCreated) {
            return $this;
        }

        $values = array();
        $queryId = $this->getQuery()->getId();

        if (!$queryId) {
            $queryId = 0;
        }

        foreach ($matchedIds as $id => $relevance) {
            $values[] = '('.$queryId.','.$id.','.$relevance.')';
        }

        $connection = $this->getConnection();

        $query = '';
        if ($this->_cache) {
            $query .= "CREATE TABLE IF NOT EXISTS `".$this->_getTemporaryTableName()."` (";
        } else {
            $query .= "CREATE TEMPORARY TABLE IF NOT EXISTS `".$this->_getTemporaryTableName()."` (";
        }
        $query .= "
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `query_id` int(11) unsigned NOT NULL,
                `entity_id` int(11) unsigned NOT NULL,
                `relevance` int(11) unsigned NOT NULL,
                PRIMARY KEY (`id`)";
        if ($this->_cache) {
            $query .= ")ENGINE=MEMORY DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
            $query .= "DELETE FROM `".$this->_getTemporaryTableName()."` WHERE `query_id`=".$queryId.";";
        } else {
            $query .= ")ENGINE=MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        }
        if (count($values)) {
            $query .= "INSERT INTO `".$this->_getTemporaryTableName()."` (`query_id`, `entity_id`, `relevance`)".
                "VALUES ".implode(',', $values).";";
        }

        $connection->raw_query($query);
        $this->_tmpTableCreated = true;

        return $this;
    }

    protected function _getTemporaryTableName()
    {
        return 'searchindex_result_'.$this->getCode();
    }
}