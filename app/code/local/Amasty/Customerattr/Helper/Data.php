<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function fields($fields = array())
    {
    	    	
        return Mage::app()->getLayout()->createBlock('amcustomerattr/customer_fields')
        	->setData('fields', $fields)
        	->toHtml();
    }
    
    public function getAttributesHash()
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        
        $alias = $this->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
        $collection->addFieldToFilter($alias . 'is_user_defined', 1);
        
        $alias = $this->getProperAlias($collection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $collection->addFieldToFilter($alias . 'is_visible_on_front', 1);
        
        $attributes = $collection->load();
        $hash = array();
        foreach ($attributes as $attribute) {
            $hash[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        return $hash;
    }
    
    public function getAttributeImageUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'amcustomerattr' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . '/' . 'amcustomerattr' . '/' . 'images' . '/' . $optionId . '.jpg';
        }
        return '';
    }
    
    public function getAttributeFileUrl($fileName, $download = false, $front = false, $customerId = null)
    {
        // files directory
        $fileDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 'customer';
        $this->checkAndCreateDir($fileDir);
        if (false === strpos($fileName, DIRECTORY_SEPARATOR)) {
            $fileDir .= DIRECTORY_SEPARATOR . $fileName[0];
            $this->checkAndCreateDir($fileDir);
            $fileDir .= DIRECTORY_SEPARATOR . $fileName[1];
            $this->checkAndCreateDir($fileDir);
        } else {
            $temp = $this->cleanFileName($fileName);
            $tempFileDir = $fileDir . DIRECTORY_SEPARATOR . $temp[1];
            $this->checkAndCreateDir($tempFileDir);
            $tempFileDir = $tempFileDir . DIRECTORY_SEPARATOR . $temp[2];
            $this->checkAndCreateDir($tempFileDir);
        }
        
        if ($download) { // URL for download
            if (file_exists($fileDir . DIRECTORY_SEPARATOR . $fileName)) {
                if ($front) {
                    return Mage::getModel('core/url')->getUrl('amcustomerattrfront/attachment/download', array('customer' => $customerId, 'file' => Mage::helper('core')->urlEncode($fileName)));
                } else {
                    return Mage::helper('adminhtml')->getUrl('adminhtml/customer/viewfile', array('file' => Mage::helper('core')->urlEncode($fileName)));
                }
            }
            return '';
        } else { // Path for upload/download
            return $fileDir . DIRECTORY_SEPARATOR;
        }
    }
    
    public function checkAndCreateDir($path)
    {
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        } 
    }
    
    public function cleanFileName($fileName)
    {
        return explode(DS, $fileName);
    }
    
    public function deleteFile($fileName)
    {
        $fileName = str_replace('/', DS, $fileName);
        @unlink($this->getAttributeFileUrl($fileName) . $fileName);
    }
    
    public function getCorrectFileName($fileName)
    {
        $fileName = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $fileName);

        if (preg_match('/^_+$/', $fileName)) {
            $fileName = uniqid(date('ihs'));
        }
        return $fileName;
    }
    
    public function getFolderName($f)
    {
        $alp = array('p', 'o', 'i', 'u', 'y', 't', 'r', 'e', 'w', 'q', 'm', 'n', 'b', 'v', 'c', 'x', 'z', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l',
                     'P', 'O', 'I', 'U', 'Y', 'T', 'R', 'E', 'W', 'Q', 'M', 'N', 'B', 'V', 'C', 'X', 'Z', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L');
        if (in_array($f, $alp)) {
            return $f;
        }
        return $alp[mt_rand(0, 51)];
    }
    
    public function getFileAttributes($column = '')
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        
        $alias = $this->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
        $collection->addFieldToFilter($alias . 'is_user_defined', 1);
        
        $alias = $this->getProperAlias($collection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $collection->addFieldToFilter($alias . 'type_internal', 'file');
        
        if ($column) {
            $collection->addFieldToFilter($alias . $column, 1);
        }
        
        return $collection;
    }
    
    public function getProperAlias($from, $needTableName)
    {
        $needTableName = Mage::getConfig()->getTablePrefix() . $needTableName;
        foreach ($from as $key => $table) {
            $fullTableName = explode('.', $table['tableName']);
            if (isset($fullTableName[1])) {
                $tableName = $fullTableName[1];
            } else {
                $tableName = $fullTableName[0];
            }
            if ($needTableName == $tableName) {
                return $key . '.';
            }
        }
        return '';
    }
    
    public function getAttributeTypes($asHash = false)
    {
        if ($asHash) {
            return array('text'           => Mage::helper('amcustomerattr')->__('Text Field'),
                         'textarea'       => Mage::helper('amcustomerattr')->__('Text Area'),
                         'date'           => Mage::helper('amcustomerattr')->__('Date'),
                         'multiselect'    => Mage::helper('amcustomerattr')->__('Multiple Select'),
                         'multiselectimg' => Mage::helper('amcustomerattr')->__('Multiple Checkbox Select with Images'),
                         'select'         => Mage::helper('amcustomerattr')->__('Dropdown'),
                         'boolean'        => Mage::helper('amcustomerattr')->__('Yes/No'),
                         'selectimg'      => Mage::helper('amcustomerattr')->__('Single Radio Select with Images'),
                         'selectgroup'    => Mage::helper('amcustomerattr')->__('Customer Group Selector'),
                         'statictext'     => Mage::helper('amcustomerattr')->__('Static Text'),
                         'file'           => Mage::helper('amcustomerattr')->__('Single File Upload'),
                        );
        }
        return array(
            array(
                'value' => 'text',
                'label' => Mage::helper('amcustomerattr')->__('Text Field')
            ),
            array(
                'value' => 'textarea',
                'label' => Mage::helper('amcustomerattr')->__('Text Area')
            ),
            array(
                'value' => 'date',
                'label' => Mage::helper('amcustomerattr')->__('Date')
            ),
            array(
                'value' => 'multiselect',
                'label' => Mage::helper('amcustomerattr')->__('Multiple Select')
            ),
            array(
                'value' => 'multiselectimg',
                'label' => Mage::helper('amcustomerattr')->__('Multiple Checkbox Select with Images')
            ),
            array(
                'value' => 'select',
                'label' => Mage::helper('amcustomerattr')->__('Dropdown')
            ),
            array(
                'value' => 'boolean',
                'label' => Mage::helper('amcustomerattr')->__('Yes/No')
            ),
            array(
                'value' => 'selectimg',
                'label' => Mage::helper('amcustomerattr')->__('Single Radio Select with Images')
            ),
            array(
                'value' => 'selectgroup',
                'label' => Mage::helper('amcustomerattr')->__('Customer Group Selector')
            ),
            array(
                'value' => 'statictext',
                'label' => Mage::helper('amcustomerattr')->__('Static Text')
            ),
            array(
                'value' => 'file',
                'label' => Mage::helper('amcustomerattr')->__('Single File Upload')
            ),
        );
    }
}