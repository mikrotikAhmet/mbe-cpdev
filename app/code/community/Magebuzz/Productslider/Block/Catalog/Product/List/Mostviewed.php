<?php
class Magebuzz_Productslider_Block_Catalog_Product_List_Mostviewed extends Mage_Catalog_Block_Product_List
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
			$this->_productCollection = Mage::getResourceModel('reports/product_collection')
								->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
								->addViewsCount()
								->addMinimalPrice()
								->addTaxPercents()
								->addStoreFilter();	
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);	
        }
        return $this->_productCollection;
    }
	protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        $collection = $this->_getProductCollection();

        $toolbar
		->setAvailableOrders(array(
			'views_count'      => $this->__('View Count'),
			'name'      => $this->__('Name'),
			'price'     => $this->__('Price')
			))
		->setDefaultOrder('views_count')
		->setDefaultDirection('desc');
		
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection'=>$this->_getProductCollection(),
        ));

        $this->_getProductCollection()->load();
        Mage::getModel('review/review')->appendSummary($this->_getProductCollection());
        return parent::_beforeToHtml();
    }
}