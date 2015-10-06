<?php

class Mbemro_CustomCatalog_Model_Observer {

    public function __construct()   {    }

    public function updatePrice(Varien_Event_Observer $observer)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $event = $observer->getEvent();
        $quote_item = $event->getQuoteItem();
        $product = $quote_item->getProduct();
        $customProduct = Mage::getModel('customcatalog/product');
        if (Mage::getResourceModel("customcatalog/product")->loadByProduct($customProduct, $product)){
            if (!is_null($customProduct->getCustomPrice()) && ($customProduct->getCustomPrice() != 0.00)) {
                $new_price = $customProduct->getCustomPrice();
                $quote_item->setOriginalCustomPrice($new_price);
                $quote_item->save();
                $quote_item->getProduct()->setIsSuperMode(true);
            }
            
        } else {
            //check if product belongs to categories added to customers catalog
            //discount is percentage as only option for now
            $price = $product->getFinalPrice();
            $discounted_price = Mage::getResourceModel('customcatalog/category')->getDiscountedPrice($product, $customer, $price);
            if ($discounted_price != $price) {
                //calculate from main product price
                $quote_item->setOriginalCustomPrice($discounted_price);
                $quote_item->save();
                $quote_item->getProduct()->setIsSuperMode(true);

            }
        }

    }

    
 }
