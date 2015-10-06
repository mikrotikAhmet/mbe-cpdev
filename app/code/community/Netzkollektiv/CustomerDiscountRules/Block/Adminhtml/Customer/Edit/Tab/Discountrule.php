<?php
class Netzkollektiv_CustomerDiscountRules_Block_Adminhtml_Customer_Edit_Tab_Discountrule extends Mage_Adminhtml_Block_Promo_Quote_Grid {
	public function __construct() {
		parent::__construct();
		$this->setUseAjax(true);
	}

	public function getGridUrl() {
		return $this->getUrl('*/*/discountrules', array('_current'=>true));
	}

	protected function _addColumnFilterToCollection($column) {
		if ($column->getId() == 'customer_discount_rules') {
			$itemIds = $this->_getSelectedItems();
			if (empty($itemIds)) {
				$itemIds = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('rule_id', array('in'=>$itemIds));
			} elseif(!empty($itemIds)) {
				$this->getCollection()->addFieldToFilter('rule_id', array('nin'=>$itemIds));
			}
			return $this;
		}
		
		return parent::_addColumnFilterToCollection($column);
	}

	public function setCollection($collection) {
		Mage::getResourceModel('customerdiscountrules/rule_collection')->joinDiscountAmount($collection, $this->_getCustomer());
		return parent::setCollection($collection);
	}

	protected function _prepareColumns() {
		$this->addColumn('customer_discount_rules', array(
			'header_css_class' => 'a-center',
			'type'			=> 'checkbox',
			'name'			=> 'customer_discount_rules',
			'values'		=> $this->_getSelectedItems(),
			'align'			=> 'center',
			'index'			=> 'rule_id',
		));

		$this->addColumnAfter('customer_discount_amount', array(
			'header'	=> Mage::helper('catalog')->__('Discount Amount'),
			'width'	=> '1',
			'type'	=> 'number',
			'index'	=> 'customer_discount_amount',
			'editable'	=> '1'
		),'is_active');

		$return = parent::_prepareColumns();

		unset(
			$this->_columns['sort_order'],
			$this->_columns['coupon_code']
		);

		return $return;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
	}

	protected function _getCustomer()
	{
		return Mage::registry('current_customer');
	}

	protected function _getSelectedItems()
	{
		$items = $this->getRequest()->getPost('selected_items');

		if (is_null($items)) {
			$itemsCollection = Mage::getModel('customerdiscountrules/rule')
				->getCustomerRules($this->_getCustomer());

			$items = array();
			foreach ($itemsCollection as $item) {
				$items[] = $item->getId();
			}
		}
		return $items;
	}
}
