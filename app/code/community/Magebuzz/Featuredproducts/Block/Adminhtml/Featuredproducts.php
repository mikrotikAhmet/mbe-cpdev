<?php
class Magebuzz_Featuredproducts_Block_Adminhtml_Featuredproducts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_featuredproducts';
    $this->_blockGroup = 'featuredproducts';
    $this->_headerText = Mage::helper('featuredproducts')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('featuredproducts')->__('Add Item');
    parent::__construct();
  }
}