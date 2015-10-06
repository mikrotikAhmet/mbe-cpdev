<?php
    class Magebuzz_Testimonial_Block_Sidebar extends Mage_Core_Block_Template {
        public function getTestimonialsLast(){
            $collection = Mage::getModel('testimonial/testimonial')->getCollection();
			$collection->setOrder('created_time', 'DESC');
			$collection->addFieldToFilter('status',1);
			$collection->setPageSize(5);
			return $collection;
		}
		
		public function getContentTestimonialSidebar($_description, $count){
		   $short_desc = substr($_description, 0, $count);
		   
		   if(substr($short_desc, 0, strrpos($short_desc, ' '))!='') {
				$short_desc = substr($short_desc, 0, strrpos($short_desc, ' '));
				$short_desc = $short_desc.'...';
		    }


		   return $short_desc;
		}

        public function getNumberWordsOnSidebar(){
            $countWord = Mage::getStoreConfig('testimonial/general_option/number_words');
            return $countWord;
        }
    }
?>