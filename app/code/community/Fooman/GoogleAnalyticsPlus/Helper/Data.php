<?php

class Fooman_GoogleAnalyticsPlus_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const XML_PATH_GOOGLEANALYTICSPLUS_SETTINGS = 'google/analyticsplus/';

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getGoogleanalyticsplusStoreConfig ($key, $flag=false)
    {
        $path = self::XML_PATH_GOOGLEANALYTICSPLUS_SETTINGS . $key;
        if ($flag) {
            return Mage::getStoreConfigFlag($path);
        } else {
            return Mage::getStoreConfig($path);
        }
    }

    public function convert($order, $dataMethod, $item=null)
    {
        $basecur = $order->getBaseCurrency();
        if($basecur) {
            return sprintf("%01.4f", Mage::app()->getStore()->roundPrice(
                $basecur->convert(
                    (is_null($item))?$order->$dataMethod():$item->$dataMethod(),
                    Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('convertcurrency')
                ))
            );
        } else {
            //unable to load base currency return zero
            return '0.0000';
            //return (is_null($item))?$order->$dataMethod():$item->$dataMethod();
        }
    }

}