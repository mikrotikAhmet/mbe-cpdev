<?php
/**
 * User: Sofija
 * Date: 1/21/15
 * Time: 12:55 PM
 */
class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'customcatalog';
        $this->_controller = 'adminhtml_customer_edit_tab';
        $this->_headerText = Mage::helper('customcatalog/catalog')->__('Products');

        parent::__construct();

//        $this->_objectId = 'id';
//        $this->_blockGroup = 'form';
//        $this->_controller = 'adminhtml_form';

        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('back');
        $this->removeButton('reset');

//        $this->_updateButton('save', 'label', Mage::helper('customcatalog/catalog')->__('Save'));
//        $this->_updateButton('delete', 'label', Mage::helper('customcatalog/catalog')->__('Delete'));
//
//        $this->_addButton('saveandcontinue', array(
//            'label'     => Mage::helper('customcatalog/catalog')->__('Save And Continue Edit'),
//            'onclick'   => 'saveAndContinueEdit()',
//            'class'     => 'save',
//        ), -100);

    }

    public function publicPrepare()
    {
        $this->setChild('form', new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Form());
    }

    protected function _prepareLayout()
    {
        //$this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form'));
        //$this->setChild('grid', new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Grid());
        $this->setChild('form', new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Form());
        //$this->getLayout()->addBlock(new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Grid(), 'custom_catalog_grid');
        return parent::_prepareLayout();
    }

    public function getFormHtml()
    {
        $this->getChild('form')->setData('action', $this->getSaveUrl());
        return $this->getChildHtml('form');// . $this->getChildHtml('grid');
    }

}