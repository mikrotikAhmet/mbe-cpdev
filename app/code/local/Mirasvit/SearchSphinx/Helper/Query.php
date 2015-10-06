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
 * Ð¥ÐµÐ»Ð¿ÐµÑ Ð´Ð»Ñ Ð¿ÑÐµÐ¾Ð±ÑÐ°Ð·Ð¾Ð²Ð°Ð½Ð¸Ñ Ð¿Ð¾Ð»ÑÐ·Ð¾Ð²Ð°ÑÐµÐ»ÑÑÐºÐ¾Ð³Ð¾ Ð·Ð°Ð¿ÑÐ¾ÑÐ° Ð² Ð³Ð¾ÑÐ¾Ð²ÑÐ¹ Ð·Ð°Ð¿ÑÐ¾Ñ (Ð´Ð»Ñ ÑÐ¸ÑÑÐµÐ¼Ñ - Ð¼Ð°ÑÐ¸Ð²)
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Helper_Query extends Mage_Core_Helper_Abstract
{
    /**
     * ÐÐ»ÑÑÐµÐ²Ð°Ñ ÑÑÐ½ÐºÑÐ¸Ñ. ÐÑÐµÐ¾Ð±ÑÐ°Ð·ÑÐµÑ Ð¿Ð¾Ð»ÑÐ·Ð¾Ð²Ð°ÑÐµÐ»ÑÑÐºÐ¸Ð¹ Ð·Ð°Ð¿ÑÐ¾Ñ Ð² Ð¼Ð°ÑÐ¸Ð² Ñ
     * ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼Ð°Ð¼Ð¸, Ð±ÐµÐ· ÑÑÐ¾Ð¿cÐ»Ð¾Ð², Ð¸ÑÐºÐ»ÑÑÐµÐ½Ð¸ÑÐ¼Ð¸ wildcard, Ð/ÐÐÐ ÑÑÐ»Ð¾Ð²Ð¸ÑÐ¼Ð¸
     *
     * @param  string  $query      Ð¿Ð¾Ð»ÑÐ·Ð¾Ð²Ð°ÑÐµÐ»ÑÑÐºÐ¸Ð¹ Ð·Ð°Ð¿ÑÐ¾Ñ
     * @param  int     $store      ÐÐ Ð¼Ð°Ð³Ð°Ð·Ð¸Ð½Ð°
     * @param  boolean $inverseNot Ð¸Ð·Ð¼ÐµÐ½Ð¸ÑÐµÐ¼ Ð Ð½Ð° ÐÐÐ
     *
     * @return array
     */
    public function buildQuery($query, $store, $inverseNot = false)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        if (Mage::helper('catalogsearch')->isMinQueryLength()) {
            return false;
        }

        $result = array();
        $config = Mage::getSingleton('searchsphinx/config');

        $query = strtolower($query);

        // Ð½ÐµÐ¾Ð±ÑÐ¾Ð´Ð¸Ð¼Ð¾ ÐµÑÐ»Ð¸ ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼ ÑÐ¾ÑÑÐ¾Ð¸Ñ Ð¸Ð· 2Ñ Ð¸ Ð±Ð¾Ð»ÐµÐµ ÑÐ»Ð¾Ð²
        $query = ' '.$query.' ';

        $replaceWords = Mage::getSingleton('searchsphinx/config')->getReplaceWords();

        foreach ($replaceWords as $replace => $find) {
            foreach ($find as $word) {
                $query = str_replace(' '.$word.' ', ' '.$replace.' ', $query);
            }
        }

        $arWords    = Mage::helper('core/string')->splitWords($query, true, Mage::helper('catalogsearch')->getMaxQueryWords());
        $arSynonyms = Mage::getSingleton('searchsphinx/synonym')->getSynonymsByWord($arWords, $store);

        $logic = 'like';

        foreach ($arWords as $word) {
            if (in_array($word, $config->getNotwords())) {
                $logic = 'not like';
                continue;
            }

            if ($this->isStopword($word, $store)) {
                continue;
            }

            $wordArr = array();
            $this->_addWord($wordArr, $word);

            if ($logic == 'like') {
                $longTail = $this->longtail($word);
                $this->_addWord($wordArr, $longTail);

                $singular = Mage::helper('searchsphinx/inflect')->singularize($word);
                $this->_addWord($wordArr, $singular);

                if (isset($arSynonyms[$word])) {
                    $this->_addWord($wordArr, $arSynonyms[$word]);
                }

                $template = Mage::getSingleton('searchsphinx/config')->getSearchTemplate();
                if (Mage::getSingleton('searchsphinx/config')->getMatchMode() == 1) {
                    $template = 'or';
                }
                $result[$logic][$template][$word] = array('or' => $wordArr);

                Mage::helper('mstcore/debug')->dump($uid, array('$wordArr', $wordArr));

            } else {
                if (!$inverseNot) {
                    $result[$logic]['and'][$word] = array('and' => $wordArr);
                } else {
                    $result[$logic]['or'][$word] = array('and' => $wordArr);
                }
            }
        }

        Mage::helper('mstcore/debug')->end($uid, $result);

        return $result;
    }

    /**
     * Ð­ÑÐ¾ ÑÑÐ¾Ð¿-ÑÐ»Ð¾Ð²Ð¾?
     *
     * @param  string  $word
     * @param  int     $store
     *
     * @return boolean
     */
    public function isStopword($word, $store)
    {
        if (Mage::getSingleton('searchsphinx/stopword')->isStopWord($word, $store)) {
            return true;
        }

        return false;
    }

    /**
     * ÐÐ»Ñ ÑÐ»Ð¾Ð²Ð° Ð²ÑÐ¿Ð¾Ð»Ð½ÑÐµÑ ÑÐµÐ³ÑÐ»ÑÑÐ½ÑÐµ Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ Ð·Ð°Ð´Ð°Ð½ÑÐµ Ð² Ð½Ð°ÑÑÑÐ¾Ð¹ÐºÐ°Ñ SearchIndex
     *
     * @param  string $word
     *
     * @return string
     */
    public function longtail($word)
    {
        $expressions = Mage::getSingleton('searchindex/config')->getMergeExpressins();

        foreach ($expressions as $expr) {
            $matches = null;
            preg_match_all($expr['match'], $word, $matches);

            foreach ($matches[0] as $math) {
                $math = preg_replace($expr['replace'], $expr['char'], $math);
                $word = $math;
            }
        }

        return $word;
    }

    /**
     * ÐÐ¾Ð±Ð°Ð²Ð»ÑÐµÑ ÑÐ»Ð¾Ð²Ð° Ð² Ð¼Ð°ÑÐ¸Ð², Ð¿ÑÐ¸ ÑÑÐ¾Ð¼ ÑÑÐ¸ÑÑÐ²Ð°ÐµÑ wildcard Ð¸ Ð¸ÑÐºÐ»ÑÑÐµÐ½Ð¸Ñ Ð¸Ð· wildcard
     *
     * @param array $to    Ð¼Ð°ÑÐ¸Ð² ÐºÑÐ´Ð° Ð½Ð°Ð´Ð¾ Ð´Ð¾Ð±Ð°Ð²Ð¸ÑÑ ÑÐ»Ð¾Ð²Ð°
     * @param array $words ÑÐ»Ð¾Ð²Ð°, ÐºÐ¾ÑÐ¾ÑÑÐµ Ð½Ð°Ð´Ð¾ Ð´Ð¾Ð±Ð°Ð²Ð¸ÑÑ
     *
     * @return void
     */
    protected function _addWord(&$to, $words)
    {
        $exceptions = Mage::getSingleton('searchsphinx/config')->getWildcardExceptions();
        $wildcard   = Mage::getSingleton('searchsphinx/config')->isAllowedWildcard();

        if (!is_array($words)) {
            $words = array($words);
        }

        foreach ($words as $word) {
            if (!$wildcard || in_array($word, $exceptions)) {
                $word = ' '.$word.' ';
            }

            if (trim($word)) {
                $to[$word] = $word;
            }
        }

        ksort($to);
    }
}
