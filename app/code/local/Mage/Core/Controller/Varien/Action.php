<?php

$original = file_get_contents(Mage::getBaseDir() . '/app/code/core/Mage/Core/Controller/Varien/Action.php');
$original = str_replace("class Mage_Core_Controller_Varien_Action", "class MageActionBase", $original);
$original = preg_replace("/^<\\?php.*$/m", "", $original);
eval($original);

abstract class Mage_Core_Controller_Varien_Action extends MageActionBase
{
    /**
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        return true;
    }
}