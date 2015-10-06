<?php

/**
 *
 * @category    Mbemro
 * @package     Mbemro_CustomCatalog
 * @author      Sofija Blazevski<sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_Model_Resource_Category extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection
     *
     */
    public function _construct()
    {
        $this->_init('customcatalog/category', 'id');
    }

    public function loadByCategory(
        Mbemro_CustomCatalog_Model_Category $object,
        Mage_Customer_Model_Customer $customer,
        Mage_Catalog_Model_Category $category)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where('customer_id = ?', $customer->getId())
            ->where('category_id = ?', $category->getId());

        $data = $read->fetchRow($select);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    public function getCategories(Mage_Customer_Model_Customer $customer)
    {
        $resource = Mage::getSingleton('core/resource');
        $eav = Mage::getModel('eav/config');
        $categoryNameAttribute = $eav->getAttribute('catalog_category', 'name');
        $categoryNameTable = $resource->getTableName('catalog/category') . '_' . $categoryNameAttribute->getBackendType();
        $categoryNameAttributeId = $categoryNameAttribute->getAttributeId();


        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->join(
                array('name' => $categoryNameTable),
                'main_table.category_id = name.entity_id and name.attribute_id='.$categoryNameAttributeId,
                array('name.value as category_name')
            )
            ->where('customer_id = ?', $customer->getId())
        ;

        return $read->fetchAll($select);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Customer_Model_Customer $customer
     * @return float
     */
    public function getProductDiscount(
        Mage_Catalog_Model_Product $product,
        Mage_Customer_Model_Customer $customer)
    {
        $categoryIds = $product->getCategoryIds();
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where('category_id IN (?)', $categoryIds)
            ->where('customer_id = ?', $customer->getId());
        ;
        //use best discount found
        $discount = null;
        $results = $read->fetchAll($select);
        foreach($results as $row) {
            $currentDiscount = $row['discount_amount'];
            if(is_null($discount) || $discount > $currentDiscount) {
                $discount = $currentDiscount;
            }
        }

        return is_null($discount) ? 0.00 : $discount;
    }

    public function getDiscountedPrice(Mage_Catalog_Model_Product $product,
                                       Mage_Customer_Model_Customer $customer, $originalPrice = null)
    {
        $price = is_null($originalPrice)? $product->getPrice() : $originalPrice;
        $discount = $this->getProductDiscount($product, $customer);
        if ($discount > 0.00) {
//            $min = $product->getMinimalPrice();
            $price = $price / ( 1 + $discount / 100);
//            if($price < $min) {
//                $price = $min;
//            }
        }
        return $price;
    }
}