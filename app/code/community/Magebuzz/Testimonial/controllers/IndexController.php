<?php
class Magebuzz_Testimonial_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
       	$this->loadLayout();
		$this->renderLayout();
    }
	
	public function checkAction() {
		if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
			if(Mage::getStoreConfig('testimonial/testimonial_options/allow_guest_write_testimonial', Mage::app()->getStore())=="1") {
				$this->_redirect('*/form');
			}
			else {
				Mage::getSingleton('customer/session')->authenticate($this);
			}
		}
		else {
			$this->_redirect('*/form');
		}
	}
	
	
	public function thankmessageAction() {
		$message=Mage::getStoreConfig('testimonial/testimonial_options/thank_message', Mage::app()->getStore());
		if(Mage::getStoreConfig('testimonial/testimonial_options/approve_testimonial', Mage::app()->getStore())==true) {
			Mage::getSingleton('core/session')->addSuccess($message);
		}
		else 
			Mage::getSingleton('core/session')->addSuccess($message);
		
		$this->_redirect('*/index');
		
	}
}