<?php
/**
 * Category list
 *
 * @category   Scommerce
 * @package    Scommerce_CatalogUrl
 * @author     Sommerce Mage <core@scommerce-mage.co.uk>
 */
class Scommerce_CatalogUrl_Model_Entity_Attribute_Source_Categories extends Mage_Eav_Model_Entity_Attribute_Source_Table
{	
	/**
     * get the list of all categories
     */
    public function getCategories() {
        $categoryOption = array('' => Mage::helper('scommerce_catalogurl')->__('Please select primary category'));
		$categoryIds = array();
		$excludedRootCategories = array();
		
		//if product exists then retrieve only associated categories 
		$product = Mage::registry('current_product');
		if ($product) $categoryIds = $product->getCategoryIds();

		//getting all the categories for the particular store and sorting them by level and parent_id
        $categories = Mage::getModel('catalog/category')->getCollection()
							->addAttributeToSelect('name')
							->setStoreId(Mage::app()->getRequest()->getParam('store'))
							->addAttributeToSort('level', 'ASC')
							->addAttributeToSort('parent_id', 'ASC');
							
		//retrieve excluded categories selected in system configuration settings and if selection found apply it to filter category collection
		$excludeCategories = Mage::helper('scommerce_catalogurl')->getExcludedCategoryIds();
		if (strlen($excludeCategories)>0) $excludedRootCategories = split(',',$excludeCategories);
		if (!empty($excludedRootCategories)) $categories = $categories->addAttributeToFilter('entity_id', array('nin' => $excludedRootCategories));
		
		//all making sure that none of the child get loaded for excluded root categories
		$sqlQuery = '';
		foreach($excludedRootCategories as $excat){
			$sqlQuery = $sqlQuery . ' instr(path,'.$excat.')=0 and ';
		}
		if ($sqlQuery){
			$sqlQuery = substr($sqlQuery,0,strlen($sqlQuery)-5);
			$categories->getSelect()->where($sqlQuery);
		}
		
		//if product has categories then load only those categories
		if (!empty($categoryIds)) $categories = $categories->addAttributeToFilter('entity_id', array('in' => $categoryIds));
		
		//load category collection at the end to apply all the above filters
		$categories=$categories->load();
		
		//loop through loaded categories collection 
		foreach ($categories as $cat):
			//loading collection with all the parent category ids using getPathIds, in simple terms it will load all parent categories associated with this category 
			$collection = $cat->getResourceCollection();
			$collection->addAttributeToSelect('name')
				->setStoreId(Mage::app()->getRequest()->getParam('store'))
				->addAttributeToFilter('entity_id', array('in' => $cat->getPathIds()))
				->addAttributeToSort('level', 'ASC');
				
			$fullCategoryPath = '';
			
			//concatenating the whole path using name instead of id 
			foreach ($collection as $col) {
				if (strlen($col->getName())) $fullCategoryPath .= $col->getName().' -> ';
			}
			if ($fullCategoryPath) $fullCategoryPath = substr($fullCategoryPath,0,strlen($fullCategoryPath)-4);
			
			if (strlen($fullCategoryPath)>0){
				$temp = array('value' => $cat->getId(), 'label' => $fullCategoryPath);
				array_push($categoryOption, $temp);
			}			
        endforeach;
		
        return $categoryOption;
    }

    /**
     * return the list of categories from getCategories function
     */
    public function getAllOptions() {
        return ($this->getCategories());
    }


}
?>
