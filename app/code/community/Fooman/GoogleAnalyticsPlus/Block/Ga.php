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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_GoogleAnalytics
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Analytics block
 *
 * @category   Fooman
 * @package    Fooman_GoogleAnalyticsPlus
 * @author     Magento Core Team <core@magentocommerce.com>
 * @author     Fooman, Kristof Ringleff <kristof@fooman.co.nz>
 */
class  Fooman_GoogleAnalyticsPlus_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{

    /**
     * Return REQUEST_URI for current page
     * Magento default analytics reports can include the same page as
     * /checkout/onepage/index/ and   /checkout/onepage/
     * filter out index/ here
     *
     * @return string
     */
    public function getPageName() {
        if (!$this->hasData('page_name')) {
            $pageName = Mage::getSingleton('core/url')->escape($_SERVER['REQUEST_URI']);
            $pageName = rtrim(str_replace('index/','',$pageName), '/'); 
            $this->setPageName($pageName);
        }
        return $this->getData('page_name');
    }
   
    /**
     * Prepare and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $secure = Mage::app()->getStore()->isCurrentlySecure() ? 'true' : 'false';
        $handles = $this->getLayout()->getUpdate()->getHandles();
        
        if (in_array('checkout_onepage_success', $handles) || in_array('checkout_multishipping_success', $handles))
            $success = true;
        else {
            $success = false;
        }
        
        $helper = Mage::helper('googleanalytics');        
        if (method_exists($helper, 'isGoogleAnalyticsAvailable')) {
            //Mage 1.4.2 +
            $new = true;
            if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
                return '';
            }
            $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        } else {
            //Mage 1.4.1.1 and below
            $new = false;
            if (!Mage::getStoreConfigFlag('google/analytics/active')) {
                return '';
            }
            $accountId = $this->getAccount();
        }
        $accountId2 = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('accountnumber2');

        $html = '
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[
            (function() {
                var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;';
                if ($secure == 'true') {
                    if(Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('remarketing')){
                        $html .= 'ga.src = \'https://stats.g.doubleclick.net/dc.js\';';
                    } else {
                        $html .= 'ga.src = \'https://ssl.google-analytics.com/ga.js\';';
                    }
                } else {
                    if(Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('remarketing')){
                        $html .= 'ga.src = \'https://stats.g.doubleclick.net/dc.js\';';
                    } else {
                        $html .= 'ga.src = \'http://google-analytics.com/ga.js\';';
                    }
                }
                $html .= '
                var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
            })();
            var _gaq = _gaq || [];
'
  . $this->_getPageTrackingCode($accountId,$accountId2)
  . ($new?$this->_getOrdersTrackingCode($accountId2):'')
  . $this->_getAjaxPageTracking($accountId2) . '
//]]>
</script>
'
 . ($new?'':$this->_getQuoteOrdersHtml($accountId2))
 . ($success?$this->_getCustomerVars($accountId2):'').'
<!-- END GOOGLE ANALYTICS CODE -->
';
        return $html;
    }

    /**
     * Retrieve Order Data HTML
     *
     * @return string
     **/
    public function getOrderHtml()
    {
        if(!Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('convertcurrencyenabled')) {
            return parent::getOrderHtml();
        }

        $order = $this->getOrder();
        if (!$order) {
            return '';
        }

        if (!$order instanceof Mage_Sales_Model_Order) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!$order) {
            return '';
        }


        $address = $order->getBillingAddress();

