<?php

/**
 * The Punchout Session controls most punchout related
 * actions.
 *
 * This session does not actually use it's own storage but stores
 * information in the mage catalog/session.
 *
 * Requires \Vbw\Procurement library
 *
 */
require_once "Vbw/Procurement/Punchout/Catalog.php";



class Vbw_Punchout_Model_Catalog
{

    /**
     * @var \Vbw\Procurement\Punchout\Catalog
     */
    protected $_portableCatalog = null;

    /**
     *
     * @var Vbw_Punchout_Helper_Config
     */
    protected $_configHelper = null;


    public function loadStore ()
    {

    }

    public function loadCategories ()
    {
        $helper = Mage::helper('catalog/category');
        $categories = $helper->getStoreCategories();
        $this->addCategories($categories);
    }

    public function addCategories ($categories,$level = 0)
    {
        /**
         * @var $helper Mage_Catalog_Helper_Category
         */
        $helper = Mage::helper('catalog/category');
        foreach ($categories AS $category) {
            if ($category->getIsActive() == 1) {
                $array = array (
                    "supplierid" => $category->getId(),
                    "description" => array ("en-US" => $category->getName()),
                    "url" => $helper->getCategoryUrl($category),
                    "level" => $level
                );
                $this->getPortableCatalog()->addCategory($array);
            }
        }
    }


    public function loadProducts()
    {

    }

    public function load()
    {
        if ($this->exportsStore()) {
            $this->loadStore();
        }
        if ($this->exportsCategories()) {
            $this->loadCategories();
        }
        if ($this->exportsProducts()) {
            $this->loadProducts();
        }
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

    public function exportsStore ()
    {
        if ($this->getConfig('catalog_export/store') == 1) {
            return true;
        }
        return false;
    }

    public function exportsCategories ()
    {
        if ($this->getConfig('catalog_export/category') == 1) {
            return true;
        }
        return false;
    }

    public function exportsSubcategories()
    {
        if ($this->getConfig('catalog_export/subcategory') == 1) {
            return true;
        }
        return false;
    }

    public function exportsProducts ()
    {
        if ($this->getConfig('catalog_export/product') == 1) {
            return true;
        }
        return false;
    }


    /**
     * @param \Vbw\Procurement\Punchout\Catalog $portableCatalog
     */
    public function setPortableCatalog($portableCatalog)
    {
        $this->_portableCatalog = $portableCatalog;
    }

    /**
     * @return \Vbw\Procurement\Punchout\Catalog
     */
    public function getPortableCatalog()
    {
        if ($this->_portableCatalog === null) {
            $this->setPortableCatalog(new \Vbw\Procurement\Punchout\Catalog);
        }
        return $this->_portableCatalog;
    }


}