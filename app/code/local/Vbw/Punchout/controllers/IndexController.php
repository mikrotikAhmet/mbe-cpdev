<?php

require_once "Mage/Checkout/controllers/IndexController.php";

class Vbw_Punchout_IndexController extends Mage_Checkout_IndexController
{
	
	public function indexAction ()
	{
		$mageSessHandler = Mage::getSingleton("vbw_punchout/session");
		if ($mageSessHandler->getPunchoutId()) {
			$this->_redirect('checkout/cart/punchout');
		} else {
			return parent::indexAction();
		}
	}

    public function inspectAction ()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        echo "<pre>";

        $products = $quote->getAllVisibleItems();

        /**
         * @var $distiller Vbw_Punchout_Model_Punchout_Distiller
         */
        $distiller = Mage::getModel('vbw_punchout/punchout_distiller');

        foreach ($products AS $k=> $lineItem) {
            $distiller->setLineItem($lineItem);
            echo $distiller->getDetails() ."\n\n";
        }

        echo "</pre>";

        exit;

    }

    public function seeshippingAction ()
    {
        $sess = Mage::getSingleton('catalog/session');
        echo $sess->getSessionId();
        echo "[". $sess->getArbData() ."]";
        echo "<pre>";
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        print_r($quote->debug());
        print_r($quote->getShippingAddress()->debug());
    }

    public function setshippingAction ()
    {
        echo "<pre>";

        $csess = Mage::getSingleton('checkout/session');
        $quote = $csess->getQuote();

        /**
         * @var $shipp Mage_Sales_Model_Quote_Address
         * @var $helper Vbw_Punchout_Helper_Session
         */
        $shipp = $quote->getShippingAddress();

        $helper = Mage::helper('vbw_punchout/session');

        $data = new \Vbw\Procurement\Punchout\Request\Body\Shipping;

        $data->setShippingTo('Shawn McKnight');
        $data->setShippingStreet('2444 townfield dr');
        $data->setShippingCity('cape charles');
        $data->setShippingState('va');
        $data->setShippingZip('23310');
        print_r($data->toArray());
        $helper->addQuoteShipping($data,$quote);
        print_r($quote->getShippingAddress()->debug());

        $quote->save();

        $csess->setQuoteId($quote->getId());

        echo "Quote Id ". $csess->getQuoteId() ."\n";

        print_r($quote->getShippingAddress()->debug());
        print_r($quote->debug());

    }

}