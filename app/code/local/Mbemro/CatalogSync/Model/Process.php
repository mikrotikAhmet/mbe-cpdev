<?php
class Mbemro_CatalogSync_Model_Process
{
    public
        // general
        $is_enabled,
        $core_resource,
        $dbread,
        $dbwrite,
        $media_api,
        $images_path,
        $process_id,
        $root_category,
        $root_category_id,
        $attribute_set_id,
        $reduce_list_price,
        $debug,
        $debug_product_limit,
        $logfile_sync,
        $logfile_stats,

        // report email
        $email_subject,
        $email_sender,
        $email_recipients,

        // statistics
        $stats = array(
            'new'                  => 0,
            'old'                  => 0,
            'total'                => 0,
            'deleted'              => 0,
            'categories'           => array(),
            'categories_per'       => 0,
            'changed_category'     => 0,
            'changed_category_per' => 0,
            'new_categories'       => array(),
            'no_category'          => 0,
            'invalid_sku'          => 0,
            'invalid_name'         => 0,
            'invalid_desc'         => 0,
            'invalid_price'        => 0,
            'product_errors'       => 0,
            'product_errors_per'   => 0,
            'total_errors'         => 0,
        ),
        $last_stats,
        $start_datetime,
        $manual_products_time = "2013-08-18 00:00:00",
        $category_total       = 1250,

        // validation
        $sku_length  = 50,
        $name_length = 255,
        $desc_length = 2000,
        $price_min   = 0,
        $price_max   = 1000000,
        $product_error_limit         = 0.5,
        $category_total_error_limit  = 95,
        $category_change_error_limit = 2
    ;

    public function __construct()
    {
        // get general options
        $general                 = 'catalogsync/catalogsync_general_options/';
        $this->is_enabled        = Mage::getStoreConfig($general . 'is_enabled');
        $this->debug             = Mage::getStoreConfig($general . 'debug');
        $this->attribute_set_id  = Mage::getStoreConfig($general . 'attribute_set');
        $this->root_category_id  = Mage::getStoreConfig($general . 'root_category');
        $this->reduce_list_price = Mage::getStoreConfig($general . 'reduce_list_price');

        $datetime            = date('Ymdhis');
        $this->logfile_sync  = 'ptstools' . DS . $datetime . '-ptstools-sync.log';
        $this->logfile_stats = 'ptstools' . DS . $datetime . '-ptstools-stats.log';

        // get email options
        $email                  = 'catalogsync/catalogsync_email_options/';
        $this->email_subject    = Mage::getStoreConfig($email . 'subject');
        $this->email_sender     = Mage::getStoreConfig($email . 'sender');
        $this->email_recipients = explode(',', Mage::getStoreConfig($email . 'recipients'));

        // requirements
        $this->root_category = Mage::getModel('catalog/category')->load($this->root_category_id);
        $this->core_resource = Mage::getSingleton('core/resource');
        $this->dbread        = $this->core_resource->getConnection('core_read');      // read database connection
        $this->dbwrite       = $this->core_resource->getConnection('core_write');     // write database connection
        $this->media_api     = Mage::getModel("catalog/product_attribute_media_api"); // media API
        $this->images_path   = $new_image_path = Mage::getBaseDir() . DS . 'shell' . DS . 'ptstools' . DS . 'image';
    }

