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


class Mirasvit_SearchIndex_Block_Results extends Mage_CatalogSearch_Block_Result
{
    static $_outputs = 0;

    protected $_indexes = null;

    protected function _prepareLayout()
    {
        if (Mage::registry('current_searchlandingpage')) {
            $page = Mage::registry('current_searchlandingpage');

            // add Home breadcrumb
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbs) {
                $breadcrumbs->addCrumb('search', array(
                    'label' => $page->getTitle(),
                    'title' => $page->getTitle()
                ));
            }

            $this->getLayout()->getBlock('head')
                ->setTitle($page->getMetaTitle())
                ->setKeywords($page->getMetaKeywords())
                ->setDescription($page->getMetaDescription());
        } else {
            return parent::_prepareLayout();
        }
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_searchlandingpage')) {
            $page = Mage::registry('current_searchlandingpage');

            return $page->getTitle();
        }

        return false;
    }


    /**
     * If layouts in other themes add this block too, we clear template
     * for not output search results twice
     */
    public function _toHtml()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        self::$_outputs++;

        if (self::$_outputs > 1) {
            $this->setTemplate(null);
        }

        Mage::helper('mstcore/debug')->end($uid);

        return parent::_toHtml();
    }

    /**
     * Retrieve all enabled indexes
     * @return array
     */
    public function getIndexes()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if ($this->_indexes == null) {
            $this->_indexes = Mage::helper('searchindex/index')->getIndexes(true);

            foreach ($this->_indexes as $code => $index) {
                $index->setContentBlock($this->getContentBlock($index));
            }
        }

        Mage::helper('mstcore/debug')->end($uid, $this->_indexes);

        return $this->_indexes;
    }

    /**
     * Return url to search by specific index
     * @param  Mirasvit_SearchIndex_Model_Index_Abstract $index
     * @return string
     */
    public function getIndexUrl($index)
    {
        return Mage::getUrl('*/*/*', array(
            '_current' => true,
            '_query'   => array('index' => $index->getCode(), 'p' => null)
        ));
    }

    /**
     * Return first index with results greater zero or catalog index
     * @return Mirasvit_SearchIndex_Model_Index_Abstract
     */
    public function getFirstMatchedIndex()
    {
        foreach ($this->getIndexes() as $index) {
            if ($index->getCountResults()) {
                return $index;
            }
        }

        return Mage::helper('searchindex/index')->getIndex('mage_catalog_product');
    }

    /**
     * Return current index or first matched index
     * @return Mirasvit_SearchIndex_Model_Index_Abstract
     */
    public function getCurrentIndex()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $indexCode    = $this->getRequest()->getParam('index');
        $currentIndex = Mage::helper('searchindex/index')->getIndex($indexCode);
        if ($indexCode === null || $currentIndex->getCountResults() == 0) {
            $currentIndex = $this->getFirstMatchedIndex();
        }

        Mage::helper('mstcore/debug')->end($uid, $currentIndex);

        return $currentIndex;
    }

    public function getListBlock()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        Mage::unregister('current_layer');
        Mage::register('current_layer', Mage::getSingleton('catalogsearch/layer'));

        $html = $this->getChild('search_result_list');

        Mage::helper('mstcore/debug')->end($uid, $html);

        return $html;
    }

    /**
     * Return current search content
     * @return string
     */
    public function getCurrentContent()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $currentIndex = $this->getCurrentIndex();
        $html = $this->getContentBlock($currentIndex)->toHtml();

        Mage::helper('mstcore/debug')->end($uid, $html);

        return $html;
    }

    public function getContentBlock($indexModel)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if ($indexModel->getCode() == 'mage_catalog_product') {
            $block =  $this->getChild('search_result_list');
        } else {
            $block = $this->getChild('searchindex_result_'.$indexModel->getCode());
        }


        if (!$block) {
            Mage::throwException("Can't find child block for index ".$indexModel->getCode());
        }

        Mage::helper('mstcore/debug')->end($uid, $block);

        return $block;
    }
}