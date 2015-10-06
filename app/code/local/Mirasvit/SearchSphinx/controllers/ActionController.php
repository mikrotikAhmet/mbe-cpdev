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


/**
 * ÐÐ¾Ð½ÑÑÐ¾Ð»Ð»ÐµÑ Ð´Ð»Ñ Ð¿Ð¾Ð»ÑÑÐµÐ½Ð¸Ñ Ð·Ð°Ð¿ÑÐ¾ÑÐ¾Ð² Ð½Ð° ÑÐ¿ÑÐ°Ð²Ð»ÐµÐ½Ð¸Ðµ ÑÑÐ¸Ð½ÐºÑÐ¾Ð¼ (ÑÑÐ¾ Ð±Ñ Ð²ÑÐµ Ð´ÐµÐ¹ÑÑÐ²Ð¸Ñ Ð¿ÑÐ¾Ð¸Ð·Ð²Ð¾Ð´Ð¸Ð»Ð¸ÑÑ Ð¿Ð¾Ð´ apache Ð¿Ð¾Ð»ÑÐ·ÑÐ·Ð¾Ð²Ð°ÑÐµÐ»ÐµÐ¼)
 * 
 * @category Mirasvit
 * @package  Mirasvit_SearchSphinx
 */
class Mirasvit_SearchSphinx_ActionController extends Mage_Core_Controller_Front_Action
{
    public function startAction()
    {
        try {
            $this->_getEngine()->doStart();
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
    }

    public function stopAction()
    {
        try {
            $this->_getEngine()->doStop();
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }

    }

    public function reindexAction()
    {
        $result = null;

        try {
            $result = $this->_getEngine()->doReindex();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('searchsphinx')->__($result));
    }

    public function reindexdeltaAction()
    {
        try {
            $this->_getEngine()->doReindexDelta();
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }

    }

    protected function _getEngine()
    {
        return Mage::getSingleton('searchsphinx/engine_sphinx_native');
    }
}