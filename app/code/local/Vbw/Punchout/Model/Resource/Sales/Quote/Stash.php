<?php

class Vbw_Punchout_Model_Resource_Sales_Quote_Stash
        extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Main table and field initialization
     *
     */
    protected function _construct()
    {
        $this->_init('vbw_punchout/sales_quote_stash', 'id');
    }

}
