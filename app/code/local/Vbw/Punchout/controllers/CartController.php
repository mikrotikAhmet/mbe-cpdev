<?php

#
require_once 'Mage/Checkout/controllers/CartController.php';

class Vbw_Punchout_CartController extends Mage_Checkout_CartController
{

	public function indexAction ()
	{
		$mageSessHandler = Mage::getSingleton("vbw_punchout/session");
		if ($mageSessHandler->getPunchoutId()) {
		    $this->_redirect('checkout/cart/punchout');
		} else {
            parent::indexAction();
		}
	}
	
	public function punchoutAction ()
	{

            $cart = $this->_getCart();
            if ($cart->getQuote()->getItemsCount()) {
                $cart->init();
                $cart->save();
                if (!$this->_getQuote()->validateMinimumAmount()) {
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                    $cart->getCheckoutSession()->addNotice($warning);
                }
            }
            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {
                    $cart->getCheckoutSession()->addMessage($message);
                }
            }
            /**
             * if customer enteres shopping cart we should mark quote
             * as modified bc he can has checkout page in another window.
             */
            $this->_getSession()->setCartWasUpdated(true);


            Varien_Profiler::start(__METHOD__ . 'cart_display');
            $this->loadLayout();
            $this->_initLayoutMessages('checkout/session');
            $this->_initLayoutMessages('catalog/session');
            $this->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
            $this->renderLayout();
            Varien_Profiler::stop(__METHOD__ . 'cart_display');

	}



        public function confirmAction ()
        {
            $this->_redirect('checkout/cart/punchout');
            return;
            $cart = $this->_getCart();
            if ($cart->getQuote()->getItemsCount()) {
                $cart->init();
                $cart->save();
                if (!$this->_getQuote()->validateMinimumAmount()) {
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                    $cart->getCheckoutSession()->addNotice($warning);
                    $this->_redirect('checkout/cart');
                }
            } else {
                $this->_redirect('checkout/cart');
            }
            /*
            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {
                    $this->_redirect('checkout/cart');
                }
            }
             */

            /*
            $quote = $cart->getQuote();
            if ($quote->getShippingAddress()->getShippingAmount() == 0)  {
                // $error = Mage::getStoreConfig('sales/minimum_order/description');
                $e = new Exception('You must supply a shipping method in order to punchout.');
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                Mage::getSingleton('checkout/session')->getMessages();
                $this->_redirect('checkout/cart/punchout');
                return;
            }
             */

            Varien_Profiler::start(__METHOD__ . 'cart_display');
            $this->loadLayout();
            $this->_initLayoutMessages('checkout/session');
            $this->_initLayoutMessages('catalog/session');
            $this->getLayout()->getBlock('head')->setTitle($this->__('Punchout Order'));
            $this->renderLayout();
            Varien_Profiler::stop(__METHOD__ . 'cart_display');
        }

}