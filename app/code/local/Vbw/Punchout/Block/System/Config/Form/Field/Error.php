<?php

class Vbw_Punchout_Block_System_Config_Form_Field_Error
    extends  Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->addColumn('error', array(
            'label' => Mage::helper('vbw_punchout')->__('Error Code'),
            'style' => 'width:80px',
        ));

        $this->addColumn('cms', array(
            'label' => Mage::helper('vbw_punchout')->__('CMS Page'),
            'style' => 'width:140px',
        ));

        $this->addColumn('priority', array(
            'label' => Mage::helper('vbw_punchout')->__('Priority'),
            'style' => 'width:20px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('vbw_punchout')->__('Add error map');
        
        // run parent constructor
        parent::__construct();
    }
    
}