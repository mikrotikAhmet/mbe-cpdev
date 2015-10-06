<?php
class Cpdev_CatalogUpdate_Helper_Data extends Mage_Core_Helper_Abstract
{
    private
        $core_resource,
        $dbread,
        $dbwrite;

    public function __construct()
    {
        $this->core_resource = Mage::getSingleton('core/resource');
        $this->dbread        = $this->core_resource->getConnection('core_read');      // read database connection
        $this->dbwrite       = $this->core_resource->getConnection('core_write');     // write database connection
    }

    /**
     * Returns category by name
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
     * Returns categories by name
     */
    public function getCategoriesByName($name)
    {
        $categories = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', $name);

        if (!$categories) return null;

        return $categories;
    }

    /**
     * Returns category by name and parent ID
     */
    public function getCategoryByNameAndParentId($name, $parent_id)
    {
        $category = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', $name)
            ->addFieldToFilter('parent_id', $parent_id)
            ->getFirstItem();

        if (!$category || !$category->getId()) return null;

        return $category;
    }

    /**
     * Returns all products
     */
    public function getAllProducts()
    {
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->load();

        return $products;
    }

    /**
     * Returns manually added products
     */
    public function getManuallyAddedProducts()
    {
        /*
        // get products by date of creation
        $time = "2013-08-18 00:00:00";

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            // ->addAttributeToSort('created_at', 'desc')
            // ->addAttributeToFilter('created_at', array('gteq' => $time));
            ->addAttributeToFilter('id', array('lteq' => 153953));
        */

        // just an example
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

        // get products by ID
        $maximum_id = 153953;

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSort('entity_id', 'desc')
            ->addAttributeToFilter('entity_id', array('lteq' => $maximum_id))
            ->load();

        return $products;
    }

    /**
     * Selects all PTS tools products
     */
    public function getPTSProducts($table = 'pts_tools')
    {
        // get products
        $products = $this->dbread->fetchAll("SELECT * FROM $table;");

        return $products;
    }

    /**
     * Selects specific PTS tools products
     */
    public function getPTSProductsSpecific()
    {
        // get products
        $products = $this->dbread->fetchAll("SELECT * FROM `pts_tools` WHERE Category LIKE '1%' OR Category = 'OSG Tap & Die,Cutting Tools,Taps,Special Thread Taps';");

        return $products;
    }

    /**
     * Selects PTS product by SKU
     */
    public function getPTSProductBySKU($sku, $table = 'pts_product')
    {
        $query = "SELECT * FROM pts_product WHERE pts_item_number = :pts_item_number";

        if ($table == 'pts_tools') {
            $query = "SELECT * FROM pts_tools WHERE Item_num = :pts_item_number";
        }

        $binds = array(
            'pts_item_number' => $sku,
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

    /**
     * Selects product by SKU
     */
    public function getPTSProductBySKUAndBrand($sku, $brand)
    {
        $query = "SELECT * FROM pts_product WHERE PTS_Item_num = :PTS_Item_num AND Brand = :Brand";

        $binds = array(
            'PTS_Item_num' => $sku,
            'Brand'        => $brand,
        );
        $result = $this->dbread->query($query, $binds);

        $product = array();

        while ( $row = $result->fetch() )
        {
            $product = $row;
        }

        // if (!$product) return false;
        if (!count($product)) return false;

        return $product;
    }

    /**
     * Selects product by SKU
     */
    public function getPTSToolsProductBySKU($sku)
    {
        $query = "SELECT * FROM pts_tools WHERE Item_num = :Item_num";

        $binds = array(
            'Item_num' => $sku,
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

    /**
     * Selects product by SKU
     */
    public function getPTSProductsByMFGPartNumber($sku, $mfg_part_number)
    {
//         $query = "SELECT pts_product.*, pts_tools.* 
// FROM pts_product 
// INNER JOIN pts_tools 
// ON pts_product.mfg_part_number = pts_tools.MFG_Part_num
// WHERE pts_product.mfg_part_number = '".$mfg_part_number."'
// ";

        $query = "SELECT * FROM pts_tools WHERE MFG_Part_num = :MFG_Part_num";

        $binds = array(
            'MFG_Part_num' => $mfg_part_number,
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

    /**
     * Clears spaces, tabs and new lines at the end of string
     */
    public function clearText($text = '')
    {
        $text = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $text);

        return trim($text);
    }

    /**
     * Resolves special cases in category string and return an array of correct categories
     */
    public function resolveCategories($category_names, $brand)
    {
        $category_names_arr = explode(',', $category_names); // convert category string to array
        $category_name      = current($category_names_arr);  // get first category name

        // remove brand of the begining of the category string
        if ($category_name == $brand) {
            array_shift($category_names_arr);
        }

        // remove srecific labels of the category string
        $labels = array(
            '118&deg;',
            '120&deg;',
            '130&deg;',
            '140&deg;',
            'OSG Tap & Die',
        );

        if ( in_array($category_name, $labels) ) {
            array_shift($category_names_arr);
        }

        $category_name = current($category_names_arr); // get second category name

        // remove 'OSG Tap & Die' category of the begining of the category string
        if ($category_name == 'OSG Tap & Die') {
            array_shift($category_names_arr);
        }

        return $category_names_arr;
    }

    /**
     * Disable indexing to speed up
     */
    public function disableIndexing($log_file = '_indexer.log', $do_log = false)
    {
        if ($do_log) Mage::log('> disabling indexes', null, $log_file);

        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        
        foreach($indexingProcesses as $process)
        {
            if ($do_log) Mage::log($process->getIndexer()->getName().' - disabled', null, $log_file);
            $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        }

        if ($do_log) Mage::log('> disabling indexes done', null, $log_file);
    }

    /**
     * Set all indexes to update on save.
     */
    public function enableIndexing($log_file = '_indexer.log', $do_log = false)
    {
        if ($do_log) Mage::log('> enabling indexes', null, $log_file);
        
        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        
        foreach($indexingProcesses as $process)
        {
            if ($do_log) Mage::log($process->getIndexer()->getName().' - enabled', null, $log_file);
            $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
        }

        if ($do_log) Mage::log('> enabling indexes done', null, $log_file);
    }

    /**
     * Reindex everything.
     */
    public function reindexAll($log_file = '_indexer.log', $do_log = false)
    {   
        if ($do_log) Mage::log('> indexing data', null, $log_file);
        
        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        
        foreach($indexingProcesses as $process)
        {
            $process->reindexEverything();
            if ($do_log) Mage::log($process->getIndexer()->getName().' - Done!', null, $log_file);
        }
        
        if ($do_log) Mage::log('> indexing data done', null, $log_file);
    }

    /**
     * Clears Magento cache
     */
    public function clearCache($log_file = '_clearcache.log', $do_log = false)
    {
        if ($do_log) Mage::log('> clear cache', null, $log_file);
        
        $mage        = Mage::app();
        $cache_types = $mage->useCache();

        foreach($cache_types as $cache_type => $cache)
        {
            $mage->getCacheInstance()->cleanType($cache_type);
        }
    }

    /**
     * Log into file
     */
    public function logDebug($message, $file = 'debug.log', $output = false)
    {
        Mage::log($message, null, $file);

        if (!$output) return true;

        echo $message . "\n";
    }
}