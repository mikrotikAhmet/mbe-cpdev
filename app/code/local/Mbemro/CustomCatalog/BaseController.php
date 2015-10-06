<?php
/**
 * Mbemro Base Controller.
 *
 * @category Mbemro
 * @package Mbemro_CustomCatalog
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_BaseController extends Mage_Core_Controller_Front_Action
{
    /**
     *  Authentication and priviledge control is setup in this method.
     *
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $helper = Mage::Helper('customcatalog/customcatalog');
        $enabled = $helper->isEnabled();
        $allowedGroups = $helper->getAllowedGroups();

        $session = Mage::getSingleton('customer/session');
        $authenticated = $session->authenticate($this);

        if ($authenticated && (!$enabled || !$helper->inGroup($session, $allowedGroups))) {
            $session->setNoReferer(true);
            $this->_redirect('customer/account');
            return;
        }

        if (!$authenticated) {
            $this->setFlag('', 'no-dispatch', true);
        }

    }

}
