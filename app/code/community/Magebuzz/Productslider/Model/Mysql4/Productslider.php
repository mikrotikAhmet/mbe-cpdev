<?php

class Magebuzz_Productslider_Model_Mysql4_Productslider extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the productslider_id refers to the key field in your database table.
        $this->_init('productslider/productslider', 'productslider_id');
    }
}