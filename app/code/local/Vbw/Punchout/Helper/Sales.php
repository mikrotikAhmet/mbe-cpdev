<?php


/**
 * provies simplified access to configuration variables and elements
 *
 *
 */
class Vbw_Punchout_Helper_Sales
    extends Mage_Core_Helper_Abstract
{

    /**
     * get a product option based on quote option
     *
     * @param Mage_Sales_Model_Quote_Item_Option $option
     * @return Mage_Catalog_Model_Product_Option|null
     */
    public function getOptionProductOption($option)
    {

        $optionId = null;
        if (strpos($option->getCode(), Mage_Catalog_Model_Product_Type_Abstract::OPTION_PREFIX) === 0) {
            $optionId = str_replace(Mage_Catalog_Model_Product_Type_Abstract::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }

        $productOption = null;
        if ($optionId) {
            /** @var $productOption Mage_Catalog_Model_Product_Option */
            $productOption = Mage::getModel('catalog/product_option')->load($optionId);
        }

        if (!$productOption || !$productOption->getId()
            || $productOption->getProductId() != $option->getProductId()) {
            return null;
        } else {
            return $productOption;
        }
    }

    /**
     * gets the "http://<domain>/" host path for the current site.
     *
     * @param Mage_Sales_Model_Quote_Item_Option $option
     * @return string
     */
    public function getOptionType ($option)
    {
        $productOption = $this->getOptionProductOption($option);
        if ($productOption != null)  {
            return $productOption->getType();
        }
        return null;
    }


    /**
     * get the punchout media reference for a file option.
     *
     * @param Mage_Sales_Model_Quote_Item_Option $option
     * @return string
     */
    public function getFileOptionMediaRef ($option)
    {
        $type = $this->getOptionType($option);
        if ($type == 'file') {
            $data = unserialize($option->getValue());
            $host = Mage::app()->getStore(0)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            return $host .'ia/m/d/i/'. $option->getId() .'/f/'. $data['title'];
        }
    }



    /**
     * get a line item with it's options loaded
     *
     * @param string $lineItemId
     * @return Mage_Sales_Model_Quote_Item
     */
    public function rebuildLineItem ($lineItemId)
    {
        /** @var $lineItem Mage_Sales_Model_Quote_Item */
        $lineItem = Mage::getModel('sales/quote_item')->load($lineItemId);
        if (!empty($lineItem)) {
            /** @var $collection Mage_Sales_Model_Resource_Quote_Item_Option_Collection */
            $collection = Mage::getModel('sales/quote_item_option')->getCollection();
            $collection->addFieldToFilter('item_id',$lineItemId);
            $lineItem->setOptions($collection);
            return $lineItem;
        }
        return false;
    }

    /**
     * get the "info_buyRequest" made when adding the item to the cart.
     *
     * @param $lineItemId
     * @return array
     */
    public function getRequestDataFromLineItem ($lineItemId,$force = false)
    {
        if ($lineItemId instanceof Mage_Sales_Model_Quote_Item) {
            $lineItem = $lineItemId;
            $force = true;
        } else {
            /** @var $lineItem Mage_Sales_Model_Quote_Item */
            $lineItem = $this->rebuildLineItem($lineItemId);
        }
        $data = array();
        $option = $lineItem->getOptionByCode('info_buyRequest');
        if (!empty($option)) {
            $data = unserialize($option->getValue());
            if (isset($data['uenc'])) unset($data['uenc']);
            if (isset($data['related_product'])) unset($data['related_product']);
        }
        if (!isset($data['product'])) $data['product'] = $lineItem->getProductId();
        if (!isset($data['qty']) || $force) $data['qty'] = $lineItem->getQty();
        return $data;
    }

    /**
     *
     * @param $order Mage_Sales_Model_Quote
     * @return Vbw_Punchout_Model_Sales_Quote_Stash
     */
    public function stashBaseOrderData ($quote)
    {
        $lastCart = Mage::getSingleton('catalog/session')->getLastCart();

        /** @var $product Mage_Catalog_Model_Product */
        //$product->getUr


        if (!empty($lastCart)
                && $lastCart->quote_id != null) {
            /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
            $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByQuoteId($lastCart->quote_id);
            $stash->setQuoteId($lastCart->quote_id);
            $stash->setCustomerId($quote->getCustomerId());
            $stash->setStoreId($quote->getStoreId());
            $stash->setItemId(0);
        } else {
            /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
            $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByQuoteId($quote->getId());

            $stash->setQuoteId($quote->getId());
            $stash->setCustomerId($quote->getCustomerId());
            $stash->setStoreId($quote->getStoreId());
            $stash->setItemId(0);
        }

        $stash->setRequest(serialize(array('date'=>date('c'))));

        $stash->stash('shipping_method',$quote->getShippingAddress()->getShippingMethod());
        $stash->stash('coupon_code',$quote->getCouponCode());

        $customData = Mage::getStoreConfig('vbw_punchout/order/stash_order_list');
        $customData = unserialize($customData);
        if (!empty($customData)) {
            foreach($customData as $datum) {
                $info = $quote->getData($datum['key']);
                $stash->stash($datum['key'], $info);
            }
        }

        Mage::dispatchEvent('punchout_order_stash',array('stash_quote'=>$stash,'quote'=>$quote));

        $stash->save();
        return $stash;
    }

    /**
     * @param $cartObj
     * @param $previous_quote
     */
    public function unstashBaseOrderData ($cartObj,$previous_quote)
    {
        Mage::helper('vbw_punchout')->debug('unstashing base');

        $quoteObj = $cartObj->getQuote();

        $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByQuoteId($previous_quote);

        Mage::helper('vbw_punchout')->debug('stashed : '. $stash->getRequest() .":". $stash->getStash());

        // shipping method
        $method = $stash->stash('shipping_method');
        if (!empty($method)) {
            Mage::helper('vbw_punchout')->debug('adding shipping method '. $method);
            $quoteObj->getShippingAddress()->setShippingMethod($method);
        }

        // coupon
        $coupon = $stash->stash('coupon_code');
        if (!empty($coupon)) {
            Mage::helper('vbw_punchout')->debug('adding coupon code '. $coupon);
            $quoteObj->setCouponCode($coupon);
        }

        // config stashed data
        $customData = Mage::getStoreConfig('vbw_punchout/order/stash_order_list');
        $customData = unserialize($customData);
        if (!empty($customData)) {
            foreach($customData as $datum) {
                $info = $stash->stash($datum['key']);
                $quoteObj->setData($datum['key'], $info);
            }
        }

        // event
        Mage::dispatchEvent('punchout_cart_unstash',array (
                                   'stash_quote' => $stash,
                                   'cart' => $cartObj
                                    ));
        $quoteObj->save();

        Mage::helper('vbw_punchout')->debug('base unstashed');
    }


    /**
     * @param $lineItem Mage_Sales_Model_Quote_Item
     * @return Vbw_Punchout_Model_Sales_Quote_Stash
     */
    public function stashLineItemData ($lineItem)
    {

        $lastCart = Mage::getSingleton('catalog/session')->getLastCart();

        $data = $this->getRequestDataFromLineItem($lineItem);


        if (!empty($lastCart)
                && $lastCart->quote_id != null) {
            $quoteId = $lastCart->quote_id;
            $edit_key = $lineItem->getSku();

            if (null != ($lastLineItemObj = $this->getLineFromLastCart($edit_key,$lastCart))) {
                $lineItemId = $lastLineItemObj->getItemId();
                $lastLineItemObj->setUsed(1);
            } else {
                $lineItemId = $lineItem->getId();
            }

            /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
            $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByLineItemId($lineItemId,$quoteId);

            $stash->setQuoteId($quoteId);
            $stash->setItemId($lineItemId);
            $stash->setCustomerId($lineItem->getQuote()->getCustomerId());
            $stash->setStoreId($lineItem->getQuote()->getStoreId());
            $stash->setStash('');
        } else {
            /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
            $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByLineItemId($lineItem->getId(),$lineItem->getQuoteId());

            $stash->setQuoteId($lineItem->getQuoteId());
            $stash->setItemId($lineItem->getId());
            $stash->setCustomerId($lineItem->getQuote()->getCustomerId());
            $stash->setStoreId($lineItem->getQuote()->getStoreId());
        }

        $stash->setRequest(serialize($data));

        $customData = Mage::getStoreConfig('vbw_punchout/order/stash_item_list');
        $customData = unserialize($customData);
        if (!empty($customData)) {
            foreach($customData as $datum) {
                $info = null;
                if (preg_match('/^([^\/]+)\/([^\/]+)$/',$datum['key'],$s)) {
                    $src = $s[1];
                    $code = $s[2];
                    switch ($src) {
                        case "option" :
                            $option = $lineItem->getOptionByCode($code);
                            if (!empty($option)) {
                                $info = $option->getValue();
                            }
                    }
                } else {
                    $info = $lineItem->getData($datum['key']);
                }
                if ($info !== null) {
                    $stash->stash($datum['key'], $info);
                }
            }
        }

        Mage::dispatchEvent('punchout_order_item_stash',array('stash_item'=>$stash,'lineitem'=>$lineItem));

        $stash->save();
        return $stash;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $lineItem
     * @param Vbw_Punchout_Model_Sales_Stash $stash
     * @param Mage_Checkout_Model_Cart $cartObj
     * @param $options
     */
    public function unstashLineItemData ($lineItem,$stash,$cartObj,$options)
    {

        $customData = Mage::getStoreConfig('vbw_punchout/order/stash_item_list');
        $customData = unserialize($customData);
        if (!empty($customData)) {
            foreach($customData as $datum) {
                $info = $stash->stash($datum['key']);
                if (!empty($info)
                        || is_numeric($info)) {
                    if (preg_match('/^([^\/]+)\/([^\/]+)$/',$datum['key'],$s)) {
                        $src = $s[1];
                        $code = $s[2];
                        switch ($src) {
                            case "option" :
                                $option = array('code'=>$code,'value'=>$info);
                                $lineItem->addOption($option);
                        }
                    } else {
                        $lineItem->setData($datum['key'], $info);
                    }
                }
            }
        }

        Mage::dispatchEvent('punchout_cart_item_unstash',array('stash_item'=>$stash, 'item'=>$lineItem,'cart'=>$cartObj,'options'=>$options));
        Mage::helper('vbw_punchout')->debug('unstash item '. print_r(Mage::helper('vbw_punchout/debug')->debugData($lineItem->getData()),true));
    }

    /**
     * @param $lineItemId
     * @param $quoteId
     * @return Vbw_Punchout_Model_Sales_Quote_Stash
     */
    public function getLineItemStash ($lineItemId,$quoteId)
    {
        /** @var $stash Vbw_Punchout_Model_Sales_Quote_Stash */
        $stash = Mage::getModel('vbw_punchout/sales_quote_stash')->loadByLineItemId($lineItemId,$quoteId);
        if (!is_numeric($stash->getId())) {
            return null;
        }
        return $stash;
    }

    /**
     *
     * @param $edit_key
     * @param $lastCart
     * @return null
     */
    public function getLineFromLastCart ($edit_key,$lastCart)
    {
        Mage::helper('vbw_punchout')->debug('Matching '. $edit_key);
        $lineItemId = null;
        if (isset($lastCart->items[$edit_key])) {
            for ($i = 0; $i < count($lastCart->items[$edit_key]); $i++) {
                if ($lastCart->items[$edit_key][$i]->used == 0) {
                    $lineItemId = $lastCart->items[$edit_key][$i]->getItemId();
                    Mage::helper('vbw_punchout')->debug('Matched '. $edit_key .' with '. $lineItemId);
                    return $lastCart->items[$edit_key][$i];
                }
            }
        }
        return $lineItemId;
    }

}
	