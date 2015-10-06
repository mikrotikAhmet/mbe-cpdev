<?php
class Vbw_Punchout_Block_Adminhtml_Sales_Stash_Renderer_Unserialize extends
    Mage_Adminhtml_Block_Widget_Grid_Column_renderer_Abstract {

    public function render(Varien_Object $row){
        $value = $row->getData($this->getColumn()->getIndex());
        $uns = unserialize($value);

      //  return var_export($uns, true);
        return $this->_buildString($uns, "");
    }

    private function _buildString($array, $string) {
        $product = null;
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                if (gettype($v) != 'array'){
                    if ($k == 'product') {
                        $product = Mage::getModel('catalog/product')->load($v);
                        $name = $product->getName();
                        $string .= $k . " : " . $name . "<br/>";
                    }  else {
                        $string .= $k . " : " . $v . "<br/>";
                    }
                } else {
                    $string .= $k . "<br/><span style='margin-left:15px'>";
                    $string = $this->_buildString($v, $string);
                    $string .= "</span>";
                }
            }
        }
        return $string;
    }
}
