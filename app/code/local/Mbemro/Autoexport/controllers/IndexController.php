<?php

class Mbemro_Autoexport_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction(){
		Mage::helper('autoexport')->doAutoExport();
	}
}