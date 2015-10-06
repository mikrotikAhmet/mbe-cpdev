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


class Mirasvit_MstCore_Model_Notification extends Mage_Core_Model_Abstract
{
    public function check($e)
    {
        $section = Mage::app()->getRequest()->getParam('section');
        $module = Mage::app()->getRequest()->getControllerModule();
        
        if ($helper = Mage::helper('mstcore/code')->getCodeHelper2($section)) {
            $helper->checkConfig();
        }
        if ($helper = Mage::helper('mstcore/code')->getCodeHelper2($module)) {
            $helper->checkConfig();
        }
    }
}