<?php

class Magebuzz_Productslider_Model_Productslider extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productslider/productslider');
    }
}