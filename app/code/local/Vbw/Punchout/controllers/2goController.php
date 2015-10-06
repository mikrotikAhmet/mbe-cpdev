<?php


class Vbw_Punchout_2goController
    extends Mage_Core_Controller_Front_Action {

    protected $_punchout = null;
    protected $_input = null;


    /**
     * disables any punchnout only tests
     *
     * @return Mage_Core_Controller_Front_Action|void
     */
    public function preDispatch()
    {
        // for any actions within this controller, disable the punchout only test.
        $event = Mage::getConfig()->getEventConfig('frontend', 'controller_action_predispatch');
        $event->observers->session_check_punchout_only->type = 'disabled';
        Mage::helper('vbw_punchout')->debug(' pre-pre dispatch ');

        parent::preDispatch();
        Mage::helper('vbw_punchout')->debug(' post-pre dispatch ');
    }

    /**
     * Save the customer
     */
    public function postDispatch()
    {
        //Mage::helper('vbw_punchout')->debug(' pre-post dispatch ');
        parent::preDispatch();
        //Mage::helper('vbw_punchout')->debug(' post-post dispatch ');
        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');
//        if ($session->isLoggedIn()) {
            try {
                /** @var $visitor Mage_Log_Model_Visitor */
                $visitor = Mage::getSingleton("log/visitor");
                if (null != $visitor->getId()) {
                    $visitor->saveByRequest(null);
                }
            } catch (Exception $e) {
                // lets not worry about it.
            }
            /** this should not happen
            if ($visitor->getIsNewVisitor()) {
                throw new Exception('New visisotr not save..');
                $visitor->save();
            }
            */
            /**
             * call save on the visitor again.
            if ($visitor->getDoCustomerLogin()) {
                $visitor->save();
            }
            throw new Exception('Did it work?');
            // $session->getCustomer()->save();
             */
 //       }
    }

    /**
     * not allowed to access through this
     *
     * @throws Exception
     */
    public function indexAction ()
    {
        throw new Exception('Your punchout request was invalid.');
    }

    /**
     * receives the setup request punchout2go document
     *
     * @throws Exception
     */
    public function setupAction ()
    {
        /**
         * @var $session Vbw_Punchout_Model_Session
         */
        $body = file_get_contents('php://input');
        try {
            /**@var $config Vbw_Punchout_Helper_Config*/
            $config = Mage::helper('vbw_punchout/config');
            if ($config->isPunchoutEnabled() == false) {
                throw new Exception('This store is not punchout enabled.',500);
            }
            try {
                $data = Zend_Json::decode($body);
            } catch (Exception $e) {
                throw new Exception('Body does not appear to be a valid json request.');
            }
            $id = $data['pos'];
            if (empty($id)) {
                throw new Exception('No session ID found.',402);
            }
            $session = Mage::getSingleton("vbw_punchout/session");
            // sets the po id to the session.
            $session->setPunchoutId($id);
            // just sets the data
            $session->setPunchoutData($data['params']);
            // start will process the data and inject it in to the magento session
            $session->startPunchoutSession();
            $location = $session->getSessionUrl();

            $response = new stdClass();
            $response->sessionId = $session->getSessionId();
            $response->errors = null;
            $response->results = $location;
            $responseObj = $this->getResponse();
            $responseObj->setHeader('content-type',"application/json");
            $responseObj->setBody(Zend_Json::encode($response));

        } catch (Exception $e) {
            $response = new stdClass();
            $response->errors = array (
                "code" => $e->getCode(),
                "messages" => $e->getMessage(),
            );
            $response->results = null;
            $responseObj = $this->getResponse();
            $responseObj->setHeader('content-type',"application/json");
            $responseObj->setBody(Zend_Json::encode($response));
        }

    }

    /**
     * start action will redirect
     *
     */
    public function startAction ()
	{
        try {
            /**@var $config Vbw_Punchout_Helper_Config*/
            $config = Mage::helper('vbw_punchout/config');
            if ($config->isPunchoutEnabled() == false) {
                throw new Exception("This store is not punchout enabled. Please contact us for support.",'400');
            }

            /**
             * @var $session Vbw_Punchout_Model_Session
             */
            // get the param.
            $id = $this->getRequest()->getParam('pos');

            // load the session handler from punchout
            $session = Mage::getSingleton("vbw_punchout/session");
            // see if the id's match
            // don't test, always start anew.
            $method = $this->getRequest()->getServer('REQUEST_METHOD');
            // if ($id != $session->getPunchoutId()) {
            if ($this->getRequest()->has('params')) {
                try {
                    $params = Zend_Json::decode($this->getRequest()->get('params'));

                    $session->setPunchoutId($id);
                    // just sets the data
                    $session->setPunchoutData($params);
                    // start will process the data and inject it in to the magento session
                    $session->startPunchoutSession();

                    $session->redirect($this->getResponse());

                } catch (Exception $e) {
                    throw $e;
                }

            } else {
                // if not load from remote session data.
                $response = $session->loadPunchoutSession($id);
                // if it is successful then start the session
                if ($response != false) {
                    $session->startPunchoutSession();
                } else {
                    // throw new Exception('Your punchout session was invalid.');
                    throw new Exception("Your punchout session was invalid.",'401');
                }
            }
            // }

        } catch (Exception $e) {
            Mage::log('Start Msg : '. $this->getRequest()->get('params'),null,'punchout_start_error.log',true);
            Mage::log('Start Error : ['. $e->getCode() .'] '. $e->getMessage(),null,'punchout_start_error.log',true);
            $code = $e->getCode();
            if (!empty($code)) {
                $cms_page_id = Mage::helper('vbw_punchout/config')->matchErrorMap($code);
                if (!empty($cms_page_id)) {
                    return $this->_forward('view','page','cms',array('page_id'=>$cms_page_id));
                }

            }
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            } else {
                return $this->_forward('noRoute','index','cms');
            }
        }

        $session->redirect($this->getResponse());

	}

    /**
     *  view the session off punchout2go
     */
    public function sessionAction ()
	{
            $this->_punchout->sessionAction($this->getRequest()->getParam('pos'),$this->getResponse());
	}

    /**
     *  view order?
     */
    public function orderAction ()
	{
            $this->_punchout->orderAction($this->_input,$this->getResponse());
	}


    /**
     * transfer the cart over, dispatches transfer event
     */
    public function transferAction ()
    {

        $response = $this->getResponse();
        if ($response->isRedirect()) {
            /** @var $customer Mage_Customer_Model_Session */
            //$customer = Mage::getSingleton('customer/session');
            //if ($customer->isLoggedIn()) {
            //    $customer->logout();
            //    $customer = $customer->getCustomer();
            //    $customerLog = Mage::getModel('log/customer')->loadByCustomer($customer);
            //    $customerLog->setLogoutAt(date('Y-m-d H:i:s'));
            //    $customerLog->save();
            //}
            // if a redirect is already defined, don't process the body.
            return true;
        }

        /**
         * @var $session Vbw_Punchout_Model_Session
         */
        $session = Mage::GetSingleton("vbw_punchout/session");
        $punchoutOrder = $session->getPunchoutOrder();
        // $punchoutRequest = $session->getPunchoutRequest();
        // $string = $session->getPunchoutOrderFrom();

        $host = $session->getRemoteHost() ."/gateway/link/punchin/id/". $session->getPunchoutId();
        $string  = '<form action="'. $host .'" method=POST name="punchoutSend" id="punchoutSend">';
        $string .= '<input type=hidden name=apikey value="'. $session->getConfig("api/key") .'">';
        $string .= '<input type=hidden name=version value="'. $session->getConfig("api/version") .'">';
        $string .= '<input type=hidden name=params value="'. base64_encode(Zend_Json::encode($punchoutOrder->toArray())) .'">';
        $string .= '<input type="submit" name="transfer" value="Transfer Order">';
        $string .= '</form>';

        $html  = "<html>\n";
        $html .= "<head><title>Preparing Punchout Order...</title></head>\n";
        $html .= "<body onload=\"self.document.forms[0].submit()\">\n";
        $html .= $string;
        //$html .= "<script>self.document.forms[0].submit()</script>";
        $html .= "</body>\n";
        $html .= "</html>";

        Mage::dispatchEvent('punchout_order_transfer',array('session'=>$session,'po_order'=> $punchoutOrder, 'quote' => $session->getQuote()));

        $this->getResponse()->setBody($html);

        /** @var $customer Mage_Customer_Model_Session */
        $customer = Mage::getSingleton('customer/session');
        if ($customer->isLoggedIn()) {
            $customer->logout();
            //    $customer = $customer->getCustomer();
            //    $customerLog = Mage::getModel('log/customer')->loadByCustomer($customer);
            //    $customerLog->setLogoutAt(date('Y-m-d H:i:s'));
            //    $customerLog->save();
        }

    }


    /**
     * inspect the session
     */
    public function inspectAction ()
	{
        /**@var $session Vbw_Punchout_Model_Session*/
        $session = Mage::getSingleton("vbw_punchout/session");
        $response = $session->getPunchoutOrderDocument();
        if (!empty($response)) {
            header("content-type: {$response['content-type']}");
            echo $response['body'];
        }  else {
            header("Content-type: text/plain");
            echo (string) $session->getError();
        }
        exit;
	}

    /**
     * show something
     */
    public function showAction ()
	{
        $session = Mage::getSingleton("vbw_punchout/session");
        $order = $session->getPunchoutOrder();
        header('Content-type: text/plain');
        if (isset($_GET['array'])) {
            print_r($order->toArray());
        } else {
            print_r($order);
        }
	}

    /**
     * pull the catalog
     */
    public function catalogAction ()
    {
        /**
         * @var $catalog Vbw_Punchout_Model_Catalog
         */
        $catalog = Mage::getModel('vbw_punchout/catalog');
        $catalog->load();
        $cat = $catalog->getPortableCatalog();
        header("content-type: text/plain");
        echo Zend_Json::encode($cat->toArray());
        exit;
    }

    /**
     * view the magento cart
     */
    public function magecartAction ()
    {

        if ($this->getRequest()->has('reset')) {
            /** @var $session Mage_Checkout_Model_Session */
            $session = Mage::getSingleton('checkout/session');
            $session->replaceQuote(Mage::getModel('sales/quote'));
        }

        if ($this->getRequest()->has('restore')) {
            /** @var $salesHelper Vbw_Punchout_Helper_Sales */
            $salesHelper = Mage::helper('vbw_punchout/sales');
            $data = $salesHelper->getRequestDataFromLineItem($this->getRequest()->get('restore'));
            if (!empty($data)) {
                print_r($data);
                exit;
            }
        }

        /** @var $debugger Vbw_Punchout_Helper_Debug */
        $debugger = Mage::helper('vbw_punchout/debug');
        $debug = $debugger->getCartDebugInfo();

        $response = $this->getResponse();
        $response->setBody($debug);
        $response->setHeader('Content-type','text/plain');
    }

    /**
     * view the magento cart
     */
    public function inspectcartAction ()
    {

        /** @var $session Vbw_Punchout_Model_Session */
        $session = Mage::getSingleton('vbw_punchout/session');

        /** @var $cart Mage_Checkout_Model_Cart */
        $cart = Mage::getSingleton('checkout/cart');

        $quote = $session->getQuote();
        $items = $quote->getAllVisibleItems();

        $return = '';

        foreach ($items AS $k => $item) {

            $return .= "\n- - - - - - - -\n";
            $data = $item->getData();

            foreach ($data AS $j=>$y) {
                if (!is_object($y)) {
                    $return .= $j ." = ". $y ."\n";
                }
            }

        }

        $return .= "\n- - - - - - - -\n";
        $return .= "- - - - - - - -\n";

        $totals = $quote->getTotals();


        foreach ($totals AS $k => $total) {

            $return .= "\n- - - - - - - -\n";
            $return .= $k ."\n";
            $data = $total->getData();

            foreach ($data AS $j=>$y) {
                if (!is_object($y)) {
                    $return .= $j ." = ". print_r($y,true) ."\n";
                }
            }

        }


        $this->getResponse()->setHeader('Content-Type','text/plain');
        $this->getResponse()->setBody($return);

    }


}
