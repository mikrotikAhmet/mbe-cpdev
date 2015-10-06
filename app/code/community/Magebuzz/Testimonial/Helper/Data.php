<?php
class Magebuzz_Testimonial_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getFormUrl() {
		return $this->_getUrl('testimonial/index/check'); 	
	}
	
	public function getBack() {
		return $this->_getUrl('testimonial/index'); 	
	}
}