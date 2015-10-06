<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Associated_Apparel extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Simple_Apparel {

    /**
     * @return $this
     */
    public function _beforeMap() {
    	return $this;
    }

    /**
     * Overriden here because subclass implements custom skip code, which we don't want in this class
     *
     * @param $rows
     * @return array $rows
     */
    public function _afterMap($rows) {
        return $rows;
    }

    /**
     * @param string $column
     * @return string
     */
    public function mapColumn($column) {

        if (!array_key_exists($column, $this->_columns_map)) {
            return "";
        }

        // Return from cache as there are few columns like price, sale_price and shipping who would share the same info.
        if (array_key_exists($column, $this->_cache_map_values) && !empty($this->_cache_map_values[$column])) {
            return $this->_cache_map_values[$column];
        }

        $arr = $this->_columns_map[$column];
        $args = array('map' => $arr);

        // Run overwrite attributes only for apparel
        $overwriteAttributes = explode(',', $this->getConfigVar('attribute_overwrites', 'apparel'));
        if(array_key_exists('attribute', $args['map']) && in_array($args['map']['attribute'], $overwriteAttributes)) {
            return $this->getParentMap()->mapColumn($column);
        }

        /*
           Column methods are required in a few cases.
           e.g. When child needs to get value from parent first. Further if value is empy takes value from it's own mapColumn* method.
           Can loop infinitely if misused.
        */
        $method = 'mapColumn'.$this->_camelize($column);
        if (method_exists($this, $method)) {
            $value = $this->$method($args);
        } else {
            $value = $this->getCellValue($args);
        }

        // Run replace empty rules if no value so far
        if ($value == '') {
            foreach ($this->_empty_columns_replace_map as $arr) {
                if ($column == $arr['column']) {
                    $args = array('map' => $arr);
                    $method = 'mapAttribute'.$this->_camelize($arr['attribute']);
                    if (method_exists($this, $method)) {
                        $value = $this->$method($args);
                    } else {
                        $value = $this->mapAttribute($args);
                    }
                }
            }
        }

        $this->_cache_map_values[$column] = $value;
        return $value;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnDescription($params = array()) {

		$args = array('map' => $params['map']);

        switch ($this->getConfigVar('associated_products_description', 'columns')) {
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_ASSOCIATED:
                $value = $this->getCellValue($args);
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_CONFIGURABLE:
                $value = $this->getParentMap()->mapColumn('description');
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_CONFIGURABLE_ASSOCIATED:
                $value = $this->getParentMap()->mapColumn('description');
                if ($value == "")
                    $value = $this->getCellValue($args);
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsdesc::FROM_ASSOCIATED_CONFIGURABLE:
                $value = $this->getCellValue($args);
                if ($value == "")
                    $value = $this->getParentMap()->mapColumn('description');
                break;

            default:
                $value = $this->getCellValue($args);
                if ($value == "")
                    $value = $this->getParentMap()->mapColumn('description');

        }

        return $value;
	}

    /**
     * @param array $params
     * @return mixed|string
     */
    public function mapDirectiveUrl($params = array()) {

        $args = array('map' => $params['map']);
        $product = $this->getProduct();

        switch ($this->getConfigVar('associated_products_link', 'columns')) {
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodslink::FROM_CONFIGURABLE:
                $value = $this->getParentMap()->mapColumn($args['map']['column']);
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodslink::FROM_ASSOCIATED_CONFIGURABLE:
                if ($product->isVisibleInSiteVisibility()) {
                    return parent::mapDirectiveUrl($params);
                } else {
                    $value = $this->getParentMap()->mapColumn($args['map']['column']);
                }
                break;

            default:
                $value = $this->getParentMap()->mapColumn($args['map']['column']);
        }

        $typeId = $this->getParentMap()->getProduct()->getTypeId();
        $linkAddUnique = $this->getConfigVar('associated_products_link_add_unique', 'columns') && $this->getParentMap()
            && ($typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE || $typeId == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);

        if ($linkAddUnique) {
            $value = $this->addUrlUniqueParams($value, $this->getProduct(), $this->getParentMap()->getOptionCodes(), $typeId);
        }

        return $value;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnImageLink($params = array()) {

        $args = array('map' => $params['map']);

        switch ($this->getConfigVar('associated_products_image_link_configurable', 'columns')) {
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_CONFIGURABLE:
                $value = $this->getParentMap() ? $this->getParentMap()->mapColumn('image_link') : '';
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_ASSOCIATED:
                $value = $this->getCellValue($args);
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_ASSOCIATED_CONFIGURABLE:
                $value = $this->getCellValue($args);
                if ($value == '' && $this->getParentMap()) {
                    $value = $this->getParentMap()->mapColumn('image_link');
                }
                break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Source_Assocprodsimagelink::FROM_CONFIGURABLE_ASSOCIATED:
                $value = $this->getParentMap() ? $this->getParentMap()->mapColumn('image_link') : '';
                if ($value == '') {
                    $value = $this->getCellValue($args);
                }
                break;

            default:
                $value = $this->getCellValue($args);
                if ($value == '' && $this->getParentMap()) {
                    $value = $this->getParentMap()->mapColumn('image_link');
                }
        }

        return $value;
    }

    /**
     * @param null $product
     * @return mixed
     */
    public function getPrice($product = null) {

		if (is_null($product)) {
			$product = $this->getProduct();
		}
		return $this->getParentMap()->getCacheAssociatedPrice($product->getId());
	}

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnAvailability($params = array()) {

		$args = array('map' => $params['map']);
    	$value = $this->getParentMap()->mapColumn('availability');

        // Gets out of stock if parent is out of stock
        if ($this->getConfigVar('inherit_parent_out_of_stock', 'settings') && strcasecmp($this->getConfig()->getOutOfStockStatus(), $value) == 0) {
    		return $value;
        }
    	
    	return $this->getCellValue($args);
	}

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnBrand($params = array()) {

		$args = array('map' => $params['map']);
    	
    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('brand');
    	if ($value != "")
    		return $value;
    	
    	return $this->getCellValue($args);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnGoogleProductCategory($params = array()) {

		$args = array('map' => $params['map']);
    	
    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('google_product_category');

        // Grab it from attribute
        if (empty($value)) {
            $value = $this->mapAttribute($args);
        }

        // Grab it from by category map
        if (empty($value)) {
            $map_by_category = $this->getConfig()->getMapCategorySorted('google_product_category_by_category', $this->getStoreId());
            $category_ids = $this->getProduct()->getCategoryIds();
            if (empty($category_ids)) {
                $category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
            }
            $value = $this->matchByCategory($map_by_category, $category_ids);
        }

        // Grab it from directive
        if (empty($value)) {
            $value = $this->getCellValue($args);
        }

        $this->_findAndReplace($value, $params['map']['column']);
		return html_entity_decode($value);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnProductType($params = array()) {

		$args = array('map' => $params['map']);

    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('product_type');
    	if ($value != "")
    		return html_entity_decode($value);
    	
    	if ($value == "") {
    		$value = $this->getCellValue($args);
    		if ($value != "") {
    			return html_entity_decode($value);
    		}
    	}
    	
    	$map_by_category = $this->getConfig()->getMapCategorySorted('product_type_by_category', $this->getStoreId());
    	$category_ids = $this->getProduct()->getCategoryIds();
    	if (empty($category_ids))
    		$category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
    	if (!empty($category_ids) && count($map_by_category) > 0) {
    		foreach ($map_by_category as $arr) {
    			if (array_search($arr['category'], $category_ids) !== false) {
    				$value = $arr['value'];
                    $this->_findAndReplace($value, $params['map']['column']);
    				break;
    			}
    		}
    	}

		return html_entity_decode($value);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnAdwordsGrouping($params = array()) {

        $args = array('map' => $params['map']);

        // get value from parent first
        $value = $this->getParentMap()->mapColumn('adwords_grouping');
        if ($value != "")
                return html_entity_decode($value);

        if ($value == "") {
            $value = $this->getCellValue($args);
            if ($value != "") {
                return html_entity_decode($value);
            }
        }

        $map_by_category = $this->getConfig()->getMapCategorySorted('adwords_grouping_by_category', $this->getStoreId());
        $category_ids = $this->getProduct()->getCategoryIds();
        if (empty($category_ids))
            $category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
        if (!empty($category_ids) && count($map_by_category) > 0) {
            foreach ($map_by_category as $arr) {
                if (array_search($arr['category'], $category_ids) !== false) {
                    $value = $arr['value'];
                    $this->_findAndReplace($value, $params['map']['column']);
                    break;
                }
            }
        }

        return html_entity_decode($value);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnAdwordsLabels($params = array()) {

        $args = array('map' => $params['map']);

        // get value from parent first
        $value = $this->getParentMap()->mapColumn('adwords_labels');
        if ($value != "")
                return html_entity_decode($value);

        if ($value == "") {
            $value = $this->getCellValue($args);
            if ($value != "") {
                return html_entity_decode($value);
            }
        }

        $map_by_category = $this->getConfig()->getMapCategorySorted('adwords_labels_by_category', $this->getStoreId());
        $category_ids = $this->getProduct()->getCategoryIds();
        if (empty($category_ids))
            $category_ids = $this->getParentMap()->getProduct()->getCategoryIds();
        if (!empty($category_ids) && count($map_by_category) > 0) {
            foreach ($map_by_category as $arr) {
                if (array_search($arr['category'], $category_ids) !== false) {
                    $value = $arr['value'];
                    $this->_findAndReplace($value, $params['map']['column']);
                    break;
                }
            }
        }

        return html_entity_decode($value);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapDirectiveApparelItemGroupId($params = array()) {
		return $this->getParentMap()->mapColumn('id');
	}

    /**
     * @param array $params
     * @return mixed|string
     */
    public function mapDirectiveApparelColor($params = array()) {

        $attributes_codes = $this->getConfig()->getMultipleSelectVar('color_attribute_code', $this->getStoreId(), 'apparel');
        $cell = $this->_mapDirectiveApparel($params, $attributes_codes);
		
		// Multi-select attributes - comma replaced with /
		return str_replace(",", "/", $cell);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveApparelSize($params = array()) {

        $attributes_codes = $this->getConfig()->getMultipleSelectVar('size_attribute_code', $this->getStoreId(), 'apparel');
        $cell = $this->_mapDirectiveApparel($params, $attributes_codes);

		// Try get from parent configurable, may be a non superattribute value.
		if ($cell == "") {
			$cell = $this->getParentMap()->mapColumn('size');
        }

		return $cell;
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveApparelMaterial($params = array()) {

        $attributes_codes = $this->getConfig()->getMultipleSelectVar('variant_material_attribute_code', $this->getStoreId(), 'apparel');
        return $this->_mapDirectiveApparel($params, $attributes_codes);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveApparelPattern($params = array()) {

        $attributes_codes = $this->getConfig()->getMultipleSelectVar('variant_pattern_attribute_code', $this->getStoreId(), 'apparel');
        return $this->_mapDirectiveApparel($params, $attributes_codes);
	}

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnGender($params = array()) {

		$args = array('map' => $params['map']);

    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('gender');
    	if ($value != "")
    		return $value;
    	
    	return $this->getCellValue($args);
	}

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnAgeGroup($params = array()) {

		$args = array('map' => $params['map']);
    	
    	// get value from parent first
    	$value = $this->getParentMap()->mapColumn('age_group');
    	if ($value != "")
    		return $value;
    	
    	return $this->getCellValue($args);
	}
    
    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnSizeType($params = array())
    {
        $args = array('map' => $params['map']);

        // get value from parent first
        $value = $this->getParentMap()->mapColumn('size_type');
        if ($value != "") {
    		return $value;
        }
        return $this->getCellValue($args);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnSizeSystem($params = array())
    {
        $args = array('map' => $params['map']);

        // get value from parent first
        $value = $this->getParentMap()->mapColumn('size_system');
        if ($value != "") {
    		return $value;
        }
        return $this->getCellValue($args);
    }
}
