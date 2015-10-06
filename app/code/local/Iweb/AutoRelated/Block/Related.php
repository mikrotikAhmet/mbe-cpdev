<?php

class Iweb_AutoRelated_Block_Related extends Mage_Catalog_Block_Product_List_Related
{
	protected function _construct() {
		// Only cache if we have something thats keyable..
		$_time = (int)Mage::getStoreConfig('autorelated/general/cache_lifetime');
		if($_time > 0 && $cacheKey = $this->_cacheKey()) {
			$this->addData(array(
				'cache_lifetime'    => $_time,
				'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
				'cache_key'         => $cacheKey,
			));
		}
    }

    protected function _cacheKey(){
		$product = Mage::registry('current_product');
		if($product) {
			return get_class() . '::' .  Mage::app()->getStore()->getCode() . '::' . $product->getId();
		}
		
		return false;
    }

	protected function _prepareData (){
		parent::_prepareData();
		
		$_enabled = Mage::getStoreConfig('autorelated/general/enabled');
		if ($_enabled && count($this->getItems()) == 0){
			$_products = Mage::getModel('autorelated/collection')->getRelatedProducts();
			if ($_products){
				$this->_itemCollection = $_products;
			}
		}

		return $this;
	}
}
