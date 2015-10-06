<?php

/**
 * The Punchout Session controls most punchout related
 * actions.
 *
 * This session does not actually use it's own storage but stores
 * information in the mage catalog/session.
 *
 * Requires \Vbw\Procurement library
 *
 */
require_once "Vbw/Procurement/Punchout/Request.php";
require_once "Vbw/Procurement/Punchout/Order.php";



class Vbw_Punchout_Model_Session
    extends Varien_Object
{

    const SESSION_TYPE_ANONYMOUS = "anonymous";
    const SESSION_TYPE_SINGLE    = "single";
    const SESSION_TYPE_DUAL      = "dual";
    const SESSION_TYPE_DISCOVER  = "discover";

    /**
     * the inital po2go request
     *
     * @var Vbw_Procurement_Punchout_Request
     */
    protected $_punchoutRequest = null;

    /**
     * @var null
     */
    protected $_session_url = null;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     *
     * @var Vbw_Punchout_Helper_Config
     */
    protected $_configHelper = null;


    protected $_error = null;
//	protected $_poOrder	= null;

	public function __construct()
	{

	}

    /**
     *
     * @return Mage_Catalog_Model_Session
     */
	public function getMageSession ()
	{
		return Mage::getSingleton('catalog/session');
	}

    /**
     *
     * @return Mage_Checkout_Model_Session
     */
	protected function _getCheckoutSession()
	{
		return Mage::getSingleton('checkout/session');
	}

    /**
     *
     * @return Mage_Checkout_Model_Cart
     */
	protected function _getCart()
	{
        return Mage::getSingleton('checkout/cart');
	}


    /**
     *
     * @return Mage_Sales_Model_Quote
     */
	public function getQuote()
	{
		return $this->_getCheckoutSession()->getQuote();
	}


    /**
     * set the punchout session id
     *
     * @param $punchoutId
     */
	public function setPunchoutId ($punchoutId)
	{
		$this->getMageSession()->setPunchoutId($punchoutId);
	}

    /**
     * set the data from the punchout gateway
     *
     * @param $data
     */
    public function setPunchoutData ($data)
    {
        $this->getMageSession()->setPunchoutData($data);
    }

    /**
     * get the punchout data from the mage session
     *
     * @return array
     */
    public function getPunchoutData ()
    {
        return $this->getMageSession()->getPunchoutData();
    }

    /**
     * get the inflated punchout request object.
     *
     * @return \Vbw\Procurement\Punchout\Request
     */
    public function getPunchoutRequest ()
    {
        if ($this->_punchoutRequest == null) {
            $this->_punchoutRequest = new \Vbw\Procurement\Punchout\Request();
            $this->_punchoutRequest->inflate($this->getPunchoutData());
        }
        return $this->_punchoutRequest;
    }

    /**
     * get the punchout session id
     *
     * @return string
     */
	public function getPunchoutId ()
	{
		return $this->getMageSession()->getPunchoutId();
	}

    /**
     * test if a punchout session is active.
     *
     * @return boolean
     */
    public function isPunchoutSession ()
    {
        $id = $this->getPunchoutId();
        if (!empty($id)) {
            return true;
        }
        return false;
    }

    /**
     * get the mage session id.
     *
     * @return mixed
     */
	public function getSessionId ()
	{
		return $this->getMageSession()->getSessionId();
	}

    /**
     * dumps all the identifying content of the session.
     *
     */
    public function emptySession()
    {
        $this->setPunchoutId(null);
        $this->setPunchoutData(null);
        $this->_getCheckoutSession()->clear();
    }


    /**
     * return the config helper which is used to access configurations
     * related to the module.
     *
     * @return Vbw_Punchout_Helper_Config
     */
    public function getConfigHelper()
    {
        if ($this->_configHelper == null)  {
            $this->_configHelper = Mage::helper('vbw_punchout/config');
        }
        return $this->_configHelper;
    }

    /**
     * get a config value, this will hopefully be modified
     * with a later version that allows the admin to update.
     *
     * @param string $xpath
     * @return mixed
     */
    public function getConfig($xpath)
    {
        return $this->getConfigHelper()->getConfig($xpath);
    }


    /**
     * log a user in to the session
     *
     * @param $id
     */
    public function loginById ($id)
    {
        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton('customer/session');
        $customerSession->loginById($id);
        $customerSession->renewSession();
        if (!$this->hasCustomer()
                || $this->getCustomer()->getId() != $id) {
            $this->setCustomer($customerSession->getCustomer());
        }
        return $this->getCustomer();
    }


    /**
     *  get the inflated punchout order object from the magento order
     *
     * @return \Vbw\Procurement\Punchout\Order
     */
    public function getPunchoutOrder ()
    {
        /** @var $salesHelper Vbw_Punchout_Helper_Sales */
        $salesHelper = Mage::helper('vbw_punchout/sales');
        $stash = $salesHelper->stashBaseOrderData($this->getQuote());

        $request = $this->getPunchoutRequest();
        $poOrder = new \Vbw\Procurement\Punchout\Order;
        /**
         * @var $body \Vbw\Procurement\Punchout\Order\Body
         */
        $body = $poOrder->getBody();
        $body->setBuyerCookie($request->getBody()->getBuyerCookie());

        /**@var $distiller Vbw_Punchout_Model_Punchout_Distiller*/
        $distiller = Mage::getModel("vbw_punchout/punchout_distiller");

        // get items to add
        $products = $this->getQuote()->getAllVisibleItems();
        $distiller->addItems($poOrder,$products);

        // set edit mode
        $body->setEditMode($distiller->getOrderEditMode($products));

        // add totals to the order
        $distiller->addTotals($poOrder,$this->getQuote());

        Mage::dispatchEvent('punchout_order_after_setup',array('po_order'=>$poOrder,'quote'=>$this->getQuote()));

        return $poOrder;
    }

    /**
     * gets the remote host for the punchout2go gateway.
     *
     * @return string
     */
    public function getRemoteHost ()
    {
        $host = $this->getConfig("api/host");
        $secure = $this->getConfig("api/secure");
        $root = ($secure == 1 ? "https://" : "http://") . $host;
        return $root;
    }

    /**
     * get the order document from punchout2go as a form ready to be posted to the procurement system.
     *
     * @return mixed (data or null on error)
     */
    public function getPunchoutOrderFrom ()
    {
        $options = array (
            "apikey" => $this->getConfig("api/key"),
            "instructions" => array('order_encoding'=>$this->getConfig('site/order_encoding')),
            "gateway" => $this->getRemoteHost() . $this->getConfig("api/order_path") ."/id/". $this->getPunchoutId(),
            "version" => $this->getConfig("api/version"),
        );
        $client = new Vbw_Procurement_Link_Client();
        $client->setOptions($options);
        $punchoutOrder = $this->getPunchoutOrder();
        $response = $client->request($punchoutOrder->toArray());
        if ($client->hasError()) {
            echo $client->getError();
            exit;
           $this->setError($client->getError());
        }
        return $response;
    }

    /**
     * get just the punchout order document from punchout2go
     *
     * @return mixed (data or null on error)
     */
    public function getPunchoutOrderDocument ()
    {
        $options = array (
            "apikey" => $this->getConfig("api/key"),
            "gateway" => $this->getRemoteHost() . $this->getConfig("api/inspect_path") ."/id/". $this->getPunchoutId(),
            "version" => $this->getConfig("api/version"),
        );
        $client = new Vbw_Procurement_Link_Client();
        $client->setOptions($options);
        $results = $client->request($this->getPunchoutOrder()->toArray());
        if (!empty($results)) {
            return $results;
        } else {
            $this->setError($client->getError() . $client->getResults());
            return null;
        }
    }


    /**
     * load the punchout request data from the punchout2go gateway
     *
     * @return array
     */
    public function loadPunchoutSession ($id)
    {
        $config = array (
            "apikey" => $this->getConfig("api/key"),
            "gateway" => $this->getRemoteHost() . $this->getConfig("api/pull_path") ."/id/". $id,
            "version" => $this->getConfig("api/version"),
        );

        $client = new Vbw_Procurement_Link_Client();
        $client->setOptions($config);
        $response = $client->request();

        if (is_array($response)) {
            $this->setPunchoutId($id);
            $this->setPunchoutData($response);
            return $response;
        } else {
            return false;
        }

    }


    /**
     * start the punchout session by emptying the current
     * order and inserting any incoming order data, this
     * might work better to just clear out the order object.
     *
     */
    public function startPunchoutSession ()
    {
        Mage::helper('vbw_punchout')->debug('Starting punchout session');
        $this->_getCheckoutSession()->setIsPunchoutCartInit(true);
        Mage::dispatchEvent('punchout_session_starting',array ('session'=> $this));
        /**
         * @var $request \Vbw\Procurement\Punchout\Request
         */
        $request = $this->getPunchoutRequest();

        // add user/buyer to session
        $this->injectPunchoutUserToSession();

        // add new product
        $this->injectPunchoutProductToSession();
        if (Mage::helper('vbw_punchout/session')->isEdit()) {
            if ($this->_getCheckoutSession()->getCheckoutState() == null) {
                Mage::helper('vbw_punchout')->debug('Updating checkout state to : '. Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN);
                $this->_getCheckoutSession()->setCheckoutState(Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN);
            }
        }

        $quote = $this->getQuote();
        $id = $quote->getId();
        Mage::helper('vbw_punchout')->debug('Quote ID '. (is_numeric($id) ? $id : 'new'));

        // clear the checkout session. - replaced with better loading
        // $this->clearOutExistingSession();
        // add shipping information
        $this->injectPunchoutShippingToSession();

        $quote->setIsActive(1);
        $quote->save();

        Mage::helper('vbw_punchout')->debug('Quote Ready '. (is_numeric($id) ? $id : 'new'));
        if ($this->_getCheckoutSession()->getQuoteId() != $quote->getId()) {
            Mage::helper('vbw_punchout')->debug('Non matching quote id : '. $this->_getCheckoutSession()->getQuoteId());
            $this->_getCheckoutSession()->setQuoteId($quote->getId());
        }
        Mage::dispatchEvent('punchout_session_ready',array ('session'=> $this));

    }

    /**
     * clear out any pre-existing session data if it happens to attach a previous
     * session.
     */
    public function clearOutExistingSession ()
    {
        // dump the cart
        $checkoutSession = $this->_getCheckoutSession();
        //$checkoutSession->unsetAll();
        //$checkoutSession->setQuoteId(null);
        //$checkoutSession->setLastSuccessQuoteId(null);

        // reset the punchout quote.
        /** @var $helper Vbw_Punchout_Helper_Session */

        /* remove logic from here, only event : custom_quote_process
        $helper = Mage::helper('vbw_punchout/session');
        if ($checkoutSession->hasQuote()) {
            $helper->setupPunchoutQuote($checkoutSession,true);
        }
        */

        //$checkoutSession->getQuote()->save();

//        Mage::unregister('_singleton/checkout/cart');
//        Mage::unregister('_singleton/sales/quote');
//        $quote = $this->getQuote();
//        if (is_numeric($quote->getId())) {
//            $emptyQuote = Mage::getModel('sales/quote');
//           $this->_getCheckoutSession()->replaceQuote($emptyQuote);
//       }
    }

    /**
     * add punchout products to the session
     */
    public function injectPunchoutProductToSession ()
    {
        $request = $this->getPunchoutRequest();
        /** @var $helper Vbw_Punchout_Helper_Session */
        $helper = Mage::helper('vbw_punchout/session');
        $dataHelper = Mage::helper('vbw_punchout');

        $cartObj = $this->_getCart();
        $quoteObj = $this->getQuote();

        if (Mage::helper('vbw_punchout/session')->isEdit()) {

            /** @var $items \Vbw\Procurement\Punchout\Request\Body\Items  */
            $items = $request->getBody()->getItems();
            $item = $items->current();
            $secondaryId = $item->get('secondaryId');

            if (preg_match('/^(\d+)\/(\d+)$/',$secondaryId,$s)) {

                $dataHelper->debug('Trying to reload quote : '. $s[1]);
                /** @var $existingQuote Mage_Sales_Model_Quote */
                $existingQuote = Mage::getModel('sales/quote')->load($s[1]);
                if (!empty($existingQuote)
                        && is_numeric($existingQuote->getId())
                        && $existingQuote->getId() == $s[1]) {
                    if ($existingQuote->getStoreId() == $quoteObj->getStoreId()) {
                        $checkout = $this->_getCheckoutSession();
                        $checkout->unsetAll();
                        $checkout->setQuoteId($existingQuote->getId());
                        $dataHelper->debug('Trying to set quote : '. $existingQuote->getId());
                        Mage::helper('vbw_punchout/sales')->unstashBaseOrderData($cartObj,$existingQuote->getId());
                        return;
                    } else {
                        $dataHelper->debug('quote belongs to a different store');
                    }
                } else {
                    $dataHelper->debug('quote not found');
                }

            }

            // if the above does not do anything, then use normal processing

            // remove existing.
            $helper->dumpCartProduct($quoteObj,$cartObj);

            // add incoming
            $items = $request->getBody()->getItems();
            $matching = count($items);
            $previous_quote = $helper->addCartProduct($items,$cartObj);

            Mage::helper('vbw_punchout/sales')->unstashBaseOrderData($cartObj,$previous_quote);

        } else {
            if (false == $helper->usePersistentCart()) {
                // remove anything in the current session.
                // mainly applies to someone who has a cookie
                // already on their browser.
                $helper->dumpCartProduct($quoteObj,$cartObj);
            } else {
                // make no changes to the cart items in the session.
            }
        }
        $cartObj->init();
        try {
            $cartObj->save();
        } catch (Exception $e) {
            $msg = "Exception saving cart to session : ". $e->getMessage();
            Mage::helper('vbw_punchout')->debug($msg);
        }
        if (isset($matching)
                && $matching > 0) {
            $cartObj->getQuote()->collectTotals();
            Mage::helper('vbw_punchout')->debug("matching with {$matching} and {$cartObj->getQuote()->getItemsCount()}");
            if ($cartObj->getQuote()->getItemsCount() < $matching) {
                Mage::getSingleton('core/session')->addError('Some items were not added to your cart. Please review your cart.');
            } else {
                foreach ($cartObj->getQuote()->getAllVisibleItems() AS $item) {
                    if ($item->getProduct()->getStatus() != 1) {
                        Mage::getSingleton('core/session')->addError($item->getName() .' is no longer available. Please review your cart.');
                    }
                }
            }
        }


    }

    /**
     * inserts the shipping information from the PO setup in to the
     * session's shipping address.
     *
     */
    public function injectPunchoutShippingToSession ()
    {
        /** @var $dataHelper Vbw_Punchout_Helper_Data */
        $dataHelper = Mage::helper('vbw_punchout');

        $request = $this->getPunchoutRequest();
        /** @var $helper Vbw_Punchout_Helper_Session */
        $helper = Mage::helper('vbw_punchout/session');
        $quoteObj = $this->getQuote();

        $shippingAddress = $quoteObj->getShippingAddress();

        // if the shipping address is already populated, then leave it alone.
        if (Mage::helper('vbw_punchout/session')->isEdit()
                && $shippingAddress->getCity() != null) {
            $dataHelper->debug('This is an edit with shipping data, leave it alone.');
            return true;
        }

        $shipping = $this->getConfig('customer/attach_shipping');

        $data = $request->getBody()->getShipping();

        $dataHelper->debug('Add shipping to quote : '. $shipping);
        try {
            if ($shipping > 0
                && $shipping != 3) {
                $helper->addQuoteShipping($data,$quoteObj);
            }
        } catch (Exception $e) {
            $msg = "Exception adding shipping to quote : ". $e->getMessage();
            $dataHelper->debug($msg);
        }

//        $quoteObj->save();
//        $this->_getCheckoutSession()->setQuoteId($quoteObj->getId());

    }

    public function injectPunchoutUserToSession ()
    {
        /**
         * @var $request \Vbw\Procurement\Punchout\Request
         * @var $configHelper Vbw_Punchout_Helper_Config
         * @var $sessionHelper Vbw_Punchout_Helper_Session
         */
        $request = $this->getPunchoutRequest();
        $configHelper = Mage::helper('vbw_punchout/config');
        $sessionHelper = Mage::helper('vbw_punchout/session');
        if ($configHelper->isAnonymousSession()) {
            $sessionHelper->prepareAnonymousSession($request);
        } elseif ($configHelper->isSingleLoginSession()) {
            $sessionHelper->prepareSingleLoginSession($request);
        } elseif ($configHelper->isDiscoverLoginSession()) {
            $sessionHelper->prepareDiscoverLoginSession($request);
        //} elseif ($configHelper->isDualLoginSession()) {
        //    $sessionHelper->prepareDualLoginSession($request);
        }
    }

    public function setError ($error = null)
    {
        $this->_error = $error;
    }

    public function getError ()
    {
        return $this->_error;
    }

    /**
     *
     * @return url
     */
    public function getSessionUrl ()
    {
        /** @var $dataHelper Vbw_Punchout_Helper_Data */
        $dataHelper = Mage::helper('vbw_punchout');
        if ($this->getData('session_url') == null) {
            $request = $this->getPunchoutRequest();
            $SID = $this->getMageSession()->getSessionIdQueryParam();
            if (Mage::helper('vbw_punchout/session')->isEdit()) {
                //header('HTTP/1.1 302 Redirect');
                $custom = $this->getConfig('site/start_redirect_edit');
                $dataHelper->debug('Custom Edit : '. $custom);
                $url = (!empty($custom) ? $custom : 'checkout/cart');
                $dataHelper->debug('Using Edit : '. $custom);
                $params = 'POID='. $this->getPunchoutId() ."&{$SID}=". $this->getSessionId();
            } else {
                // header('HTTP/1.1 302 Redirect');
                $product = $this->getSelectedItemFromRequest();
                if (!empty($product)) {
                    $params = array (
                        'POID' =>  $this->getPunchoutId(),
                        $SID =>  $this->getSessionId()
                    );
                    $url = $product->getUrlInStore($params);
                    return $url;
                } else {
                    $params = 'POID='. $this->getPunchoutId() ."&{$SID}=". $this->getSessionId();
                    $custom = $this->getConfig('site/start_redirect_new');
                    $url = (!empty($custom) ? $custom : '/');
                }
            }
            // when using this event, use this helper and format
            $helper = Mage::Helper('vbw_punchout/url');
            $this->setData('session_url',$helper->getPunchoutLandingRootUrl($url,$params));
            Mage::dispatchEvent('punchout_get_session_url',array('session'=>$this));
        }
        $dataHelper->debug('Redirecting to : '. $this->getData('session_url'));
        return $this->getData('session_url');
    }

    /**
     * @return bool|Mage_Catalog_Model_Product
     */
    public function getSelectedItemFromRequest(){
        $request = $this->getPunchoutRequest();
        $items = $request->getBody()->getItems();
        foreach ($items as $item) {
            if($item->get('type') == "in") {
                $sku = $item->get('primaryId');
                if ($sku != 'AAA') {
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                    if (!empty($product)){
                        return $product;
                    }
                }
            }
        }

        return false;
    }

    /**
     * based on the punchout request, figure out where to
     * send the user.
     *
     * @param null $responseObj
     */
    public function redirect ($responseObj = null)
    {
        $location = $this->getSessionUrl();

        $text = "Redirecting to : <a href='{$location}'>{$location}</a>";
        if ($responseObj instanceof Mage_Core_Controller_Response_Http) {
            $responseObj->setRedirect($location, 302);
            $responseObj->setBody($text);
            //$responseObj->sendResponse();
            return;
        } else {
            header('HTTP/1.1 302 Redirect');
            header('P3P: CP="CAO DSP COR CUR ADM DEV CONi OPTi OUR NOR PHY ONL COM NAV DEM CNT STA HEA PRE"');
            header('Location: '. $location);
            echo $text;
            exit;
        }
    }

    /**
     * @param \Mage_Customer_Model_Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
    }

    /**
     * @return \Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->_customer == null) {
            // load from somewhere?
        }
        return $this->_customer;
    }

    /**
     * @return bool
     */
    public function hasCustomer()
    {
        if ($this->_customer == null) {
            return false;
        }
        return true;
    }

    /*
     * old mechanism.
	public function injectPunchoutSession($punchoutSession)
	{

            self::InjectShippingToQuote($punchoutSession);
            self::InjectItemsToCart($punchoutSession);
	}

        public static function InjectItemsToCart ($punchoutSession)
        {
            if ($punchoutSession->punchoutOperation == "edit") {
                $cartObj = Mage::getSingleton('checkout/cart');
                $quoteObj = Mage::getSingleton('checkout/session')->getQuote();
                $products = $quoteObj->getAllItems();
                foreach ($products AS $product) {
                    $cartObj->removeItem($product->getId());
                }
                foreach ($punchoutSession->cart->item AS $k=>$item) {
                    $cartObj->addProduct((int) $item->supplierAuxId,array('qty'=>(int) $item->quantity));
                }
                $cartObj->save();
            } else {
                $cartObj = Mage::getSingleton('checkout/cart');
                $quoteObj = Mage::getSingleton('checkout/session')->getQuote();
                $products = $quoteObj->getAllItems();
                foreach ($products AS $product) {
                    $cartObj->removeItem($product->getId());
                }
                $cartObj->save();
            }
        }

	public static function InjectShippingToQuote ($punchoutSession)
	{
            $quoteObj = Mage::getSingleton('checkout/session')->getQuote();
            $quoteObj->getShippingAddress()
                ->setCountryId('US')
                ->setCity($punchoutSession->shipping_city)
                ->setPostcode($punchoutSession->shipping_zip)
                // ->setRegionId($regionId)
                ->setRegion($punchoutSession->state)
                ->setCollectShippingRates(true);
            $quoteObj->save();
	}
	     */

	public function reviewSession ()
	{
		Lib_Firebug::Log("Review");
		// $cart = $this->_getCart();
		if ($this->getQuote()->getItemsCount() > 0) {
			Lib_Firebug::Log("Count ". $this->getQuote()->getItemsCount());
			if (!$this->getQuote()->getShippingAddress()->getPostcode()) {
				Lib_Firebug::Log("Postal ". $this->getQuote()->getShippingAddress()->getPostcode());
				$punchoutSession =Vbw_Punchout::GetSession($this->getPunchoutId());
				if ($punchoutSession->shipping_zip != '') {
					Lib_Firebug::Log("Zip " .$punchoutSession->shipping_zip);
					self::InjectShippingToQuote($punchoutSession);
				}
			}
		}
	}

}