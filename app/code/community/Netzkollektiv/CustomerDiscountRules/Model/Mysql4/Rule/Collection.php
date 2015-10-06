<?php
class Netzkollektiv_CustomerDiscountRules_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	protected function _construct()
	{
		$this->_init('customerdiscountrules/rule');
	}

	public function joinDiscountAmount($collection, $customer) {
		$collection->getSelect()->joinLeft(
			array('crule'=>$this->getTable('customerdiscountrules/rule')),
			'main_table.rule_id = crule.rule_id AND '.$this->getConnection()->quoteInto('crule.customer_id=?',$customer->getId()),
			array('customer_discount_amount'=>'discount_amount')
		);
	}
}
