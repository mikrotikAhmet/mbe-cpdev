<?php
class Magebuzz_Testimonial_Block_Form extends Mage_Core_Block_Template {


	public function _prepareLayout() {
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('testimonial')->__('Testimonial Form'));
		$this->setTemplate('testimonial/form.phtml');
		return parent::_prepareLayout();
	}
	
	
	public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
	
	
    public function getCustomer () {
        return Mage::getSingleton('customer/session')->getCustomer();
	}
	
	
	public function getBack() {
		return $this->helper('testimonial')->getBack();
	}
	
	
}