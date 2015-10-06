<?php

class Magebuzz_Testimonial_Block_Adminhtml_Testimonial_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $form->setHtmlIdPrefix('testimonial_');
        $fieldset = $form->addFieldset('testimonial_form', array('legend'=>Mage::helper('testimonial')->__('General Information')));

        if ( Mage::getSingleton('adminhtml/session')->getTestimonialData() )
        {
            $data = Mage::getSingleton('adminhtml/session')->getTestimonialData();
            Mage::getSingleton('adminhtml/session')->setTestimonialData(null);
        } elseif ( Mage::registry('testimonial_data') ) {
            $data = Mage::registry('testimonial_data')->getData();
        }

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('testimonial')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('testimonial')->__('Email'),
            'required'  => true,
            'name'      => 'email',
        ));

        //If avatar exists -> view
        $data['change_avatar'] = 'Change Avatar';
        if(isset($data['avatar_name']) && $data['avatar_name'] != ''){
            $avatarLink = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .'magebuzz/avatar/'.$data['avatar_name'];
			$avatarName = $data['avatar_name'];
            $fieldset->addField('image', 'label', array(
                'label' => Mage::helper('testimonial')->__('Avatar'),
                'name'  =>'image',
                'value'     => $avatarLink,
                'after_element_html' => '<img src="'.$avatarLink .'" alt=" '. $avatarName .'" height="120" width="120" />',
            ));

            $fieldset->addField('avatar', 'image', array(
                'label' => Mage::helper('testimonial')->__('Change Avatar'),
                'required' => false,
                'name' => 'avatar',
            ));

        }else{
            $fieldset->addField('avatar', 'image', array(
                'label' => Mage::helper('testimonial')->__('Avatar'),
                'required' => false,
                'name' => 'avatar',
            ));
        }


        $fieldset->addField('website', 'link', array(
            'label'     => Mage::helper('testimonial')->__('Website'),
            'required'  => false,
            'name'      => 'website',
            'href'      => 'website',
        ));

        $fieldset->addField('company', 'text', array(
            'label'     => Mage::helper('testimonial')->__('Company'),
            'required'  => false,
            'name'      => 'company',
        ));

        $fieldset->addField('address', 'text', array(
            'label'     => Mage::helper('testimonial')->__('Address'),
            'required'  => false,
            'name'      => 'address',
        ));

        $fieldset->addField('testimonial', 'textarea', array(
            'label'     => Mage::helper('testimonial')->__('Testimonial'),
            'required'  => true,
            'name'      => 'testimonial',
        ));



        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('testimonial')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('testimonial')->__('Approved'),
                ),

                array(
                    'value'     => 2,
                    'label'     => Mage::helper('testimonial')->__('Not Approved'),
                ),

                array(
                    'value'     => 3,
                    'label'     => Mage::helper('testimonial')->__('Pending'),
                ),
            ),
        ));


        $form->setValues($data);
        return parent::_prepareForm();


    }
}