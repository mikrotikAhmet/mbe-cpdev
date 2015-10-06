<?php
/**
 * User: Sofija
 * Date: 1/21/15
 * Time: 11:55 AM
 */
class Mbemro_CustomCatalog_Block_Adminhtml_Customer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('customcatalog/catalog');
        $form = new Varien_Data_Form(array(
            'id'      => 'customcatalog_edit_form',
            'action'  => '',//$this->getUrl('*/*/validate'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Add / Edit products in custom catalog.')));

//        $fieldset->addField('catalog_product_id', 'hidden', array(
//            'name'     => 'product_id',
//            'required' => false,
//        ));

        $fieldset->addField('catalog_sku', 'text', array(
            'name'     => 'sku',
            'title'    => $helper->__('Sku'),
            'label'    => $helper->__('Sku'),
            'required' => false, // to prevent interfere with other  options on  save
        ));

        $fieldset->addField('catalog_part_number', 'text', array(
            'name'     => 'part_number',
            'title'    => $helper->__('Part Number'),
            'label'    => $helper->__('Part Number'),
            'required' => false,
        ));

        $fieldset->addField('catalog_price', 'text', array(
            'name'     => 'price',
            'title'    => $helper->__('Price'),
            'label'    => $helper->__('Price'),
            'required' => false,
        ));

        $element = $fieldset->addField('catalog_notes', 'text', array(
            'name'     => 'notes',
            'title'    => $helper->__('Notes'),
            'label'    => $helper->__('Notes'),
            'required' => false,
        ));

        // for post methods use Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey()
        // for get methods use Mage::getSingleton('adminhtml/url')->getSecretKey()

        $removeTitle = $helper->__('Remove');
        $saveTitle = $helper->__('Add or Update');
        $customerId = Mage::registry('current_customer')->getId();
        $customCatalogEnabled = Mage::getResourceModel('customcatalog/customer')->isCustomerEnabled($customerId, 0) ? 'true' : 'false';

        $element->setAfterElementHtml("
        <div style=\"margin-top: 10px;\">
        <button id=\"mycatalog_save\" title=\"$saveTitle\" type=\"button\" class=\"scalable \" onclick=\"saveMyCatalog()\" >
            <span><span><span>$saveTitle</span></span></span></button>&nbsp;&nbsp;
        <button id=\"mycatalog_remove\" title=\"$removeTitle\" type=\"button\" class=\"scalable \" onclick=\"removeMyCatalog()\" >
            <span><span><span>$removeTitle</span></span></span></button>
        </div>
        <div id=\"mycatalog-ajax-status\"></div>
        <script type=\"text/javascript\">
            customCatalog = {
                reloadUrl: '". $this->getUrl('customcatalog/adminhtml_admin/save') . "',
                deleteUrl: '". $this->getUrl('customcatalog/adminhtml_admin/remove') . "',
                enableUrl: '". $this->getUrl('customcatalog/adminhtml_admin/enable') . "',
                disableUrl: '". $this->getUrl('customcatalog/adminhtml_admin/disable') . "',
                keypressUrl: '". $this->getUrl('customcatalog/adminhtml_admin/price') . "',
                formKey: '".Mage::getSingleton('core/session')->getFormKey()."',
                customerId: $customerId,
                customCatalogEnabled: $customCatalogEnabled,
            };

</script>");

        $form->setUseContainer(true);
        $this->setForm($form);


    }

}