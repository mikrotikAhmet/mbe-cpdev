<?php



class Vbw_Punchout_Model_Checkout_Session
    extends Mage_Checkout_Model_Session {

    /*
    public function __construct ()
    {
        parent::__construct();
        $session = Mage::getSingleton('customer/session');
        if ($session->getPunchoutId()
                && $session->getSetupPunchout() == 1) {
            $poSession = Vbw_Punchout::GetSession($session->getPunchoutId());
            Vbw_Punchout_Session_Magento::InjectShippingToQuote($poSession,$this);
            Vbw_Punchout_Session_Magento::InjectItemsToCart($poSession,$this);
        }
    }
     */

/*    public function  __destruct() {
        $session = Mage::getSingleton('customer/session');
        if ($session->getPunchoutId()
                && $session->getSetupPunchout() == 1) {
            $cartObj = Mage::getSingleton('checkout/cart');
            $cartObj->save();
        }
    } */

}
