<?php

class Vbw_Punchout_Block_System_Config_Form_Field_Mapping extends  Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->addColumn('source', array(
            'label' => Mage::helper('vbw_punchout')->__('Source'),
            'style' => 'width:120px',
        ));

        $this->addColumn('destination', array(
            'label' => Mage::helper('vbw_punchout')->__('Destination'),
            'style' => 'width:120px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('vbw_punchout')->__('Add Another Mapping Pair');
        
        // run parent constructor
        parent::__construct();
    }
    
}