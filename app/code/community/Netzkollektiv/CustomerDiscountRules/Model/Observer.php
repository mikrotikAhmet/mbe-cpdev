<?php
class Netzkollektiv_CustomerDiscountRules_Model_Observer {
	public function setCustomerDiscountRules($observer) {
		$rule = $observer->getEvent()->getRule();

		$rules = Mage::getSingleton('customer/session')->getCustomer()
			->getDiscountRules();
		if (!is_array($rules)) {
			return $this;
		}

		foreach ($rules as $_rule) {
			if ($rule->getId() == $_rule['rule_id']) {
				$rule->setBeforeCustomerDiscountRulesRules($rule->getData());
				$rule->setDiscountAmount($_rule['discount_amount']);
				$rule->setName($rule->getName().', '.sprintf("%02d", $_rule['discount_amount']).'%');
				break;
			}
		}
		return $this;
	}

	public function removeCustomerDiscountRulesBeforeSave($observer) {
		$rule = $observer->getEvent()->getRule();
		if ($rule->hasBeforeCustomerDiscountRulesRules()) {
			$rule->setData($rule->getBeforeCustomerDiscountRulesRules());
		}
	}

	public function addCustomerDiscountRulesRulesToCustomer($observer) {
		$customer = $observer->getEvent()
			->getCustomer();

//		if (Mage::getDesign()->getArea() != 'adminhtml') {
//			return $this;
//		}

		$rules = array();
		foreach (Mage::getModel('customerdiscountrules/rule')->getCustomerRules($customer) as $_rule) {
			$rules[] = array(
				'rule_id'		=> $_rule->getRuleId(),
				'discount_amount'	=> $_rule->getDiscountAmount()
			);
		}
		$customer->setDiscountRules($rules);

		return $this;
	}

	public function saveCustomerDiscountRulesRules($observer) {
		$customer = $observer->getEvent()
		   ->getCustomer();

		$rules = array();
		if (!is_array($customer->getDiscountRules())) {
			return $this;
		}

		foreach ($customer->getDiscountRules() as $ruleId => $_rule) {
			if (is_array($_rule)) {
				$rules[] = $_rule;
			} else {
				$rules[] = array(
					'rule_id'			=> $ruleId,
					'discount_amount'	=> $_rule
				);
			}
		}
		Mage::getModel('customerdiscountrules/rule')->saveCustomerRules($customer,$rules);

		return $this;
	}

	public function prepareSaveCustomerDiscountRulesRules($observer) {
		$event = $observer->getEvent();
		$customer = $event->getCustomer();
		$data = $event->getRequest()->getPost();

		if (isset($data['discount_rules'])) {
			$rules = array();
			parse_str($data['discount_rules'], $rules);
			$customer->setDiscountRules($rules);
		}
	}
}
