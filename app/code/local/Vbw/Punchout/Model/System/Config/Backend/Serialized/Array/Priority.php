<?php

/**
 * Backend for serialized array data with a priority sorting
 *
 */
class Vbw_Punchout_Model_System_Config_Backend_Serialized_Array_Priority extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        usort($value,array($this,'sortByPriority'));
        $this->setValue($value);
        parent::_beforeSave();
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortByPriority ($a,$b)
    {
        if (is_array($a)
                && is_array($b)) {
            if (isset($a['priority'])
                    && isset($b['priority'])
                    && $a['priority']!= $b['priority']) {
                if ($a['priority'] > $b['priority']) {
                    return 1;
                } else {
                    return -1;
                }
            }
        }
        return 0;
    }

}
