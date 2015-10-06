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
 * ÐÐ»Ð°ÑÑ ÑÐµÐ°Ð»Ð¸Ð·ÑÐµÑ Ð¼ÐµÑÐ¾Ð´Ñ Ð´Ð»Ñ Ð¿Ð¾Ð¸ÑÐºÐ° Ð¿Ð¾ Ð³Ð¾ÑÐ¾Ð²ÑÐ¼ mysql ÑÐ°Ð±Ð»Ð¸ÑÐ°Ð¼ Ð¿Ð¾Ð¸ÑÐºÐ¾Ð²ÑÑ Ð¸Ð½Ð´ÐµÐºÑÐ¾Ð²
 *
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_Model_Engine_Fulltext extends Mirasvit_SearchIndex_Model_Engine
{
    /**
     * ÐÐ¾Ð´Ð³Ð¾ÑÐ°Ð²Ð»Ð¸Ð²Ð°ÐµÑ Ð·Ð°Ð¿ÑÐ¾Ñ, Ð²ÑÐ¿Ð¾Ð»Ð½ÑÑÐµ Ð·Ð°Ð¿ÑÐ¾Ñ, Ð²Ð¾Ð·ÑÐ°ÑÐ°ÐµÑ Ð¿Ð¾Ð´Ð³Ð¾ÑÐ¾Ð²Ð»ÑÐ½Ð½ÑÐµ ÑÐµÐ·ÑÐ»ÑÑÐ°ÑÑ
     *
     * @param  string  $queryText Ð¿Ð¾Ð¸ÑÐºÐ¾Ð²ÑÐ¹ Ð·Ð°Ð¿ÑÐ¾Ñ (Ð² Ð¾ÑÐ¸Ð³Ð¸Ð½Ð°Ð»ÑÐ½Ð¾Ð¼ Ð²Ð¸Ð´Ðµ)
     * @param  integer $store     ÐÐ ÑÐµÐºÑÑÐµÐ³Ð¾ Ð¼Ð°Ð³Ð°Ð·Ð¸Ð½Ð°
     * @param  object  $index     Ð¸Ð½Ð´ÐµÐºÑ Ð¿Ð¾ ÐºÐ¾ÑÐ¾ÑÐ¾Ð¼Ñ Ð½ÑÐ¶Ð½Ð¾ Ð¿ÑÐ¾Ð²ÐµÑÑÐ¸ Ð¿Ð¾Ð¸ÑÐº
     *
     * @return array Ð¼Ð°ÑÐ¸Ð² ÐÐ ÐµÐ»ÐµÐ¼ÐµÐ½ÑÐ¾Ð², Ð³Ð´Ðµ ÐÐ - ÐºÐ»ÑÑ, ÑÐµÐ»ÐµÐ²Ð°Ð½ÑÐ½Ð¾ÑÑÑ Ð·Ð½Ð°ÑÐµÐ½Ð¸Ðµ
     */
    public function query($query, $store, $index)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $connection = $this->_getReadAdapter();
        $table      = $index->getIndexer()->getTableName();
        $attributes = $this->_getAttributes($index);
        $pk         = $index->getIndexer()->getPrimaryKey();

        $select = $connection->select();
        $select->from(array('s' => $table), array($pk));

        $arQuery = Mage::helper('searchsphinx/query')->buildQuery($query, $store);

        if (count($arQuery) == 0 || count($attributes) == 0) {
            return array();
        }

        Mage::helper('mstcore/debug')->dump($uid, array('$query' => $query, '$store' => $store, '$arQuery' => $arQuery));

        $caseCondition  = $this->_getCaseCondition($query, $arQuery, $attributes);
        $whereCondition = $this->_getWhereCondition($arQuery, $attributes);

        if ($store != null) {
            $select->where('s.store_id = ?', (int) $store);
        }

        if ($whereCondition != '') {
            $select->where($whereCondition);
        }

        $select->columns(array('relevance' => $caseCondition));
        $select->columns('searchindex_weight');

        $select->limit(Mage::getSingleton('searchsphinx/config')->getResultLimit());
        $select->order('relevance desc');

        Mage::helper('mstcore/debug')->dump($uid, array('$select', $select->__toString()));

        $result = array();
        $weight = array();
        // echo $select;
        $stmt = $connection->query($select);
        while ($row = $stmt->fetch(Zend_Db::FETCH_NUM)) {
            $result[$row[0]] = $row[1];
            $weight[$row[0]] = $row[2];
        }

        $result = $this->_normalize($result);

        foreach ($result as $key => $value) {
            $result[$key] += $weight[$key];
        }

        Mage::helper('mstcore/debug')->end($uid, $result);

        return $result;
    }

    /**
     * Ð¡ÑÑÐ¾Ð¸Ñ sql CASE WHEN .. THEN .. ELSE .. END Ð´Ð»Ñ ÑÐµÐºÑÐ¸Ð¸ SELECT
     * Ñ.Ðµ. Ð½Ð° Ð¾ÑÐ½Ð¾Ð²Ð°Ð½Ð¸Ð¸Ðµ Ð²ÐµÑÐ¾Ð² Ð°ÑÑÐ¸Ð±ÑÑÐ¾Ð² ÑÑÑÐ¾Ð¸Ñ ÑÐ°ÑÑÐ¸ Ð·Ð°Ð¿ÑÐ¾ÑÐ° Ð´Ð»Ñ Ð²ÑÑÐµÑÐ»ÐµÐ½Ð¸Ñ ÑÐµÐ»ÐµÐ²Ð°Ð½ÑÐ½Ð¾ÑÑÐ¸
     *
     * @param  string $query      Ð¾ÑÐ¸Ð³Ð¸Ð½Ð°Ð»ÑÐ½ÑÐ¹ Ð·Ð°Ð¿ÑÐ¾Ñ
     * @param  array  $arQuery    Ð¿Ð¾Ð´Ð³Ð¾ÑÐ¾Ð²Ð»ÐµÐ½Ð½ÑÐ¹ Ð·Ð°Ð¿ÑÐ¾Ñ
     * @param  array  $attributes Ð°ÑÑÐ¸Ð±ÑÑÑ Ñ Ð²ÐµÑÐ¾Ð¼
     *
     * @return string
     */
    protected function _getCaseCondition($query, $arQuery, $attributes)
    {
        $uid = Mage::helper('mstcore/debug')->start();
        $select    = '';
        $cases     = array();
        $fullCases = array();
        $words = Mage::helper('core/string')->splitWords($query, true);

        foreach ($attributes as $attr => $weight) {
            if ($weight == 0) {
                continue;
            }

            $cases[$weight * 4][] = $this->getCILike('s.'.$attr, $query);
            $cases[$weight * 3][] = $this->getCILike('s.'.$attr, ' '.$query.' ', array('position' => 'any'));
        }

        foreach ($words as $word) {
            foreach ($attributes as $attr => $weight) {
                $w = intval($weight / count($arQuery));
                if ($w == 0) {
                    continue;
                }
                $cases[$w][] = $this->getCILike('s.'.$attr, $word, array('position' => 'any'));
                $cases[$w + 1][] = $this->getCILike('s.'.$attr, ' '.$word.' ', array('position' => 'any'));
            }
        }

        foreach ($words as $word) {
            foreach ($attributes as $attr => $weight) {
                $w = intval($weight / count($arQuery));

                if ($w == 0) {
                    continue;
                }

                // $locate = new Zend_Db_Expr('LOCATE("'.$word.'", s.'.$attr.')');
                // $cases[$w.'-'.$locate->__toString()][] = $locate;
                $locate = new Zend_Db_Expr('(LENGTH(s.'.$attr.') - LOCATE("'.$word.'", s.'.$attr.')) / LENGTH(s.'.$attr.')');
                $cases[$w.'*'.$locate->__toString()][] = $locate;
            }
        }

        foreach ($cases as $weight => $conds) {
            foreach ($conds as $cond) {
                $fullCases[] = 'CASE WHEN '.$cond.' THEN '.$weight.' ELSE 0 END';
            }
        }

        if (count($fullCases)) {
            $select = '('.implode('+', $fullCases).')';
        } else {
            $select = new Zend_Db_Expr('0');
        }

        Mage::helper('mstcore/debug')->end($uid, (string) $select);

        return $select;
    }

    /**
     * ÐÐ¾Ð·Ð²ÑÐ°ÑÐ°ÐµÑ sql WHERE ÑÑÐ»Ð¾Ð²Ð¸Ðµ - ÑÑÐ¾ Ð¸ ÐµÑÑÑ Ð¿Ð¾Ð¸ÑÐº
     * WHERE ÑÐ¾ÑÑÐ¾Ð¸Ñ Ð¸Ð· ÑÐµÐºÑÐ¸Ð¹ - 1 ÑÐ»Ð¾Ð²Ð¾ - 1 ÑÐµÐºÑÐ¸Ñ
     *
     * @param  array $arWords    Ð¿Ð¾Ð´Ð³Ð¾ÑÐ¾Ð²Ð»ÐµÐ½Ð½ÑÐ¹ Ð·Ð°Ð¿ÑÐ¾Ñ
     * @param  array $attributes Ð°ÑÑÐ¸Ð±ÑÑÑ Ñ Ð²ÐµÑÐ°Ð¼Ð¸
     *
     * @return string
     */
    protected function _getWhereCondition($arWords, $attributes)
    {
        if (!is_array($arWords)) {
            return '';
        }

        $result = array();
        foreach ($arWords as $key => $array) {
            $result[] = $this->_buildWhere($key, $array);
        }

        $where = '(' . join(' AND ', $result) . ')';

        return $where;
    }

    /**
     * Ð¡ÑÑÐ¾Ð¸Ñ ÑÐµÐºÑÐ¸Ñ Ð´Ð»Ñ ÑÐ»Ð¾Ð²Ð°/ÑÐ»Ð¾Ð²
     *
     * @param  string $type  Ð»Ð¾Ð³Ð¸ÐºÐ° Ð/ÐÐÐ
     * @param  array  $array ÑÐ»Ð¾Ð²Ð°
     *
     * @return array
     */
    protected function _buildWhere($type, $array)
    {
        if (!is_array($array)) {
            return $this->getCILike('s.data_index', $array, array('position' => 'any'), $type);
        }

        foreach ($array as $key => $subarray) {
            if ($key == 'or') {
                $array[$key] = $this->_buildWhere($type, $subarray);
                if (is_array($array[$key])) {
                    $array = '('.implode(' OR ', $array[$key]).')';
                }
            } elseif ($key == 'and') {
                $array[$key] = $this->_buildWhere($type, $subarray);
                if (is_array($array[$key])) {
                    $array = '('.implode(' AND ', $array[$key]).')';
                }
            } else {
                $array[$key] = $this->_buildWhere($type, $subarray);
            }
        }

        return $array;

    }

    /**
     * ÐÐ¾Ð·Ð²ÑÐ°ÑÐ°ÐµÑ Ð¾Ð±ÑÐµÐ´ÐµÐ½ÐµÐ½ÑÐ¹ Ð¼Ð°ÑÐ¸Ð² Ð°ÑÑÐ¸Ð±ÑÑÐ¾Ð² Ð¸ ÐºÐ¾Ð»Ð¾Ð½Ð¾Ðº Ð² ÑÐ°Ð±Ð»Ð¸ÑÐµ ÑÐµÐºÑÑÐµÐ³Ð¾ Ð¸Ð½Ð´ÐµÐºÑÐ°
     *
     * @param  object $index Ð¾Ð±ÑÐµÐºÑ Ð¸Ð½Ð´ÐµÐºÑÐ°
     *
     * @return array
     */
    protected function _getAttributes($index)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $attributes = $index->getAttributes(true);
        $columns    = $this->_getTableColumns($index->getIndexer()->getTableName());

        foreach ($attributes as $attr => $weight) {
            if (!in_array($attr, $columns)) {
                unset($attributes[$attr]);
            }
        }

        foreach ($columns as $column) {
            if (!in_array($column, array($index->getIndexer()->getPrimaryKey(), 'store_id', 'updated'))
                && !isset($attributes[$column])) {
                $attributes[$column] = 0;
            }
        }

        Mage::helper('mstcore/debug')->end($uid, array('$attributes' => $attributes, '$index' => $index));

        return $attributes;
    }

    /**
     * Ð¤ÑÐ½ÐºÑÐ¸Ñ ÐµÑÑÑ ÑÐ¾Ð»ÑÐºÐ¾ Ð² magento 1.6+, Ð´ÑÐ±Ð»Ð¸ÑÑÐµÐ¼
     */
    public function getCILike($field, $value, $options = array(), $type = 'LIKE')
    {
        $quotedField = $this->_getReadAdapter()->quoteIdentifier($field);
        return new Zend_Db_Expr($quotedField . ' '.$type.' "' . $this->escapeLikeValue($value, $options).'"');
    }

    /**
     * Ð¤ÑÐ½ÐºÑÐ¸Ñ ÐµÑÑÑ ÑÐ¾Ð»ÑÐºÐ¾ Ð² magento 1.6+, Ð´ÑÐ±Ð»Ð¸ÑÑÐµÐ¼
     */
    public function escapeLikeValue($value, $options = array())
    {
        $value = addslashes($value);

        $from = array();
        $to = array();
        if (empty($options['allow_string_mask'])) {
            $from[] = '%';
            $to[] = '\%';
        }
        if ($from) {
            $value = str_replace($from, $to, $value);
        }

        if (isset($options['position'])) {
            switch ($options['position']) {
                case 'any':
                    $value = '%' . $value . '%';
                    break;
                case 'start':
                    $value = $value . '%';
                    break;
                case 'end':
                    $value = '%' . $value;
                    break;
            }
        }

        return $value;
    }

    /**
     * ÐÐ¾Ð·ÑÐ°ÑÐ°ÐµÑ Ð¼Ð°ÑÐ¸Ð² ÐºÐ¾Ð»Ð¾Ð½Ð¾Ðº Ð´Ð»Ñ ÑÐ°Ð±Ð»Ð¸ÑÑ ÐÐ
     *
     * @param  string $tableName
     * @return array
     */
    protected function _getTableColumns($tableName)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $columns = array_keys($this->_getReadAdapter()->describeTable($tableName));

        Mage::helper('mstcore/debug')->end($uid, array('$tableName' => $tableName, '$columns' => $columns));
        return $columns;
    }
}
