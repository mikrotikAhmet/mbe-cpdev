<?php

class Mbemro_Autoexport_Model_Export_Entity_Customer extends Mage_ImportExport_Model_Export_Entity_Customer
{

    public function exportCustomers($mode)
    {
        $collection     = $this->_prepareEntityCollection(Mage::getResourceModel('customer/customer_collection'));
        
        if($mode == 'auto'){
	        $time = Mage::helper('autoexport')->getTime()->getTimestamp();
	        $collection->addFieldToFilter('created_at', array(
					'from'     => $time - Mage::helper('autoexport')->getFromLastHours(),
					'to'       => $time,
					'datetime' => true
			));
        }
        
        list($lastOrderId, $lastCustomerId) = Mage::helper('autoexport')->getLastExportedId();
        
        if($lastCustomerId){
        	$collection->addFieldToFilter('entity_id', array('gt' => $lastCustomerId));
        }
        
        $validAttrCodes = $this->_getExportAttrCodes();
        $writer         = $this->getWriter();
        $defaultAddrMap = Mage_ImportExport_Model_Import_Entity_Customer_Address::getDefaultAddressAttrMapping();

        // prepare address data
        $addrAttributes = array();
        $addrColNames   = array();
        $customerAddrs  = array();

        foreach (Mage::getResourceModel('customer/address_attribute_collection')
                    ->addSystemHiddenFilter()
                    ->addExcludeHiddenFrontendFilter() as $attribute) {
            $options  = array();
            $attrCode = $attribute->getAttributeCode();

            if ($attribute->usesSource() && 'country_id' != $attrCode) {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : array($option) as $innerOption) {
                        if (strlen($innerOption['value'])) { // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = $innerOption['label'];
                        }
                    }
                }
            }
            $addrAttributes[$attrCode] = $options;
            $addrColNames[] = Mage_ImportExport_Model_Import_Entity_Customer_Address::getColNameForAttrCode($attrCode);
        }
        foreach (Mage::getResourceModel('customer/address_collection')->addAttributeToSelect('*') as $address) {
            $addrRow = array();

            foreach ($addrAttributes as $attrCode => $attrValues) {
                if (null !== $address->getData($attrCode)) {
                    $value = $address->getData($attrCode);

                    if ($attrValues) {
                        $value = $attrValues[$value];
                    }
                    $column = Mage_ImportExport_Model_Import_Entity_Customer_Address::getColNameForAttrCode($attrCode);
                    $addrRow[$column] = $value;
                }
            }
            $customerAddrs[$address['parent_id']][$address->getId()] = $addrRow;
        }

        // create export file
        $writer->setHeaderCols(array_merge(
            $this->_permanentAttributes, $validAttrCodes,
            array('password'), $addrColNames,
            array_keys($defaultAddrMap)
        ));
        
        $custumers = array();
        
        foreach ($collection as $itemId => $item) { // go through all customers
            $row = array();
            $custumers[] = $item->getId();
            // go through all valid attribute codes
            foreach ($validAttrCodes as $attrCode) {
                $attrValue = $item->getData($attrCode);

                if (isset($this->_attributeValues[$attrCode])
                    && isset($this->_attributeValues[$attrCode][$attrValue])
                ) {
                    $attrValue = $this->_attributeValues[$attrCode][$attrValue];
                }
                if (null !== $attrValue) {
                    $row[$attrCode] = $attrValue;
                }
            }
            $row[self::COL_WEBSITE] = $this->_websiteIdToCode[$item['website_id']];
            $row[self::COL_STORE]   = $this->_storeIdToCode[$item['store_id']];

            // addresses injection
            $defaultAddrs = array();

            foreach ($defaultAddrMap as $colName => $addrAttrCode) {
                if (!empty($item[$addrAttrCode])) {
                    $defaultAddrs[$item[$addrAttrCode]][] = $colName;
                }
            }
            if (isset($customerAddrs[$itemId])) {
                while (($addrRow = each($customerAddrs[$itemId]))) {
                    if (isset($defaultAddrs[$addrRow['key']])) {
                        foreach ($defaultAddrs[$addrRow['key']] as $colName) {
                            $row[$colName] = 1;
                        }
                    }
                    $writer->writeRow(array_merge($row, $addrRow['value']));

                    $row = array();
                }
            } else {
                $writer->writeRow($row);
            }
        }
        return array($custumers, $writer->getContents());
    }
}
