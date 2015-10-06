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
 * ÐÐ¸Ð´Ð¶ÐµÑ ÑÐ¾ÑÐ¼Ñ Ð¿Ð¾Ð¸ÑÐºÐ° (Ñ.Ðµ. ÐµÐ³Ð¾ Ð¼Ð¾Ð¶Ð½Ð¾ Ð´Ð¾Ð±Ð°Ð²Ð¸ÑÑ Ð½Ð° ÑÑÑÐ°Ð½Ð¸ÑÑ ÑÐµÑÐµÐ· ÑÐµÐ´Ð°ÐºÑÐ¾Ñ)
 * 
 * @category Mirasvit
 * @package  Mirasvit_SearchAutocomplete
 */
class Mirasvit_SearchAutocomplete_Block_Widget_Form extends Mirasvit_SearchAutocomplete_Block_Form implements Mage_Widget_Block_Interface
{
    public function _prepareLayout()
    {
        $this->setTemplate('searchautocomplete/widget/form.phtml');

        return parent::_prepareLayout();
    }
}