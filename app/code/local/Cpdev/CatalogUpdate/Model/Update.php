<?php
class Cpdev_CatalogUpdate_Model_Update
{
    public
        $filename,
        $filepath,
        $is_enabled,                    // is module enabled
        $attribute_set_id,              // attribute set
        $root_category_id,              // top category
        $product_type         = 'simple', // simple product type
        $product_visibility   = 4,        // product visibility as 'catalog, search'
        $product_status       = 1,
        $product_tax_class_id = 0,
        $reduce_list_price    = 0,
        $logfile,
        $date,
        $debug,

        // validation
        $sku_length  = 50,
        $name_length = 255,
        $desc_length = 2000,
        $price_min   = 0,
        $price_max   = 1000000;

    private
        $products   = array(),
        $categories = array(),
        $result     = array();

    public function __construct()
    {
        $this->debug = true;

        // get settings
        $settings = 'catalogupdate/catalogupdate_settings/';
        $this->is_enabled       = Mage::getStoreConfig($settings . 'is_enabled');
        $this->attribute_set_id = Mage::getStoreConfig($settings . 'attribute_set');
        $this->root_category_id = Mage::getStoreConfig($settings . 'root_category');
        $this->website_ids      = array(Mage::app()->getStore(true)->getWebsite()->getId());

        // log file path
        $this->date    = date('Ymdhis');
        $this->logfile = 'catalog-update' . DS . $this->date . '.log';

        // requirements
        $this->root_category = Mage::getModel('catalog/category')->load($this->root_category_id);
        $this->core_resource = Mage::getSingleton('core/resource');
        $this->dbread        = $this->core_resource->getConnection('core_read');      // read database connection
        $this->dbwrite       = $this->core_resource->getConnection('core_write');     // write database connection
        $this->media_api     = Mage::getModel("catalog/product_attribute_media_api"); // media API
        $this->images_path   = Mage::getBaseDir() . DS . 'shell' . DS . 'ptstools' . DS . 'images';
    }

    public function importPTSProducts()
    {
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile, true);

        // get exported products from databse
        $pts_products       = Mage::helper('catalogupdate')->getPTSProducts('pts_product');
        $products_processed = 0;
        $c                  = 0;

        foreach ($pts_products as $product)
        {
            $c++;

            $sku    = trim($product['pts_item_number']);
            $brand  = trim($product['brand']);

            // look for the product in database among products sent by client
            // $data = Mage::helper('catalogupdate')->getPTSProductBySKUAndBrand($sku, $brand);
            $data = Mage::helper('catalogupdate')->getPTSProductBySKU($sku, 'pts_tools');

            if ($data) {

                $name               = Mage::helper('catalogupdate')->clearText($product['product_info']);
                $description        = Mage::helper('catalogupdate')->clearText($product['description']);
                $brand              = Mage::helper('catalogupdate')->clearText($product['brand']);
                $mfg_part_number    = Mage::helper('catalogupdate')->clearText($product['mfg_part_number']);
                $erp_part_number    = Mage::helper('catalogupdate')->clearText($product['erp_part_number']);
                $erp_vendor_number  = Mage::helper('catalogupdate')->clearText($product['erp_vendor_number']);
                $weight             = Mage::helper('catalogupdate')->clearText($product['weight']);
                $dist_center        = $product['dist_center'];
                $tech_specs         = $product['tech_specs'];
                $your_price         = $this->fixPrice($product['my_price']);
                $price              = $this->fixPrice($product['list_price']); // $this->reducePrice($this->fixPrice($product['list_price']));
                $per_quantity       = $product['per_quantity'];
                $image              = $product['image'];
                $discount           = $data['Discount'];
                $shipping_info      = $data['Shipping_Info'];
                $category_names     = Mage::helper('catalogupdate')->clearText($data['Category']);

                // skip invalid product
                if ($this->productHasDataError($name, $description, $brand, $mfg_part_number, $erp_vendor_number, $erp_part_number, $weight)) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                    continue;

                }

                // find category >>

                // skip product without category string
                if ($category_names == '') {

                    Mage::helper('catalogupdate')->logDebug("error: missing categories", $this->logfile, true);
                    continue;

                }

                // resolves special cases in category string and return an array of correct categories
                $category_names_arr = Mage::helper('catalogupdate')->resolveCategories($category_names, $brand);

                // create category if necessary
                $category           = $this->createCategories($category_names_arr);
                $category_id        = $category->getId();
                $category_ids       = explode('/', $category->getPath());

                // find product by MFG part number as SKU
                $product    = Mage::getModel('catalog/product')->loadByAttribute('sku', $mfg_part_number);
                $is_new     = !$product || !$product->getId();

                // new product
                if ($is_new) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);

