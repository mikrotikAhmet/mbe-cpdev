<?php
class Cpdev_CatalogUpdate_Model_Import
{
    public
        $logfile = 'catalogupdate-import.log';

    public function __construct()
    {
        $this->core_resource = Mage::getSingleton('core/resource');
        $this->dbread        = $this->core_resource->getConnection('core_read');    // read database connection
        $this->dbwrite       = $this->core_resource->getConnection('core_write');   // write database connection
    }

    public function importIntoPTSProduct($filepath)
    {
        // get content of the csv file
        if (($fp = @fopen($filepath, 'r')) === false) {
            Mage::helper('catalogupdate')->logDebug("File \"$filepath\" does not exist or it is not readable.", $this->logfile, true);
            return false;
        }

        $columns = array(
            'brand',
            'pts_item_number',
            'mfg_part_number',
            'erp_part_number',
            'erp_vendor_number',
            'product_info',
            'description',
            'tech_specs',
            'weight',
            'dist_center',
            'list_price',
            'my_price',
            'per_quantity',
            'image',
        );
        $columns_names  = "`" . implode("`,`", $columns) . "`";
        $columns_values = ":" . implode(",:", $columns);
        $query          = sprintf("INSERT INTO pts_product (%s) VALUES (%s)", $columns_names, $columns_values);
        $c              = -1;

        // read csv rows
        while (($data = fgetcsv($fp, 0, ",")) !== FALSE)
        {
            $c++;

            // csv file header (skip it)
            if ($c == 0) continue;

            $binds = array(
                'brand'             => $data[0],
                'pts_item_number'   => $data[4],
                'mfg_part_number'   => $data[2],
                'erp_part_number'   => $data[3],
                'erp_vendor_number' => $data[1],
                'product_info'      => $data[8],
                'description'       => $data[6],
                'tech_specs'        => $data[7],
                'weight'            => $data[9],
                'dist_center'       => $data[10],
                'list_price'        => $data[11],
                'my_price'          => $data[12],
                'per_quantity'      => $data[13],
                'image'             => $data[14],
            );

            $this->dbwrite->query($query, $binds);

            echo $c . ". #" . $binds['pts_item_number'] . "\n";

            // break;
        }

        fclose($fp);
    }

    public function importIntoPTS3M($filepath, $dest_filepath)
    {
        // get content of the csv file
        if (($fp = @fopen($filepath, 'r')) === false) {
            Mage::helper('catalogupdate')->logDebug("File \"$filepath\" does not exist or it is not readable.", $this->logfile, true);
            return false;
        }

        // open new CSV file to export duplicates
        $fpd = fopen($dest_filepath, 'w');

        $columns = array(
            'mfg_part_number',
            'erp_part_number',
            'erp_vendor_number',
            'product_info',
            'description',
            'weight',
            'list_price',
            'my_price',
            'category',
        );
        $columns_names  = "`" . implode("`,`", $columns) . "`";
        $columns_values = ":" . implode(",:", $columns);
        $query          = sprintf("INSERT INTO pts_3m (%s) VALUES (%s)", $columns_names, $columns_values);
        $c              = -1;

        // read csv rows
        while (($data = fgetcsv($fp, 0, ",")) !== FALSE)
        {
            $c++;

            // csv file header (skip it)
            if ($c == 0) {
                // add CSV file header
                fputcsv($fpd, $data);
                continue;
            }

            $mfg_part_number = $data[2];

            // look for duplicates
            $product = $this->getPTS3MByMFGPartNumber($mfg_part_number);

            if ($product) {
                // Mage::log(print_r($product, true), null, 'catalog-update-3m-import-duplicates.log');

                // add duplicate into CSV file
                fputcsv($fpd, $data);
                continue;
            }

            $binds = array(
                'mfg_part_number'   => $mfg_part_number,
                'erp_part_number'   => $data[1],
                'erp_vendor_number' => $data[0],
                'product_info'      => $data[5],
                'description'       => $data[6],
                'weight'            => $data[7],
                'list_price'        => $data[3],
                'my_price'          => $data[4],
                'category'          => $data[8],
            );

            $this->dbwrite->query($query, $binds);

            echo $c . ". #" . $binds['mfg_part_number'] . "\n";

            // break;
        }

        fclose($fp);
        fclose($fp);
    }

    /**
     * Selects PTS 3M product by MFG part number
     */
    public function getPTS3MByMFGPartNumber($mfg_part_number)
    {
        $query = "SELECT * FROM pts_3m WHERE mfg_part_number = :mfg_part_number";

        $binds = array(
            'mfg_part_number' => $mfg_part_number,
        );
        $result = $this->dbread->query($query, $binds);

        $product = array();

        while ( $row = $result->fetch() )
        {
            $product = $row;
        }

        if (!count($product)) return false;

        return $product;
    }

    public function updateProduct($data)
    {
        // find product
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $data['sku']);
        $is_new  = !$product || !$product->getId();

        Mage::helper('catalogupdate')->logDebug($c . ". product [" . ($is_new ? "insert" : "update") . "] " . $data['sku'], $this->logfile, true);

        if ($is_new) {
            // new product object
            $product = Mage::getModel('catalog/product');

            // assign product to the default website
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));

            $product->setData('sku', $data['sku']);
            $product->setTaxClassId(0);                             // none | taxable goods | shipping (0 | 2 | 4)
            $product->setTypeId('simple');                          // create only "simple" products
            $product->setAttributeSetId($this->attribute_set_id);   // default attribute set
            $product->setVisibility($this->product_visibility);     // sets visibility as 'catalog, search'
            $product->setStatus($this->product_status);             // enabled
        }

        // set product attributes
        $product->setData('name', $data['name']);
        $product->setData('description', $data['description']);
        $product->setData('mfg_part', $data['mfg_part']);
        $product->setData('erp_part_number', $data['erp_part_number']);
        $product->setData('erp_vendor_number', $data['erp_vendor_number']);
        $product->setData('brand', $data['brand']);
        $product->setData('dist_center', $data['dist_center']);
        $product->setData('shipping_info', $data['shipping_info']);
        $product->setData('tech_specs', $data['tech_specs']);
        $product->setData('your_price', $data['your_price']);
        $product->setData('price', $data['price']);
        $product->setWeight($data['weight']);

        // set product category IDs
        $product->setCategoryIds($category_ids);

        // set product stock
        $product->setStockData(array(
               'use_config_manage_stock' => 0,                // 'Use config settings' checkbox
               // 'manage_stock'            => 0,             // manage stock
               // 'min_sale_qty'            => 0,             // Minimum Qty Allowed in Shopping Cart
               // 'max_sale_qty'            => 0,             // Maximum Qty Allowed in Shopping Cart
               'is_in_stock'             => 1,                // Stock Availability
               // 'qty'                     => 0,             // qty
           )
        );

        // save product
        $product->save();

        // add new product image
        // $this->addProductImage($product, $data['image']);
    }
}