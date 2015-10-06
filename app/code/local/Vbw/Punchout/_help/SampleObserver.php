<?php

class Sample_Code_Model_Observer
{

    /**
     * observing [punchout_order_item_after_setup]
     * This is called when building an item to be sent back to punchout2go
     *
     * @param Varien_Event_Observer $observer
     */
    public function punchoutOrderItemAfterSetup (Varien_Event_Observer $observer)
    {
        /** @var $punchoutItem \Vbw\Procurement\Punchout\Order\Item */
        $punchoutItem = $observer->getEvent()->getPoItem();
        /** @var $lineItem Mage_Sales_Model_Quote_Item */
        $lineItem = $observer->getEvent()->getLineitem();

        /*
         * set data in to the item just like setting in to Varian_Object
         * ie.. $obj->setMyField($myData)
         */
        $punchoutItem->setCustomData($lineItem->getCustomData());

    }

    /**
     * Observing [punchout_order_item_stash]
     * Used to stash custom data that can be recalled and rebuilt in to
     * the cart when an "edit" method is used.
     *
     * @param Varien_Event_Observer $observer
     */
    public function punchoutOrderItemStash (Varien_Event_Observer $observer)
    {
        /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
        $stash = $observer->getEvent()->getData('stash_item');

        /** @var $item Mage_Sales_Model_Quote_Item */
        $item = $observer->getEvent()->getData('lineitem');

        /*
         * just use the method "stash(key,value)".
         */
        $stash->stash('note',$item->getNote());

    }

    /**
     * Observing [punchout_cart_item_unstash]
     * Used with an "edit" mode, and adding items back in to a cart.
     *
     * @param Varien_Event_Observer $observer
     */
    public function punchoutCartItemUnstash (Varien_Event_Observer $observer)
    {
        /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
        $stash = $observer->getEvent()->getData('stash_item');

        /** @var $cart Mage_Checkout_Model_Cart */
        /** @var $quote Mage_Sales_Model_Quote */
        $cart = $observer->getEvent()->getData('cart');
        $quote = $cart->getQuote();

        // get the last added item.
        $allItems = $quote->getItemsCollection();
        $lastItem = $allItems->getLastItem();

        /* set back in to your lineItem from the stash.
         * uses the same method "stash" with no second parameter.
         */
        $lastItem->setNote($stash->stash('note'));
        $lastItem->save(); // i think you have to save it.

    }

}