                    // new product object
                    $product = Mage::getModel('catalog/product')
                        ->setWebsiteIds($this->website_ids)             // assign product to the default website
                        ->setTypeId($this->product_type)                // create only "simple" products
                        ->setTaxClassId($this->product_tax_class_id)    // none | taxable goods | shipping (0 | 2 | 4)
                        ->setAttributeSetId($this->attribute_set_id)    // default attribute set
                        ->setVisibility($this->product_visibility)      // sets visibility as 'catalog, search'
                        ->setStatus($this->product_status)              // enabled
                        ->setData('sku', $mfg_part_number)              // set MFG part number as SKU
                    ;

                }
                // existing product
                else {

                    Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);

                    // load product
                    $product = Mage::getModel('catalog/product')->load($product->getId());

                }

                $product
                    ->setData('name', $name)
                    ->setData('description', $description)
                    ->setData('short_description', $description)
                    ->setData('brand', $brand)
                    ->setData('mfg_part', $mfg_part_number)
                    ->setData('erp_part_number', $erp_part_number)
                    ->setData('erp_vendor_number', $erp_vendor_number)
                    ->setData('weight', $weight)
                    ->setData('tech_specs', $tech_specs)
                    ->setData('shipping_info', $shipping_info)
                    ->setData('dist_center', $dist_center)
                    ->setData('your_price', $your_price)
                    ->setData('price', $price)

                    // set stock
                    ->setStockData(array(
                           'use_config_manage_stock' => 0,      // 'Use config settings' checkbox
                           'manage_stock'            => 0,      // manage stock
                           // 'min_sale_qty'            => 0,   // Minimum Qty Allowed in Shopping Cart
                           // 'max_sale_qty'            => 0,   // Maximum Qty Allowed in Shopping Cart
                           'is_in_stock'             => 1,      // Stock Availability
                           // 'qty'                     => 0,   // qty
                       )
                    )

                    // set category
                    ->setCategoryIds($category_ids)
                ;

                // save product
                $product->save();

                // add new product image
                $this->addProductImage($product, $image);

                $products_processed++;

                // if ($products_processed == 3) break; // uncomment to limit processed products and check results

                continue;
            }

            Mage::helper('catalogupdate')->logDebug($c . ". product does not exist " . $sku, $this->logfile, true);
        }

        Mage::helper('catalogupdate')->logDebug("processed products total: " . $products_processed, $this->logfile, true);

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex
        Mage::unregister('isSecureArea');                                    // close secured area
    }

    public function importPTSProductsMissing()
    {/*
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile, true);
*/
        // get exported products from databse
        $pts_products       = Mage::helper('catalogupdate')->getPTSProducts('pts_product');
        $products_processed = 0;
        $products_insert    = 0;
        $products_update    = 0;
        $c                  = 0;

        foreach ($pts_products as $product)
        {
            $c++;

            $sku                = trim($product['pts_item_number']);
            $brand              = trim($product['brand']);
            $mfg_part_number    = trim($product['mfg_part_number']);

            // look for a product extracted earlier that can't be matched 
            // by PTS item number but by MFG part number
            $data = Mage::helper('catalogupdate')->getPTSProductBySKU($sku, 'pts_tools');

            if ($data) continue; // product exists

            // try find it by MFG part number
            $data = Mage::helper('catalogupdate')->getPTSProductsByMFGPartNumber($mfg_part_number);

            if (!$data) {
                // Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);
                continue;
            }

            // find product by MFG part number as SKU
            $product    = Mage::getModel('catalog/product')->loadByAttribute('sku', $mfg_part_number);
            $is_new     = !$product || !$product->getId();

            // new product
            if ($is_new) {

                // Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);
                echo $c . ". product [insert] " . $sku . "\n";

                $products_insert ++;
            }
            // existing product (already inserted)
            else {

                // Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);
                echo $c . ". product [update] " . $sku . "\n";

                $products_update ++;
            }
/*
            $name               = Mage::helper('catalogupdate')->clearText($product['product_info']);
            $description        = Mage::helper('catalogupdate')->clearText($product['description']);
            $brand              = Mage::helper('catalogupdate')->clearText($product['brand']);
            $mfg_part_number    = Mage::helper('catalogupdate')->clearText($product['mfg_part_number']);
            $erp_part_number    = Mage::helper('catalogupdate')->clearText($product['erp_part_number']);
            $erp_vendor_number  = Mage::helper('catalogupdate')->clearText($product['erp_vendor_number']);
            $weight             = Mage::helper('catalogupdate')->clearText($product['weight']);
            $dist_center        = $product['dist_center'];
            $tech_specs         = $product['tech_specs'];
            $your_price         = $this->fixPrice($product['my_price']);
            $price              = $this->fixPrice($product['list_price']); // $this->reducePrice($this->fixPrice($product['list_price']));
            $per_quantity       = $product['per_quantity'];
            $image              = $product['image'];
            $discount           = $data['Discount'];
            $shipping_info      = $data['Shipping_Info'];
            $category_names     = Mage::helper('catalogupdate')->clearText($data['Category']);
*/
            $products_processed++;

            // if ($products_processed == 3) break; // uncomment to limit processed products and check results

            // break;
            continue;

            // Mage::helper('catalogupdate')->logDebug($c . ". product exists " . $sku, $this->logfile, true);
        }

        echo "insert: " . $products_insert . "\n";
        echo "update: " . $products_update . "\n";

/*
        Mage::helper('catalogupdate')->logDebug("processed products total: " . $products_processed, $this->logfile, true);

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex
        Mage::unregister('isSecureArea');                                    // close secured area*/
    }

    public function importPTS3M()
    {
        $log_statistics = 'catalog-update/3M-statistics.log';
        $log_categories = 'catalog-update/3M-categories.log';

        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile, true);

        // get exported products from databse
        $pts_products       = Mage::helper('catalogupdate')->getPTSProducts('pts_3m');
        $products_inserted  = 0;
        $products_exist     = 0;
        $products_errors    = 0;
        $c                  = 0;

        foreach ($pts_products as $product)
        {
            $c++;

            $name               = Mage::helper('catalogupdate')->clearText($product['product_info']);
            $description        = Mage::helper('catalogupdate')->clearText($product['description']);
            $brand              = '3M';
            $mfg_part_number    = Mage::helper('catalogupdate')->clearText($product['mfg_part_number']);
            $sku                = $mfg_part_number;
            $erp_part_number    = Mage::helper('catalogupdate')->clearText($product['erp_part_number']);
            $erp_vendor_number  = Mage::helper('catalogupdate')->clearText($product['erp_vendor_number']);
            $weight             = Mage::helper('catalogupdate')->clearText($product['weight']);
            $your_price         = $this->fixPrice($product['my_price']);
            $price              = $this->fixPrice($product['list_price']);
            $category_names     = Mage::helper('catalogupdate')->clearText($product['category']);

            Mage::helper('catalogupdate')->logDebug($c . ". product " . $sku, $this->logfile, true);

            // skip invalid product
            if ($this->product3MHasDataError($name, $description, $mfg_part_number, $erp_vendor_number, $erp_part_number, $category_names)) {

                Mage::helper('catalogupdate')->logDebug("product invalid " . $sku, $this->logfile, true);
                $products_errors++;
                continue;

            }

            // find category >>

            // resolves special cases in category string and return an array of correct categories
            $category_names_arr = Mage::helper('catalogupdate')->resolveCategories($category_names, $brand);

            // create category if necessary
            $category           = $this->createCategories($category_names_arr, $log_categories);
            $category_id        = $category->getId();
            $category_ids       = explode('/', $category->getPath());

            // find product by MFG part number as SKU
            $product    = Mage::getModel('catalog/product')->loadByAttribute('sku', $mfg_part_number);
            $is_new     = !$product || !$product->getId();

            // existing product
            if (!$is_new) {

                Mage::helper('catalogupdate')->logDebug("product exists", $this->logfile, true);
                $products_exist++;
                continue;

            }

            // new product
            $product = Mage::getModel('catalog/product')
                ->setWebsiteIds($this->website_ids)             // assign product to the default website
                ->setTypeId($this->product_type)                // create only "simple" products
                ->setTaxClassId($this->product_tax_class_id)    // none | taxable goods | shipping (0 | 2 | 4)
                ->setAttributeSetId($this->attribute_set_id)    // default attribute set
                ->setVisibility($this->product_visibility)      // sets visibility as 'catalog, search'
                ->setStatus($this->product_status)              // enabled

                ->setData('sku', $mfg_part_number)              // set MFG part number as SKU
                ->setData('name', $name)
                ->setData('description', $description)
                ->setData('short_description', $description)
                ->setData('brand', $brand)
                ->setData('mfg_part', $mfg_part_number)
                ->setData('erp_part_number', $erp_part_number)
                ->setData('erp_vendor_number', $erp_vendor_number)
                ->setData('weight', $weight)
                ->setData('your_price', $your_price)
                ->setData('price', $price)

                // set stock
                ->setStockData(array(
                       'use_config_manage_stock' => 0,      // 'Use config settings' checkbox
                       'manage_stock'            => 0,      // manage stock
                       // 'min_sale_qty'            => 0,   // Minimum Qty Allowed in Shopping Cart
                       // 'max_sale_qty'            => 0,   // Maximum Qty Allowed in Shopping Cart
                       'is_in_stock'             => 1,      // Stock Availability
                       // 'qty'                     => 0,   // qty
                   )
                )

                // set category
                ->setCategoryIds($category_ids)
            ;

            // save product
            $product->save();

            $products_inserted++;

            // if ($products_inserted == 3) break; // uncomment to limit inserted products and check results

        }

        Mage::helper('catalogupdate')->logDebug("
inserted products:    " . $products_inserted . "
existing products:    " . $products_exist . "
products with errors: " . $products_errors . "
",
            $log_statistics,
            true
        );

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex
        Mage::unregister('isSecureArea');                                    // close secured area
    }

    public function importExtractedProducts()
    {
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile, true);

        // get exported products from databse
        $pts_products    = Mage::helper('catalogupdate')->getPTSProducts();
        $insert_products = 0;
        $c = 0;

        foreach ($pts_products as $data)
        {
            $c++;

            $sku   = trim($data['Item_num']);
            $brand = trim($data['Brand']);

            // look for the product in database among products sent by client
            $product = Mage::helper('catalogupdate')->getPTSProductBySKU($sku);

            if ($product) {

                $description       = preg_replace("/^\s*Technical Specifications\s*$/s", "", $data['Description']);
                $description       = Mage::helper('catalogupdate')->clearText($description);

                $name              = Mage::helper('catalogupdate')->clearText($data['Product_Info']);
                $brand             = Mage::helper('catalogupdate')->clearText($data['Brand']);
                $mfg_part          = Mage::helper('catalogupdate')->clearText($data['MFG_Part_num']);
                $erp_vendor_number = Mage::helper('catalogupdate')->clearText($product['ERP_Vendor_Number']);

                // skip invalid product
                if ($this->productHasDataError($name, $description, $brand, $mfg_part, $erp_vendor_number)) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                    continue;

                }

                // save product >>>
                Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

                $insert_products++;

                $weight         = $product['weight'];
                $dist_center    = $data['Dist_Center'];
                $shipping_info  = $data['Shipping_Info'];
                $tech_specs     = $data['Tech_specs'];
                $your_price     = $this->fixPrice($data['My_price']);
                $price          = $this->reducePrice($this->fixPrice($data['List_Price']));
                $discount       = $data['Discount'];
                $per_quantity   = $data['Per_Quantity'];
                $image          = $data['Image'];
                $category_names = $data['Category'];

                // find product
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                // find category >>

                // no category from resource website
                if (empty($category_names)) continue;
/*
                $category_names = explode(',', $category_names);
                $category_name  = end($category_names);
                $category       = $this->getCategoryByName($category_name);

                if (!$category) {
                    $category = $this->createCategory(reset($category_names), $category_names, $this->logfile);
                }
*/
/*
                // remove brand of the begining of the category string
                $category_names_arr = explode(',', $category_names);
                $category_name      = current($category_names_arr);

                if ($category_name == $brand) {
                    array_shift($category_names_arr);
                }
*/
                $category_names_arr = Mage::helper('catalogupdate')->resolveCategories($category_names, $brand);

                // create category if necessary
                $category     = $this->createCategories($category_names_arr);
                $category_id  = $category->getId();
                $category_ids = explode('/', $category->getPath());

                // new product
                if (!$product || !$product->getId()) {
                    // insert product
                    Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);

                    $product = Mage::getModel('catalog/product');

                    $product->setData('sku', $sku);
                    $product->setTaxClassId(0); // none | taxable goods | shipping (0 | 2 | 4)
                    $product->setTypeId('simple');
                    $product->setAttributeSetId($this->attribute_set_id); // default attribute set
                    $product->setVisibility($this->product_visibility);   // sets visibility as 'catalog, search'
                    $product->setStatus($this->product_status);           // enabled

                    // assign product to the default website
                    $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                }
                // existing product
                else {
                    // update product
                    Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);
                }

                $product->setData('name', $name);
                $product->setData('description', $description);
                $product->setData('mfg_part', $mfg_part);
                $product->setData('erp_vendor_number', $erp_vendor_number);
                $product->setData('brand', $brand);
                $product->setData('dist_center', $dist_center);
                $product->setData('shipping_info', $shipping_info);
                $product->setData('tech_specs', $tech_specs);
                $product->setData('your_price', $your_price);
                $product->setData('price', $price);
                // $product->setData('Discount', $data[4]);
                // $product->setData('Per_Quantity', $data[9]);
                $product->setTaxClassId(0);
                $product->setWeight($weight);
                $product->setCategoryIds($category_ids); // set product category

                $product->save(); // save product

                // add new product image
                // $this->addProductImage($product, $image);

                continue;

            }

            Mage::helper('catalogupdate')->logDebug($c . ". product does not exist " . $sku, $this->logfile, true);

            // if ($insert_products) break;
        }

        Mage::helper('catalogupdate')->logDebug("inserted products total: " . $insert_products, $this->logfile, true);

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex
        Mage::unregister('isSecureArea');                                    // close secured area
    }

    /**
     * Imports products exported from PTS tools that
     * not exist in the file sent by client
     */
    public function importFilteredProducts()
    {
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile);

        // get exported products from databse
        $pts_products    = Mage::helper('catalogupdate')->getPTSProducts();
        $insert_products = 0;
        $c = 0;

        foreach ($pts_products as $data)
        {
            $c++;

            $sku   = trim($data['Item_num']);
            $brand = trim($data['Brand']);

            // look for the product in database among products sent by client
            $product = Mage::helper('catalogupdate')->getPTSProductBySKU($sku);

            if (!$product) {

                $name        = Mage::helper('catalogupdate')->clearText($data['Product_Info']);
                $description = Mage::helper('catalogupdate')->clearText($data['Description']);
                $brand       = Mage::helper('catalogupdate')->clearText($data['Brand']);
                $mfg_part    = Mage::helper('catalogupdate')->clearText($data['MFG_Part_num']);

                // skip invalid product
                if ($this->productHasDataError($name, $description, $brand, $mfg_part)) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                    continue;

                }

                // save product >>>
                Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

                $insert_products++;

                $dist_center    = $data['Dist_Center'];
                $shipping_info  = $data['Shipping_Info'];
                $tech_specs     = $data['Tech_specs'];
                $your_price     = $this->fixPrice($data['My_price']);
                $price          = $this->reducePrice($this->fixPrice($data['List_Price']));
                $discount       = $data['Discount'];
                $per_quantity   = $data['Per_Quantity'];
                $image          = $data['Image'];
                $category_names = $data['Category'];

                // find product
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                // find category >>

                // no category from resource website
                if (empty($category_names)) continue;

                $category_names = explode(',', $category_names);
                $category_name  = end($category_names);
                $category       = $this->getCategoryByName($category_name);

                if (!$category) {
                    $category = $this->createCategory(reset($category_names), $category_names, $this->logfile);
                }

                $category_id  = $category->getId();
                $category_ids = explode('/', $category->getPath());

                // new product
                if (!$product || !$product->getId()) {
                    // insert product
                    Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);

                    $product = Mage::getModel('catalog/product');

                    $product->setData('sku', $sku);

                    // $product->setWeight(1.8);
                    // $product->setTaxClassId(0); // none | taxable goods | shipping (0 | 2 | 4)
                    $product->setTypeId('simple');
                    $product->setAttributeSetId($this->attribute_set_id); // default attribute set
                    $product->setVisibility($this->product_visibility);   // sets visibility as 'catalog, search'
                    $product->setStatus($this->product_status);           // enabled

                    // assign product to the default website
                    $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                }
                // existing product
                else {
                    // update product
                    Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);
                }

                $product->setData('name', $name);
                $product->setData('description', $description);
                $product->setData('mfg_part', $mfg_part);
                $product->setData('brand', $brand);
                $product->setData('dist_center', $dist_center);
                $product->setData('shipping_info', $shipping_info);
                $product->setData('tech_specs', $tech_specs);
                $product->setData('your_price', $your_price);
                $product->setData('price', $price);
                // $product->setData('Discount', $data[4]);
                // $product->setData('Per_Quantity', $data[9]);
                $product->setCategoryIds($category_ids); // set product category

                $product->save(); // save product

                // add new product image
                $this->addProductImage($product, $image);

                continue;

            }

            Mage::helper('catalogupdate')->logDebug($c . ". product exists " . $sku, $this->logfile, true);

            // if ($insert_products) break;
        }

        Mage::helper('catalogupdate')->logDebug("inserted products total: " . $insert_products, $this->logfile, true);

        Mage::helper('catalogupdate')->clearCache();                   // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile);     // reindex
        Mage::unregister('isSecureArea');                              // close secured area
    }

    public function importProductImageBySku($sku)
    {
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // get exported product from database
        $data    = Mage::helper('catalogupdate')->getPTSToolsProductBySKU($sku);
        $insert_products = 0;
        $c = 0;

        // foreach ($pts_products as $data)
        // {
            $c++;

            $sku   = trim($data['Item_num']);
            $brand = trim($data['Brand']);

            // look for the product in database among products sent by client
            $product = Mage::helper('catalogupdate')->getPTSProductBySKU($sku);

            if ($product) {

                $description       = preg_replace("/^\s*Technical Specifications\s*$/s", "", $data['Description']);
                $description       = Mage::helper('catalogupdate')->clearText($description);

                $name              = Mage::helper('catalogupdate')->clearText($data['Product_Info']);
                $brand             = Mage::helper('catalogupdate')->clearText($data['Brand']);
                $mfg_part          = Mage::helper('catalogupdate')->clearText($data['MFG_Part_num']);
                $erp_vendor_number = Mage::helper('catalogupdate')->clearText($product['ERP_Vendor_Number']);

                // skip invalid product
                if ($this->productHasDataError($name, $description, $brand, $mfg_part)) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                    return false;

                }

                // save product >>>
                Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

                $insert_products++;

                $weight         = $product['weight'];
                $dist_center    = $data['Dist_Center'];
                $shipping_info  = $data['Shipping_Info'];
                $tech_specs     = $data['Tech_specs'];
                $your_price     = $this->fixPrice($data['My_price']);
                $price          = $this->reducePrice($this->fixPrice($data['List_Price']));
                $discount       = $data['Discount'];
                $per_quantity   = $data['Per_Quantity'];
                $image          = $data['Image'];
                $category_names = $data['Category'];

                // find product
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                // find category >>

                // no category from resource website
                if (empty($category_names)) return false;;

                $category_names_arr = Mage::helper('catalogupdate')->resolveCategories($category_names, $brand);

                // create category if necessary
                $category     = $this->createCategories($category_names_arr);
                $category_id  = $category->getId();
                $category_ids = explode('/', $category->getPath());

                // new product
                if (!$product || !$product->getId()) {
                    // insert product
                    Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);

                    $product = Mage::getModel('catalog/product');

                    $product->setData('sku', $sku);
                    $product->setTaxClassId(0); // none | taxable goods | shipping (0 | 2 | 4)
                    $product->setTypeId('simple');
                    $product->setAttributeSetId($this->attribute_set_id); // default attribute set
                    $product->setVisibility($this->product_visibility);   // sets visibility as 'catalog, search'
                    $product->setStatus($this->product_status);           // enabled

                    // assign product to the default website
                    $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                }
                // existing product
                else {
                    // update product
                    Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);
                }

                $product->setData('name', $name);
                $product->setData('description', $description);
                $product->setData('mfg_part', $mfg_part);
                $product->setData('erp_vendor_number', $erp_vendor_number);
                $product->setData('brand', $brand);
                $product->setData('dist_center', $dist_center);
                $product->setData('shipping_info', $shipping_info);
                $product->setData('tech_specs', $tech_specs);
                $product->setData('your_price', $your_price);
                $product->setData('price', $price);
                // $product->setData('Discount', $data[4]);
                // $product->setData('Per_Quantity', $data[9]);
                $product->setTaxClassId(0);
                $product->setWeight($weight);
                $product->setCategoryIds($category_ids); // set product category

                $product->save(); // save product

                // add new product image
                $this->addProductImage($product, $image);

                return true;
            }

            Mage::helper('catalogupdate')->logDebug($c . ". product does not exist " . $sku, $this->logfile, true);

            // break;
        // }

        Mage::helper('catalogupdate')->logDebug("inserted products total: " . $insert_products, $this->logfile, true);

        Mage::unregister('isSecureArea'); // close secured area
    }

    public function importFilteredProductsImages()
    {
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile);

        // get exported products from databse
        $pts_products    = Mage::helper('catalogupdate')->getPTSProducts();
        $insert_products = 0;
        $c = 0;

        foreach ($pts_products as $data)
        {
            $c++;

            $sku   = trim($data['Item_num']);
            $brand = trim($data['Brand']);

            // look for the product in database among products sent by client
            $product = Mage::helper('catalogupdate')->getPTSProductBySKU($sku);

            if (!$product) {

                $name        = Mage::helper('catalogupdate')->clearText($data['Product_Info']);
                $description = Mage::helper('catalogupdate')->clearText($data['Description']);
                $brand       = Mage::helper('catalogupdate')->clearText($data['Brand']);
                $mfg_part    = Mage::helper('catalogupdate')->clearText($data['MFG_Part_num']);

                // skip invalid product
                if ($this->productHasDataError($name, $description, $brand, $mfg_part)) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                    continue;

                }

                // save product >>>
                Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

                $insert_products++;

                $image          = $data['Image'];
                $category_names = $data['Category'];

                // no category from resource website
                if (empty($category_names)) continue;

                // find product
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                // new product
                if (!$product || !$product->getId()) {
                    // no need for this, all these products exist
                    continue;
                }
                // existing product

                Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);

                // add new product image
                $this->addProductImage($product, $image);

                continue;

            }

            Mage::helper('catalogupdate')->logDebug($c . ". product exists " . $sku, $this->logfile, true);

            // if ($insert_products) break;
        }

        Mage::helper('catalogupdate')->logDebug("inserted products total: " . $insert_products, $this->logfile, true);

        Mage::helper('catalogupdate')->clearCache();                   // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile);     // reindex
        Mage::unregister('isSecureArea');                              // close secured area
    }

    public function importProductsFromCSV($filepath)
    {
        // get content of the csv file
        if (($fp = @fopen($filepath, 'r')) === false) {
            Mage::helper('catalogupdate')->logDebug("File \"$filepath\" does not exist or it is not readable.", $this->logfile, true);
            return false;
        }

        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile, true);

        $c = 0;

        // get product rows
        while (($data = fgetcsv($fp, 0, ",")) !== false)
        {
            $c++;

            $sku         = trim($data[1]); // Item_num
            $brand       = trim($data[4]); // Brand
            $name        = Mage::helper('catalogupdate')->clearText($data[7]); // Product_Info
            $brand       = Mage::helper('catalogupdate')->clearText($data[4]); // Brand
            $mfg_part    = Mage::helper('catalogupdate')->clearText($data[2]); // MFG_Part_num
            $erp_vendor_number = Mage::helper('catalogupdate')->clearText($data[14]); // ERP Vendor Number
            // Description
            $description       = preg_replace("/^\s*Technical Specifications\s*$/s", "", $data[0]);
            $description       = Mage::helper('catalogupdate')->clearText($description);

            // skip invalid product
            if ($this->productHasDataError($name, $description, $brand, $mfg_part)) {

                Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                continue;

            }

            // save product >>>
            Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

            $dist_center    = $data[9]; // Dist_Center
            $shipping_info  = $data[8];  // Shipping_Info
            $tech_specs     = $data[6];  // Tech_specs
            $your_price     = $this->fixPrice($data[11]);                     // My_price
            $price          = $this->reducePrice($this->fixPrice($data[10])); // List_Price
            $discount       = $data[5];  // Discount
            $per_quantity   = $data[12]; // Per_Quantity
            $image          = $data[13]; // Image
            $category_names = $data[3];  // Category

            // find product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

            // find category >>

            // no category from resource website
            if (empty($category_names)) {

                Mage::helper('catalogupdate')->logDebug($c . ". error: missing category", $this->logfile, true);
                continue;

            }
/*
            $category_names = explode(',', $category_names);
            $category_name  = end($category_names);
            $category       = $this->getCategoryByName($category_name);

            if (!$category) {
                $category = $this->createCategory(reset($category_names), $category_names, $this->logfile);
            }

            $category_id  = $category->getId();
            $category_ids = explode('/', $category->getPath());
*/
            $category_names_arr = Mage::helper('catalogupdate')->resolveCategories($category_names, $brand);

            // create category if necessary
            $category     = $this->createCategories($category_names_arr);
            $category_id  = $category->getId();
            $category_ids = explode('/', $category->getPath());

            // new product
            if (!$product || !$product->getId()) {
                // insert product
                Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);

                $product = Mage::getModel('catalog/product');

                $product->setData('sku', $sku);

                // $product->setWeight(1.8);
                $product->setTypeId('simple');
                $product->setTaxClassId(0);                           // none | taxable goods | shipping (0 | 2 | 4)
                $product->setAttributeSetId($this->attribute_set_id); // default attribute set
                $product->setVisibility($this->product_visibility);   // sets visibility as 'catalog, search'
                $product->setStatus($this->product_status);           // enabled

                // assign product to the default website
                $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
            }
            // existing product
            else {
                // update product
                Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);
            }

            $product->setData('name', $name);
            $product->setData('description', $description);
            $product->setData('mfg_part', $mfg_part);
            $product->setData('brand', $brand);
            $product->setData('dist_center', $dist_center);
            $product->setData('shipping_info', $shipping_info);
            $product->setData('tech_specs', $tech_specs);
            $product->setData('your_price', $your_price);
            $product->setData('price', $price);
            // $product->setData('Discount', $data[4]);
            // $product->setData('Per_Quantity', $data[9]);
            $product->setCategoryIds($category_ids); // set product category

            $product->save(); // save product

            // add new product image
            $this->addProductImage($product, $image);

            Mage::helper('catalogupdate')->logDebug($c . ". product does not exist " . $sku, $this->logfile, true);

            // break;
        }

        fclose($fp); // close file

        // backup file
        $path         = str_replace(basename($filepath), '', $filepath);
        $new_filepath = $path . date('Ymdhis') . '-' . basename($filepath);
        copy($filepath, $new_filepath);

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex
        Mage::unregister('isSecureArea');                                    // close secured area
    }

    public function importProductsFromCSVFeed($filepath)
    {
        // get content of the csv file
        if (($fp = @fopen($filepath, 'r')) === false) {
            Mage::helper('catalogupdate')->logDebug("File \"$filepath\" does not exist or it is not readable.", $this->logfile, true);
            return false;
        }

        // open secured area - tells magento that we know what we are doing
        // Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile, true);

        $c = -1;

try {

        // read csv rows
        while (($data = fgetcsv($fp, 0, ",")) !== FALSE)
        {
            $c++;

            // csv file header (skip it)
            if ($c == 0) {
                print_r($data);
                continue;
            }

            $sku = $data[3];

            // csv product values
            print_r($data);

            // get category IDs
            $category     = Mage::getModel('catalog/category')->load($data[7]);
            $category_id  = $category->getId();
            $category_ids = explode('/', $category->getPath());

            // find product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $is_new  = !$product || !$product->getId();

            if ($is_new) {
                echo "new product\n";
                $product = Mage::getModel('catalog/product'); // new product object
            }
            else {
                echo "existing product\n";
            }

            $product
                ->setWebsiteIds($this->website_ids)            // assign product to the default website
                ->setTypeId($this->product_type)               // create only "simple" products
                ->setTaxClassId($this->product_tax_class_id)   // none | taxable goods | shipping (0 | 2 | 4)
                ->setAttributeSetId($this->attribute_set_id)   // default attribute set
                ->setVisibility($this->product_visibility)     // sets visibility as 'catalog, search'
                ->setStatus($this->product_status)             // enabled

                ->setData('sku', $sku)
                ->setData('name', $data[0])
                ->setData('description', $data[1])
                ->setData('short_description', $data[1])
                ->setData('brand', $data[2])
                ->setData('mfg_part', $data[3])
                ->setData('weight', $data[4])
                ->setData('your_price', $data[6])
                ->setData('price', $data[10])
                ->setData('erp_part_number', $data[8])
                ->setData('erp_vendor_number', $data[9])

                // set stock
                ->setStockData(array(
                       'use_config_manage_stock' => 0,      // 'Use config settings' checkbox
                       'manage_stock'            => 0,      // manage stock
                       // 'min_sale_qty'            => 0,   // Minimum Qty Allowed in Shopping Cart
                       // 'max_sale_qty'            => 0,   // Maximum Qty Allowed in Shopping Cart
                       'is_in_stock'             => 1,      // Stock Availability
                       // 'qty'                     => 0,   // qty
                   )
                )

                // set category
                ->setCategoryIds($category_ids)
            ;

            echo "save product\n";

            // save product
            $product->save();

            $this->downloadAndAddProductImage($product, $data[5]);

            // break;
        }

        fclose($fp);

} catch (Exception $e) {
    Mage::helper('catalogupdate')->logDebug($e->getMessage(), 'cu-error.log', true);
}

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex

exit;











        $c = 0;

        // get product rows
        while (($data = fgetcsv($fp, 0, ",")) !== false)
        {
            $c++;

            $sku         = trim($data[1]); // Item_num
            $brand       = trim($data[4]); // Brand
            $name        = Mage::helper('catalogupdate')->clearText($data[7]); // Product_Info
            $brand       = Mage::helper('catalogupdate')->clearText($data[4]); // Brand
            $mfg_part    = Mage::helper('catalogupdate')->clearText($data[2]); // MFG_Part_num
            $erp_vendor_number = Mage::helper('catalogupdate')->clearText($data[14]); // ERP Vendor Number
            // Description
            $description       = preg_replace("/^\s*Technical Specifications\s*$/s", "", $data[0]);
            $description       = Mage::helper('catalogupdate')->clearText($description);

            // skip invalid product
            if ($this->productHasDataError($name, $description, $brand, $mfg_part)) {

                Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                continue;

            }

            // save product >>>
            Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

            $dist_center    = $data[9]; // Dist_Center
            $shipping_info  = $data[8];  // Shipping_Info
            $tech_specs     = $data[6];  // Tech_specs
            $your_price     = $this->fixPrice($data[11]);                     // My_price
            $price          = $this->reducePrice($this->fixPrice($data[10])); // List_Price
            $discount       = $data[5];  // Discount
            $per_quantity   = $data[12]; // Per_Quantity
            $image          = $data[13]; // Image
            $category_names = $data[3];  // Category

            // find product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

            // find category >>

            // no category from resource website
            if (empty($category_names)) {

                Mage::helper('catalogupdate')->logDebug($c . ". error: missing category", $this->logfile, true);
                continue;

            }
/*
            $category_names = explode(',', $category_names);
            $category_name  = end($category_names);
            $category       = $this->getCategoryByName($category_name);

            if (!$category) {
                $category = $this->createCategory(reset($category_names), $category_names, $this->logfile);
            }

            $category_id  = $category->getId();
            $category_ids = explode('/', $category->getPath());
*/
            $category_names_arr = Mage::helper('catalogupdate')->resolveCategories($category_names, $brand);

            // create category if necessary
            $category     = $this->createCategories($category_names_arr);
            $category_id  = $category->getId();
            $category_ids = explode('/', $category->getPath());

            // new product
            if (!$product || !$product->getId()) {
                // insert product
                Mage::helper('catalogupdate')->logDebug($c . ". product [insert] " . $sku, $this->logfile, true);

                $product = Mage::getModel('catalog/product');

                $product->setData('sku', $sku);

                // $product->setWeight(1.8);
                $product->setTypeId('simple');
                $product->setTaxClassId(0);                           // none | taxable goods | shipping (0 | 2 | 4)
                $product->setAttributeSetId($this->attribute_set_id); // default attribute set
                $product->setVisibility($this->product_visibility);   // sets visibility as 'catalog, search'
                $product->setStatus($this->product_status);           // enabled

                // assign product to the default website
                $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
            }
            // existing product
            else {
                // update product
                Mage::helper('catalogupdate')->logDebug($c . ". product [update] " . $sku, $this->logfile, true);
            }

            $product->setData('name', $name);
            $product->setData('description', $description);
            $product->setData('mfg_part', $mfg_part);
            $product->setData('brand', $brand);
            $product->setData('dist_center', $dist_center);
            $product->setData('shipping_info', $shipping_info);
            $product->setData('tech_specs', $tech_specs);
            $product->setData('your_price', $your_price);
            $product->setData('price', $price);
            // $product->setData('Discount', $data[4]);
            // $product->setData('Per_Quantity', $data[9]);
            $product->setCategoryIds($category_ids); // set product category

            $product->save(); // save product

            // add new product image
            $this->addProductImage($product, $image);

            Mage::helper('catalogupdate')->logDebug($c . ". product does not exist " . $sku, $this->logfile, true);

            // break;
        }

        fclose($fp); // close file

        // backup file
        $path         = str_replace(basename($filepath), '', $filepath);
        $new_filepath = $path . date('Ymdhis') . '-' . basename($filepath);
        copy($filepath, $new_filepath);

        Mage::helper('catalogupdate')->clearCache($this->logfile, true);     // clear cache
        Mage::helper('catalogupdate')->enableIndexing($this->logfile, true); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($this->logfile, true);     // reindex
        Mage::unregister('isSecureArea');                                    // close secured area
    }

    /**
     * Deletes all products
     * Optionaly, it skips products added manually
     */
    public function deleteAllProducts($delete_manual = false)
    {
        // open secured area - tells magento that we know what we are doing
        Mage::register('isSecureArea', true);

        // disable indexing to speed up
        Mage::helper('catalogupdate')->disableIndexing($this->logfile);

        // time when products have been added manually
        $time = "2013-08-18 00:00:00";

        // get products
        $products = Mage::getModel('catalog/product')->getCollection();

        // exclude manually added products
        if (!$delete_manual) {

            $products->addAttributeToFilter('created_at', array('lt' => $time));

        }

        foreach ($products as $product)
        {
            try {

                // delete product
                $product->delete();
                Mage::helper('catalogupdate')->logDebug("delete product ID: " . $product->getId(), $this->logfile, true);

            } catch(Exception $e) {

                Mage::helper('catalogupdate')->logDebug("error: can not be deleted product ID: " . $product->getId(), $this->logfile, true);
                Mage::helper('catalogupdate')->logDebug("error: : " . $e->getMessage(), $this->logfile, true);

            }

            // break;
        }

        Mage::helper('catalogupdate')->clearCache();             // clear cache
        Mage::helper('catalogupdate')->enableIndexing($logfile); // enable indexing
        Mage::helper('catalogupdate')->reindexAll($logfile);     // reindex
        Mage::unregister('isSecureArea');                        // close secured area
    }

    /**
     * Deletes product images with zero size
     */
    public function deleteProductZeroSizeImages()
    {
        $exit = false;
        $p = 0;
        $products = Mage::helper('catalogupdate')->getAllProducts();

        foreach ($products as $product)
        {
            $p++;
            // $product = Mage::getModel('catalog/product')->load($product->getId());

            // get existing images
            $images = $this->media_api->items($product->getId());

            foreach($images as $image)
            {
                $existing_image      = $image['file'];
                $existing_image_path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $existing_image;
                $existing_image_size = filesize($existing_image_path);

                // delete image if its size is equals 0
                if (!$existing_image_size) {
                    $this->media_api->remove($product->getId(), $existing_image);
                    $product->save();

                    Mage::helper('catalogupdate')->logDebug($p . ". " . $product->getData('sku') . " product image " . $existing_image . " deleted", 'catalogupdate-deleted-images.log', true);

                    $exit = true;
                }
            }

            // if ($exit) break;
        }
    }

    /**
     * Returns category selected by name
     */
    public function getCategoryByName($name)
    {
        $category = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', $name)
            ->getFirstItem();

        if (!$category || !$category->getId()) {
            return null;
        }

        return $category;
    }

    /**
     * Creates categories
     */
    public function createCategories($category_names, $logfile = false)
    {
        $categories = $category_names;
        $last       = end($categories);
        $parent     = $this->root_category;
        $category   = null;
        $logfile    = !$logfile ? $this->logfile : $logfile;

        foreach ($category_names as $c => $category_name)
        {
            $category_name  = trim($category_name);

            // skip empty values
            if ($category_name == '') continue;

            $category = Mage::helper('catalogupdate')->getCategoryByNameAndParentId($category_name, $parent->getId());

            if (!$category) {
                // echo "create category \"$category_name\"\n";
                Mage::helper('catalogupdate')->logDebug("create category \"" . $category_name . "\"", $logfile, true);

                $category = new Mage_Catalog_Model_Category();
                $category->setName($category_name);
                $category->setIsActive(1);
                $category->setDisplayMode('PRODUCTS');
                $category->setIsAnchor(1);
                $category->setPath($parent->getPath());

                if ($last != $category_name) {
                    $category->setLandingPage(16);
                }

                $category->save();
            }

            $parent = $category;
        }

        return $category;
    }

    /**
     * Creates categories
     */
    public function createCategoryBKP($name, $category_names, $logfile)
    {
        $cats          = $category_names;
        $last          = end($cats);
        $parent        = $this->root_category;
        $last_category = null;

        foreach ($category_names as $c => $category_name)
        {
            if ($c > 0) {
                $parent_name = $cats[$c-1];
                $parent      = $this->getCategoryByName($parent_name);
            }

            $category = $this->getCategoryByName($category_name);

            if (!$category) {

                $category = new Mage_Catalog_Model_Category();
                $category->setName($category_name);
//                $category->setIsActive(1);
                $category->setIsActive(0);
                $category->setDisplayMode('PRODUCTS');
                $category->setIsAnchor(1);
                $category->setPath($parent->getPath());

                if ($last != $category_name) {
                    $category->setLandingPage(16);
                }

                $category->save(); // save category

                if ($last == $category_name) {
                    $last_category = $category;
                }

                echo '> created category "' . $category_name . '"' . "\n";
                if ($this->debug) Mage::log('> created category "' . $category_name . '" [' . $category->getId() . ']', null, $logfile);
            }
        }

        return $last_category;
    }

    /**
     * Compares existing and new image sizes, removes existing image and adds new one
     */
    public function addProductImage($product, $new_image)
    {
        $add_image      = true;
        $new_image_path = $this->images_path . DS . $new_image;
        $new_image_size = filesize($new_image_path);

        if (empty($new_image) || !file_exists($new_image_path) || $new_image_size == 0) {

            Mage::helper('catalogupdate')->logDebug("invalid image " . basename($new_image), $this->logfile, true);
            return false;

        }

        // load product
        $product = Mage::getModel('catalog/product')->load($product->getId());

        // get existing images
        $images = $this->media_api->items($product->getId());

        foreach($images as $image)
        {
            $existing_image      = $image['file'];
            $existing_image_path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $existing_image;
            $existing_image_size = filesize($existing_image_path);

            // don't update image if existing and the new one are the same
            if ($new_image_size == $existing_image_size) {
                $add_image = false;
                continue;
            }

            // remove existing image if it's different then the new one
            $this->media_api->remove($product->getId(), $existing_image);

            // remove existing image
            @unlink($existing_image_path);

            $product->save();
        }

        if (!$add_image) return false;

        // reload product
        $product = Mage::getModel('catalog/product')->load($product->getId());

        // add image to product gallery
        $product->addImageToMediaGallery($new_image_path, array('image', 'small_image', 'thumbnail'), false, false);

        // remove tmp image
        // @unlink($new_image_path);

        // save product
        $product->save();

        return true;
    }

    public function downloadAndAddProductImage($product, $new_image = '')
    {
        if ($new_image == '') return false;

        // load product
        $product = Mage::getModel('catalog/product')->load($product->getId());

        // get existing images
        $this->deleteProductImages($product);

        // reload product
        $product = Mage::getModel('catalog/product')->load($product->getId());

        $new_image_path = Mage::getBaseDir() . DS . 'media' . DS . $product->getData('sku') . '.jpg';

        // echo "image " . $new_image_path . "\n";

        $curl = curl_init($new_image);
        $fp   = fopen($new_image_path, 'wb');
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($curl);
        curl_close($curl);
        fclose($fp);

        // add image to product gallery
        try {
            $product->addImageToMediaGallery($new_image_path, array('image', 'small_image', 'thumbnail'), false, false);
        } catch (Exception $e) {
            Mage::helper('catalogupdate')->logDebug("invalid image " . basename($new_image), $this->logfile, true);
        }

        // remove tmp image
        @unlink($new_image_path);

        // save product
        $product->save();

        return true;
    }

    /**
     * Removes product images from database and filesystem
     */
    public function deleteProductImages($product)
    {
        // get existing images
        $images = $this->media_api->items($product->getId());

        // remove existing image if it's different then the new one
        foreach($images as $image)
        {
            $this->media_api->remove($product->getId(), $image['file']);
            $product->save();

            // remove old image
            @unlink(Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $image['file']);
        }
    }

    /**
     * Check product data
     */
    public function productHasDataError($name, $desc, $brand, $mfg_part, $erp_vendor_number, $erp_part_number, $weight)
    {
        $error = false;

        if ( 
            ($name == '' && $desc == '') || 
            $brand == '' || $mfg_part == '' || 
            $erp_vendor_number == '' || $erp_part_number == '' || 
            ($weight == '' || $weight == '0')
        ) {

            $error = true;

            if ($name == '')                        Mage::helper('catalogupdate')->logDebug("error: missing name", $this->logfile, true);
            if ($desc == '')                        Mage::helper('catalogupdate')->logDebug("error: missing description", $this->logfile, true);
            if ($brand == '')                       Mage::helper('catalogupdate')->logDebug("error: missing manufacturer", $this->logfile, true);
            if ($mfg_part == '')                    Mage::helper('catalogupdate')->logDebug("error: missing MFG part number", $this->logfile, true);
            if ($erp_vendor_number == '')           Mage::helper('catalogupdate')->logDebug("error: missing ERP vendor number", $this->logfile, true);
            if ($erp_part_number == '')             Mage::helper('catalogupdate')->logDebug("error: missing ERP part number", $this->logfile, true);
            if ($weight == '' || $weight == '0')    Mage::helper('catalogupdate')->logDebug("error: missing weight", $this->logfile, true);

        }

        return $error;
    }

    /**
     * Check product 3M data
     */
    public function product3MHasDataError($name, $desc, $mfg_part, $erp_vendor_number, $erp_part_number, $category_names)
    {
        $error = false;

        if ( 
            ($name == '' && $desc == '') || $mfg_part == '' || 
            $erp_vendor_number == '' || $erp_part_number == '' || 
            $category_names == ''
        ) {

            $error = true;

            if ($name == '')                Mage::helper('catalogupdate')->logDebug("error: missing name", $this->logfile, true);
            if ($desc == '')                Mage::helper('catalogupdate')->logDebug("error: missing description", $this->logfile, true);
            if ($mfg_part == '')            Mage::helper('catalogupdate')->logDebug("error: missing MFG part number", $this->logfile, true);
            if ($erp_vendor_number == '')   Mage::helper('catalogupdate')->logDebug("error: missing ERP vendor number", $this->logfile, true);
            if ($erp_part_number == '')     Mage::helper('catalogupdate')->logDebug("error: missing ERP part number", $this->logfile, true);
            if ($category_names == '')      Mage::helper('catalogupdate')->logDebug("error: missing categories", $this->logfile, true);

        }

        return $error;
    }

    /**
     * Fixes price
     */
    public function fixPrice($price)
    {
        $price = ltrim($price, '$');

        // convert .25 to 0.25
        if (preg_match('/^\.(\d+)/', $price, $match)) {
            // if ($this->debug) Mage::log("weird PRICE: " . $price, null, 'ptstools-invalid.log');
            return (float) '0' . $price;
        }

        // clear commas
        $price = (float) str_replace(',', '', $price);

        return $price;
    }

    /**
     * Reduce price by the setting
     */
    public function reducePrice($price)
    {
        if ($this->reduce_list_price > 0) {
            return round($price - ($price * $this->reduce_list_price / 100), 2);
        }

        return $price;
    }
}
