<?php
namespace Vbw\Procurement\Punchout\Catalog;

require_once "Vbw/Procurement/Punchout/Data/Access.php";


use Vbw\Procurement\Punchout;


class Item
    extends Punchout\Data\Access
{

    protected $_supplierid = null;

    protected $_supplierkey = null;

    protected $_url = null;

    protected $_description = null;

    protected $_unspsc = null;

    protected $_level = 0;

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->inflate($data);
        }
    }

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch (strtolower($key))  {
                    default :
                        $this->{"set". $key}($value);
                        break;
                }
            }
        }
    }

    public function toArray ()
    {
        $data = parent::toArray();
        $params = array (
            "supplierid","url","description","unspsc","level"
        );
        foreach ($params AS $idx=>$key) {
            $data[$key] = $this->{"get". ucfirst($key)}();
        }
        return $data;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setLevel($level)
    {
        $this->_level = $level;
    }

    public function getLevel()
    {
        return $this->_level;
    }

    public function setSupplierid($supplierid)
    {
        $this->_supplierid = $supplierid;
    }

    public function getSupplierid()
    {
        return $this->_supplierid;
    }

    public function setUnspsc($unspsc)
    {
        $this->_unspsc = $unspsc;
    }

    public function getUnspsc()
    {
        return $this->_unspsc;
    }

    public function setUrl($url)
    {
        $this->_url = $url;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setSupplierkey($supplierkey)
    {
        $this->_supplierkey = $supplierkey;
    }

    public function getSupplierkey()
    {
        return $this->_supplierkey;
    }


}