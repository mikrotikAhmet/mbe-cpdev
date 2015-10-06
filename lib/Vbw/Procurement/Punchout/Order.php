<?php
namespace Vbw\Procurement\Punchout;

require_once "Vbw/Procurement/Punchout/Header.php";
require_once "Vbw/Procurement/Punchout/Order/Body.php";

/**
 * This is the generic order object that is used by the application.
 *
 */

class Order {

    public $header = null;

    public $body = null;

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch ($key)  {
                    case "header" :
                    case "body" :
                        $this->{"get". $key}()->inflate($value);
                        break;
                }
            }
        }
    }

    public function setHeader ($header)
    {
        if ($header instanceof Header) {
            $this->header = $header;
        } else {
            $header = $this->getHeader();
            $header->inflate($header);
        }
        return $this;
    }

    public function getHeader ()
    {
        if ($this->header === null) {
            $this->header = new Header();
        }
        return $this->header;
    }

    public function getBody ()
    {
        if ($this->body === null) {
            $this->body = new Order\Body();
        }
        return $this->body;
    }

    public function setBody ($body)
    {
        $this->body = $body;
        return $this;
    }

    public function toArray ()
    {
        $data = array (
            "header" => $this->getHeader()->toArray(),
            "body" => $this->getBody()->toArray(),
        );
        return $data;
    }

}
/*
$json =  {
            "header" : {
                "to" : {0={"domain":"idy","value":"myidentity"}],
                       {"data
                "from" : [{"domain":"idy","value":"myidentity"}],
                "sender" : { },
                },
            "body" : {

                    }
*/