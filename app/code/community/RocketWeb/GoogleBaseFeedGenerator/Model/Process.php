<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Process extends Mage_Core_Model_Abstract {

    const STATUS_PENDING = 0;
    const STATUS_PROCESSED = 1;

    public function _construct() {
        parent::_construct();
        $this->_init('googlebasefeedgenerator/process');
    }

    /**
     * Marks a product in the feed as potential duplicate
     */
    public function initialize() {

        if (!$this->hasId()) {
            $result = $this->getCollection()
                ->addFieldToSelect('id')
                ->addFieldToFilter('item_id', $this->getItemId())
                ->addFieldToFilter('store_id', $this->getStoreId())
                ->load();

            if (count($result)) {
                $changes = $this->getData();
                unset($changes['status']);
                $this->load($result->getFirstItem()->getId());
                $this->addData($changes);
            }
        }

        return $this->save();
    }

    /**
     * @return mixed
     */
    public function process() {
        return $this->setStatus(self::STATUS_PROCESSED)->save();
    }
}