<?php

class Mbemro_Autoexport_Block_Adminhtml_Export extends Mage_Adminhtml_Block_System_Config_Form_Field {

 protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        return $this->_getAddRowButtonHtml($this->__('Run Manual Export'));
    }

  protected function _getAddRowButtonHtml($title) {

	$buttonBlock = $this->getElement()->getForm()->getParent()->getLayout()->createBlock('adminhtml/widget_button');
    
	$url = Mage::helper('adminhtml')->getUrl("autoexport/admin_export/export");

	return $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setLabel($this->__($title))
                ->setOnClick("window.location.href='".$url."'")
                ->toHtml();
    }
}
