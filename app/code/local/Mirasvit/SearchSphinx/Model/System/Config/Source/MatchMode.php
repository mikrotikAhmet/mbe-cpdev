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
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Model_System_Config_Source_MatchMode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('searchsphinx')->__('Matches all query words')),
            array('value' => 1, 'label'=>Mage::helper('searchsphinx')->__('Matches any of the query words')),
            array('value' => 4, 'label'=>Mage::helper('searchsphinx')->__('Matches query as an expression')),
        );
    }

    public function toArray()
    {
        return array(
            0 => Mage::helper('searchsphinx')->__('Matches all query words'),
            1 => Mage::helper('searchsphinx')->__('Matches any of the query words'),
            4 => Mage::helper('searchsphinx')->__('Matches query as an expression'),
        );
    }

}
