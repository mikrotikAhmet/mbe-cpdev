<?php
class Mbemro_Autoexport_Model_Cron{	
	public function autoexportrecords(){
		Mage::helper('autoexport')->doAutoExport();
	} 
}