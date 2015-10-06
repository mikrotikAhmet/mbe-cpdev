<?php
class Magebuzz_Featuredproducts_Model_System_Config_Source_Leftright
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Left')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Right')),
        );
    }

}
