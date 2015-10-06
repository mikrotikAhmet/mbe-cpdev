<?php

/**
 * Mbemro CustomCatalog Product List Block.
 *
 * @category Mbemro
 * @package Mbemro_CustomCatalog
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_Block_Productlist extends Mage_Core_Block_Template
{
    private $collection = null;
    private $keyword = '';

    public function __construct()
    {
        parent::__construct();

        $keyword = Mage::registry('keyword');
        $limit   = Mage::getStoreConfig('catalog/frontend/list_per_page');
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if (empty($keyword)) {
            $collection = Mage::getResourceModel('catalog/product_collection')
                // ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('short_description')
                ->addAttributeToSelect('media_gallery_images')
                ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                ->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->addAttributeToFilter('visibility', array('neq' => 1))
            ;

            $collection->getSelect()
                ->join(
                    array(
                        'cpe' => 'customcatalog_product_entity'), 'e.entity_id = cpe.product_id', array('cpe.*')
                )
                ->where('cpe.customer_id = ?', $customer->getId())
                ->where('cpe.store_id = ?', 0)
                ->limit($limit);

            $this->setCollection($collection);
        } else {
            $this->search($keyword);
        }

    }

    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function search($keyword)
    {
        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         * @var $limit int
         */
        $limit = Mage::getStoreConfig('catalog/frontend/list_per_page');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $collection = Mage::getResourceModel('catalog/product_collection')
            // ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addAttributeToSelect('name', '%')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('media_gallery_images')
            ->addAttributeToFilter('type_id', array('eq' => 'simple'))
            ->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', array('neq' => 1))
        ;

//        $collection->addFieldToFilter('name',array('like'=>'%' . $keyword .'%'))
//            ->addAttributeToFilter ('price', array('eq'=> $keyword))
//            ;


        $collection->getSelect()
            ->join(array(
                    'cpe' => 'customcatalog_product_entity'),
                'e.entity_id = cpe.product_id',
                array('cpe.*')
            )
            ->where('cpe.customer_id = ?', $customer->getId())
            ->where('cpe.store_id = ?', 0)
            ->where(
                '(cpe.part_number LIKE ?) OR (cpe.notes LIKE ?) OR (IF(at_name.value_id > 0, at_name.value, at_name_default.value) like ?)',
                '%' . $keyword  . '%'
            )
            ->limit($limit);

        $this->setKeyword($keyword);
        $this->setCollection($collection);

    }

    // public function getCustomCatalogCollection()
    // {
    //     if (!isset($this->collection)) {
    //         $this->collection = Mage::getResourceModel('catalog/product_collection')
    //                     ->addAttributeToSelect('name')
    //                     ->addAttributeToSelect('sku')
    //                     ->addAttributeToSelect('price')
    //                     ->addAttributeToSelect('status')
    //                     ->addAttributeToSelect('visibility')
    //                     ->addAttributeToFilter('type_id', array('eq' => 'simple'))
    //                     ->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    //                     ->addAttributeToFilter('visibility', array('neq' => 1))
    //                     ;

    //         $this->collection->getSelect()->join(array('cpe' => 'customcatalog_product_entity'), 'e.entity_id = cpe.product_id', array('cpe.*'))->limit(2);

    //         $this->collection->load();
    //     }

    //     $this->setCollection($this->collection);


    //     return $this->collection;

    // }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //$this->getLayout()->createBlock('catalog/breadcrumbs');

        $this->getLayout()
            ->getBlock('breadcrumbs')
            ->addCrumb('home', array(
                'label' => $this->__('My Account'),
                'title' => $this->__('Go to My Account'),
                'link' => Mage::getUrl('customer/account')
                )
            )
            ->addCrumb('customcatalog.list', array(
                    'label' => $this->__('My Catalog'),
                )
            )
        /*
            ->addCrumb('customcatalog', array(
                'label' => $this->__('My Catalog'),
                'title' => $this->__('Go to My Catalog'),
                    'link' => $this->helper('customcatalog/customcatalog')->getModuleUrl()
                )
            )
            ->addCrumb('customcatalog.list', array(
                    'label' => $this->__('My Products'),
                )
            )
        */
        ;

        $this->getLayout()->getBlock('head')->setTitle('My Catalog');

        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5, 10=>10, 15=>15));
        $pager->setCollection($this->getCollection());

        $this->getCollection()->load();

        $this->setChild('pager', $pager);

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }



}


    /*
    array(22) {
  ["entity_id"]=>
  string(1) "1"
  ["entity_type_id"]=>
  string(1) "4"
  ["attribute_set_id"]=>
  string(1) "4"
  ["type_id"]=>
  string(6) "simple"
  ["sku"]=>
  string(9) "RZ559W27G"
  ["has_options"]=>
  string(1) "0"
  ["required_options"]=>
  string(1) "0"
  ["created_at"]=>
  string(19) "2013-08-17 15:57:14"
  ["updated_at"]=>
  string(19) "2013-08-31 11:24:11"
  ["status"]=>
  string(1) "1"
  ["visibility"]=>
  string(1) "4"
  ["store_id"]=>
  string(1) "0"
  ["customer_id"]=>
  string(2) "10"
  ["product_id"]=>
  string(1) "1"
  ["part_number"]=>
  string(13) "test part num"
  ["custom_price"]=>
  NULL
  ["notes"]=>
  string(20) "my favourite product"
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(80) "Rubbermaid -- 50 Gallon Brute Rollout Containter with Lid. Heavy-duty, 8" wheels"
  ["price"]=>
  string(8) "173.8500"
  ["is_salable"]=>
  string(1) "1"
  ["stock_item"]=>
  object(Varien_Object)#6696 (7) {
    ["_data":protected]=>
    array(1) {
      ["is_in_stock"]=>
      string(1) "1"
    }
    ["_hasDataChanges":protected]=>
    bool(false)
    ["_origData":protected]=>
    NULL
    ["_idFieldName":protected]=>
    NULL
    ["_isDeleted":protected]=>
    bool(false)
    ["_oldFieldsMap":protected]=>
    array(0) {
    }
    ["_syncFieldsMap":protected]=>
    array(0) {
    }
  }
}

    */
