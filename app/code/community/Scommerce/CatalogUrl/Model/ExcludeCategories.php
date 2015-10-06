<?php
/**
 * Category list
 *
 * @category   Scommerce
 * @package    Scommerce_CatalogUrl
 * @author     Sommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_CatalogUrl_Model_ExcludeCategories {

	/**
     * get the list of all categories
     */
    public function getCategories() {
        $categoryOption = array(array('value' => '','label'=>''));
        $categories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToFilter('level',1)
                ->addAttributeToSelect('name')
                ->load();
		
        foreach ($categories as $cat):
            $temp = array('value' => $cat->getId(), 'label' => $cat->getName());
            array_push($categoryOption, $temp);
        endforeach;
        return $categoryOption;
    }

    /**
     * return the list of categories from getCategories fuction
     */
    public function toOptionArray() {
        return ($this->getCategories());
    }

}
?>
