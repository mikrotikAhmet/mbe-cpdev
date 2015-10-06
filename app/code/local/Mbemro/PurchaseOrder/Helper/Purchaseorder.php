<?php

/**
 * MBE helper class for usage of Purchase Order Number on credit card payments.
 *
 * @category Mbemro Payments
 * @package Mbemro_PurchaseOrder
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */

class Mbemro_PurchaseOrder_Helper_Purchaseorder extends Mage_Core_Helper_Abstract
{
    public function getPONumberForOrder($orderId)
    {
        $order= Mage::getModel('sales/order')->load($orderId);
        return $order->getPurchaseOrderNumber();

    }

    public function savePONumberForOrder($order, $po_number)
    {
        if (!($order instanceof Mage_Sales_Model_Order)) {
            $order= Mage::getModel('sales/order')->load( intval($order));
        }

        if ($order->getId()) {
            $order->setPurchaseOrderNumber($po_number);
            $order->save();
        }
    }
}
