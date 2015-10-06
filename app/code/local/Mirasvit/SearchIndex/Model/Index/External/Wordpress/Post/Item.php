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


class Mirasvit_SearchIndex_Model_Index_External_Wordpress_Post_Item extends Varien_Object
{
    public function getUrl()
    {
        $this->joinCategoryInfo();

        $url = $this->getIndex()->getProperty('url_template');
        foreach ($this->getData() as $key => $value) {
            $key = strtolower($key);
            $url = str_replace('{'.$key.'}', $value, $url);
        }
        return $url;

    }

    public function joinCategoryInfo()
    {
        $pr         = $this->getIndex()->getProperty('db_table_prefix');
        $connection = $this->getIndex()->getConnection();
        $select     = $connection->select();

        $select
            ->from(array('rel' => $pr.'term_relationships'), array())
            ->joinLeft(array('tax' => $pr.'term_taxonomy'),
                'tax.term_taxonomy_id = rel.term_taxonomy_id',
                array()
            )
            ->joinLeft(array('terms' => $pr.'terms'),
                'terms.term_id = tax.term_id',
                array('category_name' => 'name', 'category_slug' => 'slug')
            )
            ->where('tax.taxonomy = "category"')
            ->where('rel.object_id = ?', $this->getData('ID'));

        $row = $connection->fetchRow($select);

        $this->addData($row);

        return $this;
    }

    public function getIndex()
    {
        return Mage::helper('searchindex/index')->getIndexModel('external_wordpress_post');
    }
}