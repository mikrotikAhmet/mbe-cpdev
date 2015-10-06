<?php
class Magebuzz_Featuredproducts_Block_Rightsidebar extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getFeaturedProducts()     
    { 
        if (!$this->hasData('featuredproducts')) {
            $this->setData('featuredproducts', Mage::registry('featuredproducts'));
        }
        return $this->getData('featuredproducts');
        
    }
	
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
                    $collection = Mage::getModel('catalog/product')->getCollection();

			$attributes = Mage::getSingleton('catalog/config')
				->getProductAttributes();

			$collection->addAttributeToSelect($attributes)
				->addMinimalPrice()
				->addFinalPrice()
				->addTaxPercents()
				->addAttributeToFilter('magebuzz_featured_product', 1, 'left')
				->addStoreFilter();

			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
			$this->_productCollection = $collection;
		}
		return $this->_productCollection;
	}

    public function getFeaturedProductCollection()
    {
        return $this->_getProductCollection();
    }
	
	public function getFilteredProductCollection($category)
	{
		$_filteredProductCollection = $this->_getProductCollection();
		$_filteredProductCollection->addCategoryFilter($category)->addAttributeToSelect('*');
		return $_filteredProductCollection;
	}
}