<?php

class Mbemro_Autoexport_Admin_ExportController extends Mage_Adminhtml_Controller_Action {
	
	public function exportAction() {
		Mage::helper('autoexport')->doManualExport();
		$this->_redirectReferer();
	}
} 