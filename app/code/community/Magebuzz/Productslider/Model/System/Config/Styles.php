<?php

class Magebuzz_Productslider_Model_System_Config_Styles
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'simple-slider', 'label'=>Mage::helper('adminhtml')->__('Simple Slider')),
            array('value' => 'slider-scrollbar', 'label'=>Mage::helper('adminhtml')->__('Slider With Horizontal Scrollbar')),
            array('value' => 'slider-lightbox', 'label'=>Mage::helper('adminhtml')->__('Slider With Lightbox')),
        );
    }
}