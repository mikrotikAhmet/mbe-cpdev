<?php
namespace Vbw\Procurement\Punchout;

class Data {

    protected $_data = array();

    public function __construct ($data = array())
    {
        if (is_array($data)) {
            foreach ($data AS $k => $v) {
                $this->set($k,$v);
            }
        }
    }

    public function set ($name,$value)
    {
        $this->_data[strtolower($name)] = array (
            "key" => $name,
            "value" => $value
        );
    }

    public function get ($name = null)
    {
        if ($name === null) {
            return $this->_data;
        } else {
            if (isset($this->_data[strtolower($name)])) {
                return $this->_data[strtolower($name)]['value'];
            }
        }
    }

    public function toArray ()
    {
        $data = array();
        foreach ($this->_data AS $k => $v)  {
            $data[$v['key']] = $v['value'];
        }
        return $data;
    }

}