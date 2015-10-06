<?php

class Magebuzz_Productslider_Model_System_Config_Type
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'random', 'label'=>Mage::helper('adminhtml')->__('Random Products')),
            array('value' => 'bestseller', 'label'=>Mage::helper('adminhtml')->__('Bestseller')),
            array('value' => 'mostviewed', 'label'=>Mage::helper('adminhtml')->__('Most Viewed')),
            array('value' => 'recentlyadded', 'label'=>Mage::helper('adminhtml')->__('Recently Added')),
            array('value' => 'special', 'label'=>Mage::helper('adminhtml')->__('Special Products')),
        );
    }
}