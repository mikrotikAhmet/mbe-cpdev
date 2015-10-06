<?php
class Magebuzz_Productslider_Block_Adminhtml_Productslider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_productslider';
    $this->_blockGroup = 'productslider';
    $this->_headerText = Mage::helper('productslider')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('productslider')->__('Add Item');
    parent::__construct();
  }
}