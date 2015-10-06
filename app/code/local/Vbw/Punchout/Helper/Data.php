<?php

/**
 * @ref http://blog.onlinebizsoft.com/mvc-developers-part-7-%e2%80%93-custom-magento-system-configuration/
 */

class Vbw_Punchout_Helper_Data
	extends Mage_Core_Helper_Abstract
{

    // collect nodes to add to the layout.
    protected static $_layout_updates = array();

    /**
     * convert a price in to the store's currency
     *
     * @param $price
     * @return mixed
     */
    public function getAsStoreCurrency ($price)
    {
        $core = Mage::helper('core/data');
        // do not include formatting (ie $) or containing html.
        $currency = $core->currency($price,false,false);
        return $currency;
    }

    /**
     * get the store's currency code
     *
     * @return string|false
     */
    public function getStoreCurrencyCode ()
    {
        $store = Mage::app()->getStore();
        $currency = $store->getCurrentCurrency();
        if (empty($currency)) {
            $currency = $store->getBaseCurrency();
        }
        if ($currency instanceof Mage_Directory_Model_Currency) {
            return $currency->getCode();
        }
        return false;
    }

    /**
     * get the store's language. (ie. "en")
     *
     * @return string
     */
    public function getStoreLanguage ()
    {
        $code = Mage::app()->getLocale()->getLocale()->getLanguage();
        return $code;
    }

    /**
     * get the store's language. (ie. "US")
     *
     * @return string
     */
    public function getStoreRegion ()
    {
        $code = Mage::app()->getLocale()->getLocale()->getRegion();
        return $code;
    }

    /**
     * get the store's locale code (ie. en_US)
     *
     * @return string
     */
    public function getStoreLocaleCode ()
    {
        $code = Mage::app()->getLocale()->getLocale()->toString();
        return $code;
    }

    /**
     *
     *
     * @param $node
     */
    public function addLayoutUpdateNode ($node)
    {
        self::$_layout_updates[] = $node;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLayoutUpdateNodes ()
    {
        return self::$_layout_updates;
    }

    public function debug ($string)
    {
        if (Mage::getStoreConfigFlag('vbw_punchout/api/debug_logging')) {
            Mage::log($string,null,'vbw_punchout.debug.log',true);
        }
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return bool
     */
    public function allowRequestThroughPunchoutOnly ($request)
    {
        /* @var $config Vbw_Punchout_Helper_Config */
        $config = Mage::helper('vbw_punchout/config');

        $allow = $config->getConfig('site/punchout_only_allow');
        $allows = explode(',',$allow);
        foreach ($allows AS $k => $route) {
            if ($this->isMatchedRequestRoute($request,$route)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param $route
     * @return bool
     */
    public function isMatchedRequestRoute ($request,$route)
    {
        $this->debug('Testing route : '. $route .' with '. $request->getControllerModule() .'/'. $request->getControllerName() .'/'. $request->getActionName());
        $route = trim($route);
        $route_parts = explode('/',$route);
        if (isset($route_parts[0])) {
            $module = $route_parts[0];
            $module_name = strtolower($request->getControllerModule());
            if (strtolower($module) == strtolower($module_name)) {
                if (isset($route_parts[1])) {
                    $controller = $route_parts[1];
                    $controller_name = strtolower($request->getControllerName());
                    if (strtolower($controller) == strtolower($controller_name)) {
                        if (isset($route_parts[2])) {
                            $action = $route_parts[2];
                            $action_name = strtolower($request->getActionName());
                            if (strtolower($action) == strtolower($action_name)) {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * @param $region
     * @param $country_id
     * @return Mage_Directory_Model_Region
     */
    public function getDirectoryRegionByData ($region,$country_id)
    {
        $directory = Mage::getModel('directory/region');
        $directory->loadByCode($region,$country_id);
        if (!is_numeric($directory->getId())) {
            /** @var $collection Mage_Directory_Model_Resource_Region_Collection */
            $collection = $directory->getCollection();
            $collection->addFieldToFilter('country_id',$country_id);
            $collection->addFieldToFilter('default_name',$region);
            if ($collection->count() == 1) {
                return $collection->getFirstItem();
            }
        } else {
            return $directory;
        }
        return null;
    }

}
