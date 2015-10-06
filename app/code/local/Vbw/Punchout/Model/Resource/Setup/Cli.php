<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cli
 *
 * @author shawnmck
 */
class Vbw_Punchout_Model_Resource_Setup_Cli {

    /**
     *
     * @var Vbw_Punchout_Helper_Config
     */
    protected $_configHelper = null;


    /**
     * return the config helper which is used to access configurations
     * related to the module.
     *
     * @return Vbw_Punchout_Helper_Config
     */
    public function getConfigHelper()
    {
        if ($this->_configHelper == null)  {
            $this->_configHelper = Mage::helper('vbw_punchout/config');
        }
        return $this->_configHelper;
    }

    /**
     * get a config value, this will hopefully be modified
     * with a later version that allows the admin to update.
     *
     * @param string $xpath
     * @return mixed
     */
    public function getConfig($xpath)
    {
        return $this->getConfigHelper()->getConfig($xpath);
    }

    public function start ()
    {
        if ($this->confirm()) {
            $this->printline("Great, then we will get started.");

            $this->runUnspsc();

            $this->runCategoryExport();


        } else {
            $this->printline("Okay, nothing was changed.");
        }
        $this->printline('Done.');
    }

    public function runUnspsc ()
    {
        $this->printline("Checking category UNSPSC.");
        if (false !== $code = $this->checkCategoryUnspc()) {
            $this->printline("Your UNSPSC field is already configured. ({$code})");
        } else {
            $this->printline('Your UNSPSC field is not yet configured');
            if ($this->confirmCategoryUnspsc()) {
                if (false != $code = $this->createCategoryUnspsc()) {
                    $this->printline("Your UNSPSC field has been configured.");
                } else {
                    $this->printline('Error : Your uspsc was not created.');
                }
            }
        }
        if (is_numeric($code)) {
            $this->printline("Checking category UNSPSC placement");
            if (false != $data = $this->checkCategoryUnspscPlacement($code)) {
                $this->printline("Your UNSPSC field is already in a form.");
            } else {
                $this->printline('Your UNSPSC field is not yet in a form');
                if ($this->confirmCategoryUnspscPlacement()) {
                    if (false != $data = $this->createCategoryUnspscPlacement($code)) {
                        $this->printline("Your UNSPSC field placement has been configured.");
                    } else {
                        $this->printline('Error : Your uspsc was not added to a form.');
                    }
                }
            }
        }
    }

    public function runCategoryExport ()
    {
        $this->printline("Checking category Level 2 Export.");
        if (false !== $code = $this->checkCategoryExport()) {
            $this->printline("Your Export field is already configured. ({$code})");
        } else {
            $this->printline('Your Export field is not yet configured');
            if ($this->confirmCategoryExport()) {
                if (false != $code = $this->createCategoryExport()) {
                    $this->printline("Your Export field has been configured.");
                } else {
                    $this->printline('Error : Your uspsc was not created.');
                }
            }
        }
        if (is_numeric($code)) {
            $this->printline("Checking category Export placement");
            if (false != $data = $this->checkCategoryExportPlacement($code)) {
                $this->printline("Your Export field is already in a form.");
            } else {
                $this->printline('Your Export field is not yet in a form');
                if ($this->confirmCategoryExportPlacement()) {
                    if (false != $data = $this->createCategoryExportPlacement($code)) {
                        $this->printline("Your Export field placement has been configured.");
                    } else {
                        $this->printline('Error : Your Export was not added to a form.');
                    }
                }
            }
        }
    }

    public function checkCategoryUnspc ()
    {
        $field = $this->getConfigHelper()->getCategoryUnspscField();
        if (empty($field)) {
            return null;
        }
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_code         = $attribute_model->getIdByCode(Mage_Catalog_Model_Category::ENTITY, $field);
        if (is_numeric($attribute_code)) {
            return $attribute_code;
        }
        return false;
    }

