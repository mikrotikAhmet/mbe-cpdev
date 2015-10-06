<?php

class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_CategoryEdit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'customcatalog';
        $this->_controller = 'adminhtml_customer_edit_tab';
        $this->_headerText = Mage::helper('customcatalog/catalog')->__('Categories');

        parent::__construct();

        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('back');
        $this->removeButton('reset');

    }

    protected function _prepareLayout()
    {
        $this->setChild('form', new Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_CategoryEditForm());
        return parent::_prepareLayout();
    }

    public function getFormHtml()
    {
//        $this->getChild('form')->setData('action', $this->getSaveUrl());
        return $this->getChildHtml('form');// . $this->getChildHtml('grid');
    }

}