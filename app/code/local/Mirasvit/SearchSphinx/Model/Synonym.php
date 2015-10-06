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
 * ÐÐ¾Ð´ÐµÐ»Ñ Ð´Ð»Ñ ÑÐ°Ð±Ð¾ÑÑ Ñ ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼Ð°Ð¼Ð¸
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Model_Synonym extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('searchsphinx/synonym');
    }

    /**
     * ÐÐ¼Ð¿Ð¾ÑÑ ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼Ð¾Ð²
     *
     * @param  string $filePath Ð¿Ð¾Ð»Ð½ÑÐ¹ Ð¿ÑÑÑ Ðº ÑÐ°Ð¹Ð»Ñ (csv)
     * @param  array  $stores
     *
     * @return integer ÐºÐ¾Ð»-Ð²Ð¾ Ð¸Ð¼Ð¿Ð¾ÑÑÐ¸ÑÐ¾Ð²Ð°Ð½ÑÑ ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼Ð¾Ð²
     */
    public function import($filePath, $stores)
    {
        if (!is_array($stores)) {
            $stores = array($stores);
        }

        $resource   = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $tableName  = Mage::getSingleton('core/resource')->getTableName('searchsphinx/synonym');


        $content = file_get_contents($filePath);
        $lines   = explode("\n", $content);

        foreach ($stores as $store) {
            foreach ($lines as $value) {
                $value = strtolower($value);
                $value = explode(',', $value);

                $synonyms = array();
                // ÑÐ¸Ð½Ð¾Ð½Ð¸Ð¼ Ð´Ð¾Ð»Ð¶ÐµÐ½ ÑÐ¾ÑÑÐ¾ÑÑÑ Ð¸Ð· Ð¾Ð´Ð½Ð¾Ð³Ð¾ ÑÐ»Ð¾Ð²Ð°
                foreach ($value as $val) {
                    $val = trim($val);
                    if (count(Mage::helper('core/string')->splitWords($val, true)) == 1 && $val) {
                        $synonyms[$val] = $val;
                    }
                }

                if (count($synonyms) > 1) {
                    $synonyms = implode(',', $synonyms);

                    $rows[] = array(
                        'synonyms' => $synonyms,
                        'store'    => $store,
                    );
                }

                if (count($rows) > 1000) {
                    $connection->insertArray($tableName, array('synonyms', 'store'), $rows);
                    $rows = array();
                }
            }

            if (count($rows) > 0) {
                $connection->insertArray($tableName, array('synonyms', 'store'), $rows);
            }
        }

        return count($lines);
    }

    public function getSynonymsByWord($arWord, $storeId)
    {
        $result = array();

        if (!is_array($arWord)) {
            $arWord = array($arWord);
        }

        $collection = $this->getCollection();

        foreach ($arWord as $word) {
            $collection->getSelect()->orWhere(new Zend_Db_Expr("FIND_IN_SET('".addslashes($word)."', synonyms)"));
        }

        foreach ($collection as $synonym) {
            $synonyms = explode(',', $synonym->getSynonyms());

            foreach ($arWord as $word) {
                if (in_array($word, $synonyms)) {
                    foreach ($synonyms as $synonym) {
                        $result[$word][$synonym] = $synonym;
                    }
                }
            }
        }

        return $result;
    }
}