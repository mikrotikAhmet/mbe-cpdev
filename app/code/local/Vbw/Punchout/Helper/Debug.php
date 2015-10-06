<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shawnmck
 * Date: 9/6/12
 * Time: 10:07 AM
 * To change this template use File | Settings | File Templates.
 */

class Vbw_Punchout_Helper_Debug
        extends Mage_Core_Helper_Abstract
{



    public function getCartDebugInfo ()
    {
        $request = Mage::app()->getRequest();

        if ($request->has('quote')) {
            $quote = Mage::getModel('sales/quote')->load($request->get('quote'));
        } else {
            /** @var $cart Mage_Checkout_Model_Session */
            $session = Mage::getSingleton('checkout/session');
            /** @var $quote Mage_Sales_Model_Quote */
            $quote = $session->getQuote();
        }
        $order = Mage::getModel('sales/order')->load($quote->getId(),'quote_id');

        $return = "Quote & Order Debugging\n\n";

        $return .= "sales/quote ". get_class($quote) ."\n";
        $return .= print_r($quote->debug(),true);

        $return .= "\n";
        $items = $quote->getAllVisibleItems();

        // $return .= get_class($items) ."\n";
        foreach ($items AS $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            $return .= "\n";
            $return .= get_class($item) ."\n";
            $return .= print_r($item->debug(),true);

            $options = $item->getOptions();
            //$return .= get_class($options) ."\n";
            foreach ($options AS $option) {
                /** @var $option Mage_Sales_Model_Quote_Item */
                $return .= "\n";
                $return .= get_class($option) ."\n";
                $return .= print_r($option->debug(),true);
            }

        }

        $shipping = $quote->getShippingAddress();
        $return .= "\n";
        $return .= "sales/quote_shipping ". get_class($shipping) ."\n";
        $return .= print_r($shipping->debug(),true);


        $return .= "\n";
        $return .= "sales/order ". get_class($order) ."\n";
        $return .= print_r($order->debug(),true);





        return $return;
    }


    /**
     * @param $data
     * @param int $level
     * @return array
     */
    public function debugData ($data,$level = 0)
    {
        $return = array();
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                if (is_object($value)) {
                    if ($level == 0) {
                        $return[$key] = get_class($value);
                    } else {
                        $return[$key] = get_class($value);
                    }
                } elseif (is_array($value)) {
                    $return[$key] = $this->debugData($value);
                } else {
                    $return[$key] = $value;
                }
            }
        }

        return $return;
    }

}