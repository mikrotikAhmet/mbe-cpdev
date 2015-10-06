<?php
class Magebuzz_Productslider_Block_Productslider extends Mage_Catalog_Block_Product_Abstract
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getProductslider()     
     {	 
        if (!$this->hasData('productslider')) {
            $this->setData('productslider', Mage::registry('productslider'));
        }
        return $this->getData('productslider');
        
    }
	public function __construct() {
		$productType = $this->getProductsType();
		if(!$this->getProductCollection()){
			switch ($productType) {
				case 'random':
					$this->setProductCollection($this->getRandomProducts());
					$this->setPageTitle('Random Products');
					break;
				case 'bestseller':
					$this->setProductCollection($this->getBestsellerProducts());
					$this->setPageTitle('Bestseller Products');
					break;
				case 'mostviewed':
					$this->setProductCollection($this->getMostviewedProducts());
					$this->setPageTitle('Most Viewed Products');
					break;
				case 'recentlyadded':
					$this->setProductCollection($this->getRecentlyAdded());
					$this->setPageTitle('Recently Added');
					break;
				case 'special':
					$this->setProductCollection($this->getSpecialProducts());
					$this->setPageTitle('Special Products');
					break;
				default:
					$this->setProductCollection($this->getRandomProducts());
					$this->setPageTitle('Random Products');
					break;
			}
		}
		return $this;
	}
	public function getBestsellerProducts(){
		$_limit = $this->getNumProduct();
		$current_category = Mage::registry('current_category');
		$is_category_filter = Mage::getStoreConfig('productslider/product_setting/category_filter');
		$collection = Mage::getResourceModel('reports/product_collection')
							->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
							->addOrderedQty()
							->addMinimalPrice()
							->addTaxPercents()
							->addStoreFilter();	
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		if($current_category && $is_category_filter == '1'){
			$current_category_id = Mage::registry('current_category')->getId();
			$currentCategory = Mage::getModel('catalog/category')->load($current_category_id);
			$collection->addCategoryFilter($currentCategory);
		}
		$collection->setPageSize($_limit);
		return $collection; 
	}
	public function getMostviewedProducts(){
		$_limit = $this->getNumProduct();
		$current_category = Mage::registry('current_category');
		$is_category_filter = Mage::getStoreConfig('productslider/product_setting/category_filter');
		$collection = Mage::getResourceModel('reports/product_collection')
							->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
							->addViewsCount()
							->addMinimalPrice()
							->addTaxPercents()
							->addStoreFilter();	
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		if($current_category && $is_category_filter == '1'){
			$current_category_id = Mage::registry('current_category')->getId();
			$currentCategory = Mage::getModel('catalog/category')->load($current_category_id);
			$collection->addCategoryFilter($currentCategory);
		}
		$collection->setPageSize($_limit);	
		return $collection;
	}
	public function getRandomProducts() {
		$_limit = $this->getNumProduct();
		$current_category = Mage::registry('current_category');
		$is_category_filter = Mage::getStoreConfig('productslider/product_setting/category_filter');
		$collection = Mage::getResourceModel('catalog/product_collection')
							->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
							->addMinimalPrice()
							->addTaxPercents()
							->addStoreFilter();	
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		$collection->getSelect()->order('rand()');
		if($current_category && $is_category_filter == '1'){
			$current_category_id = Mage::registry('current_category')->getId();
			$currentCategory = Mage::getModel('catalog/category')->load($current_category_id);
			$collection->addCategoryFilter($currentCategory);
		}
		$collection->setPageSize($_limit);
		return $collection;	
	}
	public function getRecentlyAdded() {
		$_limit = $this->getNumProduct();
		$current_category = Mage::registry('current_category');
		$is_category_filter = Mage::getStoreConfig('productslider/product_setting/category_filter');
		//var_dump($is_category_filter); die();
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$collection = Mage::getResourceModel('catalog/product_collection')
							->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
							->addAttributeToSelect('*') //Need this so products show up correctly in product listing
							->addAttributeToFilter('news_from_date', array('or'=> array(
								0 => array('date' => true, 'to' => $todayDate),
								1 => array('is' => new Zend_Db_Expr('null')))
							), 'left')
							->addAttributeToFilter('news_to_date', array('or'=> array(
								0 => array('date' => true, 'from' => $todayDate),
								1 => array('is' => new Zend_Db_Expr('null')))
							), 'left')
							->addAttributeToFilter(
								array(
									array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
									array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
									)
							  )
							->addAttributeToSort('news_from_date', 'desc')
							->addMinimalPrice()
							->addTaxPercents()
							->addStoreFilter();	
							
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		if($current_category && $is_category_filter == '1'){
			$current_category_id = Mage::registry('current_category')->getId();
			$currentCategory = Mage::getModel('catalog/category')->load($current_category_id);
			$collection->addCategoryFilter($currentCategory);
		}
		$collection->setPageSize($_limit);
		return $collection;	
	}
	public function getSpecialProducts() {
		$_limit = $this->getNumProduct();
		$current_category = Mage::registry('current_category');
		$is_category_filter = Mage::getStoreConfig('productslider/product_setting/category_filter');
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$collection = Mage::getResourceModel('catalog/product_collection')
								->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
								->addAttributeToFilter('special_from_date', array('or'=> array(
									0 => array('date' => true, 'to' => $todayDate),
									1 => array('is' => new Zend_Db_Expr('null')))
								), 'left')
								->addAttributeToFilter('special_to_date', array('or'=> array(
									0 => array('date' => true, 'from' => $todayDate),
									1 => array('is' => new Zend_Db_Expr('null')))
								), 'left')
								->addAttributeToFilter(
									array(
										array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
										array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
										)
								  )
								->addAttributeToSort('special_to_date','desc')
								->addTaxPercents()
								->addStoreFilter();	
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);	
		if($current_category && $is_category_filter == '1'){
			$current_category_id = Mage::registry('current_category')->getId();
			$currentCategory = Mage::getModel('catalog/category')->load($current_category_id);
			$collection->addCategoryFilter($currentCategory);
		}
		$collection->setPageSize($_limit);
		return $collection;	
	}
	public function inCategoryPage($check='_')
    {
        return $this->getRequest()->getRequestedRouteName().$check.
            $this->getRequest()->getRequestedControllerName().$check.
            $this->getRequest()->getRequestedActionName();
    }
	public function getProductsType(){
		$producttype = Mage::getStoreConfig('productslider/product_setting/type_product');
		return $producttype;
	}
	public function getWidthSlider() {
		$width_slider = (int) Mage::getStoreConfig('productslider/slider_setting/width_slider');
		return $width_slider;
	}
	public function getHeightSlider() {
		$height_slider = (int) Mage::getStoreConfig('productslider/slider_setting/height_slider');
		return $height_slider;
	}
	public function getSliderStyle(){
		return Mage::getStoreConfig('productslider/slider_setting/type_slider');
	}
	public function getNumProduct(){
		return (int)Mage::getStoreConfig('productslider/product_setting/num_products');
	}
	public function showDescription(){
		return (int)Mage::getStoreConfig('productslider/product_setting/show_description');
	}
}