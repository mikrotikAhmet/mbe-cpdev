<?php

class Vbw_Punchout_Model_Sales_Quote_Stash
        extends Mage_Core_Model_Abstract
{

    /**
     * @var array
     */
    protected $_stash = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'vbw_punchout_sales_quote_stash';

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('vbw_punchout/sales_quote_stash');
    }

    /**
     * @param $quoteId
     * @return Vbw_Punchout_Model_Sales_Quote_Stash
     */
    public function loadByQuoteId ($quoteId)
    {
        $collection = $this->getResourceCollection();
        $collection->addFieldToFilter('quote_id',$quoteId);
        $collection->addFieldToFilter('item_id',0);
        $ids = $collection->getAllIds();
        if (count($ids) > 0) {
            $id = $ids[0];
            $this->load($id);
        }
        return $this;
    }

    /**
     * @param $lineItemId
     * @param $quoteId
     * @return Vbw_Punchout_Model_Sales_Quote_Stash
     */
    public function loadByLineItemId ($lineItemId,$quoteId)
    {
        $collection = $this->getResourceCollection();
        $collection->addFieldToFilter('quote_id',$quoteId);
        $collection->addFieldToFilter('item_id',$lineItemId);
        $ids = $collection->getAllIds();
        if (count($ids) > 0) {
            $id = $ids[0];
            $this->load($id);
        }
        return $this;
    }

    /**
     * @param $quoteId
     * @return object
     */
    public function getStashItemCollection ($quoteId)
    {
        $collection = $this->getResourceCollection();
        $collection->addFieldToFilter('quote_id',$quoteId);
        $collection->addFieldToFilter('item_id',array('gt'=>0));
        return $collection;
    }

    /**
     * @param $key
     * @param null $value
     */
    public function stash ($key, $value = null)
    {
        if ($value === null) {
            return $this->getFromStash($key);
        } else {
            $this->setToStash($key,$value);
            return $this;
        }
    }

    /**
     * @param $key
     * @return
     */
    public function getFromStash ($key)
    {
        $this->loadStash();
        if (isset($this->_stash[$key])) {
            return $this->_stash[$key];
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setToStash ($key,$value)
    {
        $this->loadStash();
        $this->_stash[$key] = $value;
        $this->setData('stash',serialize($this->_stash));
    }

    /**
     * @param $key
     */
    public function removeFromStash ($key)
    {
        $this->loadStash();
        if (isset($this->_stash[$key])) {
            unset($this->_stash[$key]);
            $this->setData('stash',serialize($this->_stash));
        }
    }

    /**
     *
     */
    public function loadStash ()
    {
        if ($this->_stash === null) {
            $stash = $this->getData('stash');
            if (empty($stash)) {
                $this->_stash = array();
            } else {
                $this->_stash = unserialize($stash);
            }
        }
    }

    public function getCollection() {
        return $this->getResourceCollection();
    }

}
