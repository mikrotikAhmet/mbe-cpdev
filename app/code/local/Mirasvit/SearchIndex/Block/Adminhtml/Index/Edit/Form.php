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


class Mirasvit_SearchIndex_Block_Adminhtml_Index_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $this->setTemplate('searchindex/form.phtml');

        $model = Mage::registry('current_model');

        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getData('action'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $general = $form->addFieldset('general', array('legend' => __('General Information')));

        if ($model->getId()) {
            $general->addField('index_id', 'hidden', array(
                'name'      => 'index_id',
                'value'     => $model->getId(),
            ));
        }

        $general->addField('index_code', 'select', array(
            'label'    => __('Index'),
            'required' => true,
            'name'     => 'index_code',
            'value'    => $model->getIndexCode(),
            'values'   => Mage::getSingleton('searchindex/system_config_source_index')->toOptionArray(),
            'disabled' => $model->getIndexCode() ? true : false,
            'note'     => Mage::helper('searchindex/help')->field('index_code')
        ));

        $general->addField('title', 'text', array(
            'name'     => 'title',
            'label'    => __('Title'),
            'required' => true,
            'value'    => $model->getTitle(),
            'note'     => Mage::helper('searchindex/help')->field('title')
        ));

        if (!$model->getId()
            || !$model->getIndexInstance()->isLocked()) {
            $general->addField('position', 'text', array(
                'name'     => 'position',
                'label'    => __('Position'),
                'required' => true,
                'value'    => $model->getPosition(),
                'note'     => Mage::helper('searchindex/help')->field('position')
            ));

            $general->addField('is_active', 'select', array(
                'name'     => 'is_active',
                'label'    => __('Active'),
                'required' => true,
                'value'    => $model->getIsActive(),
                'values'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
                'note'     => Mage::helper('searchindex/help')->field('is_active')
            ));
        }

        if ($model->getId()) {
            // adding attribute fieldset
            $attributes = $form->addFieldset('attributes', array('legend' => __('Attributes')));
            $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('searchindex/form/renderer/fieldset/attributes.phtml')
                ->setAvailableAttributes($model->getIndexInstance()->getAvailableAttributes())
                ->setAttributes($model->getAttributes());
            $attributes->setName('attributes')
                ->setRenderer($renderer);

            // adding additional index fieldsets
            foreach ($model->getIndexInstance()->getFieldsets() as $code) {
                $class = "Mirasvit_SearchIndex_Block_Adminhtml_Index_Edit_Index_$code";
                $fieldset = new $class();
                $fieldset->setModel($model->getIndexInstance())
                    ->setId($class);
                $form->addElement($fieldset);
            }
        }
        


        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}