<?php

class Magebuzz_Productslider_Model_System_Config_Position
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'only_home', 'label'=>Mage::helper('adminhtml')->__('Home')),
            array('value' => 'only_category_page', 'label'=>Mage::helper('adminhtml')->__('Category Page')),
            array('value' => 'both_home_category', 'label'=>Mage::helper('adminhtml')->__('Home page & Category page')),
        );
    }
}