<?php

class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_CategoryGrid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mbemro_customcatalog_category_grid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $eav = Mage::getModel('eav/config');
        $categoryNameAttribute = $eav->getAttribute('catalog_category', 'name');
        $categoryNameTable = $resource->getTableName('catalog/category') . '_' . $categoryNameAttribute->getBackendType();
        $categoryNameAttributeId = $categoryNameAttribute->getAttributeId();

        $collection = Mage::getResourceModel('customcatalog/category_collection');
        $collection
            ->getSelect()->join(
                array('name' => $categoryNameTable),
                'main_table.category_id = name.entity_id and name.attribute_id='.$categoryNameAttributeId,
                array('name.value as category_name')
            )
            ->where(
                'main_table.customer_id='. Mage::registry('current_customer')->getId()
            )
        ;

        //$limit = Mage::getStoreConfig('catalog/frontend/list_per_page');
        /*
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            //->addIsActiveFilter()
        ;
        $collection->getSelect()
            ->join(
                array('cpe' => 'customcatalog_category_entity'),
                'e.entity_id = cpe.category_id',
                array('cpe.*')
            )
            ->where('cpe.customer_id='. Mage::registry('current_customer')->getId())
        //    ->limit($limit)
        ;
        */
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('customcatalog/catalog');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn('category_id', array(
            'header' => $helper->__('Category Id'),
            'index'  => 'category_id',
            'column_css_class' => 'mycatalog-cat cat-id',
        ));
        $this->addColumn('category_name', array(
            'header' => $helper->__('Name'),
            'index'  => 'category_name',
            'column_css_class' => 'mycatalog-cat cat-name',
        ));

        $this->addColumn('discount_amount', array(
            'header' => $helper->__('Discount'),
            'index'  => 'discount_amount',
            'column_css_class' => 'mycatalog-cat cat-amount',
            'renderer' => 'Mbemro_CustomCatalog_Block_Product_Renderer_Number',
        ));

        $this->addColumn('apply_subcategories', array(
            'header' => $helper->__('Apply to subcategories'),
            'index'  => 'apply_subcategories',
            'column_css_class' => 'mycatalog-cat cat-apply',
            'type'     => 'checkbox',
            'renderer' => 'Mbemro_CustomCatalog_Block_Product_Renderer_Checkbox',
//            'disabled' => true,
//            'values'   => array(1, true),
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        //return $this->getUrl('*/*/grid', array('_current'=>true));
        return $this->getUrl('customcatalog/adminhtml_category/grid', array('_current'=>true));
    }
}