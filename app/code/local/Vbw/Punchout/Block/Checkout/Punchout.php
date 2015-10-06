<?php
require_once "Vbw/Procurement/Link/Client.php";

class Vbw_Punchout_Block_Checkout_Punchout extends Mage_Core_Block_Template
{

    protected static $_rand = null;

    protected $_error = null;

    public function setError ($error = null)
    {
        $this->_error = $error;
    }

    public function getError ()
    {
        return $this->_error;
    }

    public function getRand()
    {
        if (self::$_rand == null) {
            self::$_rand = (int) rand(10000,99999); //uniqid((int) rand(10000,99999));
        }
        return self::$_rand;
    }

    public function getCheckoutUrl()
    {
        $secure = $this->getConfig('site/secure_redirect');
        if ($secure) {
            return $this->getUrl('checkout', array('_secure'=>true));
        } else {
            return $this->getUrl('checkout');
        }
    }

    public function isDisabled()
    {
        // return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
        return false;
    }

    public function isPossibleOnepageCheckout()
    {
        return $this->helper('checkout')->canOnepageCheckout();
    }
    
    public function getPunchoutOnclick()
    {
        $rand = $this->getRand();
        //return "self.document.transferorder". $rand .".submit()";
        return "self.location.href='". $this->getTransferCartUrl() ."'";
//    	return "vbw_punchout.process(this)";
    }

    public function getConfig ($xpath)
    {
        $session = Mage::getSingleton('vbw_punchout/session');
        return $session->getConfig($xpath);
    }

    public function getPunchoutSessionId ()
    {
        $session = Mage::getSingleton('vbw_punchout/session');
        return $session->getPunchoutId();
    }

    public function getRemoteHost ()
    {
        $session = Mage::getSingleton('vbw_punchout/session');
        return $session->getRemoteHost();
    }

    public function isDemoSession ()
    {
        /**
         * @var $helper Vbw_Punchout_Helper_Session
         */
        $helper = Mage::helper('vbw_punchout/session');
        return $helper->isDemoSession();
    }

    public function getButtonLabel()
    {
        /** @var $helper Vbw_Punchout_Helper_Config */
        $helper = Mage::helper('vbw_punchout/config');
        return $helper->getConfig('display/transfer_button');
    }

    public function getButtonHelpText ()
    {
        /** @var $helper Vbw_Punchout_Helper_Config */
        $helper = Mage::helper('vbw_punchout/config');
        return $helper->getConfig('display/transfer_text');
    }

    public function getTransferCartUrl ()
    {
        $secure = $this->getConfig('site/secure_redirect');
        if ($secure) {
            $url = Mage::getUrl('punchout/2go/transfer',array('_forced_secure'=>1));
        } else {
            $url = Mage::getUrl('punchout/2go/transfer');
        }
        return $url;
    }

    public function getPunchoutOrderForm()
    {

        if ($this->isDemoSession()) {
            $this->setError(array('code'=>300));
            return false;
        }

        $secure = $this->getConfig('site/secure_redirect');
        if ($secure) {
            $url = Mage::getUrl('punchout/2go/transfer',array('_forced_secure'=>1));
        } else {
            $url = Mage::getUrl('punchout/2go/transfer');
        }

        $rand = $this->getRand();
        return "<form action='{$url}' name=\"transferorder{$rand}\" id=\"transferorder{$rand}\" ></form>";

        /**
         * below is no longer used.
         */

        /**
         * @var $session Vbw_Punchout_Model_Session
         */
    	$session = Mage::GetSingleton("vbw_punchout/session");
        $punchoutOrder = $session->getPunchoutOrder();
    	$punchoutRequest = $session->getPunchoutRequest();

        $string = $session->getPunchoutOrderFrom();

        if (!empty($string)) {
            $string .= <<<__EOD
<script src="{$this->getRemoteHost()}{$this->getConfig('api/tools_path')}" language="javascript"></script>
<script language="javascript">
vbw_punchout.session = "{$this->getPunchoutSessionId()}";
vbw_punchout.host = "{$this->getConfig('api/host')}";
vbw_punchout.secure = "{$this->getConfig('api/secure')}";
vbw_punchout.session_name = "{$this->getSessionName()}";
vbw_punchout.session_path = "{$this->getSessionPath()}";
</script>
__EOD;
        } else {
            $this->setError($session->getError());
        }

        /** <input type="hidden" name="cxml-base64" value=
          "Entire text of base64-encoded cXML PunchOutOrderMessage document">
          or
          <input type="hidden" name=" cxml-urlencoded" value=
          "Entire text of URL-encoded cXML PunchOutOrderMessage document">
           base64_encode */

	    return $string;

    }

    public function _toHtml()
    {
        $this->_rand = null;
        return parent::_toHtml();
    }

    public function getSessionName ()
    {
        // return Mage::getStoreConfig('web/cookie/cookie_path');
        return "frontend";
    }

    public function getSessionPath()
    {
        return Mage::getStoreConfig(Mage_Core_Model_Session_Abstract::XML_PATH_COOKIE_PATH);
    }
}