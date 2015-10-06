<?php

class Magebuzz_Productslider_Block_Adminhtml_Productslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('productslider_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('productslider')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('productslider')->__('Item Information'),
          'title'     => Mage::helper('productslider')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('productslider/adminhtml_productslider_edit_tab_main')->toHtml(),
      ));  
      return parent::_beforeToHtml();
  }
}