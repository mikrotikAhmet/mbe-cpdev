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
 * ÐÐ¾Ð´ÐµÐ»Ñ Ð´Ð»Ñ ÑÐ°Ð±Ð¾ÑÑ ÑÐ¾ ÑÑÐ¾Ð¿-ÑÐ»Ð¾Ð²Ð°Ð¼Ð¸
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Model_Stopword extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('searchsphinx/stopword');
    }

    /**
     * ÐÐ¼Ð¿Ð¾ÑÑ ÑÑÐ¾Ð¿-ÑÐ»Ð¾Ð²
     *
     * @param  string $filePath Ð¿Ð¾Ð»Ð½ÑÐ¹ Ð¿ÑÑÑ Ðº ÑÐ°Ð¹Ð»Ñ (csv)
     * @param  array  $stores
     *
     * @return integer ÐºÐ¾Ð»-Ð²Ð¾ Ð¸Ð¼Ð¿Ð¾ÑÑÐ¸ÑÐ¾Ð²Ð°Ð½ÑÑ ÑÑÐ¾Ð¿-ÑÐ»Ð¾Ð²
     */
    public function import($filePath, $stores)
    {
        if (!is_array($stores)) {
            $stores = array($stores);
        }

        $resource   = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $tableName  = Mage::getSingleton('core/resource')->getTableName('searchsphinx/stopword');


        $content = file_get_contents($filePath);
        $lines   = explode("\n", $content);
        foreach ($stores as $store) {
            foreach ($lines as $value) {
                $value = strtolower($value);
                $rows[] = array(
                    'word'  => $value,
                    'store' => $store,
                );

                if (count($rows) > 1000) {
                    $connection->insertArray($tableName, array('word', 'store'), $rows);
                    $rows = array();
                }
            }

            if (count($rows) > 0) {
                $connection->insertArray($tableName, array('word', 'store'), $rows);
            }
        }

        return count($lines);
    }

    public function isStopWord($word, $store)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $collection = $this->getCollection()->addFieldToFilter('word', $word)
            ->addFieldToFilter('store', $store);

        $cnt = $collection->count();

        Mage::helper('mstcore/debug')->end($uid, array('word' => $word, 'cnt' => $cnt, 'collection_sql' => (string) $collection->getSelect()));

        return $cnt;
    }
}