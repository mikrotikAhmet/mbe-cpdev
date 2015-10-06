<?php
class Netzkollektiv_CustomerDiscountRules_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract {
		protected function _construct() {
				$this->_init('customerdiscountrules/rule', 'rule_id');
				$this->_isPkAutoIncrement = false;
		}

	public function saveCustomerRules($customerId, $rules) {
		foreach ($rules as $key => $rule) {
			$rules[$key]['customer_id'] = $customerId;
		}

		$adapter = $this->_getWriteAdapter();
		$adapter->delete($this->getMainTable(), $adapter->quoteInto('customer_id = ?', $customerId));
		if (count($rules) > 0) {
			$adapter->insertMultiple($this->getMainTable(), $rules);
		}
		return $this;
	}
}
