<?php

class Mbemro_PurchaseOrder_Model_Observer
{
    public function onSaveOrderAfter(Varien_Event_Observer $observerEvent)
    {
        $po_number = Mage::getSingleton('core/session')->getPONumberReference();
        //$po_number = Mage::app()->getRequest()->getParam('payment[cc_po_number]');
        if ($po_number) {
            $order  = $observerEvent->getEvent()->getOrder();
            $helper = Mage::helper('purchaseorder/purchaseorder');
            $helper->savePONumberForOrder($order, $po_number);
        }


    }
}
