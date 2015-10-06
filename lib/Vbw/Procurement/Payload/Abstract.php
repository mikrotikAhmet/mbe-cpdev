<?php

abstract class Vbw_Procurement_Payload_Abstract
{

    protected $_rawPayload = null;

    protected $_payload = null;

    public function __construct ($payload = null)
    {
        if ($payload != null) {
            $this->setPayload($payload);
        }
    }

    public function setPayload ($payload) {
        if ($payload instanceof Zend_Controller_Request_Abstract) {
            $this->_rawPayload = $payload->getRawBody();
        } elseif (is_string($payload)) {
            $this->_rawPayload = $payload;
        }
    }

    public function getRawPayload ()
    {
        return $this->_rawPayload;
    }

    abstract function getPayload ();

}