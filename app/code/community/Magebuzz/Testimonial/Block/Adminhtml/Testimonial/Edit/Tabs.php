<?php

class Magebuzz_Testimonial_Block_Adminhtml_Testimonial_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('testimonial_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('testimonial')->__('Testimonial Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('testimonial')->__('General'),
          'title'     => Mage::helper('testimonial')->__('General'),
          'content'   => $this->getLayout()->createBlock('testimonial/adminhtml_testimonial_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}