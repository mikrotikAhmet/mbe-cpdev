<?php
namespace Vbw\Procurement\Punchout\Data;

require_once "Vbw/Procurement/Punchout/Data.php";

use Vbw\Procurement\Punchout;

abstract class Access
{

    protected $_data;

    public function addData ($name,$value)
    {
        $this->getData()->set($name,$value);
    }

    /**
     * @param null $name
     * @return \Procurement\Punchout\Data
     */
    public function getData ($name = null)
    {
        if ($name === null) {
            if (!($this->_data instanceof Punchout\Data)) {
                $this->_data = new Punchout\Data();
            }
            return $this->_data;
        } else {
            return $this->getData()->get($name);
        }
    }

    public function setData ($data)
    {
        if (is_array($data)) {
            $this->_data = new Punchout\Data($data);
        }
    }

    public function __call ($method,$args)
    {
        $prefix = strtolower(substr($method,0,3));
        if ($prefix == "set" || $prefix == "get") {
            $len = strlen($method);
            $key = substr($method,3,$len);
            $filter = new \Zend_Filter_Word_CamelCaseToUnderscore();
            $key = strtolower($filter->filter($key));
            if ($prefix == "set") {
                return call_user_func(array($this,'addData'),$key,$args[0]);
            } elseif ($prefix == "get") {
                return $this->getData($key);
            }
        }
        throw new \Exception("Method requested {$method} does not exist.");
    }

    public function toArray ()
    {
        return array (
            "data" => $this->getData()->toArray()
        );
    }

}