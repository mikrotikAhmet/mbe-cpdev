<?php
class Magebuzz_Testimonial_Block_Testimonial extends Mage_Core_Block_Template {

	
	public function __construct() {
		parent::__construct();
		$collection = Mage::getModel('testimonial/testimonial')->getCollection();
		$collection->setOrder('testimonial_id', 'DESC');
		$collection->addFieldToFilter('status',1);
		$this->setTestimonial($collection);
		
	}	
	
	
	public function _prepareLayout() {
		parent::_prepareLayout();
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('testimonial')->__('Testimonial'));
		$pager = $this->getLayout()->createBlock('page/html_pager', 'testimonial.pager');

        if(Mage::getStoreConfig('testimonial/general_option/enable_testimonial_paging')){
            $fieldPerPage = Mage::getStoreConfig('testimonial/general_option/divide_page');
            $fieldPerPage = explode(',', $fieldPerPage);
            $fieldPerPage = array_combine($fieldPerPage, $fieldPerPage);
            $pager->setAvailableLimit($fieldPerPage);
        }else{
            $pager->setAvailableLimit(array('all'=>'all'));
        }

		$pager->setCollection($this->getTestimonial());
		$this->setChild('pager', $pager);
		$this->getTestimonial()->load();

		return $this;
    }
    
    
	public function getPagerHtml() {
		return $this->getChildHtml('pager');
	
	}
	
	
	public function getFormUrl() {
		return $this->helper('testimonial')->getFormUrl();
	}
	
	
}