    /**
     * Executes the whole synchronization process
     */
    public function run()
    {
        if (!$this->is_enabled) {
            echo "> catalog synchronization is not enabled \n";
            echo "> aborting the script ... \n";
            echo "> bye! \n";

            return false;
        }

        echo "> start catalog synchronization \n";

        // starts a new sync process
        $this->startSync();

        // there's incompleted process, abort the script
        if (!$this->process_id) {
            echo "> there is an incompleted process \n";
            echo "> aborting the script ... \n";
            echo "> bye! \n";

            return false;
        }

        // disable indexing to speed up
        $this->disableIndexing($this->logfile_sync);

        // get products for insert/update
        $products = $this->getProducts();

        // get products for deletion
        $delete_products = $this->getProductsForDeletion();

        // check products and collect statistics
        echo '-----------------------------' . " \n";
        echo '> start collecting statistics ID [' . $this->process_id . ']' . " \n";
        if ($this->debug) Mage::log("> start collecting statistics [PROCESS ID {$this->process_id}", null, $this->logfile_stats);

        $row = 0;

        foreach($products as $data)
        {
            $row++;
            $p = $row - 1;
            $product_error = false;

            $sku            = $data['Item_num'];
            $name           = $data['Product_Info'];
            $description    = $data['Description'];
            $mfg_part       = $data['MFG_Part_num'];
            $brand          = $data['Brand'];
            $dist_center    = $data['Dist_Center'];
            $shipping_info  = $data['Shipping_Info'];
            $tech_specs     = $data['Tech_specs'];
            $your_price     = $this->fixPrice($data['My_price']);
            $price          = $this->reducePrice($this->fixPrice($data['List_Price']));
            $discount       = $data['Discount'];
            $per_quantity   = $data['Per_Quantity'];
            $image          = $data['Image'];
            $category_names = $data['Category'];

            echo "$p. $sku \n";

            // find product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

            // find category
            $category_names = explode(',', $category_names);
            $category_name  = end($category_names);
            $category       = $this->getCategoryByName($category_name);
            $category_id    = null;
            $category_ids   = array();

            // no category from resource website
            if (empty($category_name)) {
                $product_error = true;
                $this->stats['no_category']++;
                $this->stats['total_errors']++;        
            } else {
                // has category
                if ($category) {
                    $category_id  = $category->getId();
                    $category_ids = explode('/', $category->getPath());
                    $this->stats['categories'][$category_id] = $category_name;
                } else {
                    // new category
                    $this->stats['new_categories'][$category_name] = $category_name;
                }
            }

            // new product
            if (!$product || !$product->getId()) {
                $this->stats['new']++;
            }
            // existing product
            else {
                $this->stats['old']++;

                // check category change
                $product_category_ids = $product->getCategoryIds();
                $primary_category_id  = end($product_category_ids);

                if ($category_id != $primary_category_id) {
                    $this->stats['changed_category']++;
                }
            }

            // product attributes validation
            $product_error = $this->getProductValidationResult($sku, $name, $description, $price);

            // increment product error count
            if ($product_error === true) $this->stats['product_errors']++;

            // break the loop to debug
            if ($this->debug && $this->debug_product_limit > 0 && $this->debug_product_limit == $p) break;
        }

        // get validation result
        $is_valid = $this->getValidationResult();

        // log statistics
        if ($this->debug) Mage::log('> statistics' . print_r($this->stats, true), null, $this->logfile_stats);

        // end proccess if it's not valid
        if (!$is_valid) {

            if ($this->debug) Mage::log('> ended due to errors', null, $this->logfile_sync);

            // end unsuccessful sync proccess
            $this->endSync($this->logfile_sync, true);

            exit;
        }
        exit;

        // start product insert/update
        echo '-----------------------------' . " \n";
        echo '> start product insert/update' . " \n";
        if ($this->debug) Mage::log('> start product insert/update', null, $this->logfile_sync);

        $row = 0;

        foreach($products as $data)
        {
            $row++;
            $p = $row - 1;

            $sku            = $data['Item_num'];
            $name           = $data['Product_Info'];
            $description    = $data['Description'];
            $mfg_part       = $data['MFG_Part_num'];
            $brand          = $data['Brand'];
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
                $category = $this->createCategory(reset($category_names), $category_names, $this->logfile_sync);
            }

            $category_id  = $category->getId();
            $category_ids = explode('/', $category->getPath());

            // new product
            if (!$product || !$product->getId()) {
                // insert product
                echo "$p. $sku [insert] \n";
                if ($this->debug) Mage::log("$p. product $sku [insert]", null, $this->logfile_sync);

                $product = Mage::getModel('catalog/product');

                $product->setData('sku', $sku);

                $product->setTypeId('simple');
                $product->setAttributeSetId($this->attribute_set_id);
                // $product->setWeight(1.8);
                // $product->setTaxClassId(2); // taxable goods
                $product->setVisibility(4);    // sets visibility as 'catalog, search'
                $product->setStatus(1);        // enabled

                // assign product to the default website
                $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
            }
            // existing product
            else {
                // update product
                echo "$p. $sku [update] \n";
                if ($this->debug) Mage::log("$p. $sku [update]", null, $this->logfile_sync);
            }

            // product attributes validation
            $product_error = $this->getProductValidationResult($sku, $name, $description, $price);

            // skip product insert/update if it's invalid
            if ($product_error === true) continue;

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
            // $product->setData('is_synced', true);    // save as synchronized
            $product->setCategoryIds($category_ids); // set product category

            $product->save(); // save product

            // add new product image
            $this->addProductImage($product, $image);
        }

