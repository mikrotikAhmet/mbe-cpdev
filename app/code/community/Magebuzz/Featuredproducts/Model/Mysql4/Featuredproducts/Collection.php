<?php

class Magebuzz_Featuredproducts_Model_Mysql4_Featuredproducts_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('featuredproducts/featuredproducts');
    }
}