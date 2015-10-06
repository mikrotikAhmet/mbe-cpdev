<?php
namespace Vbw\Procurement\Punchout;

require_once "Vbw/Procurement/Punchout/Data/Access.php";

class Credential
        extends Data\Access
{

    public $identity = null;

    public $domain  = null;

    public function __construct($value,$domain)
    {
        $this->identity = $value;
        $this->setDomain($domain);
    }

    public function setDomain ($domain)
    {
        $this->domain = $domain;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getIdentity ()
    {
        return $this->identity;
    }

    public function toArray ()
    {
        $return = parent::toArray();
        $return["value"] = $this->identity;
        $return['domain'] = $this->domain;
        return $return;
    }

    public function __toString()
    {
        return $this->identity;
    }
}