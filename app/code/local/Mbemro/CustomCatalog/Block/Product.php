<?php

class Mbemro_CustomCatalog_Block_Product extends Mage_Core_Block_Template
{
    /**
     * @var Mbemro_CustomCatalog_Helper_Catalog
     */
    private $productHelper;

    /**
     * @var Mbemro_CustomCatalog_Model_Product
     */
    private $myproduct;

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $product;

    /**
     * @var Mage_Catalog_Helper_Image
     */
    private $imageHelper;

    public function __construct()
    {
        $this->productHelper = $this->helper('customcatalog/catalog');
        $this->product       = Mage::registry('product');
        $this->myproduct     = Mage::registry('myproduct');
        $this->imageHelper   = $this->helper('catalog/image')->init($this->product, 'small_image');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('head')->setTitle('My Catalog - Product');

    }

    public function getProductName()
    {
        return $this->product->getName();
    }

    public function getPartNumber()
    {
        return $this->myproduct->getPartNumber();
    }

    public function getNotes()
    {
        return $this->myproduct->getNotes();
    }

    public function getImageFile()
    {
        return $this->imageHelper->resize(170);
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getActionPath()
    {
        return $this->productHelper->getSaveUrl();
    }
}