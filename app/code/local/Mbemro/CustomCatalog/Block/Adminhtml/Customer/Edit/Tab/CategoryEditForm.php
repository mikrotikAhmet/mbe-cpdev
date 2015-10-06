<?php

class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_CategoryEditForm extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('customcatalog/catalog');
        $form = new Varien_Data_Form(array(
            'id'      => 'customcatalog_edit_category_form',
            'action'  => '',//$this->getUrl('*/*/validate'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('category_fieldset',
            array(
                'legend' => $helper->__('Add / Edit Category discounts in custom catalog.'),

            ));

        $element = $fieldset->addField('catalog_category_id', 'text', array(
            'name'     => 'catalog_category_id',
            'title'    => $helper->__('Category Id'),
            'label'    => $helper->__('Category Id'),
            'required' => false, // to prevent interfere with other  options on  save
        ));

        $element = $fieldset->addField('catalog_category_name', 'text', array(
            'name'     => '',
            'title'    => $helper->__('Category Name'),
            'label'    => $helper->__('Category Name'),
            'required' => false,
            'readonly' => true,
            'disabled' => false,
            'class'    => 'disabled',
        ));

//        $element = $fieldset->addField('catalog_category_discount_percentage', 'radio', array(
//            'name'     => 'catalog_category_discount_type',
//            'title'    => $helper->__('Discount by percentage'),
//            'label'    => $helper->__('Discount by percentage'),
//            'required' => false,
//            'checked'  => true,
//        ));

        $element = $fieldset->addField('catalog_category_discount_amount', 'text', array(
            'name'     => 'catalog_category_amount',
            'title'    => $helper->__('Percentage'),
            'label'    => $helper->__('Percentage'),
            'required' => false, // to prevent interfere with other  options on  save
        ));

        $element = $fieldset->addField('catalog_category_subcategories_apply', 'checkbox', array(
            'name'     => 'catalog_category_subcategories_apply',
            'title'    => $helper->__('Apply to subcategories'),
            'label'    => $helper->__('Apply to subcategories'),
            'required' => false,
        ));

        $saveTitle = $this->__('Save or Update');
        $removeTitle = $this->__('Remove');
        $allTitle = $this->__('Add All Categories');
        $allDesc = $this->__('If a category already exists, it will not be overwritten.');

        $element->setAfterElementHtml("
        <div style=\"margin-top: 10px;\">
        <button id=\"mycatalog-cat-save\" title=\"$saveTitle\" type=\"button\" class=\"scalable \" onclick=\"saveMyCatalogCat()\" >
            <span><span><span>$saveTitle</span></span></span></button>&nbsp;&nbsp;
        <button id=\"mycatalog-cat-remove\" title=\"$removeTitle\" type=\"button\" class=\"scalable \" onclick=\"removeMyCatalogCat()\" >
            <span><span><span>$removeTitle</span></span></span></button>
        </div>
        <!--
        <div style=\"margin-top: 10px;\">
        Or<br/><br/>
        <button id=\"mycatalog-cat-all\" title=\"$allDesc\" type=\"button\" class=\"scalable \" onclick=\"addAllMyCatalogCat()\" >
            <span><span><span>$allTitle</span></span></span></button><br/>

        </div>
        -->
        <div id=\"mycatalog-cat-ajax-status\"></div>
        <script type=\"text/javascript\">
            customCatalogCat = {

                reloadUrl: '". $this->getUrl('customcatalog/adminhtml_category/save') . "',
                deleteUrl: '". $this->getUrl('customcatalog/adminhtml_category/remove') . "',
                keypressUrl: '". $this->getUrl('customcatalog/adminhtml_category/details') . "',
                formKey: '".Mage::getSingleton('core/session')->getFormKey()."'
            };
        </script>
        ");

        $form->setUseContainer(true);
        $this->setForm($form);

    }
}