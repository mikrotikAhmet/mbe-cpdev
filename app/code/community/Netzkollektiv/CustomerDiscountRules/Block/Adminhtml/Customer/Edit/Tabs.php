<?php
class Netzkollektiv_CustomerDiscountRules_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs {
    protected function _beforeToHtml() {
        if (Mage::registry('current_customer')->getId()) {
            $this->addTab('customerdiscountrules', array(
                'label'     => Mage::helper('salesrule')->__('Shopping Cart Price Rules'),
                'content'   => $this->getLayout()->createBlock('adminhtml/template','customer.discountrule.container')
                    ->setTemplate('customerdiscountrules/edit/tab/discountrule.phtml')
                    ->setChild('customer.discountrule.grid',$this->getLayout()
                        ->createBlock('customerdiscountrules/adminhtml_customer_edit_tab_discountrule','customer.discountrule.grid')
                    )->toHtml()
            )   ,'tags');
        }
        return parent::_beforeToHtml();
    }
}
