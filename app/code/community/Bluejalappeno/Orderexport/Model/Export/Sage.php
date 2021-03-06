<?php
/**
 * Magento Bluejalappeno Order Export Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Bluejalappeno
 * @package    Bluejalappeno_OrderExport
 * @copyright  Copyright (c) 2010 Wimbolt Ltd (http://www.bluejalappeno.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Bluejalappeno <sales@bluejalappeno.com>
 * */
class Bluejalappeno_Orderexport_Model_Export_Sage extends Bluejalappeno_Orderexport_Model_Export_Abstractcsv
{
	 const ENCLOSURE = '"';
    const DELIMITER = ',';

 public function exportOrders($orders)
    {
    	$fileName = 'order_export_'.date("Ymd_His").'.csv';
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');

      //  $this->writeHeadRow($fp);
    	$csv = '';
    	foreach ($orders as $orderId) {
			$order = Mage::getModel('sales/order')->loadByAttribute('entity_id',$orderId);
			if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE || $order->getStatus() == Mage_Sales_Model_Order::STATE_CLOSED) {
				$this->writeOrder($order, $fp);
			}
    	}
    	fclose($fp);

        return $fileName;
    }


    protected function taxCharged($order)
    {
    	if($order->getData('tax_amount') == '0.00'){
    		$taxCharged = false;}
    	else{
    		$taxCharged = true;
    	}
    	return $taxCharged;

    }

    protected function setTaxCode($country, $order)
    {

   		if($this->isEcCountry($country) && $this->taxCharged($order)){
    		$taxcode = "T1";
    	}
        elseif($this->isEcCountry($country) && !$this->taxCharged($order) && $order->getData('subtotal') == '0.00'){
        	$taxcode = "T1";
        }
        else{
        	$taxcode = "T0";
        }
        return $taxcode;

    }

    protected function isEcCountry($country)
    {

    	$countries = array(
    	'GB',
    	'AT',
    	'BE',
    	'BG',
    	'CY',
    	'CZ',
    	'DK',
    	'EE',
    	'FI',
    	'FR',
    	'DE',
    	'EL',
    	'HU',
    	'IE',
    	'IT',
    	'LV',
    	'LT',
    	'LV',
    	'LT',
    	'LU',
    	'MT',
    	'NL',
    	'PL',
    	'PT',
    	'RO',
    	'SK',
    	'SI',
    	'ES',
    	'SE'
    	);

    	if(in_array($country, $countries)){

    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }
 /**
	 * Writes the head row with the column names in the csv file.
	 *
	 * @param $fp The file handle of the csv file
	 */
    protected function writeHeadRow($fp)
    {
       fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
	 * Writes the row(s) for the given order in the csv file.
	 * A row is added to the csv file for each ordered item.
	 *
	 * @param Mage_Sales_Model_Order $order The order to write csv of
	 * @param $fp The file handle of the csv file
	 */
    protected function writeOrder($order, $fp)
    {
    	$customerDetails = $order->getBillingAddress();

		$orderdate = substr_replace($order->getData('created_at'), '', -8);
		$orderId = $order->getData('increment_id');

		$customerFirstName = $customerDetails->getFirstname();
        $customerLastName = $customerDetails->getLastname();
        $fullName = $customerFirstName .' ' .$customerLastName;
		$grandTotal = $order->getData('subtotal');
        $taxAmount = $order->getData('tax_amount');
        $paymentMethod = $this->getPaymentMethod($order);
        $taxcode = $this->setTaxCode($customerDetails->getCountry(),$order);
        $refundedAmount = $order->getData('total_refunded');
        $refundedTaxAmount = $order->getData('tax_refunded');
		$accountCodeSales = Mage::getStoreConfig('order_export/sage/sage_sales_account');
		$nominalCodeSales = Mage::getStoreConfig('order_export/sage/sage_sales_nominal');
		$nominalCodeShip = Mage::getStoreConfig('order_export/sage/sage_ship_nominal');
		$accountCodePayments = Mage::getStoreConfig('order_export/sage/sage_pay_account');
		$nominalCodePayments = Mage::getStoreConfig('order_export/sage/sage_pay_nominal');

        $csvData = array('SI',$accountCodeSales,$nominalCodeSales, '0',$orderdate,$orderId,$fullName,$grandTotal,$taxcode,$taxAmount,'1',$paymentMethod,'import');

		fputcsv($fp, $csvData, self::DELIMITER, self::ENCLOSURE);
		if ($order->getShippingAmount()!= NULL && $order->getShippingAmount() != 0) {
			$shippingAmount = $order->getData('shipping_amount');
       		$shipTaxAmount = $order->getData('shipping_tax_amount');
	       
	       	$ship_csvData = array('SI',$accountCodeSales,$nominalCodeShip, '0',$orderdate,$orderId,$fullName,$shippingAmount,$taxcode,$shipTaxAmount,'1',$paymentMethod,'import');
	       	fputcsv($fp, $ship_csvData, self::DELIMITER, self::ENCLOSURE);
		}

        if ($refundedAmount > 0) {
        	 $refundcsvData = array('SC',$accountCodeSales,$nominalCodeSales, '0',$orderdate,$orderId,$fullName,$refundedAmount,$taxcode,$refundedTaxAmount,'1',$paymentMethod,'import');
        	 fputcsv($fp, $refundcsvData, self::DELIMITER, self::ENCLOSURE);
        }
        foreach ($order->getPaymentsCollection() as $payment) {
            $paymentAmount = $payment->getData('amount_paid');
        	$orderTax = $order->getData('tax_amount');
        	$payCsvData = array('SA' ,$accountCodePayments, $nominalCodePayments, '0', $orderdate,$orderId,$fullName,$paymentAmount,$taxcode,$orderTax,'1',$payment->getMethod(),'import');
            fputcsv($fp, $payCsvData, self::DELIMITER, self::ENCLOSURE);
        }    
       

    }

    /**
	 * Returns the head column names.
	 *
	 * @return Array The array containing all column names
	 */
    protected function getHeadRowValues()
    {
        return array(
            'SI',
            'Date',
            '..',
            '..',
            'Order Date',
            'Order Id',
            'Customer Name',
            'Order Grand Total',
            'Taxcode',
        	'Tax Amount',
            '..',
            'Payment method',
            'Action'
    	);
    }

    
}