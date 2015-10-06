<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 */

/**
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @author     RocketWeb
 */

/** @var $installer RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$skip_config_key = 'rocketweb_googlebasefeedgenerator/columns/skip_category';
$include_config_key = 'rocketweb_googlebasefeedgenerator/settings/category_tree_include';

$config_entries = $installer->getConnection()
    ->fetchAll("SELECT scope, scope_id, value FROM `{$this->getTable('core_config_data')}`
                    WHERE path = '$skip_config_key'"
    );
foreach ($config_entries as $entry) {
    $collection = Mage::getModel('catalog/category')->getCollection();
    $category_ids = $collection->getAllIds();
    $skip_ids = explode(',', $entry['value']);
    if ($entry['value'] != null && !empty($skip_ids)) {
        $included_ids = array_diff($category_ids, $skip_ids);
        Mage::app()->getConfig()->saveConfig($include_config_key, implode(',', $included_ids), $entry['scope'], $entry['scope_id']);
    }
}

$installer->endSetup();