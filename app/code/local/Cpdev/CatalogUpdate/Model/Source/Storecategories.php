<?php
class Cpdev_CatalogUpdate_Model_Source_Storecategories
{
    public function toOptionArray()
    {
        $sets   = array();
        $stores = Mage::app()->getStores();

        foreach($stores as $store)
        {
            $store_name       = $store->getName();
            $store_id         = $store->getStoreId();
            $root_category_id = $store->getRootCategoryId();

            $collection = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name')
                // ->addAttributeToFilter('path', array('like' => '%/'.$root_category_id.'/%'))
                ->addAttributeToFilter('level', 1)
            ;

            $categories = array();

            foreach($collection as $c)
            {
                $categories[] = array(
                    'value' => $c->getId(),
                    'label' => $c->getName(),
                );
            }

            $sets[] = array(
                'value' => $categories,
                'label' => $store_name,
            );
        }

        return $sets;
    }
}
