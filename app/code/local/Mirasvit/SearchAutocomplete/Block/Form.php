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
 * ÐÐ»Ð¾Ðº ÑÐ¾ÑÐ¼Ñ Ð¿Ð¾Ð¸ÑÐºÐ°
 * 
 * @category Mirasvit
 * @package  Mirasvit_SearchAutocomplete
 */
class Mirasvit_SearchAutocomplete_Block_Form extends Mage_Core_Block_Template
{
    public function getAjaxUrl()
    {
        $url = Mage::getUrl('searchautocomplete/ajax/get');

        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    public function getCategories()
    {
        $rootId = Mage::app()->getStore()->getRootCategoryId();
        $root = Mage::getModel('catalog/category')->load($rootId);

        $collection  = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addPathsFilter($root->getPath().DS);

        if ($this->getUserCategories()) {
            $collection->addFieldToFilter('entity_id', $this->getUserCategories());
        } else {
            $collection->addFieldToFilter('level', 2)
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('include_in_menu', 1)
                ->setOrder('position', 'asc');
        }

        return $collection;
    }

    public function getIndexes()
    {
        $indexes = Mage::helper('searchautocomplete')->getIndexes(false);

        return $indexes;
    }

    protected function getUserCategories()
    {
        $categories = explode(',', Mage::getStoreConfig('searchautocomplete/general/categories'));
        if (count($categories) && $categories[0] != '') {
            return $categories;
        }

        return false;
    }

     public function getAttributes()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection->addIsSearchableFilter();
        $collection->addFieldToFilter('backend_type', array('varchar', 'text'));

        return $collection;
    }

    public function getFilterType()
    {
        $filterType = Mage::getStoreConfig('searchautocomplete/general/filter_type');
        if (!$filterType) {
            $filterType = 'category';
        }
     
        return $filterType;
    }


    public function getFiltertOptions()
    {

    }
}
