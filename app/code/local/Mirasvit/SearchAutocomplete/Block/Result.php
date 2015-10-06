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


/**
 * ÐÐ»Ð¾Ðº Ð²ÑÐ²Ð¾Ð´Ð° ÑÐµÐ·ÑÐ»ÑÑÐ°ÑÐ¾Ð² Ð¿Ð¾Ð¸ÑÐºÐ°. ÐÑÐ½Ð¾Ð²Ð½Ð°Ñ Ð·Ð°Ð´Ð°ÑÐ° - Ð´Ð¾ÑÐµÑÐ½Ð¸Ðµ Ð±Ð»Ð¾ÐºÐ¸ Ð²ÑÐµÑ Ð²ÐºÐ»ÑÑÐµÐ½Ð½ÑÑ Ð¸Ð½Ð´ÐµÐºÑÐ¾Ð², Ð¾Ð³ÑÐ°Ð½Ð¸ÑÐ¸ÑÑ ÐºÐ¾Ð»-Ð²Ð¾ Ð²ÑÐ²Ð¾Ð´Ð¸Ð¼ÑÑ ÐµÐ»ÐµÐ¼ÐµÐ½ÑÐ¾Ð²
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchAutocomplete
 */
class Mirasvit_SearchAutocomplete_Block_Result extends Mage_Catalog_Block_Product_Abstract
{
    protected $_collections = array();
    protected $_indexes     = array();

    public function _prepareLayout()
    {
        $this->setTemplate('searchautocomplete/autocomplete/result.phtml');

        return parent::_prepareLayout();
    }

    public function init()
    {
        $this->_prepareIndexes();
    }

    protected function _prepareIndexes()
    {
        // Mage::dispatchEvent('searchautocomplete_prepare_collection');

        $this->_indexes = Mage::helper('searchautocomplete')->getIndexes(false);

        $maxCount   = Mage::getStoreConfig('searchautocomplete/general/max_results');
        $perIndex   = ceil($maxCount / count($this->_indexes));
        $sizes      = array();
        $additional = 0;
        foreach ($this->_indexes as $index => $label) {
            $st = microtime(true);
            $size = $this->getCollection($index)->getSize();

            if ($size >= $perIndex) {
                $sizes[$index] = $perIndex;
            } else {
                $additional = $perIndex - $size;
                $sizes[$index] = $size;
            }

            if ($size == 0) {
                unset($this->_indexes[$index]);
            }

            if ($this->getIndexFilter() && $index != $this->getIndexFilter()) {
                unset($this->_indexes[$index]);
            }
        }
        $additional = $this->_indexes ? ceil($additional / count($this->_indexes)) : 0;
        foreach ($this->_indexes as $index => $label) {
            $sizes[$index] += $additional;
        }

        foreach ($sizes as $index => $size) {
            $this->getCollection($index)->setPageSize($size);
        }
    }

    public function getIndexes()
    {
        return $this->_indexes;
    }

    public function getCollection($index)
    {
        if (!isset($this->_collections[$index])) {
            if (Mage::helper('core')->isModuleEnabled('Mirasvit_SearchIndex')) {
                $model = Mage::helper('searchindex/index')->getIndex($index);
                $collection = $model->getCollection();
            } else {
                $collection = Mage::getSingleton('catalogsearch/layer')->getProductCollection();
            }
            $collection->getSelect()->order('relevance desc');

            if ($index == 'mage_catalog_product' && $this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                $collection->addCategoryFilter($category);
            }

            $this->_collections[$index] = $collection;
        }

        return $this->_collections[$index];
    }

    public function getItemHtml($index, $item)
    {
        $block = Mage::app()->getLayout()->createBlock('searchautocomplete/result')
            ->setTemplate('searchautocomplete/autocomplete/index/'.str_replace('_', '/', $index).'.phtml')
            ->setItem($item);

        return $block->toHtml();
    }
}