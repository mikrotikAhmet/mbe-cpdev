<?php

class Vbw_Punchout_Model_System_Config_Source_Customercart
{
	
    public function toOptionArray()
    {
    	return array(
    		array(
    			'value' => 1,
    			'label' => 'Yes, Cart Only'
    		),
            array(
                'value' => 2,
                'label' => 'Yes, Cart & Customer'
            ),
            array(
                'value' => 3,
                'label' => 'Yes, Customer Only'
            ),
            array(
                'value' => 0,
                'label' => 'No'
            )
        );
    }

}
