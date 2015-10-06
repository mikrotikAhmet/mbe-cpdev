<?php

class Vbw_Punchout_Block_Refresh
    extends Mage_Core_Block_Abstract
{

    protected function _toHtml ()
    {
        //return "<meta http-equiv=\"refresh\" content="0;URL='http://example.com/'">"
        return "<meta http-equiv=\"refresh\" content=\"5\">";
    }


}
