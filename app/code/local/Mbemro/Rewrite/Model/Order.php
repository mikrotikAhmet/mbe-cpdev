<?php

class Mbemro_Rewrite_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        //set state to hold (waiting confirmation) for corporate users (id=4)
        $_customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
        if($_customer->getGroupId() == '4' && !$_customer->getCorpDepSupervisor()){
            $this->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Waiting for confirmation');
        }

    }
}