<?php
namespace Vbw\Procurement\Punchout;

require_once "Vbw/Procurement/Punchout/Data/Access.php";
require_once "Vbw/Procurement/Punchout/Credential.php";

class Identity
    extends Data\Access {

    protected $_credentials = array();

    public function inflate ($data = array())
    {

        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                if ($key === "data") {
                    $this->setData($value);
                } else {
                    $this->addCredential($value['domain'],$value['value'])->setData(isset($value['data']) ? $value['data'] : array());
                }
            }
        }
    }

    public function addCredential ($domain,$value)
    {
        $this->_credentials[strtolower($domain)] = new Credential($value,$domain);
        return $this->getCredential($domain);
    }

    public function &getCredential ($domain) {
        return $this->_credentials[strtolower($domain)];
    }

    public function getCredentials ()
    {
        return $this->_credentials;
    }

    public function toArray ()
    {
        $return = parent::toArray();
        foreach ($this->_credentials AS $k => $credential) {
            $return[] = $credential->toArray();
        }
        return $return;
    }

}