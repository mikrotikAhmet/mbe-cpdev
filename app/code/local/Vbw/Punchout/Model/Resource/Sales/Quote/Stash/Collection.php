<?php

class Vbw_Punchout_Model_Resource_Sales_Quote_Stash_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define resource model for collection
     *
     */
    protected function _construct()
    {
        $this->_init('vbw_punchout/sales_quote_stash');
    }


}
