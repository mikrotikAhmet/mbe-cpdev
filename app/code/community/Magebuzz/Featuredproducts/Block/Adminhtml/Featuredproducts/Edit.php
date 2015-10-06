<?php

class Magebuzz_Featuredproducts_Block_Adminhtml_Featuredproducts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'featuredproducts';
        $this->_controller = 'adminhtml_featuredproducts';
        
        $this->_updateButton('save', 'label', Mage::helper('featuredproducts')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('featuredproducts')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('featuredproducts_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'featuredproducts_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'featuredproducts_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('featuredproducts_data') && Mage::registry('featuredproducts_data')->getId() ) {
            return Mage::helper('featuredproducts')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('featuredproducts_data')->getTitle()));
        } else {
            return Mage::helper('featuredproducts')->__('Add Item');
        }
    }
}