        $html = '<script type="text/javascript">' . "\n";
        $html .= "//<![CDATA[\n";
        $html .= '_gaq.push(["_addTrans",';
        $html .= '"' . $order->getIncrementId() . '",';
        $html .= '"' . $order->getAffiliation() . '",';
        $html .= '"' . Mage::helper('googleanalyticsplus')->convert($order,'getBaseGrandTotal') . '",';
        $html .= '"' . Mage::helper('googleanalyticsplus')->convert($order,'getBaseTaxAmount') . '",';
        $html .= '"' . Mage::helper('googleanalyticsplus')->convert($order,'getBaseShippingAmount') . '",';
        $html .= '"' . $this->jsQuoteEscape($address->getCity(), '"') . '",';
        $html .= '"' . $this->jsQuoteEscape($address->getRegion(), '"') . '",';
        $html .= '"' . $this->jsQuoteEscape($address->getCountry(), '"') . '"';
        $html .= ']);' . "\n";

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $html .= '_gaq.push(["_addItem",';
            $html .= '"' . $order->getIncrementId() . '",';
            $html .= '"' . $this->jsQuoteEscape($item->getSku(), '"') . '",';
            $html .= '"' . $this->jsQuoteEscape($item->getName(), '"') . '",';
            $html .= '"' . $item->getCategory() . '",';
            $html .= '"' . Mage::helper('googleanalyticsplus')->convert($order,'getBasePrice', $item) . '",';
            $html .= '"' . $item->getQtyOrdered() . '"';
            $html .= ']);' . "\n";
        }

        $html .= '_gaq.push(["_trackTrans"]);' . "\n";
        $html .= '//]]>';
        $html .= '</script>';

        return $html;
    }

    /**
     *  Transaction on Mage 1.4.1.1 and below
     *  duplicate for secondary tracker
     *
     * @param bool|int $accountId2
     * @return string
     */
    protected function _getQuoteOrdersHtml ($accountId2 = false)
    {
        $html = "\n".parent::getQuoteOrdersHtml();
        if ($accountId2) {
            $html .= str_replace('_gaq.push(["_', '_gaq.push(["t2._', $html);
        }
        return $html;
    }

    /**
     * Render information about specified orders and their items
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @param bool $accountId2
     * @return string
     */
    protected function _getOrdersTrackingCode($accountId2 = false)
    {
        if (Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('convertcurrencyenabled')) {

            $orderIds = $this->getOrderIds();
            if (empty($orderIds) || !is_array($orderIds)) {
                return;
            }

            $collection = Mage::getResourceModel('sales/order_collection')
                ->addFieldToFilter('entity_id', array('in' => $orderIds));
            $result = array();
            foreach ($collection as $order) {
                if ($order->getIsVirtual()) {
                    $address = $order->getBillingAddress();
                } else {
                    $address = $order->getShippingAddress();
                }

                $result[] = sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $order->getIncrementId(),
                    Mage::app()->getStore()->getFrontendName(),
                    Mage::helper('googleanalyticsplus')->convert($order, 'getBaseGrandTotal'),
                    Mage::helper('googleanalyticsplus')->convert($order, 'getBaseTaxAmount'),
                    Mage::helper('googleanalyticsplus')->convert($order, 'getBaseShippingAmount'),
                    $this->jsQuoteEscape($address->getCity()),
                    $this->jsQuoteEscape($address->getRegion()),
                    $this->jsQuoteEscape($address->getCountry())
                );

                foreach ($order->getAllVisibleItems() as $item) {
                    $result[] = sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                        $order->getIncrementId(),
                        $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                        null, // there is no "category" defined for the order item
                        Mage::helper('googleanalyticsplus')->convert($order, 'getBasePrice', $item),
                        $item->getQtyOrdered()
                    );

                }
                $result[] = "_gaq.push(['_trackTrans']);";
                $html = implode("\n", $result);
            }
        } else {
            $html = "\n" . parent::_getOrdersTrackingCode();
        }

        if ($accountId2) {
            $html .= str_replace('_gaq.push([\'_', '_gaq.push([\'t2._', $html);
        }
        return $html;
    }

    protected function _getCustomerVars ($accountId2 = false)
    {
        //set customer variable for the current visitor c=1
        return '
<script type="text/javascript">
//<![CDATA[
    _gaq.push(["_setCustomVar", 5, "c", "1", 1]);
    '.($accountId2?'
    _gaq.push(["t2._setCustomVar", 5, "c", "1", 1]);
':'').'
//]]>
</script>
';
    }

    /**
     * @param $accountId
     * @param bool|int $accountId2
     * @return string
     */
    protected function _getPageTrackingCode ($accountId, $accountId2 = false)
    {
        //url to track
        $optPageURL = trim($this->getPageName());
        if ($optPageURL && preg_match('/^\/.*/i', $optPageURL)) {
            $optPageURL = "{$this->jsQuoteEscape($optPageURL)}";
        }

        //main profile tracking including optional first touch tracking
        $html = '
            _gaq.push(["_setAccount", "' . $this->jsQuoteEscape($accountId) . '"]';
        if ($domainName = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('domainname')) {
            $html .=' ,["_setDomainName","' . $domainName . '"]';
        }
        if($anonymise = Mage::getStoreConfigFlag('google/analyticsplus/anonymise')) {
            $html .=', ["_gat._anonymizeIp"]';
        }
        if(Mage::getStoreConfigFlag('google/analyticsplus/firstouch')) {
            $html .=');
            asyncDistilledFirstTouch(_gaq);
            _gaq.push(["_trackPageview","' . $optPageURL . '"]';
        } else {
            $html .=', ["_trackPageview","' . $optPageURL . '"]';
        }

        if(Mage::getStoreConfigFlag('google/analyticsplus/trackpageloadtime')) {
            $html .=', ["_trackPageLoadTime"]';
        }        
        $html .=');';
        
        //track to alternative profile (optional)
        if($accountId2){
            $html .= '
            _gaq.push(["t2._setAccount", "' . $this->jsQuoteEscape($accountId2) . '"]';
            if ($domainName2 = Mage::helper('googleanalyticsplus')->getGoogleanalyticsplusStoreConfig('domainname2')) {
                $html .=' ,["t2._setDomainName","' . $domainName2 . '"]';
            }
            if($anonymise){
                //anonymise requires the synchronous tracker object so likely not needed on this one
                //$html .=', ["t2._anonymizeIp"]';
            }
            $html .=', ["t2._trackPageview","' . $optPageURL . '"]';
            
            if(Mage::getStoreConfigFlag('google/analyticsplus/trackpageloadtime')) {
                $html .=', ["_trackPageLoadTime"]';
            }        
            $html .=');';            
        }

        return $html;
    }

    /**
     * return code to track AJAX requests
     *
     * @param bool|int $accountId2
     *
     * @return string
     */
    private function _getAjaxPageTracking($accountId2 = false)
    {
        $baseUrl = preg_replace('/\/\?.*/', '', $this->getPageName());
        //$query = preg_replace('/.*\?/', '', $this->getPageName());
        return '

            if(Ajax.Responders){
                Ajax.Responders.register({
                  onComplete: function(response){
                    if(!response.url.include("progress") && !response.url.include("getAdditional")){
                        if(response.url.include("saveOrder")){
                            _gaq.push(["_trackPageview", "'.$baseUrl.'"+ "/opc-review-placeOrderClicked"]);'
                            .($accountId2?'
                            _gaq.push(["t2._trackPageview", "'.$baseUrl.'"+ "/opc-review-placeOrderClicked"]);':'').'
                        }else if(accordion.currentSection){
                            _gaq.push(["_trackPageview", "'.$baseUrl.'/"+ accordion.currentSection]);'
                            .($accountId2?'
                            _gaq.push(["t2._trackPageview", "'.$baseUrl.'/"+ accordion.currentSection]);':'').'
                        }
                    }
                  }
                });
            }
';
    }

}
