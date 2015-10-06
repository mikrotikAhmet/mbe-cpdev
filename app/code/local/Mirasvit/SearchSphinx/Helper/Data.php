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
class Mirasvit_SearchSphinx_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * ÐÐ¾Ð²ÑÐ°ÑÐ°ÐµÑ Ð¾Ð±ÑÐµÐºÑ Ð¿Ð¾Ð¸ÑÐºÐ¾Ð²Ð¾Ð³Ð¾ Ð´Ð²Ð¸Ð¶ÐºÐ° (Sphinx (External), Sphinx Native Ð¸Ð»Ð¸ Fulltext)
     *
     * @return object
     */
    public function getEngine()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        switch (Mage::getSingleton('searchsphinx/config')->getSearchEngine()) {
            case Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX:
                $engine = Mage::getSingleton('searchsphinx/engine_sphinx_native');
            break;

            case Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX_EXTERNAL:
                $engine = Mage::getSingleton('searchsphinx/engine_sphinx');
            break;

            default:
                $engine = Mage::getSingleton('searchsphinx/engine_fulltext');
            break;
        }

        Mage::helper('mstcore/debug')->end($uid, $engine);

        return $engine;
    }

    /**
     * ÐÑÐ¾Ð²ÐµÑÑÐµÑ, ÑÐ²Ð»ÑÐµÑÑÑÑ Ð»Ð¸ ÑÐ»Ð¾Ð²Ð¾ Ð¸ÑÐºÐ»ÑÑÐµÐ½Ð¸ÐµÐ¼ Ð¸Ð· wildcard
     *
     * @param  string  $word
     *
     * @return boolean
     */
    public function isWildcardException($word)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $exceptions = Mage::getSingleton('searchsphinx/config')->getWildCardExceptions();

        $result = in_array($word, $exceptions);

        Mage::helper('mstcore/debug')->end($uid, $result);

        return $result;
    }
}