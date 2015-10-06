<?php

class Vbw_Punchout_Model_Observer
{

    /**
     * protect against an infinite loop with a session init event.
     *
     * @var int
     */
    protected static $_inSessionEvent = 0;


    /**
     * the single login session should always have an active
     * user, the user should match the configuration rules.
     *
     * @param Varien_Event_Observer $observer
     */
    public function storeLastItemAddedToCart (Varien_Event_Observer $observer)
    {
        /** @var $session Vbw_Punchout_Model_Session */
        $session = Mage::getSingleton('vbw_punchout/session');
        $quote_item = $observer->getEvent()->getData('quote_item');
        if ($session->isPunchoutSession()) {
            $session->setData('last_quote_item',$quote_item);
            Mage::helper('vbw_punchout')->debug('set last quote item'. $quote_item->getSku());
        }
    }



    /**
     * Create handle for persistent session if persistent cookie and customer not logged in
     *
     * @param Varien_Event_Observer $observer
     */
    public function addLayoutUpdates(Varien_Event_Observer $observer)
    {

        /** @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getLayout();

        $updates = Mage::helper('vbw_punchout')->getLayoutUpdateNodes();

        if (is_array($updates)
                && !empty($updates)) {
            foreach ($updates AS $node) {
                if (is_array($node)) {
                    $instructions = implode("\n",$node);
                    $layout->getUpdate()->addUpdate($instructions);
                } else {
                    $layout->getUpdate()->addHandle($node);
                }
            }
        }

    }


    /**
     * sets up checkout session quote
     *
     * @param Varien_Event_Observer $observer
     */
    public function punchoutCheckoutCartLayout ($observer)
    {
        /** @var $punchout Vbw_Punchout_Model_Session */
        $punchout = Mage::getSingleton('vbw_punchout/session');
        if ($punchout->isPunchoutSession()) {

            $instructions = array();
            $config = Mage::helper('vbw_punchout/config');

            $location = $config->getConfig('display/transfer_button_location');
            if ((1 & $location) == 0) {
                $instructions[] = "<remove name='checkout.cart.methods.punchout.top' />";
            }
            if ((2 & $location) == 0) {
                $instructions[] = "<remove name='checkout.cart.methods.punchout.bottom' />";
            }

            if ($config->getConfig('display/personal_checkout')) {
                if ((1 & $location) == 0) {
                    $instructions[] = '<remove name="checkout.cart.methods.onepage.personal.top" />';
                }
                if ((2 & $location) == 0) {
                    $instructions[] = '<remove name="checkout.cart.methods.onepage.personal.bottom" />';
                }
            } else {
                $instructions[] = '<remove name="checkout.cart.methods.onepage.personal.top" />';
                $instructions[] = '<remove name="checkout.cart.methods.onepage.personal.bottom" />';
            }

            if ($config->getConfig('order/include_shipping') == false) {
                $instructions[] = '<remove name="checkout.cart.shipping" />';
            }

            if ($config->getConfig('order/include_discount') == false) {
                $instructions[] = '<remove name="checkout.cart.coupon" />';
            }

            $customUpdates = $config->getConfig('display/punchout_cart_layout_update');
            if (!empty($customUpdates)) {
                $instructions[] = $customUpdates;
            }

            Mage::helper('vbw_punchout')
                ->addLayoutUpdateNode('punchout_checkout_cart_index')
                ->addLayoutUpdateNode($instructions);
        }

    }


    /**
     * checks for a punchout session in the query string, if it
     * exists, it checks for the magento session id. if that is
     * set then it tries to set the current session id.
     *
     * this is done to get around magento's protection requiring
     * an owned referring url.
     *
     * @param Varien_Event_Observer $observer
     */
    public function punchoutWithNoTax (Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        if (Mage::getSingleton('vbw_punchout/session')->isPunchoutSession()) {

            if (!$quote) {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
            }
            if (!$quote) {
                return;
            }
            $storeId = $quote->getStoreId();

            // replace only if this future enable for current store
            if (!Mage::helper('vbw_punchout/config')->getConfig('order/include_tax')) {
                // if tax is not being include, use our dummy tax renderer
                Mage::getConfig()->setNode('global/sales/quote/totals/tax/class', 'vbw_punchout/sales_quote_total_tax');
            //    Mage::getConfig()->setNode('global/sales/quote/totals/tax/renderer', null);
            }
        }

    }


    /**
     * checks for a punchout session in the query string, if it
     * exists, it checks for the magento session id. if that is
     * set then it tries to set the current session id.
     *
     * this is done to get around magento's protection requiring
     * an owned referring url.
     *
     * @param Varien_Event_Observer $observer
     */
    public function forceSessionidWithPosid (Varien_Event_Observer $observer)
    {
        /**
         * @var $helper Vbw_Punchout_Helper_Config
         * @var $poSession Vbw_Punchout_Model_Session
         * @var $session Mage_Core_Model_Session
         */
        //$session = $observer->getEvent()->getCustomerSession();
        // we don't need the singleton
        $session = Mage::getModel('core/session');
        if (isset($_GET['POID'])
                && !empty($_GET['POID'])) {
            if ($session->useSid()) {
                $_queryParam = $session->getSessionIdQueryParam();
                if (isset($_GET[$_queryParam])
                    && !empty($_GET[$_queryParam])) {
                    unset($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }

    /**
     * checks for a punchout session in the query string.
     *
     * @param Varien_Event_Observer $observer
     */
    public function validatePunchoutSessionPersonalCheckout (Varien_Event_Observer $observer)
    {
        /** @var $session Vbw_Punchout_Model_Session */
        $session = Mage::getSingleton('vbw_punchout/session');
        /** @var $config Vbw_Punchout_helper_Config */
        $config = Mage::helper('vbw_punchout/config');

        if ($session->isPunchoutSession()
                && false == $config->getConfig('display/personal_checkout')) {
            $url = Mage::getUrl('checkout/cart',array('_secure'=>1));
            $response = Mage::app()->getResponse();
            $response->setRedirect($url);
            $request = Mage::app()->getRequest();
            $request->isDispatched(true);
            //$response->sendResponse();
        }

    }


    /**
     * checks for a punchout session in the query string.
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkForPunchoutSession (Varien_Event_Observer $observer)
    {
        if (self::$_inSessionEvent == 1) {
            return;
        }
        self::$_inSessionEvent = 1;

        /**
         * @var $helper Vbw_Punchout_Helper_Config
         * @var $poSession Vbw_Punchout_Model_Session
         */
        $session = $observer->getEvent()->getCustomerSession();
        $request = Mage::app()->getRequest();
        // backup mechanism.
        $poid = $request->getQuery('POID');
        if (!empty($poid)) {
            $helper = Mage::helper('vbw_punchout/config');
            $poSession = Mage::getSingleton("vbw_punchout/session");
            if ($poSession->getPunchoutId() != $poid) {
                if ($helper->getconfig('site/demo_session_id') == $poid) {
                    if ($helper->getConfig('site/allow_demo') == 1) {
                        $poSession->setPunchoutId($poid);
                        // add user to the session, not full start
                        // because we don't have a real request.
                        $poSession->injectPunchoutUserToSession();
                    }
                    self::$_inSessionEvent = 0;
                    return;
                }

                $poSession->loadPunchoutSession($poid);
                $poSession->startPunchoutSession();

                // after the session is set we need to re-load the page.
                $response = Mage::app()->getResponse();

                $requestUri = $request->getRequestUri();

                if ($request->has('___start')) {
                    $requestPath = parse_url($requestUri, PHP_URL_PATH)  .'?POID='. $poid;
                    $body = "
                    <html>
                    <head>
                        <title>session initialization</title>
                    </head>
                    <body onload='loadSession()'>
                        Your session has been setup.<br>
                        This window should close automatically.<br>
                        If this window does not close, please close it and
                        restart your punchout session.<br>
                        <script type='text/javascript'>
                            function loadSession () {
                                window.opener.location.reload();
                                window.close();
                            }
                        </script>
                    </body>
                    </html>
                    ";
                    $response->setBody($body);
                    $response->sendResponse();
                    exit;
                } elseif ($request->has('___loaded')
                            && (!isset($_SESSION['frontend']))) {
/*
                    $requestPath = parse_url($requestUri, PHP_URL_PATH)  .'?___start=1&POID='. $poid;
                    $body = "
                        Please <a href='". $requestPath ."' target='_new'>Click Here</a> to approve your session.<br>
                    ";
                    $response->setBody($body);
                    $response->sendResponse();
                    exit;
*/
                } elseif (!$request->has('___show')) {
                    $requestPath = parse_url($requestUri, PHP_URL_PATH);
                    $response->setRedirect($requestPath .'?___loaded=1&POID='. $poid);
                    $response->sendResponse();
                    exit;
                }
            }
        }
        self::$_inSessionEvent = 0;
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function customQuoteSetup (Varien_Event_Observer $observer)
    {
        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = $observer->getEvent()->getCheckoutSession();

        /** @var $sessionHelper Vbw_Punchout_Helper_Session */
        $sessionHelper = Mage::helper('vbw_punchout/session');

        /** no longer using this method. eventually remove the observer */
        //$sessionHelper->setupPunchoutQuote($checkoutSession);

    }

    /**
     * sets up checkout session quote
     *
     * @param Varien_Event_Observer $observer
     */
    public function setupSingleLoginUniqQuotes ($observer)
    {
        /** @var $checkout Mage_Checkout_Model_Session */
        $checkout = $observer->getEvent()->getCheckoutSession();

        // only test when a punchout session.
        // only run on initialization.
        if ($checkout->getIsPunchoutCartInit()) {
            $checkout->setIsPunchoutCartInit(false);
            /** @var $dataHelper Vbw_Punchout_Helper_Data */
            $dataHelper = Mage::helper('vbw_punchout');
            if (Mage::getSingleton('vbw_punchout/session')->isPunchoutSession()) {
                    $dataHelper->debug('this is a session init. prepare new cart.');

                    /** @var $sessionHelper Vbw_Punchout_Helper_Session */
                    $sessionHelper = Mage::helper('vbw_punchout/session');

                    if (false == $sessionHelper->usePersistentCart()) {
                        //$id = $checkout->getQuoteId();
                        //if (empty($id)) {
                            /**@var $quote Mage_Sales_Model_Quote */
                            $checkout->unsetAll();
                            $quote = Mage::getModel('sales/quote')
                                ->setStoreId(Mage::app()->getStore()->getId());
                            $quote->save();
                            $checkout->setQuoteId($quote->getId());
                            $dataHelper->debug('new quote id :'. $checkout->getQuoteId());
                        //} else {
                        //    $dataHelper->debug('Checkout has a quote id :'. $id);
                        //}
                    } else {
                        $dataHelper->debug('Persistence is allowed, default handling.');
                    }
            } else {
                // $dataHelper->debug('not punchout init :'. $checkout->getQuoteId());
            }
        }
    }

    /**
     * checks the store's session handling and validates that the
     * session and login are allowed based on the configuration.
     *
     * @param Varien_Event_Observer $observer
     */
    public function validatePunchoutSessionUser ($observer)
    {
        if (self::$_inSessionEvent == 1) {
            return;
        }
        self::$_inSessionEvent = 1;

        /**
         * @var $config Vbw_Punchout_Helper_Config
         * @var $session Vbw_Punchout_Model_Session
         * @var $customerSession Mage_Customer_Model_Session
         */

        $customerSession = $observer->getEvent()->getCustomerSession();
        $session = Mage::getSingleton('vbw_punchout/session');
        if ($session->isPunchoutSession()) {
            $config = Mage::helper('vbw_punchout/config');
            try {

                if ($config->isSingleLoginSession()) {
                    $this->validateSingleLoginSessionUser($customerSession);
                } elseif ($config->isDualLoginSession()) {
                    $this->validateDualLoginSessionUser($customerSession);
                } elseif ($config->isAnonymousSession()) {
                    $this->validateAnonymousLoginSession($customerSession);
                }

                // add layout update nodes
                $type = $config->getSessionLoginType();
                $node = 'customer_punchout_in_'. (in_array($type,array ('anonymous','single','discover')) ? $type : 'anonymous');


                Mage::helper('vbw_punchout')
                    ->addLayoutUpdateNode($node)
                    ->addLayoutUpdateNode('in_punchout_session');

                $customUpdates = $config->getConfig('display/punchout_active_layout_update');
                if (!empty($customUpdates)) {
                    Mage::helper('vbw_punchout')
                        ->addLayoutUpdateNode(array($customUpdates));
                }

            } catch (Exception $e) {
                // log out the user
                $customerSession->logout();
                // empty the puncohout/checkout session data
                $session->emptySession();
//                Mage::getSingleton('message/session')->addError($e->getMessage());
                self::$_inSessionEvent = 0;
                $helper = Mage::helper('vbw_punchout/config');
                $action = Mage::app()->getFrontController()->getAction();
                $response = $action->getResponse();
                $url = $helper->getConfig('site/punchout_only_url');
                $response->setRedirect(Mage::getUrl($url, array('_query'=>"nopotest=1")));
                $response->sendResponse();
            }
        }
        self::$_inSessionEvent = 0;
    }

    /**
     * the single login session should always have an active
     * user, the user should match the configuration rules.
     *
     * @param Mage_Customer_Model_Session $customerSession
     * @return boolean
     */
    public function validateAnonymousLoginSession ($customerSession)
    {
        /**
         * @var $config Vbw_Punchout_Helper_Config
         * @var $session Vbw_Punchout_Model_Session
         */
        $config = Mage::helper('vbw_punchout/config');
        $groupId = $config->getAnonymousLoginGroup();
        if (!empty($groupId))  {
            if ($customerSession->getCustomerGroupId() != 0) {
                $customer = $customerSession->getCustomer();
                $customer->setGroupId($customerSession->getCustomerGroupId());
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $quote->setCustomer($customer);
                // $customerSession->setCustomerAsLoggedIn($customer);
            }
        }
    }


    /**
     * the single login session should always have an active
     * user, the user should match the configuration rules.
     *
     * @param Mage_Customer_Model_Session $customerSession
     * @return boolean
     */
    public function validateSingleLoginSessionUser ($customerSession)
    {
        return true;
        /**
         * @var $config Vbw_Punchout_Helper_Config
         * @var $session Vbw_Punchout_Model_Session
         */
        $config = Mage::helper('vbw_punchout/config');
        $user = $config->getSingleLoginUser();
        if (empty($user)) {
            throw new Exception('The single login user is not configured correctly.');
        }
        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');
        $customer = $session->getCustomer();
        //$customer = $customerSession->getCustomer();
        if (is_numeric($user)) {
            if ($customer->getid() != $user) {
                throw new Exception('Your customer session is not invalid for this store.');
            }
        } else {
            if ($customer->getEmail() != $user) {
                throw new Exception('Your customer session is not invalid for this store.');
            }
        }
        return true;
    }



    /**
     *
     */
    public function validateDualLoginSessionUser ()
    {

    }


    /**
     * make modifications to the system with an active punchout session.
     * this is useful to make more global adjustments.
     * Returns 1 if in a PO session.
     * Returns -1 if it is not a PO session in case this is extended.
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function updateEnvironmentStackWithPunchout ($observer)
    {
        /**
         * @var $sessionHelper Vbw_Punchout_Helper_Session
         * @var $configHelper Vbw_Punchout_Helper_Config
         * @var $poSession Vbw_Punchout_Model_Session
         */
        $poSession = Mage::getSingleton("vbw_punchout/session");
        if ($poSession->isPunchoutSession()) {
            // punchout session is active, so lets do some stuff.
            $configHelper = Mage::helper('vbw_punchout/config');
            $sessionHelper = Mage::helper('vbw_punchout/session');

            // disable modules
            $disableModules = $configHelper->getConfig('site/disable_modules');
            $disableModulesArr = explode(",",$disableModules);
            if (is_array($disableModulesArr)) {
                foreach ($disableModulesArr AS $module) {
                    $sessionHelper->disableModule(trim($module));
                }
           }

           return 1;
        }
        return -1;

    }


    /**
     * add a P3P Compact privacy header. This should become it's own module.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addP3PToResponse (Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Core_Controller_Front_Action
         */
        $controllerAction = $observer->getEvent()->getControllerAction();
        /**
         * @var Mage_Core_Controller_Response_Http
         */
        $response = $controllerAction->getResponse();
        if ($response instanceof Mage_Core_Controller_Response_Http) {
            $response->setHeader('P3P','CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        }
//            header('P3P: CP="CAO DSP COR CUR ADM DEV CONi OPTi OUR NOR PHY ONL COM NAV DEM CNT STA HEA PRE"');
    }


    /**
     * checks to for a store's "punchout only" restricted access flag
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkForPunchoutOnlySite (Varien_Event_Observer $observer)
    {
        if (self::$_inSessionEvent == 1) {
            return;
        }
        self::$_inSessionEvent = 1;

        /**
         * @var $helper Vbw_Punchout_Helper_Config
         * @var $poSession Vbw_Punchout_Model_Session
         * @var $response Mage_Core_Controller_Response_Http
         */
        // $session = $observer->getEvent()->getCustomerSession();
        $request = Mage::app()->getRequest();
        $helper = Mage::helper('vbw_punchout/config');
        $poOnly = $helper->getConfig('site/punchout_only');
        $poSession = Mage::getSingleton("vbw_punchout/session");
        $action = Mage::app()->getFrontController()->getAction();
        $response = $action->getResponse();
        if ($poOnly == 1
            && $poSession->getPunchoutId() == false
            && $action->getRequest()->getQuery('nopotest') != 1) {

            $result = new Varien_Object();
            $result->setShouldProceed(true);

            Mage::dispatchEvent('punchoutonly_restriction',array (
                'controller' => $action,
                'result' => $result,
            ));

            if ($result->getShouldProceed())  {
                //$module_name = strtolower($request->getControllerModule());
                //if ($module_name == 'vbw_punchout') {
                //    $result->setShouldProceed(false);
                //}
                self::$_inSessionEvent = 0;
                $url = $helper->getConfig('site/punchout_only_url');
                $response->setRedirect(Mage::getUrl($url, array('_query'=>"nopotest=1")));
                $response->sendResponse();
            }
        }
        self::$_inSessionEvent = 0;
    }

    /**
     * tests from punchout only restriction
     *
     * @param Varien_Event_Observer $observer
     */
    public function allowThroughPunchoutOnlyRestriction (Varien_Event_Observer $observer)
    {
        /** @var $controller Mage_Core_Controller_Front_Action */
        $controller = $observer->getEvent()->getController();
        /* @var $request Varien_Object */
        $result = $observer->getEvent()->getResult();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request    = $controller->getRequest();

        /** @var $helper Vbw_Punchout_Helper_Data */
        $helper = Mage::helper('vbw_punchout');
        if ($helper->allowRequestThroughPunchoutOnly($request)) {
            $result->setShouldProceed(false);
        }
    }


    /**
     * allow punchout controllers to behave even with restricted websites.
     * website restriction modules comes with enterprise.
     *
     * @param Varien_Event_Observer $observer
     */
    public function allowPunchoutThroughRestriction (Varien_Event_Observer $observer)
    {
        /** @var $controller Mage_Core_Controller_Front_Action */
        $controller = $observer->getEvent()->getController();
        /* @var $request Varien_Object */
        $result = $observer->getEvent()->getResult();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request    = $controller->getRequest();
        $module_name = strtolower($request->getControllerModule());
        if ($module_name == 'vbw_punchout') {
            $result->setShouldProceed(false);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkPunchoutRequiredShipping (Varien_Event_Observer $observer)
    {
        /** @var $helper Vbw_Punchout_Helper_Config */
        $helper = Mage::helper('vbw_punchout/config');
        /** @var $session Vbw_Punchout_Model_Session */
        $session = Mage::getSingleton('vbw_punchout/session');
        if ($session->isPunchoutSession()) {
            if ($helper->getConfig('order/require_shipping')) {
                $quote = $session->getQuote();
                $method = $quote->getShippingAddress()->getShippingMethod();
                if (empty($method)) {
                    Mage::getSingleton('checkout/session')->addError($helper->getConfig('order/require_shipping_error'));
                    $url = Mage::getUrl('checkout/cart',array('_secure'=>1));
                    $response = Mage::app()->getResponse();
                    $response->setRedirect($url);
                    $request = Mage::app()->getRequest();
                    $request->isDispatched(true);
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function punchoutPostTransfer (Varien_Event_Observer $observer)
    {

        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $response = Mage::app()->getResponse();
        if ($checkoutSession->getMessages()->count('error')
                && $response->isRedirect()) {
            // if there are errors and a redirect is defined, then
            // do not complete this process.
            return true;
        }

        /** @var $helper Vbw_Punchout_Helper_Config */
        $helper = Mage::helper('vbw_punchout/config');
        /** @var $session Vbw_Punchout_Model_Session */
        $session = Mage::getSingleton('vbw_punchout/session');
        if ($session->isPunchoutSession()) {
            if ($helper->getConfig('site/transfer_delete_cart')) {
                $quote = $session->getQuote();
                Mage::helper('vbw_punchout')->debug('Configured to delete on transfer. : '. $quote->getId());
                $quote->delete();
            } else {
                /** @var $core Mage_Core_Model_Session
                $core = Mage::getSingleton('core/session');
                $core->unsetAll();
                */
            }
            /** @var $customer Mage_Customer_Model_Session */
            $customer = Mage::getSingleton('customer/session');
            $customer->logout();
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function setSystemSessionManagement (Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('vbw_punchout/site/punchout_enabled')) {
            if (Mage::getStoreConfig('vbw_punchout/site/session_handling') == 3) {

            }
        }
    }


    /** LAST CART EDIT */

    /**
     * @param Varien_Event_Observer $observer
     */
    public function prepareLastCartEdit (Varien_Event_Observer $observer)
    {
        /** @var $session Vbw_Punchout_Model_Session */
        $session = $observer->getEvent()->getData('session');
        $request = $session->getPunchoutRequest();
        if (Mage::helper('vbw_punchout/session')->isEdit()) {
            if (Mage::helper('vbw_punchout/config')->getConfig('order/keep_aux')) {
                Mage::helper('vbw_punchout')->debug('keep aux enabled');
                $lastCart = new stdClass();
                $lastCart->quote_id = null;
                $lastCart->items = array();
                Mage::helper('vbw_punchout')->debug('setting last cart '. print_r($lastCart,true));
                Mage::getSingleton('catalog/session')->setLastCart($lastCart);
            } else {
                Mage::helper('vbw_punchout')->debug('keep aux disable');
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addStashToLastCartItems (Varien_Event_Observer $observer)
    {
        $lastCart = Mage::getSingleton('catalog/session')->getLastCart();

        if (!empty($lastCart)) {
            /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
            $stash = $observer->getEvent()->getData('stash_item');
            $lastItem = $observer->getEvent()->getData('item');

            /** @var $cart Mage_Checkout_Model_Cart */
            /** @var $quote Mage_Sales_Model_Quote */
            $cart = $observer->getEvent()->getData('cart');
            $quote = $cart->getQuote();

            if ($lastCart->quote_id == null) {
                $lastCart->quote_id = $stash->getQuoteId();
            }

            $lastCart->items[$lastItem->getSku()][] = new Varien_Object(array(
                "quote_id" => $stash->getQuoteId(),
                "item_id" => $stash->getItemId(),
                "used" => 0,
            ));

            Mage::helper('vbw_punchout')->debug('added item to last cart '. print_r($lastCart,true));
            Mage::getSingleton('catalog/session')->setLastCart($lastCart);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function cleanUpStashFromLastCartEdit (Varien_Event_Observer $observer)
    {
        /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
        // $stash = $observer->getEvent()->getData('stash_quote');
        $lastCart = Mage::getSingleton('checkout/session')->getLastCart();
        if (!empty($lastCart)) {
            Mage::helper('vbw_punchout')->debug('cleaning up from edit.');
            foreach ($lastCart->items AS $items) {
                foreach ($items as $item) {
                    if ($item->getUsed() == 0) {
                        Mage::helper('vbw_punchout')->debug('removing '. $item->getLineId());
                        $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByLineItemId($item->getLineId(),$item->getQuoteId());
                        $stash->delete();
                    }
                }
            }
        }
    }

    public function preventQuoteEditOnInspect (Varien_Event_Observer $observer)
    {
        if (Mage::helper('vbw_punchout/session')->isReadOnly()) {
            throw new Exception('You cannot make changes to an "inspect" cart.');
        }
    }

    /**
     * 'session' => $session,
    'request' => $request,
    'email' => $user,
    'group_id' => (is_object($groupObj) ? $groupObj->getId() : $groupObj),
    'website_id' => $websiteId
     *
     * @param Varien_Event_Observer $observer
     */
    public function manageUserByAlternateId (Varien_Event_Observer $observer)
    {
        /** @var $request \Vbw\Procurement\Punchout\Request */

        $alternateId = Mage::getStoreConfig('vbw_punchout/customer/alternate_login_id');
        // only do this if an alternateID is defined
        if (!empty($alternateId)) {
            /** @var $helper Vbw_Punchout_Helper_Data */
            $helper = Mage::helper('vbw_punchout');

            $session = $observer->getEvent()->getData('session');
            $request = $observer->getEvent()->getData('request');
            $email = $observer->getEvent()->getData('email');
            $group_id = $observer->getEvent()->getData('group_id');
            $websiteId = $observer->getEvent()->getData('website_id');

            $loginId = $request->getBody()->getContact()->getUnique();
            $helper->debug('Matching '. $loginId .' to '. $alternateId);

            if (empty($loginId)) {
                $helper->debug('Login id is empty');
                throw new Exception('Unique login identifier not found.',150);
            }

            /** @var $collection Mage_Customer_Model_Resource_Customer_Collection */
            $collection = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId)
                ->getResourceCollection();
            $collection->addFieldToFilter($alternateId, $loginId);

            if ($collection->count() == 1) {
                $ids = $collection->getAllIds();
                $helper->debug('Found user id : '. $ids[0]);
                $user = Mage::getModel('customer/customer')->load($ids[0]);

            } elseif ($collection->count() > 1) {
                $helper->debug('Found too many users : '. count($collection->count()));
                throw new Exception('Login '. $loginId .' has multiple accounts',151);

            } else {
                $helper->debug('No users found by id, trying email : '. $email);
                // try email..
                $user = Mage::getModel('customer/customer')
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);

                if ($user->getData($alternateId) != null) {
                    unset($user);

                    // setup temporary email
                    $tempEmailPattern = Mage::getStoreConfig('vbw_punchout/customer/duplicate_email_temp_pattern');
                    if (!empty($tempEmailPattern)) {
                        $match = array();
                        $replace = array();
                        foreach ($request->getBody()->getContact()->toArray() AS $k => $v) {
                            if (is_array($v)) {
                                foreach ($v AS $vk => $vv) {
                                    if (!is_array($vv)) {
                                        $match[] = '{'. $vk .'}';
                                        $replace[] = $vv;
                                    }
                                }
                            } else {
                                $match[] = '{'. $k .'}';
                                $replace[] = $v;
                            }
                        }
                        $email = str_replace($match,$replace,$tempEmailPattern);
                    } else {
                        throw new Exception('Email '. $email .' already has an attached account',152);

                    }
                } else {
                    // have account take over.
                    $user->setData($alternateId, $loginId);
                    $user->save();
                }

            }

            $makeUser = Mage::getStoreConfig('vbw_punchout/customer/create_users');
            if ((!isset($user)
                || empty($user)
                || (is_object($user) && $user->getId() == null))
                && $makeUser) {
                // create the user. Will originally create based on email.
                // then update the alt id.
                $user = Mage::helper('vbw_punchout/session')->makeUser($request,$email,$group_id);
                if (is_object($user)
                    && is_numeric($user->getId()))  {
                    $user->setData($alternateId, $loginId);
                    $user->save();
                }
            }

            if (is_object($user)
                && is_numeric($user->getid())) {
                $session->setCustomer($user);
            }

        }

    }

    /**
     * @depricated logic was moved to distiller.
     *
     * @param Varien_Event_Observer $observer
    public function updateItemAuxIdFromLastCartEdit (Varien_Event_Observer $observer)
    {
        $lastCart = Mage::getSingleton('catalog/session')->getLastCart();
        if (!empty($lastCart)
            && null != $lastCart->quote_id) {
            $poitem = $observer->getEvent()->getData('po_item');
            $stash = $observer->getEvent()->getData('stash');

            $poitem->setSupplierAuxId($stash->getQuoteId() .'/'. $stash->getItemId());
        }
    }
    */
}
