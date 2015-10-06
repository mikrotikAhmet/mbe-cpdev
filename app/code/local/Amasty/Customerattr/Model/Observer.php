<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/ 
class Amasty_Customerattr_Model_Observer
{
    /**
     * Add columns (if `Show on Orders Grid` set to `Yes`) to the Orders Grid.
     * @param Varien_Event_Observer $observer
     */    
    public function modifyOrderGrid($observer)
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout)
            return;
        
        $permissibleActions = array('index', 'grid');
        if ( false === strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order') || 
             !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) )
            return;
        
        $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'used_in_order_grid = ?', 1)
            ->order($alias . 'sorting_order');
        
        $grid = $layout->getBlock('sales_order.grid'); // Mage_Adminhtml_Block_Sales_Order_Grid
        if ( ($attributesCollection->getSize() > 0) && ($grid) ) {
            $after = 'grand_total';
            foreach ($attributesCollection as $attribute) {
                $column = array();
                switch ($attribute->getFrontendInput())
                {
                    case 'date':
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'type'         => 'date',
                            'align'        => 'center',
                            'gmtoffset'    => true
                        );
                        break;
                    case 'select':
                    case 'selectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                        );
                        break;
                    case 'multiselect':
                    case 'multiselectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                        );
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                        );
                        break;
                    default:
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'sortable'     => true,
                        );
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $column['renderer'] = 'amcustomerattr/adminhtml_renderer_file';
                }
                $grid->addColumnAfter($attribute->getAttributeCode(), $column, $after); // Mage_Adminhtml_Block_Widget_Grid
                $after = $attribute->getAttributeCode();
            }
        }
    }
    
    /**
     * Join columns to the Orders Collection.
     * @param Varien_Event_Observer $observer
     */
    public function modifyOrderCollection($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $tableNameCustomerEntity = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
        
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'used_in_order_grid = ?', 1);
        
        if ($attributesCollection->getSize() > 0) {
            foreach ($attributesCollection as $attribute) {
                $collection->getSelect()
                    ->joinLeft(array('_table_'.$attribute->getAttributeCode() => $tableNameCustomerEntity.'_'.$attribute->getBackendType()),
                               '_table_'.$attribute->getAttributeCode().'.entity_id = main_table.customer_id ' .
                               ' AND _table_'.$attribute->getAttributeCode().'.attribute_id = '.$attribute->getAttributeId(),
                               array($attribute->getAttributeCode() => '_table_'.$attribute->getAttributeCode().'.value')
                               );
            }
        }
    }
    
    /**
     * Handler for event `controller_action_layout_render_before_adminhtml_customer_index`.
     * @param Varien_Event_Observer $observer
     */
    public function forIndexCustomerGrid($observer)
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout)
            return;
        
        $permissibleActions = array('index', 'grid');
        if ( false === strpos(Mage::app()->getRequest()->getControllerName(), 'customer') || 
             !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) )
            return;
        
        $grid = $layout->getBlock('customer.grid');
        $grid = $this->_modifyCustomerGrid($grid);
    }
    
    /**
     * Handler for event `core_layout_block_create_after`.
     * @param Varien_Event_Observer $observer
     */
    public function forSearchCustomerGrid($observer)
    {
        if ('index' === Mage::app()->getRequest()->getActionName())
            return;
        
        $grid = $observer->getBlock();
        if ($grid instanceof Mage_Adminhtml_Block_Customer_Grid) {
            $grid = $this->_modifyCustomerGrid($grid);
        }
    }
    
    /**
     * Add columns (if `Show on Manage Customers Grid` set to `Yes`) to the Manage Customers Grid.
     * @param Varien_Event_Observer $observer
     */
    protected function _modifyCustomerGrid($grid)
    {
        $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
        
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_filterable_in_search = ?', 1) // `is_filterable_in_search` used to setting `Show on Manage Customers Grid`
            ->order($alias . 'sorting_order');
        
        if ( ($attributesCollection->getSize() > 0) && ($grid) ) {
            if (!Mage::app()->isSingleStoreMode()) {
                $after = 'website_id';
            } else {
                $after = 'customer_since';
            }
            foreach ($attributesCollection as $attribute) {
                $column = array();
                switch ($attribute->getFrontendInput())
                {
                    case 'date':
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'type'         => 'date',
                            'align'        => 'center',
                            'gmtoffset'    => true
                        );
                        break;
                    case 'select':
                    case 'selectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                        );
                        break;
                    case 'multiselect':
                    case 'multiselectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                        );
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                        );
                        break;
                    default:
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'sortable'     => true,
                        );
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $column['renderer'] = 'amcustomerattr/adminhtml_renderer_file';
                }
                $grid->addColumnAfter($attribute->getAttributeCode(), $column, $after); // Mage_Adminhtml_Block_Widget_Grid
                $after = $attribute->getAttributeCode();
            }
        }
        return $grid;
    }
    
    /**
     * Join columns to the Customers Collection.
     * @param Varien_Event_Observer $observer
     */
    public function modifyCustomerCollection($observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Customer_Model_Entity_Customer_Collection || $collection instanceof Mage_Customer_Model_Resource_Customer_Collection) {
            $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'is_user_defined = ?', 1)
                ->where($alias . 'attribute_code != ?', 'customer_activated');
                
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'is_filterable_in_search = ?', 1);
            
            if ($attributesCollection->getSize() > 0) {
                foreach ($attributesCollection as $attribute) {
                    $collection->addAttributeToSelect($attribute->getAttributeCode());
                }
            }
        }
    }
    
    public function handleBlockOutput($observer) 
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        
        if ($block instanceof Mage_Customer_Block_Form_Register) {
            if (Mage::helper('amcustomerattr')->getFileAttributes('on_registration')->getSize() > 0) {
                $html = str_replace('id="form-validate"', ' id="form-validate" enctype="multipart/form-data" ', $html);
            }
            if (false === strpos($html, 'amcustomerattr')) {
                $pos = strpos($html, '<div class="buttons-set">');
                $insert = Mage::helper('amcustomerattr')->fields();
                $html = substr_replace($html, $insert, $pos-1, 0);
            }
            $transport->setHtml($html);
        }
        
        if ($block instanceof Mage_Customer_Block_Form_Edit) {
            if (Mage::helper('amcustomerattr')->getFileAttributes()->getSize() > 0) {
                $html = str_replace('id="form-validate"', ' id="form-validate" enctype="multipart/form-data" ', $html);
            }
            if (false === strpos($html, 'amcustomerattr')) {
                $pos = strpos($html, '<div class="buttons-set">');
                $insert = Mage::helper('amcustomerattr')->fields();
                $html = substr_replace($html, $insert, $pos-1, 0);
            }
            $transport->setHtml($html);
        }
        
        if ($block instanceof Mage_Checkout_Block_Onepage_Billing) {
            if (false === strpos($html, 'amcustomerattr')) {
                $pos = strpos($html, '<li class="fields" id="register-customer-password">');
                $insert = Mage::helper('amcustomerattr')->fields();
                $html = substr_replace($html, $insert, $pos+51, 0);
                $transport->setHtml($html);
            }
        }
    }
    
    public function onCoreLayoutBlockCreateAfter($observer)
    {
        $block = $observer->getBlock();
        // Order Grid
        $permissibleActions = array('exportCsv', 'exportExcel');
        if (($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid || $block instanceof EM_DeleteOrder_Block_Adminhtml_Sales_Order_Grid) &&
            (in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions))) {
            // Customer Attributes
            $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'is_user_defined = ?', 1)
                ->where($alias . 'attribute_code != ?', 'customer_activated');
                
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'used_in_order_grid = ?', 1)
                ->order($alias . 'sorting_order');
            
            if ( ($attributesCollection->getSize() > 0) && ($block) ) {
                $after = 'grand_total';
                foreach ($attributesCollection as $attribute) {
                    $column = array();
                    switch ($attribute->getFrontendInput())
                    {
                        case 'date':
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'type'         => 'date',
                                'align'        => 'center',
                                'gmtoffset'    => true
                            );
                            break;
                        case 'select':
                        case 'selectimg':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                            );
                            break;
                        case 'multiselect':
                        case 'multiselectimg':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                                'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                                'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                            );
                            break;
                        case 'boolean':
                            $options = array(0 => 'No', 1 => 'Yes');
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                                'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                            );
                            break;
                        default:
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'sortable'     => true,
                            );
                            break;
                    }
                    if ('file' == $attribute->getTypeInternal()) {
                        $column['renderer'] = 'amcustomerattr/adminhtml_renderer_file';
                    }
                    $block->addColumnAfter($attribute->getAttributeCode(), $column, $after); // Mage_Adminhtml_Block_Widget_Grid
                    $after = $attribute->getAttributeCode();
                }
            }
        }
    }
}
