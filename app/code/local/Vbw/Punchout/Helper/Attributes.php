<?php



class Vbw_Punchout_Helper_Attributes
	extends Mage_Core_Helper_Url
{

    static $_attributeCache = array ();

    static $_categoryEntityCache = null;


    /**
     * Get the attribute label for a product.
     *
     * @return url
     */
    public function getAttributeOptionLabel($arg_attribute, $arg_value)
    {
        if (isset(self::$_attributeCache[$arg_attribute])) {
            $options = self::$_attributeCache[$arg_attribute];
        } else {
            $attribute_model        = Mage::getModel('eav/entity_attribute');
            $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

            $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
            $attribute              = $attribute_model->load($attribute_code);

            $attribute_table        = $attribute_options_model->setAttribute($attribute);
            $options                = $attribute_options_model->getAllOptions(false);
            self::$_attributeCache[$arg_attribute] = $options;
        }
        foreach($options as $option) {
            if ($option['value'] == $arg_value) {
                return $option['label'];
            }
        }

        return false;
        // Read more at http://www.danneh.org/2011/01/getting-value-of-attribute-option-and-adding-a-new-attribute-option-in-magento/#ixzz1ouWK1x5V
    }


    /**
     * this should only be used with know valid data,
     * no validation is applied.
     *
     * @param $data
     * @param null $id
     * @return mixed
     */
    public function getAttributeObjectData ($data,$type = Mage_Catalog_Model_Product::ENTITY)
    {

        $typeId = Mage::getModel('eav/entity')->setType($type)->getTypeId();

        /* @var $model Mage_Catalog_Model_Entity_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');
        /* @var $helper Mage_Catalog_Helper_Product */
        $helper = Mage::helper('catalog/product');

        if (!isset($data['source_model'])) {
            $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
        }
        if (!isset($data['backend_model'])) {
            $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
        }
        if (!isset($data['is_configurable'])) {
            $data['is_configurable'] = 0;
        }
        if (!isset($data['is_filterable'])) {
            $data['is_filterable'] = 0;
        }
        if (!isset($data['is_filterable_in_search'])) {
            $data['is_filterable_in_search'] = 0;
        }
        // if isUserDefined is not set, or if it does not == 0 (ie eq, 1), is user defined.
        if (!isset($data['backend_type'])) {
            if (is_null($model->getIsUserDefined())
                || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }
        }

        if (!isset($data['default_value'])) {
            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                if (isset($data[$defaultValueField])) {
                    $data['default_value'] = $data[$defaultValueField];
                }
            }
        }

        if(!isset($data['apply_to'])) {
            $data['apply_to'] = array();
        }

        $data['entity_type_id'] = $typeId;
        $data['is_user_defined'] = 1;

        return $data;

    }



    /**
     *
     */
    public function getAttributeSetGroups ($setId)
    {
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->setAttributeSetFilter($setId);
            if (method_exists($groups, 'setSortOrder')) {
                $groups->setSortOrder();
            }
            $groups->load();
        return $groups;
    }

    /*
    public function getAttributeSetGroupAttributes ($groupId)
    {
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();
        return $groups;
    }
    */

    public function getCategoryAttributeSets ()
    {
        $entityType = $this->getEntityCatalog();
        return $entityType->getAttributeSetCollection();
    }

    public function getAttributeCollectionCategory ()
    {
        $entityType = $this->getEntityCatalog();
        return $entityType->getAttributeCollection();
    }

    public function findAttributeInSet ($setObj,$attrId)
    {
        $groups = $this->getAttributeSetGroups($setObj->getId());
        foreach ($groups AS $group) {
            $items = $this->getAttributeSetGroupAttributes($group->getId());
            foreach ($items AS $item) {

            }
        }
    }

    public function findAttributeCodeInSet ($code)
    {
        $model = Mage::GetModel('eav/entity_setup','core_setup');
        $entity = $this->getEntityCatalog();
        $conn = $entity->getResource()->getReadConnection();

        $select = $conn->select()
            ->from($model->getTable('eav/entity_attribute'),array('*'))
            ->where('attribute_id = ?',$code)
            ->where('entity_type_id = ?',$entity->getEntityTypeId());
        $result = $conn->fetchAll($select);
        if (!empty($result)) {
            return $result[0];
        }
        return false;
    }

    public function addAttributeCodeToDefaultSet ($code)
    {
        /**
         *@var $defaultSet Mage_Eav_Entity_Attribute_Set
         */
        $entity = $this->getEntityCatalog();
        $default = $entity->getDefaultAttributeSetId();
        if ($default === null) {
            $sets = $entity->getAttributeSetCollection();
            $defaultSet = $sets->current();
        } else {
            $defaultSet = Mage::getModel('eav/entity_attribute_set')->load($default);
        }
        $groups = $this->getAttributeSetGroups($defaultSet->getId());
        foreach ($groups AS $group) {
            if ($group->getDefaultId() == 1) {
                $defaultGroup = $group;
            }
        }

        $model = Mage::GetModel('eav/entity_attribute')->load($code);
        $model->setAttributeSetId($defaultSet->getId());
        $model->setAttributeGroupId($defaultGroup->getId());
        $model->getResource()->saveInSetIncluding($model);
        //$model->addAttributeToSet($entity->getEntityTypeId(), $defaultSet->getId(), $defaultGroup->getId(), $code);
        return $this->findAttributeCodeInSet($code);
    }

    /**
     * @depricated
     * @return object
     */
    public function getCategoryEntityAttributes ()
    {
        $entity = $this->getEntityCatalog();
        $coll = Mage::GetModel('eav/eav_entity_attribute')->getCollection();
        $coll->setEntityTypeFilter($entity->getId());
        //$setId       = (int) $object->getAttributeSetId();
        //$groupId     = (int) $object->getAttributeGroupId();
        return $coll;
    }

    /**
     *
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityCatalog ()
    {
        if (self::$_categoryEntityCache === null) {
            self::$_categoryEntityCache = Mage::getModel('eav/entity_type')->loadByCode(Mage_Catalog_Model_Category::ENTITY);
        }
        return self::$_categoryEntityCache;
    }

}
