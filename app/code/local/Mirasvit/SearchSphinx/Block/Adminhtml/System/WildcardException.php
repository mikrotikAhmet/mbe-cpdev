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
 * ÐÐ»Ð¾Ðº ÑÐ°Ð±Ð»Ð¸ÑÑ Ð²Ð²Ð¾Ð´Ð° Ð¸ÑÐºÐ»ÑÑÐµÐ½Ð¸Ð¹ Ð¸Ð· Ð²Ð¸Ð»Ð´-ÐºÐ°ÑÐ´ Ð² Ð½Ð°ÑÑÑÐ¾Ð¹ÐºÐ°Ñ
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Block_Adminhtml_System_WildcardException extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('word', array(
            'label' => Mage::helper('adminhtml')->__('Word'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add');
        parent::__construct();
    }
}