<?php
require_once 'Mage/Adminhtml/controllers/CustomerController.php';
class Netzkollektiv_CustomerDiscountRules_CustomerController extends Mage_Adminhtml_CustomerController {
        public function discountrulesAction() {
		$this->_initCustomer();

        	$this->getResponse()->setBody(
  			$this->getLayout()->createBlock('customerdiscountrules/adminhtml_customer_edit_tab_discountrule')->toHtml()
        	);
        }
}
