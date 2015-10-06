<?php



class Vbw_Punchout_Helper_Session
	extends Mage_Core_Helper_Url
{

    /**
     * Mage::helper('vbw_punchout/session')->isEdit();
     *
     * @return bool
     */
    public function isEdit ()
    {
        /** @var $punchoutSession Vbw_Punchout_Model_Session */
        $punchoutSession = Mage::getSingleton('vbw_punchout/session');
        Mage::getSingleton('vbw_punchout/session')->isPunchoutSession();

        // only if this is a punchout session.
        if ($punchoutSession->isPunchoutSession()) {
            if ($punchoutSession->getPunchoutRequest()->getOperation() == 'edit'
                || $punchoutSession->getPunchoutRequest()->getOperation() == 'inspect') {
                return true;
            }
        }
        return false;
    }

    /**
     * Mage::helper('vbw_punchout/session')->isReadOnly();
     *
     * @return bool
     */
    public function isReadOnly ()
    {
        /** @var $punchoutSession Vbw_Punchout_Model_Session */
        $punchoutSession = Mage::getSingleton('vbw_punchout/session');
        Mage::getSingleton('vbw_punchout/session')->isPunchoutSession();

        // only if this is a punchout session.
        if ($punchoutSession->isPunchoutSession()) {
            if ($punchoutSession->getPunchoutRequest()->getOperation() == 'inspect'
                    && Mage::helper('vbw_punchout/config')->getConfig('site/strict_inspect_behavior')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Mage_Checkout_Model_Session $checkoutSession
     * @param bo
     * ol $replace
     * @return mixed
     */
    public function setupPunchoutQuote ($checkoutSession, $replace = false)
    {
        /** @var $punchoutSession Vbw_Punchout_Model_Session */
        $punchoutSession = Mage::getSingleton('vbw_punchout/session');
        Mage::getSingleton('vbw_punchout/session')->isPunchoutSession();

        // only if this is a punchout session.
        if ($punchoutSession->isPunchoutSession()) {

            if ($replace == true) {
                if ($checkoutSession->hasQuote()) {
                    $checkoutSession->replaceQuote(Mage::getModel('sales/quote')
                        ->setStoreId(Mage::app()->getStore()->getId()));

                    $checkoutSession->resetCheckout();
                }
            }

            if (!$checkoutSession->hasQuote()
                    || $replace == true) {

                $quote = Mage::getModel('sales/quote')
                    ->setStoreId(Mage::app()->getStore()->getId());

                /** @var $customerSession Mage_Customer_Model_Session */
                $customerSession = Mage::getSingleton('customer/session');

                // if no quote id exists, then take the new one and set it up.
                if (!$checkoutSession->getQuoteId()
                        || $replace) {
                    $quote->setIsCheckoutCart(true);
                    Mage::dispatchEvent('checkout_quote_init', array('quote'=>$quote));
                    $quote->setStore(Mage::app()->getStore());

                    $checkoutSession->setQuoteId($quote->getId());
                    if ($customerSession->isLoggedIn()) {
                        $checkoutSession->setCustomer($customerSession->getCustomer());
                        $quote->setCustomer($customerSession->getCustomer());
                    }
                }
            }

        }
    }


    /**
     * removes items from an existing session.
     *
     * @param Mage_Sales_Model_Quote $quoteObj
     * @param Mage_Checkout_Model_Cart $cartObj
     */
    public function dumpCartProduct ($quoteObj, $cartObj)
    {
        //remove existing
        //$products = $quoteObj->getAllItems();
        $items = $cartObj->getItems();
        foreach ($items AS $item) {
            $cartObj->removeItem($item->getId());
        }
    }

    /**
     * populates an existing session
     * this is the primary call from the outside, it takes the
     * full set of items to add to the cart.
     *
     * @param \Vbw\Procurement\Punchout\Request\Body\Items $items
     * @param $cartObj
     */
    public function addCartProduct ($items, $cartObj)
    {
        $previous_quote = null;
        foreach ($items AS $k=>$item) {
            /**
             * @var $item \Vbw\Procurement\Punchout\Data
             */
            if ($item->get('type') == "out") {
                try {
                    $return = $this->insertItemToCartWithParam($item,$cartObj);
                    if (!empty($return) && empty($previous_quote)) {
                        $previous_quote = $return;
                    }
                } catch (Exception $e) {
                    Mage::helper('vbw_punchout')->debug('Item insert exception : '. $e->getMessage());
                    Mage::getSingleton('core/session')->addError('Product item # '. $item->get('primaryId') .' was not added back to your cart.');
                }
            }
        }
        return $previous_quote;
    }

    public function insertItemToCartWithParam ($item,Mage_Checkout_Model_Cart $cartObj)
    {
        /** @var $helper Vbw_Punchout_Helper_Data */
        $helper = Mage::helper('vbw_punchout');
        $quote = $cartObj->getQuote();
        $id = $item->get('secondaryId');
        if (is_numeric($id)) {
            $helper->debug('adding by product ID : '. $id);
            $product = Mage::GetModel('catalog/product')->load($id);
            $cartObj->addProduct($product,(int) $item->get('quantity'));
        } else {
            try {
                if (preg_match('/^([0-9]+)\/([0-9]+)$/',$id,$s)) {
                    $option = array (
                        "quote_id" => $s[1],
                        "item_id" => $s[2],
                        "qty" => $item->get('quantity')
                    );
                    $helper->debug('adding by options : '. Zend_Json::encode($option));
                    $this->insertItemToCartWithParamOption($option,$cartObj);
                    return $option['quote_id'];
                } else {
                    $helper->debug('adding by json : '. $id);
                    $idData = Zend_Json::decode($id);
                    $product = Mage::GetModel('catalog/product')->load($idData['product']);
                    if (!empty($product)) {
//                    if (!isset($idData['qty'])) {
                        $idData['qty'] = $item->get('quantity');
//                    }
                        $cartObj->addProduct($product,$idData);
                    }
                }
            } catch (Exception $e) {
                Mage::helper('vbw_punchout/debug')->debug("error adding : ". $e->getMessage());
            }
        }

    }

    /**
     * @param $options
     * @param Mage_Checkout_Model_Cart $cartObj
     * @throws Exception
     */
    public function insertItemToCartWithParamOption ($options,Mage_Checkout_Model_Cart $cartObj)
    {
        /** @var $salesHelper Vbw_Punchout_Helper_Sales */
        $salesHelper = Mage::helper('vbw_punchout/sales');

        $stash = $salesHelper->getLineItemStash($options['item_id'],$options['quote_id']);

        if (!empty($stash)) {

            $requestData = unserialize($stash->getRequest());
            $requestData['qty'] = $options['qty'];
            $current = count($cartObj->getQuote()->getAllVisibleItems());
            //Mage::helper('vbw_punchout')->debug("Current count {$current}");
            $cartObj->addProduct($requestData['product'],$requestData);

            $lineItem = Mage::getSingleton('vbw_punchout/session')->getData('last_quote_item');

            //$newcount = count($cartObj->getQuote()->getAllVisibleItems());
            //Mage::helper('vbw_punchout')->debug("New count {$newcount}");
            //if ($current == $newcount) {
            //    throw new Exception('No change in cart.');
            //}

            /*
            // get the last added item.
            $quote = $cartObj->getQuote();
            $allItems = $quote->getItemsCollection();
            $lineItem = $allItems->getLastItem();
            */

            // not sure if it is "hasParent" it might be "->getData('parent_id') == null"
            // we need to set these values to the "parent" line item.
            while (null != $lineItem->getParentItem()) {
                $lineItem = $lineItem->getParentItem();
            }

            $salesHelper->unstashLineItemData($lineItem,$stash,$cartObj,$options);
           // $lineItem->save();

        } else {
            /** @var $lineItem Mage_Sales_Model_Quote_Item */
            $lineItem = $salesHelper->rebuildLineItem($options['item_id']);

            if ($lineItem != false
                && $lineItem->getQuoteId() == $options['quote_id']) {
                $optionObj = $lineItem->getOptionByCode('info_buyRequest');
                $requestData = unserialize($optionObj->getValue());
                //if (!isset($requestData['qty']))
                $requestData['qty'] = $options['qty'];
                if (isset($requestData['uenc'])) unset($requestData['uenc']);
                if (isset($requestData['related_product'])) unset($requestData['related_product']);
                $cartObj->addProduct($requestData['product'],$requestData);
            } else {
                throw new Exception('Could not add edit item : '. print_r($options,true),'601');
            }
        }

    }

    /** notes:
     * 'product_id' => array(
    'qty' => quantity,
    'super_attribute' => array(
    'attribute_id' => 'value_index',
    'attribute_id' => 'value_index',
    'attribute_id' => 'value_index',
    )
    ),
     */

    /**
     * this function takes a single item from the request and evaluates
     * the single item data to figure out the insert.
     *
     * @param $item
     * @param Mage_Checkout_Model_Cart $cartObj
     */
    public function insertItemToCartDirect ($item,Mage_Checkout_Model_Cart $cartObj)
    {
        $quote = $cartObj->getQuote();
        $id = $item->get('secondaryId');
        if (is_numeric($id)) {
            $product = Mage::GetModel('catalog/product')->load($id);
            $this->addCatalogProductToQuote($quote,$product,(int) $item->get('quantity'));
        } else {
            try {
                $idData = Zend_Json::decode($id);

                $parentItem = null;
                $parentProductId = null;
                foreach ($idData AS $id => $data) {
//                    echo $id;
                    $product = Mage::GetModel('catalog/product')->load($id);

                    $product->setIsInStock(1);
                    $product->setIsSalable(1);
                    //$product->setWebsiteIds
                    $product->setCartQty($item->get('quantity'));
                    $product->setQty($item->get('quantity'));

                    if ($parentProductId != null) {
                        $product->setParentProductId($parentProductId);
                        $product->setStickWithinParent($parentItem);
                    }
//                    print_r($product->debug());

                    $item = $this->addCatalogProductToQuote($quote,$product,($parentProductId == null ? (int) $item->get('quantity') : 1));
                    if ($parentItem == null) {
                        $parentItem = $item;
                        $parentProductId = $product->getId();
                        $item->addQty((int) $item->get('quantity'));
                    } else {
                        $item->setParentItem($parentItem);
                    }
                }
//                exit;
            } catch (Exception $e) {
                // the data was invalid.
            }
        }
    }

    /**
     * This is the actual insert command. it steps around the actual "$cart->addProduct()"
     * method and generates the line item objects directly. It skips and does not generate
     * any of the events typically included with an add to cart.
     * If this is an issues, this could be wrapped with such events.
     * This is taken from Mage_Sales_Model_Quote::addCatalogProductToQuote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Quote_Item
     */
    protected function addCatalogProductToQuote(Mage_Sales_Model_Quote $quote,Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $newItem = false;
        $item = $quote->getItemByProduct($product);
        if (!$item) {
            $item = Mage::getModel('sales/quote_item');
            $item->setQuote($quote);
            if (Mage::app()->getStore()->isAdmin()) {
                $item->setStoreId($quote->getStore()->getId());
            } else {
                $item->setStoreId(Mage::app()->getStore()->getId());
            }
            $newItem = true;
        }

        /**
         * We can't modify existing child items
         */
        if ($item->getId()
            && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions())
            ->setProduct($product);

        // Add only item that is not in quote already (there can be other new or already saved item
        if ($newItem) {
            $quote->addItem($item);
        }

        return $item;
    }


    /**
     * add shipping to the quote.
     *
     * @var $shippingObj Mage_Sales_Model_Quote_Address
     * @param \Vbw\Procurement\Punchout\Request\Body\Shipping $shipping
     * @param Mage_Sales_Model_Quote $quoteObj
     */
    public function addQuoteShipping ($shipping, $quoteObj)
    {
        /** @var $dataHelper Vbw_Punchout_Helper_Data */
        $dataHelper = Mage::helper('vbw_punchout');

        if ($shipping instanceof \Vbw\Procurement\Punchout\Request\Body\Shipping) {
            $data = array (
                "country_id" => $shipping->getData('country_id'),
                "to" => $shipping->getData('shipping_to'),
                "company" => $shipping->getData('shipping_business'),
                "street" => $shipping->getData('shipping_street'),
                "city" => $shipping->getData('shipping_city'),
                "state" => $shipping->getData('shipping_state'),
                "postcode" => $shipping->getData('shipping_zip'),
            );
            $dataHelper->debug('Built : '. json_encode($data));
            if (false == $this->testAddressData($data)) {
                $dataHelper->debug('incomplete address');
                return false;
            }
        } elseif (is_array($shipping)) {
            $data = $shipping;
            $dataHelper->debug('Sent : '. json_encode($data));
        } else {
            $dataHelper->debug('Invalid address data');
            return false;
        }

        $shippingObj = $quoteObj->getShippingAddress();
        $shippingObj->setSameAsBilling(0);
        $shippingObj->setCountryId((isset($data['country_id']) ? $data['country_id'] : 'US'));

        if (isset($data['to']) && !empty($data['to'])) {
            $split = explode(" ",$data['to']);
            if (count($split) >= 2) {
                $last = array_pop($split);
                $shippingObj->setLastname($last);
            }
            $shippingObj->setFirstname(implode(" ",$split));
        }
        if (isset($data['first_name']) && !empty($data['first_name'])) $shippingObj->setFirstname($data['first_name']);
        if (isset($data['last_name']) && !empty($data['last_name'])) $shippingObj->setLastname($data['last_name']);
        if (isset($data['company']) && !empty($data['company'])) $shippingObj->setCompany($data['company']);
        if (isset($data['street']) && !empty($data['street'])) $shippingObj->setStreet($data['street']);
        if (isset($data['city']) && !empty($data['city'])) $shippingObj->setCity($data['city']);
        // $shippingObj->setRegionId($regionId);
        if (isset($data['state']) && !empty($data['state'])) {
            $directory = Mage::helper('vbw_punchout')->getDirectoryRegionByData($data['state'],$shippingObj->getCountryId());
            if (!empty($directory)) {
                $shippingObj->setRegionId($directory->getId());
            }
        }
        if (isset($data['postcode']) && !empty($data['postcode'])) $shippingObj->setPostcode($data['postcode']);

        $shippingObj->setCollectShippingRates(false);
        if (is_numeric($quoteObj->getId())) {
           $shippingObj->setQuoteId($quoteObj->getId());
           $shippingObj->save();
           $dataHelper->debug('Saving address data : '. $quoteObj->getId() ." <- ". $shippingObj->getId());
        }

        // $quoteObj->setShippingAddress($shippingObj);

        return true;

        /**
        [address_name] => BigBuyer Headquarters
        [shipping_business] =>
        [shipping_to] => Jean Picard
        [shipping_street] => 1565 Pine, MS A.2
        [shipping_city] => New York
        [shipping_state] => NY
        [shipping_zip] => 01043
        [shipping_country] => United States
        */


    }

    /**
     * disable a module from the magento stack
     * @ref http://gabrielsomoza.com/magento/disabling-a-module-and-its-output-programmatically/
     *
     * @param string $module
     */
    public function disableModule ($module)
    {
        // Disable the module itself
        if ($this->isModuleEnabled($module)) {
            $nodePath = "modules/$module/active";
            Mage::getConfig()->setNode($nodePath, 'false', true);
        }

        // Disable its output as well (which was already loaded)
        $outputPath = "advanced/modules_disable_output/$module";
        if (!Mage::getStoreConfig($outputPath)) {
            Mage::app()->getStore()->setConfig($outputPath, true);
        }
    }

    /**
     * check to see if a module is enabled, moved to own
     * process for lower version compatibility
     *
     * @param null $moduleName
     * @return bool
     */
    public function isModuleEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            return false;
        }
        /** @var $coreHelper Mage_Core_Helper_Data */
        //$coreHelper = Mage::helper('core/data');
        //if (method_exists($coreHelper,'isModuleEnabled')) {
        //    return $coreHelper->isModuleEnabled($moduleName);
        //} else {
            if (!Mage::getConfig()->getNode('modules/' . $moduleName)) {
                return false;
            }
            $isActive = Mage::getConfig()->getNode('modules/' . $moduleName . '/active');
            if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
                return false;
            }
            return true;
        //}
    }




    public function prepareAnonymousSession ($request)
    {
        /**
         * @var $config Vbw_Punchout_Helper_Config
         * @var $customer Mage_Customer_Model_Customer
         * @var $session Vbw_Punchout_Model_Session
         */
        $session = Mage::getSingleton('vbw_punchout/session');
        $config = Mage::helper('vbw_punchout/config');
        $groupId = $config->getAnonymousLoginGroup();
        if (!empty($groupId)) {
            $group = Mage::getModel('customer/group')->load($groupId);
            if (!$group->getId()) {
                throw new Exception('Requested group id for punchout session is invalid',110);
            }
            // $email = $session->getPunchoutId() ."@". $config->getConfig('api/host');
            $customerSession = Mage::getSingleton("customer/session");
            $customer = $customerSession->getCustomer();
            $customerSession->setCustomerGroupId($group->getId());
            $customerSession->setCustomerAsLoggedIn($customer);
            // $customer->setEmail($email);
            // $customer->setGroupId($group->getId());
            // $customer->save();
        }
    }

    public function prepareSingleLoginSession ($request)
    {
        /**
         * @var $config Vbw_Punchout_Helper_Config
         * @var $customer Mage_Customer_Model_Customer
         */
        $config = Mage::helper('vbw_punchout/config');
        /** @var $requestHelper Vbw_Punchout_Helper_Request */
        $requestHelper = Mage::helper('vbw_punchout/request');

        // user to try and login.
        $user = null;
        // default/fallback store user.
        $defaultUser = $config->getSingleLoginUser();
        // default store group
        $storeGroup = $config->getSingleLoginGroup();

        // get the group to login with
        /** @var $groupObject Mage_Customer_Model_Group */
        $groupObject = $this->getGroupByGroupCode($storeGroup);

        // if we have a group, lets use it with the incoming request.
        // unless it is a demo session. in which chase it needs a user defined.
        if (!empty($groupObject)
                && !$this->isDemoSession()) {
            $user = $requestHelper->getUserEmailFromRequest($request);
        }

        // no user defined in request, use the default user.
        if (empty($user)) {
            $user = $defaultUser;
        }

        // if the user is undefined.
        if (empty($user)) {
            if ($this->isDemoSession()) {
                throw new Exception('A default user must be defined in order to utilize a Single User Login demo session.',101);
            }
            throw new Exception('This store or request is incorrectly configured for user session initialization.',102);
        }

        // try to start the session
        $makeUsers = Mage::getStoreConfig('vbw_punchout/customer/create_users');
        if ($this->startUserSession($request,$user,$groupObject, $makeUsers)) {
            return true;
        }

        throw new Exception("Single login user ({$user}) was not found.",103);
    }


    /*
     * @depricated
     * @param $request

    public function prepareDualLoginSession ($request)
    {

    }
     */

    public function prepareDiscoverLoginSession ($request)
    {
        if ($this->isDemoSession()) {
            throw new Exception('You cannot start a demo session in discover mode.','');
        }
        /**
         * @var $config Vbw_Punchout_Helper_Config
         * @var $customer Mage_Customer_Model_Customer
         */
        $config = Mage::helper('vbw_punchout/config');
        /** @var $requestHelper Vbw_Punchout_Helper_Request */
        $requestHelper = Mage::helper('vbw_punchout/request');

        // user to try and login.
        $user = null;
        // default/fallback store user.
        $defaultUser = $requestHelper->getCustomDefaultPunchoutUser($request);

        // default store group
        $storeGroup = $requestHelper->getCustomDefaultPunchoutGroup($request);

        // get the group to login with
        /** @var $groupObject Mage_Customer_Model_Group */
        $groupObject = $this->getGroupByGroupCode($storeGroup);

        // if we have a group, lets use it with the incoming request.
        // unless it is a demo session. in which chase it needs a user defined.
        if (!empty($groupObject)
            && !$this->isDemoSession()) {
            $user = $requestHelper->getUserEmailFromRequest($request);
        }

        // no user defined in request, use the default user.
        if (empty($user)) {
            $user = $defaultUser;
        }

        // if the user is undefined.
        if (empty($user)) {
            if ($this->isDemoSession()) {
                throw new Exception('A default user must be defined in order to utilize a Single User Login demo session.',104);
            }
            throw new Exception('This store or request is incorrectly configured for user session initialization.',105);
        }

        // try to start the session
        if ($this->startUserSession($request,$user,$groupObject, ($user != $defaultUser ? true : false))) {
            return true;
        }

        throw new Exception("Single login user ({$user}) was not found.",106);
    }

    /**
     * @param \Vbw\Procurement\Punchout\Request $request
     * @param email|int $user
     * @param null|Mage_Customer_Model_Group $groupObj
     * @return Mage_Customer_Model_Customer
     */
    public function loadCustomer ($request, $user, $groupObj = null)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        // $customer = Mage::getModel('customer/customer');
        $websiteId = Mage::app()->getWebsite()->getId();

        /** @var $session Vbw_Punchout_Model_Session */
        $session = Mage::getSingleton('vbw_punchout/session');

        Mage::dispatchEvent('punchout_load_customer',array (
            'session' => $session,
            'request' => $request,
            'email' => $user,
            'group_id' => (is_object($groupObj) ? $groupObj->getId() : $groupObj),
            'website_id' => $websiteId
        ));

        if (!$session->hasCustomer()) {
            if (is_numeric($user)) {
                $session->setCustomer(Mage::getModel('customer/customer')->setWebsiteId($websiteId)->load($user));
            } else {
                $session->setCustomer(Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($user));
                // $customer->loadByEmail($user);
            }
        }

        return $session->getCustomer();

    }

    /**
     * @param \Vbw\Procurement\Punchout\Request $request
     * @param email|int $user
     * @param null|Mage_Customer_Model_Group $groupObj
     * @param bool $make to try and make the user if it does not exist.
     * @return bool
     */
    public function startUserSession ($request,$user,$groupObj = null, $make = false)
    {

        $customer = $this->loadCustomer($request,$user,$groupObj);

        // customer exists, go ahead and log them in.
        if (is_numeric($customer->getId())) {
            Mage::helper('vbw_punchout')->debug(' Login the user ');
            Mage::getSingleton('vbw_punchout/session')->loginById($customer->getId());
            /**@var $customer  Mage_Customer_Model_Customer **/
            //$customerLog = Mage::getModel('log/customer')->loadByCustomer($customer);
            //$customerLog->setLoginAt(date('Y-m-d H:i:s'));
            //$customerLog->save();

            return true;
        } else {
            if ($make
                && !is_numeric($user)) {
                $customer = $this->makeUser($request,$user,$groupObj);
                if (is_object($customer)
                    && is_numeric($customer->getId()))  {
                    Mage::helper('vbw_punchout')->debug(' Login the user ');
                    Mage::getSingleton('vbw_punchout/session')->loginById($customer->getId());
                    //$customerLog = Mage::getModel('log/customer')->loadByCustomer($customer);
                    //$customerLog->setLoginAt(date('Y-m-d H:i:s'));
                    //$customerLog->save();
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * get the group object.
     *
     * @param $groupCode
     * @return Mage_Customer_Model_Group
     */
    public function getGroupByGroupCode ($groupCode)
    {
        // if the code is numeric, try a straight load
        if (is_numeric($groupCode)) {
            $groupObject = Mage::getModel('customer/group')->load($groupCode);
            if (is_numeric($groupObject->getId())) {
                return $groupObject;
            }
        }

        /** @var $collection Mage_Customer_Model_Resource_Group_Collection */
        $collection = Mage::getModel('customer/group')->getCollection();
        $collection->addFilter('customer_group_code',$groupCode);
        $collection->load();
        $groupObject = $collection->getFirstItem();

        if (!is_numeric($groupObject->getId())) {
            return null;
        }
        return $groupObject;

    }

    /**
     *
     * @ref http://inchoo.net/ecommerce/magento/programming-magento/programatically-create-customer-and-order-in-magento-with-full-blown-one-page-checkout-process-under-the-hood/
     *
     * @param \Vbw\Procurement\Punchout\Request $request
     * @param string $email
     * @param Mage_Customer_Model_Group $group
     * @return Mage_Customer_Model_Customer
     */
    public function makeUser ($request,$email,$group = null)
    {
        if ($this->isDemoSession()) {
            throw new Exception('You cannot create a new user with a demo session.');
        }

        if (!preg_match('/[^\s@]+@[^\s@]+\.[^\s@]+$/',$email)) {
            throw new Exception('Unable to create a new user without a valid email :'. ($email));
        }

        /** @var $requestHelper Vbw_Punchout_Helper_Request */
        $requestHelper = Mage::helper('vbw_punchout/request');

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');

        $password = uniqid('auto_');

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);
        //Zend_Debug::dump($customer->debug()); exit;

        if(!$customer->getId()) {

            $nameArray = $requestHelper->getUserSplitName($request);

            if (count($nameArray) > 1) {
                $first = $nameArray[0];
                $last = $nameArray[1];
            } else {
                $first = $nameArray[0];
                $last = " ";
            }

            $customer->setEmail($email);
            $customer->setFirstname($first);
            $customer->setLastname($last);
            $customer->setPassword($password);
            if (!empty($group)) {
                if (is_numeric($group)) {
                    $customer->setGroupId($group);
                } else {
                    $customer->setGroupId($group->getId());
                }
            }

            $defaults = unserialize(Mage::helper('vbw_punchout/config')->getConfig('customer/defaults'));

            if (is_array($defaults) && count($defaults) > 0) {
                foreach ($defaults AS $row) {
                    if (preg_match('/^\{([^\}]+)\}$/',$row['value'],$s)) {
                        $value = $requestHelper->getRequestNode($request,$s[1]);
                        $customer->setData($row['key'],$value);
                    } elseif (preg_match('/^\$([^\$]+)\$$/',$row['value'],$s)) {
                        $value = $customer->getData($s[1]);
                        $customer->setData($row['key'],$value);
                    } else {
                        $customer->setData($row['key'],$row['value']);
                    }
                }
            }

        }

        try {

            Mage::dispatchEvent('punchout_new_customer_setup',array (
                'customer' => $customer,
                'request' => $request,
                'email' => $email,
                'group_id' => $group
            ));

            $customer->save();
            $customer->setConfirmation(null);

            $defaults = unserialize(Mage::helper('vbw_punchout/config')->getConfig('customer/defaults_custom'));

            if (is_array($defaults) && count($defaults) > 0) {
                foreach ($defaults AS $row) {
                    if (preg_match('/^\{([^\}]+)\}$/',$row['value'],$s)) {
                        $value = $requestHelper->getRequestNode($request,$s[1]);
                        $customer->setData($row['key'],$value);
                    } elseif (preg_match('/^\$([^\$]+)\$$/',$row['value'],$s)) {
                        $value = $customer->getData($s[1]);
                        $customer->setData($row['key'],$value);
                    } else {
                        $customer->setData($row['key'],$row['value']);
                    }
                }
            }

            $customer->save();

            // attach shipping flag.
            $attach_shipping = Mage::helper('vbw_punchout/config')->getConfig('customer/attach_shipping');

            if ($attach_shipping == 2
                    || $attach_shipping == 3) {
                $this->addAddressToCustomer($customer,$request->getBody()->getShipping());
            }

            Mage::dispatchEvent('punchout_new_customer_saved',array (
                'customer' => $customer,
                'request' => $request,
                'email' => $email,
                'group_id' => $group
            ));

            return $customer;


        } catch (Exception $e) {
            Mage::helper('vbw_punchout')->debug("Error creating new user. {$email} // ". $e->getMessage());
            throw new Exception('Unable to create a new user with the specified email.');
            //Zend_Debug::dump($ex->getMessage());
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function testAddressData ($data)
    {
        foreach (array('city','state','postcode') AS $test) {
            if (!isset($data[$test])
                    || empty($data[$test])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param array $addressIn
     * @return mixed
     */
    public function addAddressToCustomer ($customer,$addressIn)
    {

        if ($addressIn instanceof \Vbw\Procurement\Punchout\Request\Body\Shipping) {
            $addressArray = array (
                'is_default_billing' => 1,
                'is_default_shipping' => 1,
                "country_id" => $addressIn->getData('country_id'),
                "to" => $addressIn->getData('shipping_to'),
                "company" => $addressIn->getData('shipping_business'),
                "street" => $addressIn->getData('shipping_street'),
                "city" => $addressIn->getData('shipping_city'),
                "state" => $addressIn->getData('shipping_state'),
                "postcode" => $addressIn->getData('shipping_zip'),
                "telephone" => $addressIn->getData('phone'),
            );
            if (false == $this->testAddressData($addressArray)) {
                return false;
            }

        } elseif (is_array($addressIn)) {
            $addressArray = $addressIn;
        } else {
            return false;
        }

        // make sure something is set here.
        if (!isset($addressArray['country_id'])
                || empty($addressArray['country_id'])) {
            $addressArray['country_id'] = 'US';
        }

        // split "to" in to first and last names.
        if (isset($addressArray['to'])
            && !empty($addressArray['to'])) {
            $split = explode(" ",$addressArray['to']);
            if (count($split) >= 2) {
                $last = array_pop($split);
                $addressArray['lastname'] = $last;
            }
            $addressArray['firstname'] = implode(" ",$split);
        }

        // evaluate region id
        if (isset($addressArray['state']) && !empty($addressArray['state'])) {
            $directory = Mage::helper('vbw_punchout')->getDirectoryRegionByData($addressArray['state'],$addressArray['country_id']);
            if (!empty($directory)
                    && is_numeric($directory->getId())) {
                $addressArray['region_id'] = $directory->getId();
            }
        }

        // requires a region id
        if (!isset($addressArray['region_id'])) {
            $addressArray['region_id'] = 0;
        }

        if (!empty($addressArray)) {

            /** @var $customerAddressApi Mage_Customer_Model_Address_Api_V2 */
            try {
                $addressData = new stdClass;
                foreach ($addressArray AS $k=>$v) {
                    $addressData->{$k} = $v;
                }
                $customerAddressApi = Mage::getModel('customer/address_api_v2');
                //$customerAddressApi->create($customer->getId(),$sData);

                $address = Mage::getModel('customer/address');
                foreach ($customerAddressApi->getAllowedAttributes($address) as $attributeCode=>$attribute) {
                    if (isset($addressData->$attributeCode)) {
                        $address->setData($attributeCode, $addressData->$attributeCode);
                    }
                }
                if (isset($addressData->is_default_billing)) {
                    $address->setIsDefaultBilling($addressData->is_default_billing);
                }
                if (isset($addressData->is_default_shipping)) {
                    $address->setIsDefaultShipping($addressData->is_default_shipping);
                }
                $address->setCustomerId($customer->getId());

                // skipping validation, don't want to error the login.
                //$valid = $address->validate();
                //if (is_array($valid)) {
                //    $this->_fault('data_invalid '. implode("\n", $valid),'');
                //}
                $address->save();

                return $address->getId();

            } catch (Exception $e) {
                // this means shipping just did not get added.
//                print_r($addressData);
//                print_r($address->getData());
//                echo $e->getMessage();
//                exit;
            }
        }
    }

    /**
     * test is persistent cart should be used.
     *
     * @return bool
     */
    public function usePersistentCart ()
    {
        /** @var $helper Vbw_Punchout_Helper_Config */
        $helper = Mage::helper('vbw_punchout/config');
        if ($helper->getConfig('site/use_persistent_cart')) {
            /** @var $customerSession Mage_Customer_Model_Session */
            $customerSession = Mage::getSingleton('customer/session');
            if ($customerSession->isLoggedIn()) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the session is a demo session
     *
     * @return bool
     */
    public function isDemoSession ()
    {
        /**
         * @var $helper Vbw_Punchout_Helper_Config
         */
        $helper = Mage::helper('vbw_punchout/config');
        $session = Mage::getSingleton('vbw_punchout/session');
        if ($helper->getConfig('site/demo_session_id') == $session->getPunchoutId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasCustomer ()
    {
        if ($this->_customer == null) {
            return false;
        }
        return true;
    }

    public function getCustomer ()
    {
        if ($this->_customer == null) {

        }
        return $this->_customer;
    }

}
	