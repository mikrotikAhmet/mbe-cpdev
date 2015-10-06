<?php

class Vbw_Punchout_Block_Body
    extends Mage_Core_Block_Abstract
{
    /**
     * adds the class to the body. assumes
     * added by xml only in a punchout session.
     * only adds if method exists.
     *
     * @param $argument
     */
    public function addBodyClass ($argument)
    {
        $parent = $this->getParentBlock();
        if (method_exists($parent,'addBodyClass')) {
            $parent->addBodyClass($argument);
        }
    }

    /**

    */
    protected function _toHtml ()
    {
        return "";
    }


}
