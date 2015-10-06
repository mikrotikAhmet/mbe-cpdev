<?php
class Magebuzz_Featuredproducts_Block_Featuredproducts extends Mage_Core_Block_Template
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
}