<?php
class Cpdev_CatalogUpdate_Model_Export
{
    public
        $filename,
        $filepath,
        $is_enabled,                    // is module enabled
        $attribute_set_id,              // attribute set
        $root_category_id,              // top category
        $product_type       = 'simple', // simple product type
        $product_visibility = 4,        // product visibility as 'catalog, search'
        $product_status     = 1,
        $logfile,
        $date;

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

        // log file path
        $this->date    = date('Ymdhis');
        $this->logfile = $this->date . '-catalogupdate-export.log';

        // requirements
        $this->root_category = Mage::getModel('catalog/category')->load($this->root_category_id);
        $this->core_resource = Mage::getSingleton('core/resource');
        $this->dbread        = $this->core_resource->getConnection('core_read');      // read database connection
        $this->dbwrite       = $this->core_resource->getConnection('core_write');     // write database connection
        $this->media_api     = Mage::getModel("catalog/product_attribute_media_api"); // media API
        $this->images_path   = Mage::getBaseDir() . DS . 'media';
    }

    public function exportExtractedProducts($dest_filepath)
    {
        // get exported products from databse
        $pts_products    = Mage::helper('catalogupdate')->getPTSProducts();
        $insert_products = 0;
        $c = 0;

        // open new CSV file to export products
        $fp = fopen($dest_filepath, 'w');

        // add CSV file header
        fputcsv($fp, array(
            'Title',
            'Description',
            'Manufacturer',
            'Part Number',
        ));

        foreach ($pts_products as $data)
        {
            $c++;

            $sku   = trim($data['Item_num']);
            $brand = trim($data['Brand']);

            // look for the product in database among products sent by client
            $product = Mage::helper('catalogupdate')->getPTSProductBySKU($sku);

            if ($product) {

                $name              = Mage::helper('catalogupdate')->clearText($data['Product_Info']);
                $brand             = Mage::helper('catalogupdate')->clearText($data['Brand']);
                $mfg_part          = Mage::helper('catalogupdate')->clearText($data['MFG_Part_num']);
                $erp_vendor_number = Mage::helper('catalogupdate')->clearText($product['ERP_Vendor_Number']);
                $description       = preg_replace("/^\s*Technical Specifications\s*$/s", "", $data['Description']);
                $description       = Mage::helper('catalogupdate')->clearText($description);

                // skip invalid product
                if ($this->productHasDataError($name, $description, $brand, $mfg_part, $erp_vendor_number)) {

                    Mage::helper('catalogupdate')->logDebug($c . ". product invalid " . $sku, $this->logfile, true);
                    continue;

                }

                // save product >>>
                Mage::helper('catalogupdate')->logDebug($c . ". save product " . $sku, $this->logfile, true);

                $category_names = $data['Category'];

                // no category from resource website
                if (empty($category_names)) continue;

                $insert_products++;

                // add prodcut info into the CSV file
                fputcsv($fp, array(
                    $name,
                    $description,
                    $brand,
                    $mfg_part,
                ));

                continue;
            }

            Mage::helper('catalogupdate')->logDebug($c . ". product does not exist " . $sku, $this->logfile, true);

            // if ($insert_products) break;
        }

        fclose($fp);

        Mage::helper('catalogupdate')->logDebug("inserted products total: " . $insert_products, $this->logfile, true);
    }


    /**
     * Export products that not in the file
     */
    public function exportFiltered($source_filepath, $dest_filepath)
    {
        // open CSV file that contains products
        $pts_products = Mage::helper('catalogupdate')->getPTSProducts();

        // open new CSV file to export products
        $fpe = fopen($dest_filepath, 'w');

        // add CSV file header
        fputcsv($fpe, array(
            'Title',
            'Description',
            'Manufacturer',
            'Part Number',
        ));

        $c = 0;
        $not_found_count = 0;

        foreach ($pts_products as $pts_product)
        {
            $c++;

            $sku    = trim($pts_product['Item_num']);
            $brand  = trim($pts_product['Brand']);

            // look for the product in database
            $product = Mage::helper('catalogupdate')->getPTSProductBySKU($sku);

            if (!$product) {

                $not_found_count++;

                // add prodcut info into the CSV file
                fputcsv($fpe, array(
                    Mage::helper('catalogupdate')->clearText($pts_product['Product_Info']),
                    Mage::helper('catalogupdate')->clearText($pts_product['Description']),
                    Mage::helper('catalogupdate')->clearText($pts_product['Brand']),
                    Mage::helper('catalogupdate')->clearText($pts_product['MFG_Part_num']),
                ));

                // log event
                Mage::log("$c. $sku NOT found", null, $this->logfile);
                echo "$c. $sku NOT found\n";

                continue;

            }

            // log event
            Mage::log("$c. $sku exists", null, $this->logfile);
            echo "$c. $sku exists\n";
        }

        Mage::log("not found total: " . $not_found_count, null, $this->logfile);
        echo "not found total: " . $not_found_count . "\n";

        fclose($fp);
        fclose($fpe);
    }

    public function clearAndExportFiltered($source_filepath, $dest_filepath)
    {
        // open source csv file
        if (($fp = @fopen($source_filepath, 'r')) === FALSE) {
            die("File \"$filename\" does not exist or it is not readable.\n");
        }

        // open new csv file to export products
        $fpe = fopen($dest_filepath, 'w');

        $c       = -1;
        $valid   = 0;
        $invalid = 0;

        while (($row = fgetcsv($fp, 0, ",")) !== FALSE)
        {
            // print_r($row);

            $c++;

            // add CSV file header
            if ($c == 0) {
                // Title, Description, Manufacturer, Part Number
                fputcsv($fpe, $row);
                continue;
            }

            $title        = trim($row[0]);
            $description  = trim($row[1]);
            $manufacturer = trim($row[2]);
            $part_number  = trim($row[3]);

            if ( ($title == '' && $description == '') || $manufacturer == '' || $part_number == '' ) {

                echo $c . ". invalid \n";
                $invalid++;
                continue;

            }

            echo $c . ". VALID \n";
            $valid++;
            fputcsv($fpe, array($title, $description, $manufacturer, $part_number));
        }

        // close files
        fclose($fp);
        fclose($fpe);

        // log statistics
        Mage::helper('catalogupdate')->logDebug('valid products: ' . $valid, $this->logfile, true);
        Mage::helper('catalogupdate')->logDebug('invalid products: ' . $invalid, $this->logfile, true);
    }

    /**
     * Export manually added products
     */
    public function exportManuallyAddedProducts($dest_filepath)
    {
        $products = Mage::helper('catalogupdate')->getManuallyAddedProducts();

        // export to csv
        $fp = fopen($dest_filepath, 'w');

        // header
        fputcsv($fp, array(
            'Title',
            'Description',
            'Manufacturer',
            'Part Number',
            'Price',
            'Category URL',
        ));

        foreach($products as $product)
        {
            $product = Mage::getModel('catalog/product')->load($product->getId());

            // get category URL
            $category_ids = $product->getCategoryIds();
            $category_id  = end($category_ids);
            $category     = Mage::getModel('catalog/category')->load($category_id);
            $category_url = $category->getUrl($category);

            $fields = array(
                Mage::helper('catalogupdate')->clearText($product->getData('name')),
                Mage::helper('catalogupdate')->clearText($product->getData('description')),
                Mage::helper('catalogupdate')->clearText($product->getData('brand')),
                Mage::helper('catalogupdate')->clearText($product->getData('mfg_part')),
                $product->getData('price'),
                $category_url,
            );

            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    public function saveProducts($source_filepath)
    {
        // open CSV file that contains products
        if (($fp = @fopen($source_filepath, 'r')) === FALSE) {
            die("File \"$filename\" does not exist or it is not readable.\n");
        }

        $c = -1;

        while (($row = fgetcsv($fp, 0, ",")) !== FALSE)
        {
            $c++;

            // echo "$p.\n";
            // print_r($row);
            // echo "\n";

            // skip header
            if ($c == 0) continue;

            $brand           = trim($row[0]);  // 'Brand'];
            $erp_item_number = $row[1];  // 'ERP Vendor Number'];
            $mfg_part        = trim($row[2]);  // 'MFGPARTNUBMERCLEAN'];
            $sku             = trim($row[3]);  // 'PTS-Item_num'];
            $mfg_part        = $row[4];  // 'MFG_Part_num'];
            $description     = $row[5];  // 'Description'];
            $tech_specs      = $row[6];  // 'Tech_specs'];
            $name            = $row[7];  // 'Product_Info'];
            $weight          = $row[8];  // 'weight'];
            $dist_center     = $row[9];  // 'Dist._Center'];
            $price           = $row[10]; // 'List_Price'];
            $your_price      = $row[11]; // 'My_price'];
            $quantity        = $row[12]; // 'Per_Quantity'];
            $image           = $row[13]; // 'Image'];

            $this->dbwrite->exec("INSERT INTO pts_product (Brand, Item_num) VALUES ('$brand', '$sku');");

            // log event
            Mage::log("$c. $sku saved", null, $this->logfile);
            echo "$c. $sku saved\n";
        }

        fclose($fp);
    }

    public function importToPTSProduct($source_filepath)
    {
        // open CSV file that contains products
        if (($fp = @fopen($source_filepath, 'r')) === FALSE) {
            die("File \"$filename\" does not exist or it is not readable.\n");
        }

        $columns = array(
            'Brand',
            'ERP_Vendor_Number',
            'MFGPARTNUBMERCLEAN',
            'PTS_Item_num',
            'MFG_Part_num',
            'Description',
            'Tech_specs',
            'Product_Info',
            'weight',
            'Dist_Center',
            'List_Price',
            'My_price',
            'Per_Quantity',
            'Image',
        );
        $q_columns_names  = "`" . implode("`,`", $columns) . "`";
        $q_columns_values = ":" . implode(",:", $columns);
        $query   = sprintf("INSERT INTO pts_product (%s) VALUES (%s)", $q_columns_names, $q_columns_values);
        $row     = array();
        $c       = 0;

        while (($data = fgetcsv($fp, 0, ",")) !== FALSE)
        {
            echo $c . ".\n";
            // sleep(1);

            foreach ($data as $r => $d)
            {
                $row[$r] = trim($d);
            }

            // echo "$p.\n";
            // print_r($row);
            // echo "\n";

            // set header columns
            if ($c == 0) {
                $c++;
                continue;
            }

            $binds = array();

            foreach ($columns as $i => $column)
            {
                $binds[$column] = $row[$i];
            }

            // print_r($binds);
            // echo "\n\n\n\n";
            // echo $query . "\n";

            $result = $this->dbwrite->query($query, $binds);

            // log event
            // Mage::log("$c. $sku saved", null, $this->logfile);
            // echo "$c. $sku saved\n";

            // if ($c < 2) break;

            $c++;
        }

        fclose($fp);
    }

    /**
     * Check product data
     */
    public function productHasDataError($name, $desc, $brand, $mfg_part, $erp_vendor_number)
    {
        $error = false;

        if ( ($name == '' && $desc == '') || $brand == '' || $mfg_part == '' || $erp_vendor_number == '' ) {

            $error = true;

            if ($name == '')     Mage::helper('catalogupdate')->logDebug("error: missing name", $this->logfile, true);
            if ($desc == '')     Mage::helper('catalogupdate')->logDebug("error: missing description", $this->logfile, true);
            if ($brand == '')    Mage::helper('catalogupdate')->logDebug("error: missing manufacturer", $this->logfile, true);
            if ($mfg_part == '') Mage::helper('catalogupdate')->logDebug("error: missing MFG part number", $this->logfile, true);
            if ($erp_vendor_number == '') Mage::helper('catalogupdate')->logDebug("error: missing ERP part number", $this->logfile, true);

        }

        return $error;
    }
}
