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


if (class_exists('EcommerceTeam_Sln_Model_Layer_Search', false)) {
    class Mirasvit_SearchIndex_Model_Catalogsearch_Layer_Extends extends EcommerceTeam_Sln_Model_Layer_Search {}
} else {
    class Mirasvit_SearchIndex_Model_Catalogsearch_Layer_Extends extends Mage_CatalogSearch_Model_Layer {}
}

class Mirasvit_SearchIndex_Model_Catalogsearch_Layer extends Mirasvit_SearchIndex_Model_Catalogsearch_Layer_Extends
{
    public function prepareProductCollection($collection)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->setStore(Mage::app()->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();

        $catalogIndex = Mage::helper('searchindex/index')->getIndex('mage_catalog_product');
        $catalogIndex->joinMatched($collection);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        $this->_addStockOrder($collection);

        Mage::helper('mstcore/debug')->dump($uid, array('collection_sql', $collection->getSelect()->__toString()));
        Mage::helper('mstcore/debug')->end($uid, $this);

        return $this;
    }

    protected function _addStockOrder($collection)
    {
        $index = Mage::helper('searchindex/index')->getIndex('mage_catalog_product');

        if ($index->getProperty('out_of_stock_to_end')) {
            $resource = Mage::getSingleton('core/resource');
            $select   = $collection->getSelect();
            $select->joinLeft(
                array('_inventory_table' => $resource->getTableName('cataloginventory/stock_item')),
                '_inventory_table.product_id = e.entity_id',
                array()
            );
            $select->order('_inventory_table.is_in_stock DESC');
        }

        return $this;
    }
}
