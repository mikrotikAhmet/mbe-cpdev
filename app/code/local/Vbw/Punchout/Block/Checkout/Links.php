<?php



class Vbw_Punchout_Block_Checkout_Links
	extends Mage_Checkout_Block_Links 
{


  /**
     * Add link on checkout page to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCheckoutLink()
    {
		$poSession = Mage::GetSingleton("vbw_punchout/session");

        // $poSession->reviewSession();
        if ($parentBlock = $this->getParentBlock()) {
            $label = Mage::helper('vbw_punchout/config')->getConfig('display/checkout_nav_button');
            $text = $this->__(!empty($label) ? $label : 'Punchout');
            $parentBlock->addLink($text, 'checkout/cart', $text, true, array('_secure'=>true), 60, null, 'class="top-link-checkout"');
        }
        return $this;

    }


}
	