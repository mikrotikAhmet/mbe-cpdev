<?php
class Magebuzz_Productslider_Block_Catalog_Product_List_Bestseller extends Mage_Catalog_Block_Product_List
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
								->addOrderedQty()
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

        $toolbar->setAvailableOrders(array(
			'ordered_qty'  => $this->__('Most Purchased'),
			'name'      => $this->__('Name'),
			'price'     => $this->__('Price')
			))
		->setDefaultOrder('ordered_qty')
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