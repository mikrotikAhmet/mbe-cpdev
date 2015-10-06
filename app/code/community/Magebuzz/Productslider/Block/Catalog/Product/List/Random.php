<?php
class Magebuzz_Productslider_Block_Catalog_Product_List_Random extends Mage_Catalog_Block_Product_List
{
	public function _prepareLayout()
    {
		parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('productslider/productslider.phtml');
        }
        return $this;
    }
    protected function _getProductCollection()
    {
		$this->_productCollection = Mage::getResourceModel('catalog/product_collection')
							->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
							->addMinimalPrice()
							->addTaxPercents()
							->addStoreFilter();	
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);	
        return $this->_productCollection;
    }
	protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        $collection = $this->_getProductCollection();

        $toolbar->setAvailableOrders(array(
			'name'      => $this->__('Name'),
			'price'     => $this->__('Price')
			))
		->setDefaultOrder('price')
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