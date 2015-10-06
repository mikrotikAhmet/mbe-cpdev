<?php

/**
 * Mbemro helper class for CustomCatalog module.
 *
 * @category Mbemro
 * @package Mbemro_CustomCatalog
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */

class Mbemro_CustomCatalog_Helper_Catalog extends Mage_Core_Helper_Abstract
{
    /**
     * Return if currently logged in user can use CustomCatalog
     *
     * @param int
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isProductInCatalog($productId, $store = null)
    {
        return Mage::getResourceModel("customcatalog/product")->existsByProduct($productId);
    }

    /**
     * Returns URL to add product to custom catalog.
     *
     * @param Product
     * @return string
     */
    public function getAddUrl($product)
    {
        return Mage::getUrl(
            'customcatalog/product/add',
            array('product'=>$product->getId())
        );
    }

    /**
     * Returns URL to remove product from custom catalog.
     *
     * @param int
     * @return string
     */
    public function getRemoveUrl($product)
    {
        return Mage::getUrl(
            'customcatalog/product/remove',
            array('product'=>$product->getId())
        );
    }

    /**
     * Returns URL to edit product in custom catalog.
     *
     * @param int
     * @return string
     */
    public function getEditUrl($product)
    {
        return Mage::getUrl(
            'customcatalog/product/edit',
            array('product'=>$product->getId())
        );
    }

    public function getSaveUrl()
    {
        return Mage::getUrl(
            'customcatalog/product/save'
        );
    }

    public function getProductListUrl()
    {
        return Mage::getUrl(
            'customcatalog/product'
        );
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @return array
     */
    public function getCategoryTreeForProduct($product)
    {
        $ids = $product->getCategoryIds();
        $categoryTree = array();
        print_r($ids);
        if(!empty($ids)) {
            $category = Mage::getModel('catalog/category')->load(array_pop($ids));
            $categoryTree = $this->getCategoryTree($category);
            krsort($categoryTree);
            print_r($categoryTree);exit;

        }
        return $categoryTree;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @return array
     */
    public function getTopCategory($product)
    {
        /**
         * @var $category Mage_Catalog_Model_Category
         */
        $ids = $product->getCategoryIds();
        $result = array();
        foreach ($ids as $id) {
            if ($id > 2) {
                $category = Mage::getModel('catalog/category')->load($id);
                $result = array(
                    'id' => $id,
                    'name'=> $category->getName(),
                    'url' => $category->getUrl()
                );
                break;
            }
        }
        return $result;
    }

    /**
     * @param $category Mage_Catalog_Model_Category
     * @return string
     */
    private function getCategoryTree($category)
    {
        $name = $category->getName();
        $url = $category->getUrl();
        $id = $category->getId();
        $result = array(
            array(
                'name'=>$name,
                'id'=>$id,
                'url'=>$url
            )
        );
        $parent = $category->getParentCategory();
        $parent_id = $parent->getId();
        if ($parent_id > 2) {
            $result = array_merge($result, $this->getCategoryTree($parent));
        }

        return $result;
    }

    public function getSearchUrl()
    {
        return Mage::getUrl(
            'customcatalog/product/search'
        );
    }


}
