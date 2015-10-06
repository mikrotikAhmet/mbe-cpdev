<?php

class RocketWeb_GoogleBaseFeedGenerator_Block_Adminhtml_Catalog_Category_Checkboxes_Tree
    extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree {

    public function getLoadTreeUrl($expanded = null) {
        $params = array('_current'=>true, 'id'=>null,'store'=>null);
        if (
            (is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }
        return Mage::helper("adminhtml")->getUrl('googlebasefeedgenerator_admin/adminhtml_googlebasefeedgenerator/categoriesJson', $params);
    }

}