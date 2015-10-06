<?php

class Magebuzz_Testimonial_Model_Testimonial extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('testimonial/testimonial');
    }

    public function loadTestimonials(){
		$this->getResource()->loadTestimonials();
    }

    public function getNewFileName($destFile)
    {
        $fileInfo = pathinfo($destFile);
        if (file_exists($destFile)) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while( file_exists($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $baseName) ) {
                $baseName = $fileInfo['filename']. '_' . $index . '.' . $fileInfo['extension'];
                $index ++;
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }
	
}