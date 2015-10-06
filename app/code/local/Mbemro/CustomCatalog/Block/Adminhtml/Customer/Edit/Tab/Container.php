<?php

class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Container 
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('customcatalog/customer/edit.phtml');

    }

    public function isCustomCatalogEnabled()
    {
        $customerId = Mage::registry('current_customer')->getId();
        return Mage::getResourceModel('customcatalog/customer')->isCustomerEnabled($customerId, 0);
    }

    protected function _prepareLayout()
    {
        $product = new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Product();
        $this->getLayout()->addBlock($product, 'product');
        $this->setChild('product', $product);

        $grid = new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Grid();
        $this->getLayout()->addBlock($grid, 'product.grid');
        $this->setChild('product.grid', $grid);

        $categoryEdit = new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_CategoryEdit();
        $this->getLayout()->addBlock($categoryEdit, 'category');
        $this->setChild('category', $categoryEdit);

        $categoryGrid = new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_CategoryGrid();
        $this->getLayout()->addBlock($categoryGrid , 'category.grid');
        $this->setChild('category.grid', $categoryGrid);

        return parent::_prepareLayout();
    }

    public function getProductHtml()
    {
        return '';
//        $product = new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Product();
//        $product->publicPrepare();
//        $grid =  new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Grid();
//
//        return $grid->toHtml() . $product->toHtml();
//        //return $this->getChildHtml('grid') . $block->getFormHtml();
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
        return $this->__('Custom Catalog');
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
