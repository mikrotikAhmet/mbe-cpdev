<?php
namespace Vbw\Procurement\Punchout\Request;

use Vbw\Procurement\Punchout;

class Custom
    extends Punchout\Data\Access
{

    public function inflate ($data)
    {
        if (is_array($data)) {
            foreach ($data AS $key => $value) {
                $this->addData($key,$value);
            }
        }
    }

    public function toArray ()
    {
        $data = parent::toArray();
        return $data['data'];
    }

}