<?php
class Data8_DataCaptureValidation_LicenseTypeList
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'None', 'label' => Mage::helper('adminhtml')->__('None')),
            array('value' => 'FreeTrial', 'label' => Mage::helper('adminhtml')->__('Free Trial (Address Level Data)')),
            array('value' => 'FreeTrialThorofare', 'label' => Mage::helper('adminhtml')->__('Free Trial (Street Level Data)')),
            array('value' => 'WebClickFull', 'label' => Mage::helper('adminhtml')->__('Per Click License (Address Level Data)')),
            array('value' => 'WebClickThorofare', 'label' => Mage::helper('adminhtml')->__('Per Click License (Street Level Data)')),
            array('value' => 'WebServerFull', 'label' => Mage::helper('adminhtml')->__('Unlimited License (Address Level Data)')),
            array('value' => 'WebServerThorofare', 'label' => Mage::helper('adminhtml')->__('Unlimited License (Street Level Data)'))
        );
    }
}
?>