<?php

class Magebuzz_Featuredproducts_Model_Featuredproducts extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('featuredproducts/featuredproducts');
    }
}