    public function checkCategoryUnspscPlacement ($code)
    {
        $attrHelper = Mage::helper('vbw_punchout/attributes');
        if (false != $placement = $attrHelper->findAttributeCodeInSet($code)) {
            return $placement;
        }
        return false;
    }


    public function confirmCategoryUnspsc ()
    {
        $this->printline("Would you like create the UNSPSC Category field?");
        echo "[Y|n] : ";
        $params['answers']['Y']['return'] = true;
        $params['answers']['n']['return'] = false;
        return self::GetInputWithParam($params);
    }

    public function confirmCategoryUnspscPlacement ()
    {
        $this->printline("Would you like to add your UNSPSC Category field to your Default form?");
        echo "[Y|n] : ";
        $params['answers']['Y']['return'] = true;
        $params['answers']['n']['return'] = false;
        return self::GetInputWithParam($params);
    }

    public function createCategoryUnspsc ()
    {
        $helper = Mage::helper('vbw_punchout/attributes');
        $field = $this->getConfigHelper()->getCategoryUnspscField();
        $data = array (
            'apply_to' => array (),
            'attribute_code' => $field,
            'default_value_date' => null,
            'default_value_text' => null,
            'default_value_textarea' => null,
            'default_value_yesno' => 0,
            'frontend_class' => null,
            'frontend_input' => 'text',
            'frontend_label' => array('UNSPSC'),
            'is_comparable' => 0,
            'is_configurable' => 0,
            'is_global' => 1,
            'is_html_allowed_on_front' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_unique' => 0,
            'is_used_for_promo_rules' => 0,
            'is_visible_on_front' => 0,
            'used_for_sort_by' => 0,
            'used_in_product_listing' => 0,
        );
        $data = $helper->getAttributeObjectData($data,Mage_Catalog_Model_Category::ENTITY);
        /* @var $model Mage_Catalog_Model_Entity_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->setData($data);
        try {
            $model->save();
            return $model->getId();
        } catch (Exception $e) {
            echo "Exception : ". $e->getMessage() ."\n";
            return false;
        }
    }

    public function createCategoryUnspscPlacement ($code)
    {
        $helper = Mage::helper('vbw_punchout/attributes');
        try {
            if (false != $data = $helper->addAttributeCodeToDefaultSet($code)) {
                 return true;
            }
        } catch (Exception $e) {
            echo "Exception : ". $e->getMessage() ."\n";
        }
        return false;
    }



    public function repairCategoryPunchoutExportField ($field)
    {
        /**
         * @var $attribute Mage_Eav_Model_Attribute
         * @var $attribute_model Mage_Eav_Model_Entity_Attribute
         */
        $resource = Mage::getModel('core/config_data')->getCollection();
        $resource->addFilter("path",'vbw_punchout/catalog_fields/category_export');
        $resource->load();
        if ($resource->count() == 1) {
            $item = $resource->getFirstItem();
            // get rid of the value in the config.
            if ($item->value === $field
                    && $item->path === 'vbw_punchout/catalog_fields/category_export') {
                $item->delete();
            }
        }

        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_code         = $attribute_model->getIdByCode(Mage_Catalog_Model_Category::ENTITY, $field);
        $attribute = Mage::getModel('eav/entity_attribute')->load($attribute_code);
        if ($attribute->getId() != false
                && $attribute->getAttributeCode() === $field) {
            $attribute->setAttributeCode('punchout_export');
            $attribute->save();
            return 'punchout_export';
        }
        return $field;
    }






    public function checkCategoryExport ()
    {
        $field = $this->getConfigHelper()->getCategoryPunchoutExportField();
        if (is_numeric($field)) {
            $field = $this->repairCategoryPunchoutExportField($field);
        }
        if (empty($field)) {
            return null;
        }
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_code         = $attribute_model->getIdByCode(Mage_Catalog_Model_Category::ENTITY, $field);
        if (is_numeric($attribute_code)) {
            return $attribute_code;
        }
        return false;
    }

