<?php
/**
 * Mbemro Index Controller.
 *
 * @category Mbemro
 * @package Mbemro_CustomCatalog
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */

class Mbemro_CustomerReports_IndexController extends Mage_Core_Controller_Front_Action {

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        $session = Mage::getSingleton('customer/session');

        if (!$session->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        } else {
            $session->setNoReferer(true);
        }
    }


    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

#        $this->getLayout()->getBlock('content')->append(
#            $this->getLayout()->createBlock('customerreports/index')
#        );
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Purchases'));
        $this->getPurchases();
        $this->renderLayout();

    }

    public function mypurchasesAction(){

        $filterObj = new StdClass;
        $filterObj->time_limit = (int) $this->getRequest()->getParam('time_limit');
        $filterObj->time_limit_start = strval($this->getRequest()->getParam('time_limit_start'));
        $filterObj->time_limit_end = strval($this->getRequest()->getParam('time_limit_end'));

        $filterObj->orders_all = (int) $this->getRequest()->getParam('orders_all');
        $filterObj->order_choices = $this->getRequest()->getParam('order_choices');

        $_helper = Mage::helper('customerreports/customerreports');
        $jsonData = $_helper->getReportAsJson($filterObj);
        /*
        $jsonData = '{
        "dataid": "An optional sourcetable identifier",
        "columns": [
            { "colvalue": "Month ", "coltext": "Month ", "header": "Month ", "sortbycol": "Month ", "groupbyrank": null, "pivot": true, "result": false },
            { "colvalue": "Subject ", "coltext": "Subject ", "header": "Subject ", "sortbycol": "Subject ", "groupbyrank": 2, "pivot": false, "result": false },
            { "colvalue": "Student ", "coltext": "Student ", "header": "Student ", "sortbycol": "Student ", "dataid": "An optional id.", "groupbyrank": 1, "pivot": false, "result": false },
            { "colvalue": "Score ", "coltext": "Score ", "header": "Score ", "sortbycol": "Score ", "groupbyrank": null, "pivot": false, "result": true}],
        "rows": [
            { "Month ": "January", "Subject ": "English", "Student ": "Elisa", "Score ": "8.7" },
            { "Month ": "January ", "Subject ": "Maths ", "Student ": "Elisa ", "Score ": "6.5 " },
            { "Month ": "January ", "Subject ": "Science ", "Student ": "Elisa ", "Score ": "5.8 " },
            { "Month ": "March ", "Subject ": "History ", "Student ": "Mary ", "Score ": "6.7 " },
            { "Month ": "March ", "Subject ": "French ", "Student ": "Mary ", "Score ": "9.0 "}]
    }';
        */
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    public function getexcelAction() {
        $filterObj = new StdClass;
        $filterObj->time_limit = (int) $this->getRequest()->getParam('time_limit');
        $filterObj->time_limit_start = strval($this->getRequest()->getParam('time_limit_start'));
        $filterObj->time_limit_end = strval($this->getRequest()->getParam('time_limit_end'));

        $filterObj->orders_all = (int) $this->getRequest()->getParam('orders_all');
        $filterObj->order_choices = $this->getRequest()->getParam('order_choices');

        $_helper = Mage::helper('customerreports/customerreports');
        $_items = $_helper->getMyPurchasesCollection($filterObj);
        $_helper->toXLS($_items);
    }

    public function getPurchases() {

        $session = Mage::getSingleton('customer/session');
        /** var $customer Mage_Customer_Model_Customer */
        $customer = $session->getCustomer();
        $customerId = $customer->getId();
/* sql with multiple categories within, usable only if there is a pivotgrid on front that can display it */
        $sql = "select
                o.entity_id,
                o.store_id,
                o.grand_total,
                cv.value customer,
                p.sku,
                p.name product ,
                ce.entity_id,
                cev.value category,
                cev_parent.value parent_category
                from sales_flat_order o
                inner join customer_entity_varchar cv on cv.entity_id = o.customer_id
                inner join sales_flat_order_item p on p.order_id = o.entity_id
                inner join catalog_category_product cp on cp.product_id= p.product_id
                inner join catalog_category_entity_varchar cev on cev.entity_id = cp.category_id
                inner join catalog_category_entity ce on ce.entity_id = cp.category_id
                inner join catalog_category_entity ce_parent on ce_parent.entity_id = ce.parent_id
                inner join catalog_category_entity_varchar cev_parent on cev_parent.entity_id = ce.parent_id
                where o.status = 'complete' and cv.attribute_id = 5 /*in (5,6,7)*/
                  and cev.entity_type_id = 3 and cev.attribute_id = 41
                  and cev_parent.entity_type_id = 3 and cev_parent.attribute_id = 41";
/* Currently sql for all purchases, should be filtered by customer for live */
        $sql = "select
                o.entity_id,
                o.store_id,
                o.grand_total,
                cv.value customer,
                p.sku,
                p.name product ,
                ce.entity_id,
                cev.value categroy
                from sales_flat_order o
                inner join customer_entity_varchar cv on cv.entity_id = o.customer_id
                inner join sales_flat_order_item p on p.order_id = o.entity_id
                inner join catalog_category_product cp on cp.product_id= p.product_id
                inner join catalog_category_entity_varchar cev on cev.entity_id = cp.category_id
                inner join catalog_category_entity ce on ce.entity_id = cp.category_id
                where o.status = 'complete' and cv.attribute_id = 5 /*in (5,6,7)*/
                  and cev.entity_type_id = 3 and cev.attribute_id = 41
                  and ce.parent_id = 2
                ";
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');


        $_items = $readConnection->fetchAll($sql);

        // $this->getResponse()->setHeader('Content-type', 'application/json');
        // $this->getResponse()->setBody($jsonData);
    }

 }
