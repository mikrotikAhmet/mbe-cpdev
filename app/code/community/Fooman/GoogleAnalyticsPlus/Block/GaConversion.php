<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kristof@fooman.co.nz so we can send you a copy immediately.
 *
 * @category   Fooman
 * @package    Fooman_GoogleAnalyticsPlus
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Analytics block
 *
 * @category   Fooman
 * @package    Fooman_GoogleAnalyticsPlus
 * @author     Fooman, Kristof Ringleff <kristof@fooman.co.nz>
 */
class  Fooman_GoogleAnalyticsPlus_Block_GaConversion extends Mage_Core_Block_Template
{

    private $_quote;

    public function isEnabled(){
        return Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('conversionenabled',true);
    }

    public function getLabel(){
        return Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('conversionlabel');
    }

    public function getColor(){
        return '#FFFFFF';
    }

    public function getLanguage(){
        return Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('conversionlanguage');
    }

    public function getConversionId(){
        return Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('conversionid');
    }

    public function getConversionUrl(){
        return ($this->getRequest()->isSecure())? 'https://www.googleadservices.com/pagead/conversion.js': 'http://www.googleadservices.com/pagead/conversion.js';      
    }

    public function getValue(){
        $order = $this->_getOrder();
        if($order){
            if(Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('convertcurrencyenabled')) {
                $curconv = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('convertcurrency');
                if ($curconv) {
                    $basecur = $order->getBaseCurrency();
                    if($basecur) {
                        return sprintf("%01.4f",Mage::app()->getStore()->roundPrice($basecur->convert($order->getBaseGrandTotal(), $curconv)));
                    }
                }
            }
            return $order->getBaseGrandTotal();
        }else {
            return 0;
        }
    }

    private function _getOrder(){
        if(!$this->_quote){
            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
            if($quoteId){
                $this->_order = Mage::getModel('sales/order')->loadByAttribute('quote_id', $quoteId);
            }else{
               $this->_order = false;
            }
        }
        return $this->_order;
    }
}

