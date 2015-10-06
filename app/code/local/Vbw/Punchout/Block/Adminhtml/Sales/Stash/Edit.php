<?php

class Vbw_Punchout_Block_Adminhtml_Sales_Stash_Edit extends Mage_Adminhtml_Block_Abstract
{
    protected $_depth = 0;
    protected $_iteration = 0;

    public function _construct() {
        parent::_construct();

        $this->setTemplate('vbw_punchout/stashedit.phtml');
    }

    protected function _buildLineItems($request, $string){
        if ($this->_iteration != 0){
            $string .= "<ul class='line-item-details-list'>";
        }
        foreach ($request as $k => $v) {
            if (gettype($v) != 'array'){
                switch ($k) {
                    case 'product':
                        $product = Mage::getModel('catalog/product')->load($v);
                        $name = $product->getName();
                        $string .= "<li>" . $k . " : " . $name . "</li>";
                        break;
                    case 'id':
                        continue;
                        break;
                    default:
                        $string .= "<li>" . $k . " : " . $v . "</li>";
                        break;
                }
            } else {
                if (count($v) > 0) {
                    $this->_depth++;
                    $string .= "<li><strong>" . $k . "</strong>";
                    $string = $this->_buildLineItems($v, $string);
                    $string .= "</li>";
                }
            }
            $i++;
        }
        if ($this->_depth > 0) {
           $this->_depth--;
        }
        if ($this->_iteration != 0){
            $string .= "</ul>";
        }
        $this->_iteration++;

        return $string;
    }
}