    public function checkCategoryExportPlacement ($code)
    {
        $attrHelper = Mage::helper('vbw_punchout/attributes');
        if (false != $placement = $attrHelper->findAttributeCodeInSet($code)) {
            return $placement;
        }
        return false;
    }


    public function confirmCategoryExport ()
    {
        $this->printline("Would you like create the Export Category field?");
        echo "[Y|n] : ";
        $params['answers']['Y']['return'] = true;
        $params['answers']['n']['return'] = false;
        return self::GetInputWithParam($params);
    }

    public function confirmCategoryExportPlacement ()
    {
        $this->printline("Would you like to add your Export Category field to your Default form?");
        echo "[Y|n] : ";
        $params['answers']['Y']['return'] = true;
        $params['answers']['n']['return'] = false;
        return self::GetInputWithParam($params);
    }

    public function createCategoryExport ()
    {
        $helper = Mage::helper('vbw_punchout/attributes');
        $field = $this->getConfigHelper()->getCategoryPunchoutExportField();
        $data = array (
            'apply_to' => array (),
            'attribute_code' => $field,
            'default_value' => 1,
            'frontend_class' => null,
            'frontend_input' => 'boolean',
            'frontend_label' => array('Punchout Export'),
            'is_comparable' => 0,
            'is_configurable' => 0,
            'is_global' => 0,
            'is_html_allowed_on_front' => 0,
            'is_required' => 0,
            'is_searchable' => 0,
            'is_unique' => 0,
            'is_used_for_promo_rules' => 0,
            'is_visible_on_front' => 0,
            'used_for_sort_by' => 0,
            'used_in_product_listing' => 0,
            'source_model' => 'eav/entity_attribute_source_boolean',
            'backend_type' => 'int',
        );
        $data = $helper->getAttributeObjectData($data,Mage_Catalog_Model_Category::ENTITY);
        $data['frontend_input'] = 'select';
        /* @var $model Mage_Catalog_Model_Entity_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->setData($data);
        try {
            $model->save();
            // new save varification sets 'select' type to source_table, so have to re-set to boolean.
            if ($model->getData('source_model') != 'eav/entity_attribute_source_boolean') {
                $model->setData('source_model','eav/entity_attribute_source_boolean');
                $model->save();
            }
            return $model->getId();
        } catch (Exception $e) {
            echo "Exception : ". $e->getMessage() ."\n";
            return false;
        }
    }

    public function createCategoryExportPlacement ($code)
    {
        $helper = Mage::helper('vbw_punchout/attributes');
        try {
            if (false != $data = $helper->addAttributeCodeToDefaultSet($code)) {
                return true;
            }
        } catch (Exception $e) {
            echo "Exception : ". $e->getMessage() ."\n";
        }
        return false;
    }











    public function confirm ()
    {
        $this->printline("This utility checks that your categories");
        $this->printline("have the correct variables needed for");
        $this->printline("managing your catalog.");
        $this->printline("Would you like to continue?");
        echo "[Y|n] : ";
        $params['answers']['Y']['return'] = true;
        $params['answers']['n']['return'] = false;
        return self::GetInputWithParam($params);
    }

    public function printline ($line)
    {
        echo $line ."\n";
    }

    public static function GetInputWithParam ($params = array())
    {
        $input = self::GetInput();
        if (isset($params['answers'])) {
            $try = 0;
            while (!isset($params['answers'][$input])) {
                $try++;
                if ($try >= 3) {
                    throw new Vbw_Punchout_Model_Resource_Setup_Cli_Exception("Too many tries to answer, exiting..",200);
                }
                echo "Try again : ";
                $input = self::GetInput();
            }
            if (isset($params['answers'][$input]['return'])) {
                return $params['answers'][$input]['return'];
            }
        }
        return $input;
    }

    
    public static function GetInput ()
    {
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        $input = trim($line);
        return $input;
    }
}
