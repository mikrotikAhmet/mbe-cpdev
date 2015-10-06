<?php

/**
 * Mage Mysql4 Model class.
 *
 * @author Sofija Blazevski <sofi@cp-dev.com>
 * @package Mbemro
 * @version 1.0.0
 */
class Mbemro_CustomCatalog_Model_Mysql4_Customcatalog extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('customcatalog/customcatalog', 'id');
    }
}
