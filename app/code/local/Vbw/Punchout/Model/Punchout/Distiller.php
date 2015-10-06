<?php

/**
 * The distiller converts magento objects in to po2go objects.
 *
 * this can be overridden as needed to provide specialized conversions
 * of product data in to po2go objects to send to provider.
 *
 */

require_once "Vbw/Procurement/Punchout/Order.php";

class Vbw_Punchout_Model_Punchout_Distiller
{

    const TYPE_SIMPLE  = "simple";
    const TYPE_GROUPED = "grouped";
    const TYPE_CONFIGUREABLE = "configurable";
    const TYPE_VIRTUAL = "virtual";
    const TYPE_BUNDLED = "bundle";
    const TYPE_DOWNLOADABLE = "downloadable";

    /**
     * @var Mage_Sales_Model_Quote_Item
     */
    protected $_lineItem = null;

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * @var Vbw_Punchout_Model_Sales_Stash
     */
    protected $_stash_item = null;

    /**
     *
     * @var Vbw_Punchout_Helper_Config
     */
    protected $_configHelper = null;


    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'base64', 'label'=>Mage::helper('vbw_punchout')->__('Base64 Encoding')),
            array('value'=>'url', 'label'=>Mage::helper('vbw_punchout')->__('URL Encoding'))
        );
    }

    /**
     * @param \Vbw\Procurement\Punchout\Order $poOrder
     * @param $products
     */
    public function addItems ($poOrder,$products)
    {
        /**
         * @var $body \Vbw\Procurement\Punchout\Order\Body
         */
        $body = $poOrder->getBody();

        foreach ($products AS $lineItem) {
            $body->getItems()->addItem($this->getOrderItem($lineItem));
            //    $poOrder->addProduct(new Vbw_Punchout_Product_Magento($product));
        }
    }

    /**
     * @param \Vbw\Procurement\Punchout\Order $poOrder
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addTotals ($poOrder,$quote)
    {
        /** @var $body \Vbw\Procurement\Punchout\Order\Body */
        $body = $poOrder->getBody();

        /**@var $dataHelper Vbw_Punchout_Helper_Data*/
        $dataHelper = Mage::helper('vbw_punchout/data');

        /** @var $configHelper Vbw_Punchout_Helper_Config */
        $configHelper = Mage::helper('vbw_punchout/config');

        // add discount
        if ($configHelper->getconfig('order/include_discount')) {
            $this->addDiscount($poOrder,$quote);
        }
        // add shipping
        if ($configHelper->getconfig('order/include_shipping')) {
            $this->addShipping($poOrder,$quote);
        }
        // add tax
        if ($configHelper->getconfig('order/include_tax')) {
            $this->addTax($poOrder,$quote);
        }

        // add total
        $this->addTotal($poOrder,$quote);
        // add currency
        $body->setCurrency($dataHelper->getStoreCurrencyCode());

    }

    /**
     * @param \Vbw\Procurement\Punchout\Order $poOrder
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addDiscount ($poOrder,$quote)
    {
        /**@var $dataHelper Vbw_Punchout_Helper_Data*/
        $dataHelper = Mage::helper('vbw_punchout/data');

        /** @var $body \Vbw\Procurement\Punchout\Order\Body */
        $body = $poOrder->getBody();

        $totals = $quote->getTotals();

        if (isset($totals['discount'])) {
            $title = $totals['discount']->getTitle();
            $totals = $totals['discount']->getValue();
            $body->setDiscount($dataHelper->getAsStoreCurrency($totals));
            $body->setDiscountTitle($title);
        }
    }


    /**
     * @param \Vbw\Procurement\Punchout\Order $poOrder
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addShipping ($poOrder,$quote)
    {
        /**@var $dataHelper Vbw_Punchout_Helper_Data*/
        $dataHelper = Mage::helper('vbw_punchout/data');

        /** @var $body \Vbw\Procurement\Punchout\Order\Body */
        $body = $poOrder->getBody();

        $totals = $quote->getTotals();

        if (isset($totals['shipping'])) {
            $shippingMethod = $totals['shipping']->getTitle();
            $shippingTotal = $totals['shipping']->getValue();
            $body->setShipping($dataHelper->getAsStoreCurrency($shippingTotal));
            $body->setShippingMethod($shippingMethod);
        }
    }

    /**
     * @param \Vbw\Procurement\Punchout\Order $poOrder
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addTax ($poOrder,$quote)
    {
        /**@var $dataHelper Vbw_Punchout_Helper_Data*/
        $dataHelper = Mage::helper('vbw_punchout/data');

        /** @var $body \Vbw\Procurement\Punchout\Order\Body */
        $body = $poOrder->getBody();

        $totals = $quote->getTotals();

        if (isset($totals['tax'])) {
            $taxTotal = $totals['tax']->getValue();
            $taxDescription = $totals['tax']->getTitle();
            $body->setTax($dataHelper->getAsStoreCurrency($taxTotal));
            $body->setTaxDescription($taxDescription);
        }

    }

    /**
     * @param \Vbw\Procurement\Punchout\Order $poOrder
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addTotal ($poOrder,$quote)
    {

        $configHelper = Mage::helper('vbw_punchout/config');
        /**@var $dataHelper Vbw_Punchout_Helper_Data*/
        $dataHelper = Mage::helper('vbw_punchout/data');

        /** @var $body \Vbw\Procurement\Punchout\Order\Body */
        $body = $poOrder->getBody();

        $totals = $quote->getTotals();
        $total = $quote->getSubtotal();

        if ($configHelper->getconfig('order/include_discount')) {
            if (isset($totals['discount'])) {
                $shippingTotal = $totals['discount']->getValue();
                $total += $shippingTotal;
            }
        }

        if ($configHelper->getconfig('order/include_shipping')) {
            if (isset($totals['shipping'])) {
                $shippingTotal = $totals['shipping']->getValue();
                $total += $shippingTotal;
            }
        }

        if ($configHelper->getconfig('order/include_tax')) {
            if (isset($totals['tax'])) {
                $taxTotal = $totals['tax']->getValue();
                $total += $taxTotal;
            }
        }

        $body->setTotal($dataHelper->getAsStoreCurrency($total));

    }


    /**
     * return the config helper which is used to access configurations
     * related to the module.
     *
     * @return Vbw_Punchout_Helper_Config
     */
    public function getConfigHelper()
    {
        if ($this->_configHelper == null)  {
            $this->_configHelper = Mage::helper('vbw_punchout/config');
        }
        return $this->_configHelper;
    }

    /**
     * get a config value, this will hopefully be modified
     * with a later version that allows the admin to update.
     *
     * @param string $xpath
     * @return mixed
     */
    public function getConfig($xpath)
    {
        return $this->getConfigHelper()->getConfig($xpath);
    }

    /**
     * primary called function to setup the distiller and return a po2go product.
     *
     * @param $lineItem
     * @return Vbw\Procurement\Punchout\Order\Item
     */
    public function getOrderItem ($lineItem)
    {
        // setup the object for a new item.
        $this->setLineItem($lineItem);
        /** @var $stash  */
        $stash = $this->makeStashItem();
        // make the item
        $item = $this->makeOrderItem();

        Mage::dispatchEvent('punchout_order_item_after_setup',array('po_item'=>$item,'lineitem'=>$lineItem,'stash'=>$stash));
        // return.
        return $item;
    }

    /**
     * @return null|Vbw_Punchout_Model_Sales_Stash
     */
    public function makeStashItem ()
    {
        /** @var $salesHelper Vbw_Punchout_Helper_Sales */
        $salesHelper = Mage::helper('vbw_punchout/sales');
        $this->setStashItem($salesHelper->stashLineItemData($this->getLineItem()));

        return $this->getStashItem();
    }

    /**
     * @param $stash
     */
    public function setStashItem ($stash)
    {
        $this->_stash_item = $stash;
    }

    /**
     * @return null|Vbw_Punchout_Model_Sales_Stash
     */
    public function getStashItem ()
    {
        return $this->_stash_item;
    }

    /**
     * the actual process that makes the item.
     * this is a good method to override to add parameters.
     * otherwise override the individual parts.
     *
     * @return \Vbw\Procurement\Punchout\Order\Item
     */
    public function makeOrderItem ()
    {
        // new item.
        $item = new \Vbw\Procurement\Punchout\Order\Item();

        // quantity
        $item->setQuantity($this->getLineItemQuantity());
        // supplierId, primaryId
        $item->setSupplierId($this->getLineItemPrimaryId());
        // secondaryId
        $item->setSupplierAuxId($this->getEditInformation());
        // price
        $item->setUnitPrice($this->getLineItemPrice());
        // currency
        $currency = $this->getStoreCurrency();
        if (!empty($currency)) $item->setCurrency($currency);
        // description
        $item->setDescription($this->getDetails());
        // language
        $language = $this->getDetailsLanguageCode();
        if (!empty($language)) $item->setLanguage($language);
        // classification.
        $item->setClassification($this->getClassification());
        // unit of measure
        $item->setUom($this->getUom());
        // manufacturer
        $item->setManufacturer($this->getProductManufacturer());
        // manufacturerId
        $item->setManufacturerId($this->getProductManufacturerId());

        $this->addFileOptions($item);

        $this->addAdditionalPricingValues($item);

        $this->addCustomMapData($item);

        $this->addAdditionalData($item);

        return $item;
    }


    /**
     * add additional pricing values that may be useful
     * to the gateway.
     *
     * includes, discount information and tax information
     *
     * @param \Vbw\Procurement\Punchout\Order\Item $item
     */
    public function addAdditionalPricingValues ($item)
    {
        /**@var $dataHelper Vbw_Punchout_Helper_Data*/
        $dataHelper = Mage::helper('vbw_punchout/data');

        $lineItem = $this->getLineItem();
        if ($lineItem->getDiscountAmount() > 0) {
            $item->setDiscountPercent($lineItem->getDiscountPercent());
            $item->setTotalDiscountAmount($dataHelper->getAsStoreCurrency($lineItem->getDiscountAmount()));
        }
        if ($lineItem->getTaxAmount() > 0) {
            $item->setTaxPercent($lineItem->getTaxPercent());
            $item->setTotalTaxAmount($dataHelper->getAsStoreCurrency($lineItem->getTaxAmount()));
        }
    }


    /**
     * quantity
     *
     * @return int
     */
    public function getLineItemQuantity()
    {
        return $this->getLineItem()->getQty();
    }

    /**
     * primary id shown in the order.
     * Typically the "sku" (not the product id)
     *
     * @return string
     */
    public function getLineItemPrimaryId ()
    {
        return $this->getLineItem()->getSku();
    }

    /**
     * get the ID or string needed to add the product back in to
     * a cart. This is not shown but returned with an edit.
     * simplest as product id or product id+ configuration options
     *
     * @return string
     */
    public function getEditInformation ()
    {
        //if ($this->isSimpleProduct()) {
        //    return $this->getLineItem()->getProductId();
        //} else {
        //    $data = $this->getEditInformationStraightIds();
            $data = $this->getEditInformationRebuildParams();
        //}
        if (is_array($data)) {
            return Zend_Json::encode($data);
        }
        return $data;
    }

    public function getEditInformationStraightIds ()
    {
        $options = $this->getLineItem()->getQtyOptions();
        $data = array (
            $this->getLineItem()->getProductId() => 1,
        );
        foreach ($options AS $option => $optionData) {
            $data[$optionData->getProductId()]  = 1;
        }
        return $data;
    }

    public function getEditInformationRebuildParams ()
    {
        $stash = $this->getStashItem();
        if ($stash != null) {
            return $stash->getQuoteId() .'/'. $stash->getItemId();
        } else {
            $item = $this->getLineItem();
            return $item->getQuoteId() ."/". $item->getId();
        }

        /** below is no longer used */

        $returnSet = array(
            'product' => $item->getProductId(),
            'qty' => $item->getQty(),
        );

        /// BUILD OPTIONS TO COMPARE WITH
        /** @var $type Mage_Catalog_Model_Product_Type_Configurable */
        /** @var $return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection */
        $product = $this->getLineItem()->getProduct();
        if ($this->isSimpleProduct()) {
            if ($product->hasCustomOptions()) {
                $buyRequest = $product->getCustomOption('info_buyRequest');
                if (!empty($buyRequest)) {
                    $data = unserialize($buyRequest->getValue());
                    if (is_array($data)) {
                        if (isset($data['uenc'])) unset($data['uenc']);
                        if (isset($data['related_product'])) unset($data['related_product']);
                        if (!isset($data['product'])) {
                            return $item->getProductId();
                            // return $returnSet;
                        }
                        if (count($data) == 2
                            && isset($data['qty'])) {
                            return $item->getProductId();
                        }
                        return $data;
                    }
                }
                $customOptions = $product->getCustomOptions();
                foreach ($customOptions AS $option) {
                    if (preg_match('/^option_([\d]+)$/',$option->getCode(),$s)) {
                        $returnSet['options'][$s[1]] = $option->getValue();
                    }
//                    print_r($option->debug());
//                    print_r(unserialize($option->getValue()));
                }
                if (count($returnSet) == 2
                    && isset($returnSet['qty'])) {
                    return $item->getProductId();
                }
                return $returnSet;
            }
            $configurableAttributes = array();
        } elseif ($this->isConfigurableProduct()) {
            $configurableAttributes = $product->getTypeInstance(true)
                ->getConfigurableAttributes($product);
        } else {
            $configurableAttributes = array();
        }
        // what is possible
        $attrOptions = array();

        foreach ($configurableAttributes AS $option)  {
            /** @var $option Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            /** @var $pat Mage_Catalog_Model_Resource_Eav_Attribute */
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attrOptions[$option->getAttributeId()]['code'] = $option->getProductAttribute()->getAttributeCode();
            $attrOptions[$option->getAttributeId()]['prices'] = $option->getPrices();
        }

        // what was chosen
        $qtyOptions = $item->getQtyOptions();

        foreach ($qtyOptions as $qtyOption) {
        //    print_r($qtyOption->debug());
            $prod = Mage::getModel('catalog/product')->load($qtyOption['product_id']);
        //    print_r($prod->debug());
            foreach ($attrOptions AS $attributeId => $attrData) {
        //        echo $attrData['code'] ." : ";
                $val = $prod->getData($attrData['code']);
        //        echo $val ."\n";
                if (!is_null($val)) {
                    $returnSet['super_attribute'][$attributeId] = $val;
                }
            }
        }

        //exit;
        if (count($returnSet) == 2
                && isset($returnSet['qty'])) {
            return $item->getProductId();
        }
        return $returnSet;
    }


    /**
     * price of the product in the currency of the current store.
     *
     * @return float
     */
    public function getLineItemPrice ()
    {
        /** @var $helper Vbw_Punchout_Helper_Data */
        $price = $this->getLineItem()->getPrice();
        $helper = Mage::helper('vbw_punchout/data');
        $currency = $helper->getAsStoreCurrency($price);
        return $currency;
    }

    /**
     *
     *
     * @return string
     */
    public function getStoreCurrency ()
    {
        /** @var $helper Vbw_Punchout_Helper_Data */
        $helper = Mage::helper('vbw_punchout/data');
        return $helper->getStoreCurrencyCode();
    }

    /**
     * unit of measure
     *
     * @return mixed
     */
    public function getUom ()
    {
        $uomField = $this->getConfigHelper()->getProductUomField();

        $uom = $this->getProduct()->getData($uomField);
        if (is_numeric($uom)) {
            /**@var $helper Vbw_Punchout_Helper_Attributes*/
            $helper = Mage::helper('vbw_punchout/attributes');
            try {
                $uom = $helper->getAttributeOptionLabel($uomField,$uom);
            } catch (Exception $e) {
                // not concerned. just means it was not valid?
            }
        }
        if (empty($uom)) {
            return $this->getConfig('defaults/uom');
        }
        return $uom;
    }

    public function getClassification ()
    {
        $unspscField = $this->getConfigHelper()->getProductUnspscField();

        $product = $this->getProduct();
        if ($product->getData($unspscField)) {
            return $product->getData($unspscField);
        } else {
            $catIds = $product->getCategoryIds();
            if (is_array($catIds)) {
                foreach ($catIds AS $catId) {
                    if ($catId == 2 || $catId == 0) continue; // system categories
                    $categoryObj = Mage::getModel('catalog/category')->load($catId);
                    $classId = $this->getClassificationFromCategory($categoryObj);
                    if ($classId != false) return $classId;
                }
            }
        }
        return $this->getConfig('defaults/classification');
    }

    public function getClassificationFromCategory ($categoryObj)
    {
        $unspscField = $this->getConfigHelper()->getCategoryUnspscField();

        if ($categoryObj instanceof Mage_Catalog_Model_Category) {
            if (false != ($code = $categoryObj->getData($unspscField))) {
                return $code;
            } else {
                $parentId = $categoryObj->getParentId();
                if (!empty($parentId)
                    && $parentId != 2
                    && $parentId != $categoryObj->getEntityId()) {
                    $parentObj = $categoryObj->getParentCategory();
                    return $this->getClassificationFromCategory($parentObj);
                }
            }
        }
        return false;
    }

    /**
     * @param Mage_Sales_Model_Quote_ItemMage_Sales_Model_Quote_Item $lineItem
     */
    public function setLineItem($lineItem)
    {
        $this->_lineItem = $lineItem;
        $this->_product = null;
        $this->_stash_item = null;
    }

    /**
     * @return Mage_Sales_Model_Quote_Item
     */
    public function getLineItem()
    {
        return $this->_lineItem;
    }

    /**
     * @param \Mage_Catalog_Model_Product $product
     */
    public function setProduct($product)
    {
        $this->_product = $product;
    }

    /**
     * get the product by the sku, not the id, this will keep it in line on a configured product.
     *
     * @return \Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_product == null) {
//            $this->_product = $this->getLineItem()->getProduct();
            $this->_product = Mage::getModel('catalog/product')->load($this->getLineItem()->getProductId());
        }
        return $this->_product;
    }

    /**
     * Manufacturer name
     *
     * @return string
     */
    public function getProductManufacturer()
    {
        $manufacturer = $this->getProduct()->getManufacturer();
        if (is_numeric($manufacturer)) {
            /**@var $helper Vbw_Punchout_Helper_Attributes*/
            $helper = Mage::helper('vbw_punchout/attributes');
            try {
                $manufacturer = $helper->getAttributeOptionLabel('manufacturer',$manufacturer);
            } catch (Exception $e) {
                // not concerned. just means it was not valid?
            }
        }
        return $manufacturer;
    }

    /**
     * For the manufacturer, we are going to "assume" it is generally the sku.
     *
     * @return string
     */
    public function getProductManufacturerId()
    {
        return $this->getProduct()->getSku();
    }


    /**
     * main descriptions for a product.
     *
     * @return string
     */
    public function getDetails ()
    {
        $return = array();

        $return[] = $this->getProduct()->getName();

        $options = $this->getOptionList();
        if (count($options) > 0) {
            foreach ($options AS $option) {
                if (is_array($option['value'])) {
                    foreach ($option['value'] AS $value) {
                        $value = strip_tags($value);
                        $return[] = "{$option['label']} : {$value}";
                    }
                } else {
                    $value = strip_tags($option['value']);
                    $return[] = "{$option['label']} : {$value}";
                }
            }
        }

        return implode(";\n ", $return);

        /**
        $type = $this->getProductType();
        if ($this->isConfigurableProduct()) {
            return $this->getConfigurableDetails();
        } elseif ($this->isGroupedProduct()) {
            return $this->getGroupedDetails();
        } elseif ($this->isBundledProduct()) {
            return $this->getBundledDetails();
        } else {
            return $this->getSimpleDetails();
        }
         */
    }

    /**
     * get the locale language code.
     *
     * @return string
     */
    public function getDetailsLanguageCode ()
    {
        /** @var $helper Vbw_Punchout_Helper_Data */
        $helper = Mage::helper('vbw_punchout/data');
        return $helper->getStoreLanguage();
    }

    /**
     * add any file options to item data.
     *
     *
     * @param \Vbw\Procurement\Punchout\Order\Item $item
     */
    public function addFileOptions ($item)
    {
        $files = $this->getFileOptions();
        foreach ($files AS $k => $fileData) {
            $extrinsics = $item->getExtrinsics();
            if (!is_array($extrinsics)) {
                $extrinsics = array();
            }
            $extrinsics[(empty($fileData['label']) ? 'File '. ($k+1) : $fileData['label'])] = $fileData['url'];
            $item->setExtrinsics($extrinsics);
        }
    }

    /**
     *[label] => Screen Image
     *[value] => <a href="http://punchoutdemo.me/buyer2/sales/download/downloadCustomOption/id/1040/key/1563bc358dc4b0e4a984/?___SID=U" target="_blank">IMG_0313.jpg</a> 640 x 480 px.
    [print_value] => IMG_0313.jpg 640 x 480 px.
    [option_id] => 13
    [option_type] => file
    [custom_view] => 1
     * @return array
     */
    public function getFileOptions ()
    {
        /** @var $helper Vbw_Punchout_Helper_Sales */
        $helper = Mage::helper('vbw_punchout/sales');
        $lineItem = $this->getLineItem();
        $product = $lineItem->getProduct();

        /** @var $collection Mage_Sales_Model_Resource_Quote_Item_Option_Collection */
        $collection = Mage::getModel('sales/quote_item_option')->getCollection();
        $collection->addFilter('item_id',$lineItem->getId());

        $fileOptions = array();
        foreach ($collection AS $k=>$option) {
            /** @var $productOption Mage_Catalog_Model_Product_Option */
            $productOption = $helper->getOptionProductOption($option);
            if (!empty($productOption)) {
                $type = $productOption->getType();
                if ('file' == $type) {
                    /** @var $anotherOption Mage_Catalog_Model_Product_Option */
                    $anotherOption = $product->getOptionById($productOption->getOptionId());
                    $fileOptions[] = array (
                        'option' => $option,
                        'label' => $anotherOption->getTitle(),
                        'url' => $helper->getFileOptionMediaRef($option)
                    );
                }
            }
        }
        return $fileOptions;

    }

    /**
     *
     * @param \Vbw\Procurement\Punchout\Order\Item $item
     * @return \Vbw\Procurement\Punchout\Order\Item
     */
    public function addCustomMapData ($item)
    {
        $map = $this->getConfigHelper()->getCustomMap();
        if (is_array($map)
                && !empty($map)) {
            foreach ($map AS $mapping) {
                $value = $this->getMapSourceValue($mapping['source']);
                $this->setMapDestination($item,$mapping['destination'],$value);
            }
        }
        return $item;
    }

    /**
     * @param string $path
     * @param string $part
     * @return mixed
     */
    public function getMapSourceValue($path,$part = null)
    {
        if (preg_match('/^([^\/]+)\/([^\/]+)$/',$path,$s)) {
            return $this->getMapSourceValue($s[2],$s[1]);
        }
        switch ($part) {
            case 'option' :
                return $this->getLineItemOptionValue($path);
            case 'product' :
                return $this->getLineItemProductValue($path);
            default :
                $src = $this->getLineItem();
                return $src->getData($path);
        }
    }

    /**
     * @param $path
     * @return null
     */
    public function getLineItemOptionValue ($path)
    {
        $option = $this->getLineItem()->getOptionByCode($path);
        if (!empty($option)) {
            return $option->getValue();
        }
        return null;
    }

    /**
     * @param $path
     * @return mixed|null
     */
    public function getLineItemProductValue ($path)
    {
        // pull from product
        $product = $this->getProduct();
        if ($product->hasData($path)) {
            return $product->getData($path);
        } else {
            // if product does not have the data, try if there is something from a child product.
            if ($this->getLineItem()->getHasChildren()) {
                $children = $this->getLineItem()->getChildren();
                foreach ($children AS $child) {
                    /** @var $child Mage_Sales_Model_Quote_Item */
                    if ($childProduct = Mage::getModel('catalog/product')->load($child->getProductId())) {
                        if ($childProduct->hasData($path)) {
                            return $childProduct->getData($path);
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param \Vbw\Procurement\Punchout\Order\Item $item
     * @param $path
     * @param $value
     * @return \Vbw\Procurement\Punchout\Order\Item
     */
    public function setMapDestination($item,$path,$value)
    {
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $method = 'set'. ucfirst($filter->filter($path));
        $item->$method($value);
        return $item;
    }

    /**
     * override to add any more extrinsic data to the item.
     *
     * @param \Vbw\Procurement\Punchout\Order\Item $item
     */
    public function addAdditionalData ($item)
    {

    }

    /**
     * get details from a configurable item.
     * note : not usued.
     *
     * @return string
     */
    public function getConfigurableDetails ()
    {

    }

    /**
     * getting details from a Bundled item.
     * note : not usued.
     *
     * @return string
     */
    public function getBundledDetails ()
    {
        $return = $this->getProduct()->getName() ."\n";
        if ($this->getLineItem()->getHasChildren()) {
            foreach ($this->getLineItem()->getChildren() AS $child) {

            }
        }
    }

    /**
     * determine the edit mode based on the product types and
     * if we are able to return them in to the cart. as of this
     * version, configurable products cannot be edited.
     *
     * not used individually but reviews the whole group
     *
     * @param $list
     * @return int
     */
    public function getOrderEditMode ($list)
    {
        $edit = 1;
        /*
        foreach ($list AS $item) {
            $this->setLineItem($item);
            if ($item->getProductType() != self::TYPE_SIMPLE
                    || count($this->getOptionList()) > 0) {
                $edit = 0;
            }
        }
        */
        return $edit;
    }


    /**
     * get the option list from a product
     *
     * @return mixed
     */
    public function getOptionList ()
    {
        if ($this->isConfigurableProduct()) {
            /** @var $helper Mage_Catalog_Helper_Product_Configuration */
            $helper = Mage::helper('catalog/product_configuration');
            $options = $helper->getConfigurableOptions($this->getLineItem());
            return $options;
        } elseif ($this->isBundledProduct()) {
            /** @var $helper Mage_Bundle_Helper_Catalog_Product_Configuration */
            $helper = Mage::helper('bundle/catalog_product_configuration');
            $options = $helper->getOptions($this->getLineItem());
            return $options;
        } else {
            /** @var $helper Mage_Catalog_Helper_Product_Configuration */
            $helper = Mage::helper('catalog/product_configuration');
            $options = $helper->getOptions($this->getLineItem());
            return $options;
        }
    }

    /**
     * get the attributes.
     *
     * @return array
     */
    public function getProductAttributes ()
    {
        if ($this->isConfigurableProduct()) {
            $attributes = $this->getProduct()->getTypeInstance(true)
                ->getSelectedAttributesInfo($this->getProduct());
            return $attributes;
        }
        return array();
    }

    /**
     * get the product type.
     *
     * @return stirng
     */
    public function getProductType ()
    {
        return $this->getLineItem()->getProductType();
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isSimpleProduct ()
    {
        if ($this->getProductType() == self::TYPE_SIMPLE) {
            return true;
        }
        return false;
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isConfigurableProduct ()
    {
        if ($this->getProductType() == self::TYPE_CONFIGUREABLE) {
            return true;
        }
        return false;
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isGroupedProduct ()
    {
        if ($this->getProductType() == self::TYPE_GROUPED) {
            return true;
        }
        return false;
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isBundledProduct ()
    {
        if ($this->getProductType() == self::TYPE_BUNDLED) {
            return true;
        }
        return false;
    }



}