<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  675
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


$installer = $this;
$installer->startSetup();

$sql = "
CREATE TABLE `{$this->getTable('mstcore/attachment')}` (
    `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` VARCHAR(255) NOT NULL DEFAULT '',
    `entity_type` VARCHAR(255) NOT NULL DEFAULT '',
    `entity_id` INT(11),
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `type` VARCHAR(255) NOT NULL DEFAULT '',
    `size` INT(11),
    `body` LONGBLOB,
    PRIMARY KEY (`attachment_id`),
    UNIQUE (uid)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

";
$installer->run($sql);

$installer->endSetup();