<?php

class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Bundleassocprodsmode extends Varien_Object {

    const ONLY_BUNDLE = 0;
    const ONLY_ASSOCIATED = 1;
    const BOTH_BUNDLE_ASSOCIATED = 2;

    public function toOptionArray() {

        $vals = array(
            self::ONLY_BUNDLE => Mage::helper('googlebasefeedgenerator')->__('Only parent bundle product / No sub-item products'),
            self::ONLY_ASSOCIATED => Mage::helper('googlebasefeedgenerator')->__('No parent bundle product / Only sub-item products'),
            self::BOTH_BUNDLE_ASSOCIATED => Mage::helper('googlebasefeedgenerator')->__('Both types - parent bundle product and all sub-item products'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array('value' => $k, 'label' => $v);

        return $options;
    }
}