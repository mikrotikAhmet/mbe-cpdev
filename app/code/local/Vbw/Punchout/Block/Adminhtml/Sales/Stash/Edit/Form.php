<?php

class Vbw_Punchout_Block_Adminhtml_Sales_Stash_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getStashData())
        {
            $data = Mage::getSingleton('adminhtml/session')->getStashData();
            Mage::getSingleton('adminhtml/session')->getStashData(null);
        }
        elseif (Mage::registry('example_data'))
        {
            $data = Mage::registry('example_data')->getData();
        }
        else
        {
            $data = array();
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('stash_form', array(
            'legend' =>Mage::helper('vbw_punchout')->__('Stash Information')
        ));

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('vbw_punchout')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
            'note'     => Mage::helper('vbw_punchout')->__('The name of the stash.'),
        ));

        $fieldset->addField('description', 'text', array(
            'label'     => Mage::helper('vbw_punchout')->__('Description'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'description',
        ));

        $fieldset->addField('other', 'text', array(
            'label'     => Mage::helper('vbw_punchout')->__('Other'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'other',
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}