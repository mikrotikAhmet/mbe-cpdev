<?php
class Data8_DataCaptureValidation_EmailValidationTypeList
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'None', 'label' => Mage::helper('adminhtml')->__('None')),
            array('value' => 'Syntax', 'label' => Mage::helper('adminhtml')->__('Syntax (lowest)')),
            array('value' => 'MX', 'label' => Mage::helper('adminhtml')->__('Domain')),
            array('value' => 'Server', 'label' => Mage::helper('adminhtml')->__('Server')),
            array('value' => 'Address', 'label' => Mage::helper('adminhtml')->__('Address (highest)'))
        );
    }
}
?>