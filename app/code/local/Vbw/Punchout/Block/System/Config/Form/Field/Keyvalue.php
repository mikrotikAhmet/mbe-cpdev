<?php


class Vbw_Punchout_Block_System_Config_Form_Field_Keyvalue extends  Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->addColumn('key', array(
            'label' => Mage::helper('vbw_punchout')->__('Key'),
            'style' => 'width:120px',
        ));

        $this->addColumn('value', array(
            'label' => Mage::helper('vbw_punchout')->__('Value'),
            'style' => 'width:120px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('vbw_punchout')->__('Add Key/Value Pair');
        
        // run parent constructor
        parent::__construct();
    }
    
}