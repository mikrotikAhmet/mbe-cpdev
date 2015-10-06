<?php

/**
 * CustomCatalog Product Class.
 *
 * @author Sofija Blazevski <sofi@cp-dev.com>
 * @package Mbemro
 * @version 1.0.0
 */
class Mbemro_CustomCatalog_Model_Customer extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('customcatalog/customer');
    }
}
