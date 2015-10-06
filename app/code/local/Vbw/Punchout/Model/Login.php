<?php
/**
 *
 *
 */
class Vbw_Punchout_Model_Login
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'anonymous', 'label'=>Mage::helper('vbw_punchout')->__('Anonymous (No Login)')),
            array('value'=>'single', 'label'=>Mage::helper('vbw_punchout')->__('Auto Customer Login')),
            // array('value'=>'dual', 'label'=>Mage::helper('vbw_punchout')->__('Dual Login (User Login)')),
            array('value'=>'discover', 'label'=>Mage::helper('vbw_punchout')->__('Discover Customer (Auto Login)')),
        );
    }
}
