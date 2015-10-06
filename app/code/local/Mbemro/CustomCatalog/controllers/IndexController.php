<?php
/**
 * Index Controller
 *
 * @package Mbemro
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_IndexController extends Mbemro_CustomCatalog_BaseController
{
    /**
     * Index
     * @return void
     */
    public function indexAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }

}
