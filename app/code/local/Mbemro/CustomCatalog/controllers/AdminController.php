<?php
 
class Mbemro_CustomCatalog_Adminhtml_AdminController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders Inchoo'));
        $this->loadLayout();
        $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('inchoo_orders/adminhtml_sales_order'));
        $this->renderLayout();
    }
 
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('inchoo_orders/adminhtml_sales_order_grid')->toHtml()
        );
    }
 
    public function exportInchooCsvAction()
    {
        $fileName = 'orders_inchoo.csv';
        $grid = $this->getLayout()->createBlock('inchoo_orders/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
 
    public function exportInchooExcelAction()
    {
        $fileName = 'orders_inchoo.xml';
        $grid = $this->getLayout()->createBlock('inchoo_orders/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}