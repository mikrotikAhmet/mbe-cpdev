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

$old_keys = array('rocketweb_googlebasefeedgenerator/settings/submit_no_img',
                    'rocketweb_googlebasefeedgenerator/apparel/submit_no_img',
                    'rocketweb_googlebasefeedgenerator/apparel/variants_submit_no_img');

$new_key = 'rocketweb_googlebasefeedgenerator/columns/skip_column_empty';


$skip_img = $installer->getConnection()
    ->fetchAll("SELECT scope, scope_id, value FROM `{$this->getTable('core_config_data')}`
                    WHERE path IN ('". implode("','", $old_keys)."') AND value > 0");

foreach ($skip_img as $entry) {

    $new_values = array('image_link');
    $old_values = $installer->getConnection()
        ->fetchOne("SELECT value FROM `{$this->getTable('core_config_data')}` WHERE path = '$new_key' AND scope = '". $entry['scope']. "' AND scope_id = '". $entry['scope_id']. "'");
    $old_values = explode(',', $old_values);

    if (!empty($old_values) && !in_array('image_link', $old_values)) {
        Mage::app()->getConfig()->saveConfig($new_key, implode(',', array_merge($old_values, $new_values)), $entry['scope'], $entry['scope_id']);
    }
}

$installer->endSetup();

