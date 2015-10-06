<?php
namespace Vbw\Procurement\Punchout;

require_once "Vbw/Procurement/Punchout/Catalog/Items.php";
require_once "Vbw/Procurement/Punchout/Catalog/Item.php";
/**
 * This is the generic request object that is used by the application.
 *
 */

class Catalog {

    /**
     * @var \Vbw\Procurement\Punchout\Catalog\Items
     */
    protected $_stores = null;

    /**
     * @var \Vbw\Procurement\Punchout\Catalog\Items
     */
    protected $_categories = null;

    /**
     * @var \Vbw\Procurement\Punchout\Catalog\Items
     */
    protected $_products = null;

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch ($key)  {
                    case "stores" :
                    case "categories" :
                    case "products" :
                        $this->{"get". $key}()->inflate($value);
                        break;
                }
            }
        }
    }

    /**
     * @param $data
     * @return \Vbw\Procurement\Punchout\Catalog\Item
     */
    public function addStore ($data)
    {
        return $this->getStores()->addItem($data);
    }

    /**
     * @param $data
     * @return \Vbw\Procurement\Punchout\Catalog\Item
     */
    public function addCategory ($data)
    {
        return $this->getCategories()->addItem($data);
    }

    /**
     * @param $data
     * @return \Vbw\Procurement\Punchout\Catalog\Item
     */
    public function addProduct ($data)
    {
        return $this->getProducts()->addItem($data);
    }


    public function toArray ()
    {
        $data = array (
            "stores" => $this->getStores()->toArray(),
            "categories" => $this->getCategories()->toArray(),
            "products" => $this->getProducts()->toArray()
        );
        return $data;
    }

    /**
     * @return \Vbw\Procurement\Punchout\Catalog\Items
     */
    public function getCategories()
    {
        if ($this->_categories === null) {
            $this->_categories = new Catalog\Items;
        }
        return $this->_categories;
    }

    /**
     * @return \Vbw\Procurement\Punchout\Catalog\Items
     */
    public function getProducts()
    {
        if ($this->_products === null) {
            $this->_products = new Catalog\Items;
        }
        return $this->_products;
    }

    /**
     * @return \Vbw\Procurement\Punchout\Catalog\Items
     */
    public function getStores()
    {
        if ($this->_stores === null) {
            $this->_stores = new Catalog\Items;
        }
        return $this->_stores;
    }

}
/*
{
    "stores" : [
        {
            "supplierId" : "storefront",
            "description" : {
                "en-US" : "text"
            },
            "unspsc" : ""
        }
        ],
    "categories" : [
        {
            "supplierId" : "storefront",
            "description" : {
                "en-US" : "text"
            },
            "level" : "1",
            "url" : "url/to/my/category.html",
            "unspsc" : ""
        }
        ],
    "prodcuts" : [
        {
            "supplierId" : "storefront",
            "description" : {
                "en-US" : "text"
            }
            "url" : "url/to/my/product.html",
            "unspsc" : ""
        }
        ]
}

*/