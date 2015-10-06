<?php

class Vbw_Punchout_Model_System_Config_Source_Topbottom
{
	
    public function toOptionArray()
    {
    	return array(
    		array(
    			'value' => 1,
    			'label' => 'Top'
    		),
            array(
                'value' => 2,
                'label' => 'Bottom'
            ),
            array(
                'value' => 3,
                'label' => 'Top & Bottom'
            )
        );
    }

}
