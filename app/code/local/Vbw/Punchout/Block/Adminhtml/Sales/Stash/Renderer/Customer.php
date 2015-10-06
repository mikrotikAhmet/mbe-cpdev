<?php
class Vbw_Punchout_Block_Adminhtml_Sales_Stash_Renderer_Customer extends
    Mage_Adminhtml_Block_Widget_Grid_Column_renderer_Abstract {

    public function render(Varien_Object $row){
        $value = $row->getData($this->getColumn()->getIndex());
        $customer = Mage::getModel('customer/customer')->load($value);
        if ($customer) {
            return $customer->getName() . "<br/>" . $customer->getEmail();
        }
        return $value;
    }
}
