<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  675
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


/**
 * @category Mirasvit
 * @package  Mirasvit_SearchAutocomplete
 */
class Mirasvit_SearchAutocomplete_Model_System_Config_Source_Attribute
{
    public function toOptionArray()
    {
        $productAttributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $productAttributeCollection->addIsSearchableFilter();
        $productAttributeCollection->addFieldToFilter('backend_type', array('varchar', 'text'));

        $values = array();
        $values['---'] = array(
            'value' => '',
            'label' => '',
        );

        foreach($productAttributeCollection as $attribute) {
            $values[$attribute->getAttributeCode()] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
            );
        }

        return $values;
    }
}
