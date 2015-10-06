<?php

class Vbw_Procurement_Payload_Json
    extends Vbw_Procurement_Payload_Abstract
{

    public function getPayload () {
        if ($this->_payload == null) {
              if ($this->isJson()) {
                 $this->_payload = Zend_Json::decode($this->getRawPayload());
              } else {
                  $this->_payload = array();
              }
        }
        return $this->_payload;
    }

    public function isJson ()
    {
        $string = $this->getRawPayload();
        $first = substr($string,0,1);
        $last = substr($string,(strlen($string)-1),1);
        if (($first == '{' && $last == '}')
                || ($first == '[' && $last == ']')) {
            return true;
        }
        return false;
    }

    public function getParam ($key)
    {
        $payload = $this->getPayload();
        if (isset($payload[$key])) {
            return $payload[$key];
        }
        return null;
    }


    public function __call ($method,$args)
    {
        $prefix = strtolower(substr($method,0,3));
        if ($prefix == "get") {
            $len = strlen($method);
            $key = substr($method,3,$len);
            $filter = new \Zend_Filter_Word_CamelCaseToUnderscore();
            $key = strtolower($filter->filter($key));
            return $this->getParam($key);
        }
        throw new Exception("Method requested {$method} does not exist.");
    }

}