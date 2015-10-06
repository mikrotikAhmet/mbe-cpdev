<?php
/**
 *
 * @category    Mbemro
 * @package     Mbemro_CustomCatalog
 * @author      Sofija Blazevski<sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_Model_Resource_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection
     *
     */
    public function _construct()
    {
        $this->_init('customcatalog/product', 'id');
    }

    public function existsByProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ? $product->getId() : (int) $product;
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where('product_id = ?', $productId);

        $data = $read->fetchRow($select);
        return $data !== false;
    }

    public function loadByProduct(Mbemro_CustomCatalog_Model_Product $object, Mage_Catalog_Model_Product $product, Mage_Customer_Model_Customer $customer = null)
    {
        if(is_null($customer)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where('store_id = ?', 0)
            ->where('customer_id = ?', $customer->getId())
            ->where('product_id = ?', $product->getId());

        $data = $read->fetchRow($select);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;

    }

//    public function search($keyword)
//    {
//        $limit = Mage::getStoreConfig('catalog/frontend/list_per_page');
//        $read = $this->_getReadAdapter();
//        $select = $read->select()->from($this->getMainTable())
//            //->where('store_id = ?', Mage::)
//            ->join(array(
//                    'cpe' => 'customcatalog_product_entity'),
//                'e.entity_id = cpe.product_id',
//                array('cpe.*')
//            )
//            ->limit($limit);
//    }
}