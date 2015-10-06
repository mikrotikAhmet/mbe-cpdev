<?php
 
class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mbemro_customcatalog_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $limit = Mage::getStoreConfig('catalog/frontend/list_per_page');

        // $collection = Mage::getResourceModel('sales/order_collection')
        //     ->join(array('a' => 'sales/order_address'), 'main_table.entity_id = a.parent_id AND a.address_type != \'billing\'', array(
        //         'city'       => 'city',
        //         'country_id' => 'country_id'
        //     ))
        //     ->join(array('c' => 'customer/customer_group'), 'main_table.customer_group_id = c.customer_group_id', array(
        //         'customer_group_code' => 'customer_group_code'
        //     ))
        //     ->addExpressionFieldToSelect(
        //         'fullname',
        //         'CONCAT({{customer_firstname}}, \' \', {{customer_lastname}})',
        //         array('customer_firstname' => 'main_table.customer_firstname', 'customer_lastname' => 'main_table.customer_lastname'))
        //     ->addExpressionFieldToSelect(
        //         'products',
        //         '(SELECT GROUP_CONCAT(\' \', x.name)
        //             FROM sales_flat_order_item x
        //             WHERE {{entity_id}} = x.order_id
        //                 AND x.product_type != \'configurable\')',
        //         array('entity_id' => 'main_table.entity_id')
        //     )
        // ;
 
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
                array('cpe' => 'customcatalog_product_entity'),
                'e.entity_id = cpe.product_id',
                array('cpe.*')
            )
            ->where('cpe.customer_id='. Mage::registry('current_customer')->getId())
            ->limit($limit);

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
 
    protected function _prepareColumns()
    {
        $helper = Mage::helper('customcatalog/catalog');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn('sku', array(
            'header' => $helper->__('Sku'),
            'index'  => 'sku',
            'column_css_class' => 'mycatalog product-id',
        ));
        $this->addColumn('part_number', array(
            'header' => $helper->__('Part Number'),
            'index'  => 'part_number',
            'column_css_class' => 'mycatalog part-number',
        ));
 
        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'index'  => 'name',
            'column_css_class' => 'mycatalog name',
        ));
 
        $this->addColumn('price', array(
            'header' => $helper->__('Price'),
            'index'  => 'price',
            'column_css_class' => 'mycatalog price',
            'renderer' => 'Mbemro_CustomCatalog_Block_Product_Renderer_Number',
        ));

        $this->addColumn('custom_price', array(
            'header' => $helper->__('Customer\'s price'),
            'index'  => 'custom_price',
            'column_css_class' => 'mycatalog custom-price',
            'renderer' => 'Mbemro_CustomCatalog_Block_Product_Renderer_Number',
        ));
 
        $this->addColumn('notes', array(
            'header' => $helper->__('Notes'),
            'index'  => 'notes',
            'column_css_class' => 'mycatalog notes',
        ));

        //$this->addExportType('customcatalog/adminhtml_admin/exportInchooCsv', $helper->__('CSV'));
        //$this->addExportType('customcatalog/adminhtml_admin/exportInchooExcel', $helper->__('Excel XML'));
 
        return parent::_prepareColumns();
    }
 
    public function getGridUrl()
    {
        //return $this->getUrl('*/*/grid', array('_current'=>true));
        return $this->getUrl('customcatalog/adminhtml_admin/grid', array('_current'=>true));
    }
}