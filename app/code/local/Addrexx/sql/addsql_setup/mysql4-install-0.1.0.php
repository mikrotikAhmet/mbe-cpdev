<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
UPDATE customer_eav_attribute
LEFT JOIN eav_attribute ON 
    eav_attribute.attribute_id = customer_eav_attribute.attribute_id
SET customer_eav_attribute.sort_order = 110
WHERE eav_attribute.attribute_code = 'street';

UPDATE customer_eav_attribute
LEFT JOIN eav_attribute ON 
    eav_attribute.attribute_id = customer_eav_attribute.attribute_id
SET customer_eav_attribute.sort_order = 100
WHERE eav_attribute.attribute_code = 'region';

UPDATE customer_eav_attribute
LEFT JOIN eav_attribute ON 
    eav_attribute.attribute_id = customer_eav_attribute.attribute_id
SET customer_eav_attribute.sort_order = 100
WHERE eav_attribute.attribute_code = 'region_id';

UPDATE customer_eav_attribute
LEFT JOIN eav_attribute ON 
    eav_attribute.attribute_id = customer_eav_attribute.attribute_id
SET customer_eav_attribute.sort_order = 90
WHERE eav_attribute.attribute_code = 'city';

UPDATE customer_eav_attribute
LEFT JOIN eav_attribute ON 
    eav_attribute.attribute_id = customer_eav_attribute.attribute_id
SET customer_eav_attribute.sort_order = 80
WHERE eav_attribute.attribute_code = 'postcode';

UPDATE customer_eav_attribute
LEFT JOIN eav_attribute ON 
    eav_attribute.attribute_id = customer_eav_attribute.attribute_id
SET customer_eav_attribute.sort_order = 70
WHERE eav_attribute.attribute_code = 'country_id';
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 