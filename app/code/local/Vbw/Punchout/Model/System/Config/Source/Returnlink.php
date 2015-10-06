<?php

class Vbw_Punchout_Model_System_Config_Source_Returnlink
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => 1,
                'label' => 'Yes, in Top Links'
            ),
            array(
                'value' => 2,
                'label' => 'Yes, Custom'
            ),
            array(
                'value' => 0,
                'label' => 'No'
            )
        );
    }

}
