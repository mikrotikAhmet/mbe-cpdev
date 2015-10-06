<?php



class Vbw_Punchout_Helper_Url
	extends Mage_Core_Helper_Url
{

  /**
     * Return url for landing root
     *
     * @return $fullUrl - the full URL
     */
    public function getPunchoutLandingRootUrl($route,$parameters = null)
    {
        /** @var $url Mage_Core_Model_Url */
        $url = Mage::getModel('core/url');

        if ($parameters != null) {
            $params = array ();
            $params['_query'] = $parameters;
        } else {
            $params = array();
        }
        $secure = Mage::helper('vbw_punchout/config')->getConfig('site/secure_redirect');
        if ($secure) {
            $params['_forced_secure'] = true;
            $url->setSecure(true);
        }
        $fullUrl = $url->getUrl($route, $params);
        return $fullUrl;
    }


    public function getReturnUrl(){
        $helper = Mage::helper('vbw_punchout/config');
        $url = $helper->getConfig('display/return_url');
        $label = $helper->getConfig('display/return_label');
        if (empty($url)){
            $url = Mage::helper('vbw_punchout/session')->getRequest()->getBody()->getPostForm();
        }
        $target = $helper->getConfig('display/return_target');

        return '<a href="' . $url . '" title="' . $label . '" target="' . $target . '">' . $label . '</a>';
    }

}
	