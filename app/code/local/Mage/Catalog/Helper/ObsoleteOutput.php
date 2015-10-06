<?php
$original = file_get_contents(Mage::getBaseDir() . '/app/code/core/Mage/Catalog/Helper/Output.php');
$original = str_replace("class Mage_Catalog_Helper_Output", "class MageCatalogHelperOutput", $original);
$original = preg_replace("/^<\\?php.*$/m", "", $original);
eval($original);

class Mage_Catalog_Helper_Output extends MageCatalogHelperOutput
{
    public function getMbemroProductUrl($product)
    {
        $_categories = $product->getCategoryIds();
        // print "productId: "  . $product->getId();
        // print_r($_categories);
        // $lastId = array_pop($_categories);
        // $_category = Mage::getModel('catalog/category')->load($categoryId);
        // $categoryPath = $this->getCategoryPath($_category);
        // print "path: $categoryPath\n";
        $url = Mage::getBaseUrl();
                
        foreach ($_categories as $key => $categoryId) {
            if ($categoryId > 2) {
                $_category = Mage::getModel('catalog/category')->load($categoryId);
                $url .= str_replace(".html", "", $_category->getUrlKey()) . "/";
            }
            
        }
        
        $product = $product->load($product->getId);
        $url .= $product->getUrlPath();

        
        return $url;
    }

    // function getCategoryPath($category){
    //         print 'parent_id:::' .$category->getParentId();
    //         $parent = $category->getParentCategory();
    //         var_dump($category);
    //         var_dump($parent);
    //         print 'id:' . $parent->getId();
    //         $parent_id = $parent->getId() . "/";
    //         if ($parent_id <= 2) {
    //             return "";
    //         }else {
    //             return $parent_id . $this->getCategoryPath($parent);
    //         }
    // }
}
