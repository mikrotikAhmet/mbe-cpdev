<?php
/**
 *
 * @category    Mbemro
 * @package     Mbemro_CustomCatalog
 * @author      Sofija Blazevski<sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_Model_Resource_Customer extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection
     *
     */
    public function _construct()
    {
        $this->_init('customcatalog/customer', 'id');
    }

    public function isCustomerEnabled($customer_id, $store_id)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where('customer_id = ?', $customer_id)
            ->where('store_id = ?', $store_id)
            ;

        $data = $read->fetchRow($select);
        return $data !== false;
    }

    public function loadRecord(Mbemro_CustomCatalog_Model_Customer $model, $customer_id, $store_id)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where('customer_id = ?', $customer_id)
            ->where('store_id = ?', $store_id)
            ;

        $data = $read->fetchRow($select);

        if ($data == null) {
            return false;
        }

        $model->setData($data);

        $this->_afterLoad($model);

        return true;
        
    }


}