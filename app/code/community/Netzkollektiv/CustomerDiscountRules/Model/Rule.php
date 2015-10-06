<?php
class Netzkollektiv_CustomerDiscountRules_Model_Rule extends Mage_Core_Model_Abstract {
	protected function _construct() {
		$this->_init('customerdiscountrules/rule');
	}

	public function getCustomerRules($customer) {
		$collection = $this->getCollection();
		$collection->getSelect()
			->where('customer_id = ?',$customer->getId());

		return $collection;
	}

	public function saveCustomerRules($customer, $rules) {
		$this->_getResource()->saveCustomerRules($customer->getId(), $rules);
		return $this;
	}

	public function getCustomerRulesJson($customer = null) {
		if ($customer == null) {
			$customer = Mage::registry('current_customer');
		}

		$rules = array();
		foreach ($this->getCustomerRules($customer) as $rule) {
			$rules[$rule->getRuleId()] = $rule->getDiscountAmount();
		}

		if (count($rules) > 0) {
			return Mage::helper('core')->jsonEncode($rules);
		}
		return '{}';
	}
}
