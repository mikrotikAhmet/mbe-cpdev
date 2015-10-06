<?php
class Mbemro_Autoexport_Model_Mysql4_Autoexportrecords extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("autoexport/autoexportrecords", "id");
    }
}