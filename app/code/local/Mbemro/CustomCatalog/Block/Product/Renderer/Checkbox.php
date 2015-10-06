<?php

class Mbemro_CustomCatalog_Block_Product_Renderer_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {

        $value =  $row->getData($this->getColumn()->getIndex());
        $checked = (in_array($value, array(1, true)));

        return $this->checkbox($checked);

    }

    private function checkbox($checked, $name='', $id='')
    {
        return sprintf('<input type="checkbox" name="%s" id="%s" %s disabled />',
            $name,
            $id,
            ($checked === true ? 'checked' : '')
        );
    }

}