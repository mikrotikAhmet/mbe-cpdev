<?php
namespace Vbw\Procurement\Punchout;

require_once "Vbw/Procurement/Punchout/Header.php";
require_once "Vbw/Procurement/Punchout/Request/Setup.php";
require_once "Vbw/Procurement/Punchout/Request/Profile.php";
require_once "Vbw/Procurement/Punchout/Request/Custom.php";

/**
 * This is the generic request object that is used by the application.
 *
 */

class Request {

    const TYPE_SETUP = "setuprequest";
    const TYPE_PROFILE = "profilerequest";

    const MODE_TEST = "testing";
    const MODE_PRODUCTION = "production";

    public $header = null;

    public $type = null;

    public $operation = null;

    public $mode = self::MODE_PRODUCTION;

    public $body = null;

    public $custom = null;

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch ($key)  {
                    case "header" :
                    case "custom" :
                    case "body" :
                        $this->{"get". $key}()->inflate($value);
                        break;
                    case "operation":
                    case "mode":
                    case "type":
                        $this->{"set". $key}($value);
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

    public function setType ($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType ()
    {
        return $this->type;
    }

    public function getOperation ()
    {
        return $this->operation;
    }

    public function setOperation ($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    public function getMode ()
    {
        return $this->mode;
    }

    public function setMode ($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function getBody ()
    {
        if ($this->body === null) {
            if ($this->type == self::TYPE_SETUP) {
                $this->body = new Request\Setup();
            } elseif ($this->type == self::TYPE_PROFILE) {
                $this->body = new Request\Profile();
            } else {
                throw new \Exception("Request data is invalid : not type was found.");
            }
        }
        return $this->body;
    }

    public function setBody ($body)
    {
        if ($body instanceof Request\Setup) {
            $this->body = $body;
            $this->type = self::TYPE_SETUP;
        } elseif ($body instanceof Request\Profile) {
            $this->body = $body;
            $this->type = self::TYPE_PROFILE;
        } else {
            throw new \Exception("Body is of an unknown request type.");
        }
        return $this;
    }

    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    public function getCustom()
    {
        if ($this->custom === null) {
            $this->custom = new Request\Custom();
        }
        return $this->custom;
    }

    public function toArray ()
    {
        $data = array (
            "header" => $this->getHeader()->toArray(),
            "type" => $this->getType(),
            "operation" => $this->getOperation(),
            "mode" => $this->getMode(),
            "body" => $this->getBody()->toArray(),
            "custom" => $this->getCustom()->toArray()
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
            "type" : "",
            "operation" : "",
            "mode" : "",
            "body" : {

                    }
*/