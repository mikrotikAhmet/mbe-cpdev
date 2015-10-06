<?php

/**
 * Mage helper class for CustomCatalog module.
 *
 * @category Mbemro
 * @package Mbemro_CustomCatalog
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */

class Mbemro_CustomCatalog_Helper_Customcatalog extends Mage_Core_Helper_Abstract
{

    /**
     * Return a configuration setting
     *
     *
     * @param string $field
     * @param int|string|Mage_Core_Model_Store $store
     * @return mixed
     */
    public function getConfig($field, $store = null)
    {
        return Mage::getStoreConfig('customcatalog/group_options/' . $field, $store);
    }

     /**
     * Return if the module is enabled for the current store view
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @param bool $checkAdmin If false don't return false just because the specified store is the admin view
     * @return bool
     */
    public function isEnabled($store = null, $checkAdmin = true)
    {
        $store = Mage::app()->getStore($store);
        if ($checkAdmin && $store->isAdmin()) {
            return false;
        }
/*
        // Temporary setting has higher priority then system config setting
        if (null !== $this->getModuleActiveFlag()) {
            return $this->getModuleActiveFlag();
        }
*/
        return (bool) $this->getConfig('is_enabled', $store);
    }

    /**
     * Return if the module is enabled for the current customer
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @param bool $checkAdmin If false don't return false just because the specified store is the admin view
     * @return bool
     */
    public function isCustomerEnabled($store = null, $checkAdmin = true)
    {
        $store = Mage::app()->getStore($store);
        if ($checkAdmin && $store->isAdmin()) {
            return false;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();

        return (bool) Mage::getResourceModel('customcatalog/customer')->isCustomerEnabled($customer->getId(), 0);

    }

    /**
     * Return array of customer groups allowed to use this extension.
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return array
     */
    public function getAllowedGroups($store = null)
    {
        return explode(',', $this->getConfig('allowed_groups', $store));
    }

    /**
     * Return array of customer groups allowed to use this extension.
     *
     * @param $customer Mage_Customer_Model_Session
     * @param $groups array
     * @return bool
     */
    public function inGroup($customer, $groups)
    {
        return in_array($customer->getCustomerGroupId(), $groups);
    }


    /**
     * Return if currently logged in user can use CustomCatalog
     *
     * @return bool
     */
    public function isModuleUsageAllowed()
    {
        return $this->isEnabled() &&
            $this->isCustomerEnabled()
        /* group allowing is not used any more.
         &&
               $this->inGroup(
                    Mage::getSingleton('customer/session'),
                    $this->getAllowedGroups()
                )
        */
               ;
    }

    public function getModuleUrl()
    {
        return Mage::getUrl(
            'customcatalog/product'
        );
    }


}
