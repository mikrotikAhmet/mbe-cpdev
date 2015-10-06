<?php 

/**
 * Adminhtml customer action tab
 *
 */
// class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Action extends Mage_Adminhtml_Block_Template 
//     implements Mage_Adminhtml_Block_Widget_Tab_Interface
class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Action extends Mage_Adminhtml_Block_Widget_Grid_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        $this->_blockGroup = 'customcatalog';
        $this->_controller = 'adminhtml_customer_edit_tab';
        $this->_headerText = Mage::helper('customcatalog/catalog')->__('Product List');

        parent::__construct();
        //$this->_removeButton('add');
    }
/*
    public function __construct()
    {
        // $this->createCollection();

        parent::__construct();

        $this->setTemplate('customertab/action.phtml');

    }
*/

    public function getGridHtml()
    {
        $block = new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Product();
        $block->publicPrepare();
        return $this->getChildHtml('grid') . $block->getFormHtml();
    }

    public function createCollection()
    {
        $limit = Mage::getStoreConfig('catalog/frontend/list_per_page');

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
            ->limit($limit);

        $this->setCollection($collection);
    }

    public function getCustomtabInfo()
    {
        $customer = Mage::registry('current_customer');

        $customtab = 'My Custom tab Action Contents Here';

        return $customtab;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Custom Catalog');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Action Tab');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'tags';
    }

}
