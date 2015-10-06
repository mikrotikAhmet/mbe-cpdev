<?php

class Magebuzz_Featuredproducts_Block_Adminhtml_Edit_Renderer_Visibility extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_values;

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        
        $this->_values = Mage::getModel('catalog/product_visibility')->getOptionArray();
        
        $html = $this->_values[$row->getData($this->getColumn()->getIndex())];
      
        return $html;
    }
}
