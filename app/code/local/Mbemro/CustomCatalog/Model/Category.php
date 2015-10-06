<?php

/**
 * CustomCatalog Category Class.
 *
 * @author Sofija Blazevski <sofi@cp-dev.com>
 * @package Mbemro
 * @version 1.1.0
 */
class Mbemro_CustomCatalog_Model_Category extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('customcatalog/category');
    }
}
