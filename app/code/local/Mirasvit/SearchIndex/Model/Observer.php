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


class Mirasvit_SearchIndex_Model_Observer
{
    /**
     * ÐÐ¾ÑÐ»Ðµ Ð·Ð°Ð²ÐµÑÑÐµÐ½Ð¸Ñ ÑÐµÐ¸Ð½Ð´ÐµÐºÑÐ° Ð¸Ð½Ð´ÐµÐºÑÐ° catalogsearc_fulltext (System/Index Management)
     * Ð¿ÐµÑÐµÐ²Ð¾Ð´Ð¸Ð¼ Ð²ÑÐµ Ð½Ð°ÑÐ¸ Ð¸Ð½Ð´ÐµÐºÑÑ Ð² ÑÑÐ°ÑÑÑ Ready
     * 
     * @return object
     */
    public function onIndexProcessComplete()
    {
        $collection = Mage::getModel('searchindex/index')->getCollection();
        foreach ($collection as $index) {
            $index->setStatus(1)
                ->save();
        }

        return $this;
    }
}