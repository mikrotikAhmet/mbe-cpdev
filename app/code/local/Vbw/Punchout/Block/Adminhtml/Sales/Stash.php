<?php

class Vbw_Punchout_Block_Adminhtml_Sales_Stash
extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_blockGroup = 'vbw_punchout';
        $this->_controller = 'adminhtml_sales_stash';
        $this->_headerText = Mage::helper('vbw_punchout')->__('Punchout Stash');
        parent::__construct();
        $this->_removeButton('add');
    }
}


?>