        echo '-----------------------------' . " \n";
        echo '> delete products: ' . $this->stats['deleted'] . " \n";
        if ($this->debug) Mage::log('> delete products: ' . $this->stats['deleted'], null, $this->logfile_sync);

        foreach ($delete_products as $delete_product)
        {
            // find product
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $delete_product['sku']);

            if ($product && $product->getId()) {
                $product->delete(); // delete from magento catalog
            }

            // delete from extraction table
            $this->updateDeletedProduct($delete_product['sku']);
        }

        // end successful sync proccess
        $this->endSync($this->logfile_sync);

        echo "> end catalog synchronization \n";
        echo "> bye! \n";

        return true;
    }

    /**
     * Check if there's incompleted sync process and,
     * if not, creates a new one
     */
    public function startSync()
    {
        // script start time
        $this->start_datetime = date('Y-m-d h:i:s');

        // check if last process is still working
        $process = $this->dbread->fetchRow("SELECT * FROM pts_sync WHERE status = 'STARTED';");

        // incomplete process
        if ($process) return false;

        // get last process data
        $this->getLastSync();

        // save proccess in database
        $this->dbwrite->exec("INSERT INTO pts_sync (status, created_at, updated_at) VALUES ('STARTED', NOW(), NOW());");

        // get proccess ID
        $this->process_id = $this->dbread->lastInsertId();

        return $this->process_id;
    }

    /**
     * Updates process record with status and statistics
     */
    public function updateSync($invalid = false)
    {
        $status = $invalid ? 'ERROR' : 'SUCCESS';

        $stats = $this->stats;
        $stats['categories']         = count($this->stats['categories']);
        $stats['new_categories']     = count($this->stats['new_categories']);
        $stats['categories_raw']     = json_encode(array_values($this->stats['categories']));
        $stats['new_categories_raw'] = json_encode(array_values($this->stats['new_categories']));

        $query = "UPDATE pts_sync SET status = '{$status}', ";

        foreach ($stats as $column => $value)
        {
            $query.= "$column = ?, ";
        }

        $query.= "updated_at = NOW() ";
        $query.= "WHERE id = {$this->process_id};";

        $this->dbwrite->query($query, array_values($stats));
    }

    /**
     * Ends sync proccess by saving it in database,
     * sending email report and enabling/disabling/running indexing
     */
    public function endSync($logfile, $invalid = false)
    {
        // send report email
        $this->sendReportEmail($invalid);

        // update process in database
        $this->updateSync($invalid);

        if ($invalid) {
            // enable indexing
            $this->enableIndexing($logfile);

            return false;
        }

        // clear magento cache
        $this->clearCache();

        // enable indexing and reindex
        $this->enableIndexing($logfile);
        $this->reindexAll($logfile);
    }

    /**
     * Selects last process data from database
     */
    public function getLastSync()
    {
        // selects last process
        $process = $this->dbread->fetchRow("SELECT * FROM pts_sync ORDER BY created_at DESC;");

        if (!$process) return false;

        $this->last_stats = $process;
    }

    /**
     * Selects products for insert/update from database
     */
    public function getProducts()
    {
        // get products
        $products = $this->dbread->fetchAll("SELECT * FROM pts_tools;");

        // set total of products for insert and update
        $this->stats['total'] = count($products);

        return $products;
    }

    /**
     * Selects products for deletion from database
     */
    public function getProductsForDeletion()
    {
        // get products
        $products = $this->dbread->fetchAll("SELECT * FROM pts_del WHERE Status = 'NEW';");

        // set total of products for delete
        $this->stats['deleted'] = count($products);

        return $products;
    }

    /**
     * Select product from database by SKU
     */
    public function findProductBySKU($sku = '')
    {
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('sku', $sku);

        return $products;
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
    public function createCategory($name, $category_names, $logfile)
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
                $category->setIsActive(1);
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
     * Returns manually added products
     */
    public function getManuallyAddedProducts()
    {
        // $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        // $collection = Mage::getModel('catalog/product')
        //     ->getCollection()
        //     ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
        //     ->addAttributeToFilter('news_to_date', array(‘or’=> array(
        //         0 => array('date' => true, 'from' => $todayDate),
        //         1 => array('is' => new Zend_Db_Expr('null')))
        //     ), 'left')
        //     ->addAttributeToSort('news_from_date', 'desc')
        //     ->addAttributeToSort('created_at', 'desc');

        // get non-synchronized (not in csv) products
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('created_at', 'desc')
            ->addAttributeToFilter('created_at', array('gteq' => $this->manual_products_time));

        return $products;
    }

    /**
     * Compares existing and new image sizes, removes existing image and adds new one
     */
    public function addProductImage($product, $new_image)
    {
        $add_image      = true;
        $new_image_path = $this->images_path . DS . $new_image;
        $new_image_size = filesize($new_image_path);

        if (empty($new_image) || !file_exists($new_image_path)) return false;

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

    /**
     * Updates deleted product status in info table
     */
    public function updateDeletedProduct($sku)
    {
        $this->dbwrite->exec("UPDATE pts_del SET Status = 'DELETE' WHERE Item_num = '{$sku}';");
    }

    /**
     * Returns product validation result
     */
    public function getProductValidationResult($sku, $name, $desc, $price)
    {
        $error = false;

        // sku length and sku contains HTML
        if (strlen($sku) < 2 || strlen($sku) > $this->sku_length || ($sku != strip_tags($sku))) {
            $error = true;
            $this->stats['invalid_sku']++;
            $this->stats['total_errors']++;
        }

        // name length and name contains HTML
        if (strlen($name) < 2 || strlen($name) > $this->name_length || ($name != strip_tags($name))) {
            $error = true;
            $this->stats['invalid_name']++;
            $this->stats['total_errors']++;

            // if ($this->debug) Mage::log("invalid NAME: " . $name, null, 'ptstools-invalid.log');
        }

        // desc length
        if (empty($desc) || strlen($desc) > $this->desc_length) {
            $error = true;
            $this->stats['invalid_desc']++;
            $this->stats['total_errors']++;
        }

        // price range
        if (empty($price) || !is_numeric($price) || $price < $this->price_min || $price > $this->price_max) {
            $error = true;
            $this->stats['invalid_price']++;
            $this->stats['total_errors']++;

            // if ($this->debug) Mage::log("invalid PRICE: " . $price, null, 'ptstools-invalid.log');
        }

        return $error;
    }

    /**
     * Returns global validation result
     */
    public function getValidationResult()
    {
        // percentage of total products with error/s
        $this->stats['product_errors_per'] = round( ($this->stats['product_errors'] * 100 / $this->stats['total']) , 2);

        // percentage of total categories collected
        $this->stats['categories_per'] = round( (count($this->stats['categories']) * 100 / $this->category_total), 2);

        // percentage of total products that changed their categories
        $this->stats['changed_category_per'] = round( ($this->stats['changed_category'] * 100 / $this->stats['old']), 2);

        // percentage of total invalid description errors
        $invalid_desc_per = round( ($this->stats['invalid_desc'] * 100 / $this->last_stats['invalid_desc']), 2);

        // invalid if more than 0.5% of products have errors
        if ($this->stats['product_errors_per'] > $this->product_error_limit) return false;

        // invalid if collected less than 95% of categories compares to initial category total
        if ($this->stats['categories_per'] < $this->category_total_error_limit) return false;

        // invalid if more than 2% changed category
        if ($this->stats['changed_category_per'] > $this->category_change_error_limit) return false;

        // invalid if there's more than 5% of description errors
        // if ($invalid_desc_per > 100 && ($invalid_desc_per - 100 > 5) ) return false;
        if ($invalid_desc_per > 105) return false;

        return true;
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

    /**
     * Sends email report with errors
     */
    public function sendReportEmail($error = false)
    {
        $subject = sprintf($this->email_subject, ($error ? 'Error' : 'Success') );

        $mail = new Zend_Mail();
        $mail->setType(Zend_Mime::MULTIPART_RELATED);
        $mail->setFrom($this->email_sender);
        foreach ($this->email_recipients as $email_recipient)
        {
            $mail->addTo($email_recipient);
        }
        $mail->setSubject($subject);
        // $mail->setBodyText($this->getReportEmailBody());
        $mail->setBodyHtml($this->getReportEmailBody());

        if ($mail->send()) return true;

        return false;
    }

    /**
     * Creates report email body
     */
    public function getReportEmailBody()
    {
        $body = "<p>Execution time: " . $this->start_datetime . "</p>\n";

        $body.= "<p>Statistics</p>\n";

        $body.= "<table border=\"1\">\n";
        $body.= "    <thead>\n";
        $body.= "        <tr>\n";
        $body.= "            <th>New Products</th>\n";
        $body.= "            <th>Old Products</th>\n";
        $body.= "            <th>Total Products</th>\n";
        $body.= "            <th>Deleted Products</th>\n";
        // $body.= "            <th>Manualy Added Products</th>\n";
        $body.= "            <th>New Categories</th>\n";
        $body.= "            <th>Collected Categories</th>\n";
        $body.= "        </tr>\n";
        $body.= "    </thead>\n";
        $body.= "    <tbody>\n";
        $body.= "        <tr>\n";
        $body.= "            <td>" . $this->stats['new'] . "</td>\n";
        $body.= "            <td>" . $this->stats['old'] . "</td>\n";
        $body.= "            <td>" . $this->stats['total'] . "</td>\n";
        $body.= "            <td>" . $this->stats['deleted'] . "</td>\n";
        // $body.= "            <td>" . $this->stats['manual'] . "</td>\n";
        $body.= "            <td>" . count($this->stats['new_categories']) . "</td>\n";
        $body.= "            <td>" . count($this->stats['categories']) . " (" . $this->stats['categories_per'] . "%)</td>\n";
        $body.= "        </tr>\n";
        $body.= "    </tbody>\n";
        $body.= "</table>\n";

        $body.= "<p>Errors</p>\n";

        $body.= "<table border=\"1\">\n";
        $body.= "    <thead>\n";
        $body.= "        <tr>\n";
        $body.= "            <th>No Category</th>\n";
        $body.= "            <th>Changed Category</th>\n";
        $body.= "            <th>Invalid Price</th>\n";
        $body.= "            <th>Invalid SKU</th>\n";
        $body.= "            <th>Invalid Title</th>\n";
        $body.= "            <th>Invalid Desc.</th>\n";
        $body.= "            <th>Total Products With Errors</th>\n";
        $body.= "            <th>Total Errors</th>\n";
        $body.= "        </tr>\n";
        $body.= "    </thead>\n";
        $body.= "    <tbody>\n";
        $body.= "        <tr>\n";
        $body.= "            <td>" . $this->stats['no_category'] . "</td>\n";
        $body.= "            <td>" . $this->stats['changed_category'] . " (" . $this->stats['changed_category_per'] . "%)</td>\n";
        $body.= "            <td>" . $this->stats['invalid_price'] . "</td>\n";
        $body.= "            <td>" . $this->stats['invalid_sku'] . "</td>\n";
        $body.= "            <td>" . $this->stats['invalid_name'] . "</td>\n";
        $body.= "            <td>" . $this->stats['invalid_desc'] . "</td>\n";
        $body.= "            <td>" . $this->stats['product_errors'] . " (" . $this->stats['product_errors_per'] . "%)</td>\n";
        $body.= "            <td>" . $this->stats['total_errors'] . "</td>\n";
        $body.= "        </tr>\n";
        $body.= "    </tbody>\n";
        $body.= "</table>\n";

        return $body;
    }

    /**
     * Disable indexing to speed up
     */
    public function disableIndexing($log_file = '_indexer.log')
    {
        if ($this->debug) Mage::log('> disabling indexes', null, $log_file);

        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        
        foreach($indexingProcesses as $process)
        {
            if ($this->debug) Mage::log($process->getIndexer()->getName().' - disabled', null, $log_file);
            $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        }

        if ($this->debug) Mage::log('> disabling indexes done', null, $log_file);
    }

    /**
     * Set all indexes to update on save.
     */
    public function enableIndexing($log_file = '_indexer.log')
    {
        if ($this->debug) Mage::log('> enabling indexes', null, $log_file);
        
        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        
        foreach($indexingProcesses as $process)
        {
            if ($this->debug) Mage::log($process->getIndexer()->getName().' - enabled', null, $log_file);
            $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
        }

        if ($this->debug) Mage::log('> enabling indexes done', null, $log_file);
    }

    /**
     * Reindex everything.
     */
    public function reindexAll($log_file = '_indexer.log')
    {   
        if ($this->debug) Mage::log('> indexing data', null, $log_file);
        
        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        
        foreach($indexingProcesses as $process)
        {
            $process->reindexEverything();
            if ($this->debug) Mage::log($process->getIndexer()->getName().' - Done!', null, $log_file);
        }
        
        if ($this->debug) Mage::log('> indexing data done', null, $log_file);
    }

    /**
     * Clears Magento Cache
     */
    public function clearCache()
    {
        echo '> clear cache' . "\n";
        
        $mage        = Mage::app();
        $cache_types = $mage->useCache();

        foreach($cache_types as $cache_type => $cache)
        {
            $mage->getCacheInstance()->cleanType($cache_type);
        }
    }
}
