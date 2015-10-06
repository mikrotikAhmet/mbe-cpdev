<?php

class Mbemro_CustomCatalog_Block_Product_Renderer_Number extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
	public function render(Varien_Object $row)
	{

		$value =  $row->getData($this->getColumn()->getIndex());
		if ($value == 0.00) {
			return '';
		}
		return number_format($value, 2);
		 
	}
 
}