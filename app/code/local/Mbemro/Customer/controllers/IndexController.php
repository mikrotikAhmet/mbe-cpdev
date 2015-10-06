<?php

class Mbemro_Customer_Reports_IndexController extends Mage_Core_Controller_Front_Action {        

    public function myPurchasesAction() {
        $categoryId = (int) $this->getRequest()->getParam('category_id');
        
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


        $results = $readConnection->fetchAll($query);
        
        // $this->getResponse()->setHeader('Content-type', 'application/json');
        // $this->getResponse()->setBody($jsonData);
    }

 }