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
class Mirasvit_SearchSphinx_Model_Observer
{
    /**
     * ÐÑÐ»Ð¸ Search Engine = Sphinx
     * Ð·Ð°Ð¿ÑÑÐºÐ°ÐµÑ ÑÐµÐ¸Ð½Ð´ÐµÐºÑ
     */
    public function reindex()
    {
        if (Mage::getSingleton('searchsphinx/config')->getSearchEngine() == Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX) {
            Mage::getModel('searchsphinx/engine_sphinx_native')->reindex();
        }
    }

    /**
     * ÐÑÐ»Ð¸ Search Engine = Sphinx
     * Ð·Ð°Ð¿ÑÑÐºÐ°ÐµÑ Ð´ÐµÐ»ÑÐ°-ÑÐµÐ¸Ð½Ð´ÐµÐºÑ
     */
    public function reindexDelta()
    {
        if (Mage::getSingleton('searchsphinx/config')->getSearchEngine() == Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX) {
            Mage::getModel('searchsphinx/engine_sphinx_native')->reindex(true);
        }
    }

    /**
     * ÐÑÐ»Ð¸ Search Engine = Sphinx
     * Ð¿ÑÐ¾Ð²ÐµÑÑÐµÑ ÐµÑÐ»Ð¸ ÑÑÐ¸Ð½ÐºÑ Ð½Ðµ Ð·Ð°Ð¿ÑÑÐµÐ½ - Ð´ÐµÐ»Ð°ÐµÑ ÑÐµÑÑÐ°ÑÑ
     */
    public function checkDaemon()
    {
        if (Mage::getSingleton('searchsphinx/config')->getSearchEngine() == Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX) {
            $engine = Mage::getModel('searchsphinx/engine_sphinx_native');
            if ($engine->isSearchdRunning() == false) {
                $engine->restart();
            }
        }
    }

    /**
     * ÐÑÐ¸ Ð¸Ð½Ð´ÐµÐºÑÐ°ÑÐ¸Ð¸ Misspell (Spell Correction) Ð´Ð¾Ð±Ð°Ð²Ð»ÑÐµÐ¼ ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼Ñ Ð·Ð°Ð´Ð°Ð½ÑÐµ Ð¿Ð¾Ð»ÑÐ·Ð¾Ð²Ð°ÑÐµÐ»Ñ Ðº Ð¿ÑÐ°Ð²Ð¸Ð»ÑÐ½ÑÐ¼ ÑÐ»Ð¾Ð²Ð°Ð¼ (Ð¸Ð½Ð´ÐµÐºÑÑ misspell'a)
     *
     * @param  object $observer
     */
    public function onMisspellIndexerPrepare($observer)
    {
        // $obj = $observer->getObj();
        // $string = '';

        // $synonyms = Mage::getSingleton('searchsphinx/config')->getSynonyms();

        // foreach ($synonyms as $key => $synonyms) {
        //     $string .= $key.' '.implode(' ', $synonyms).' ';
        // }

        // $obj->setSearchSphinxSynonyms($string);
    }
}