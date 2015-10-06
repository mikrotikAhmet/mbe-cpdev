<?php

class Magebuzz_Productslider_Block_Adminhtml_Productslider_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
	  $form->setHtmlIdPrefix('productslider_');
      $this->setForm($form);
      $fieldset = $form->addFieldset('productslider_form', array('legend'=>Mage::helper('productslider')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('productslider')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('productslider')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('productslider')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('productslider')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('productslider')->__('Disabled'),
              ),
          ),
      ));
      

      
	  $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('productslider')->__('Content'),
          'title'     => Mage::helper('productslider')->__('Content'),
          'style'     => 'width:500px; height:300px;',
          'config'    => Mage::getSingleton('productslider/wysiwyg_config')->getConfig(),
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getProductsliderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getProductsliderData());
          Mage::getSingleton('adminhtml/session')->setProductsliderData(null);
      } elseif ( Mage::registry('productslider_data') ) {
          $form->setValues(Mage::registry('productslider_data')->getData());
      }
      return parent::_prepareForm();
  }
}