<?php
class Magebuzz_Featuredproducts_Model_System_Config_Source_Sort
{
	/*
	 * Prepare data for System->Configuration dropdown
	 * */
	public function toOptionArray()
	{
		return array(
			0 => Mage::helper('adminhtml')->__('Random'),
			1 => Mage::helper('adminhtml')->__('Last Added')
		);
	}
}
