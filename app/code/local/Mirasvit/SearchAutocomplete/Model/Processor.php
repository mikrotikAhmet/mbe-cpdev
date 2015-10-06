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
 * ÐÑ Ð»Ð¾Ð²Ð¸Ð¼ Ð·Ð°Ð¿ÑÐ¾Ñ ÐµÑÐµ Ð´Ð¾ ÑÐ¾Ð³Ð¾ ÐºÐ°Ðº Ð¿ÑÐ¸ÑÐµÐ» Ð½Ð° ÐºÐ¾Ð½ÑÑÐ¾Ð»Ð»ÐµÑ Ð¸ ÐµÑÐ»Ð¸ ÐµÑÑÑ ÐºÐµÑ ÑÑÐ°Ð·Ñ Ð¾ÑÐ´Ð°ÐµÐ¼ Ð¾ÑÐ²ÐµÑ Ð¸Ð½Ð°ÑÐµ Ð·Ð°Ð¿ÑÐ¾Ñ Ð¸Ð´ÐµÑ Ð½Ð° ÐºÐ¾Ð½ÑÑÐ¾Ð»Ð»ÐµÑ.
 * ÐÐ° ÑÑÐµÑ ÑÑÐ¾Ð³Ð¾ ÑÐºÐ¾ÑÐ¾ÑÑÑ Ð²ÑÐ´Ð°ÑÐ¸ Ð¸Ð· ÐºÐµÑÐ° - Ð¼Ð³Ð½Ð¾Ð²ÐµÐ½Ð½Ð°. ÐÐ¾ ÑÑÑÐ¸ FPC ÐºÐµÑ.
 * 
 * @category Mirasvit
 * @package  Mirasvit_SearchAutocomplete
 */
class Mirasvit_SearchAutocomplete_Model_Processor
{
    const CACHE_TAG      = 'BLOCK_HTML';
    const CACHE_LIFETIME = 604800;

    protected $_cacheId = null;

    public function __construct()
    {
        $key = false;
        if (!empty($_SERVER['REQUEST_URI'])) {
            $key.= $_SERVER['REQUEST_URI'];
        }

        if ($key) {
            if (isset($_COOKIE['store'])) {
                $key = $key.'_'.$_COOKIE['store'];
            }
            if (isset($_COOKIE['currency'])) {
                $key = $key.'_'.$_COOKIE['currency'];
            }
        }

        $this->_cacheId  = 'SEARCHAUTOCOMPLETE_'.md5($key);
    }


    public function extractContent()
    {
        $content = Mage::app()->loadCache($this->_cacheId);

        return $content;
    }

    public function cacheResponse(Varien_Event_Observer $observer)
    {
        $frontController = $observer->getEvent()->getFront();
        $request = $frontController->getRequest();
        
        if ($request->getControllerModule() == 'Mirasvit_SearchAutocomplete') {
            $response = $frontController->getResponse();

            $content = Mage::app()->saveCache($response->getBody(), $this->_cacheId, array(self::CACHE_TAG), self::CACHE_LIFETIME);
        }
    }
}