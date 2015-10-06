<?php
class Cpdev_CatalogUpdate_Model_Source_Attributeset
{
    private $_attribute_sets = array();

    public function __construct()
    {
        $this->_attribute_sets = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->addFieldToFilter('entity_type_id', 4)
        ;
    }

    public function toOptionArray()
    {
        $sets = array();

        foreach($this->_attribute_sets as $key => $set)
        {
            $sets[] = array(
                'value' => $set->getAttributeSetId(),
                'label' => $set->getAttributeSetName()
            );
        }

        return $sets;
    }
}
