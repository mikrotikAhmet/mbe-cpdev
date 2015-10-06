<?php

class Vbw_Punchout_Model_System_Config_Source_Session
{
	
    public function toOptionArray()
    {
    	return array(
    		array(
    			'value' => 0,
    			'label' => 'No Change'
    		),
            array(
                'value' => 2,
                'label' => 'JS Cookie Check & Entrance Page'
            ),
            //array(
            //    'value' => 3,
            //    'label' => 'URL Based Session Keys'
            //)
        );
    }

}
