<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'product_primary_category', array(
        'label'          => 'Primary Category',
        'type'           => 'int',
        'input'          => 'select',
        'backend'        => 'eav/entity_attribute_backend_array',
        'frontend'       => '',
        'source'         => 'scommerce_catalogurl/entity_attribute_source_categories',
        'global'         => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'        => true,
        'required'       => false,
        'group'          => 'Primary Category',
        'user_defined'   => true,
        'note'           => 'Choose the primary category for this product, this will be the category which will be used in product URL for the whole store',
    )
);

$installer->endSetup();