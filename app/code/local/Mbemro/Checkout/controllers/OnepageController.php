<?php
require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';
class Mbemro_Checkout_OnepageController extends Mage_Checkout_OnepageController
{

    /**
     * @return Mbemro_Checkout_OnepageController
     */
    public function preDispatch()
    {
		$this->_isCorporateApproved();	
        parent::preDispatch();
    }
	
	protected function _isCorporateApproved()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $customer->getId()) {
			if($customer->getGroupId() == '4'){
				if(!$customer->getCorpCompanyDd() || !$customer->getCorpDepartmentDd()) {
					$errMsg='Your company and department must be approved by admin to be able to go to checkout.';
				    Mage::getSingleton('core/session')->addError($errMsg);
                    $this->_redirect('checkout/cart');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);					
					return false;
				}
			}
			return true;
        }
        return false;
    }
}