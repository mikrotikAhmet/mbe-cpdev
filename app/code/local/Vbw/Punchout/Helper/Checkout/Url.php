<?php



class Vbw_Punchout_Helper_Checkout_Url
	extends Mage_Checkout_Helper_Url 
{

  /**
     * Return url for checkout
     *
     * @return url
     */
    public function getCheckoutUrl()
    {
		$mageSessHandler = Mage::GetSingleton('vbw_punchout/session');
		if ($mageSessHandler->getPunchoutId()) {
		    return $this->_getUrl('checkout/punchout'); // , array('_secure'=>true));
		} else {
			return parent::getCheckoutUrl();
		}
    }


}
	