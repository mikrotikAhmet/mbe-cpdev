<?php
/**
 * Collects total (replaces default functionality to use ADP service)
 *
 * @category    Lyonscg
 * @package     Lyonscg_AdpTaxware
 * @copyright   Copyright (c) 2013 Lyons Consulting Group (www.lyonscg.com)
 * @author      Alexander Lazorenko (alazorenko@lyonscg.com)
 */

/**
 * Collects total (replaces default functionality to use ADP service)
 *
 * @category    Lyonscg
 * @package     Lyonscg_AdpTaxware
 */
class Vbw_Punchout_Model_Sales_Quote_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Tax
{


    /**
     * Does nothing, calculates no taxes.
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        // parent::collect($address);

        $store = $address->getQuote()->getStore();

        $address->setTaxAmount(0);
        $address->setBaseTaxAmount(0);
        //$address->setShippingTaxAmount(0);
        //$address->setBaseShippingTaxAmount(0);
        $address->setAppliedTaxes(array());

        return $this;
    }


}