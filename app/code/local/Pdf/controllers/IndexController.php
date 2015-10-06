<?php

class Pdf_IndexController extends Mage_Core_Controller_Front_Action {        

    public function invoicesAction() {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($order->getId())
                    ->load();
            if ($invoices->getSize() > 0) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);

                return $this->_prepareDownloadResponse(
                    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );

            }
        }
    }

    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }
 }