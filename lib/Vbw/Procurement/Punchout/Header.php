<?php
namespace Vbw\Procurement\Punchout;

require_once "Vbw/Procurement/Punchout/Identity.php";

class Header {

    public $to = null;

    public $from  = null;

    public $sender = null;

    /**
     * @param array $data
     * @return Header
     */
    public function inflate ($data = array())
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                switch ($key)  {
                    case "to":
                    case "from":
                    case "sender":
                        $this->{"get". $key}()->inflate($value);
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * @param $sender
     */
    public function setSender ($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return null
     */
    public function getSender ()
    {
        if ($this->sender === null) {
            $this->sender = new Identity();
        }
        return $this->sender;
    }

    public function setFrom ($from)
    {
        $this->from = $from;
    }

    public function getFrom ()
    {
        if ($this->from === null) {
            $this->from = new Identity();
        }
        return $this->from;
    }

    public function setTo ($to)
    {
        $this->to = $to;
    }

    public function getTo ()
    {
        if ($this->to === null) {
            $this->to = new Identity();
        }
        return $this->to;
    }

    public function toArray ()
    {
        return array (
            "to" => $this->getTo()->toArray(),
            "from" => $this->getFrom()->toArray(),
            "sender" => $this->getSender()->toArray()
        );
    }

}