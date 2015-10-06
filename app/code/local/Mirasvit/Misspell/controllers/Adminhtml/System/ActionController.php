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
 * @category Mirasvit
 * @package  Mirasvit_Misspell
 */
class Mirasvit_Misspell_Adminhtml_System_ActionController extends Mage_Adminhtml_Controller_Action
{
    public function reindexAction()
    {
        try {
            $cntWords = Mage::getModel('misspell/indexer')->reindexAll();
            $this->getResponse()->setBody('Reindex completed! Total words: '.$cntWords);
        } catch(Exception $e) {
            $this->getResponse()->setBody(nl2br($e->getMessage()));
        }
    }
}
