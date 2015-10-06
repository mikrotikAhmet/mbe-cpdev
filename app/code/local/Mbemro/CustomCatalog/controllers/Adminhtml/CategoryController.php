<?php

class Mbemro_CustomCatalog_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{

    public function saveAction()
    {
        $request = Mage::app()->getRequest();
        $category_id = intval($request->getParam('cat_id'));
        $customerId = intval($request->getParam('customer_id'));
        if(!$customerId) {
            $customerId = Mage::registry('current_customer')->getId();
        }
        $amount = ($request->getParam('amount'));
        $apply_sub = ($request->getParam('apply_sub'));
        if(in_array($apply_sub, array('true', '1'))) {
            $apply_sub = true;
        } else {
            $apply_sub = false;
        }

        $category = Mage::getModel('catalog/category')->load($category_id);
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $response = new StdClass;
        if(!$customer->getId()) {
            $response->status = false;
            $response->message = 'Customer invalid';
            $this->sendResponse($response);
            return;
        }

        if($category->getId()) {

            $customCategory = Mage::getModel('customcatalog/category');
            $new = true;
            if (Mage::getResourceModel("customcatalog/category")->loadByCategory($customCategory, $customer, $category)){

                $new = false;

            }

            $customCategory->setCategoryId($category_id);
            $customCategory->setCustomerId($customerId);
            $customCategory->setDiscountAmount($amount);
            $customCategory->setApplySubcategories($apply_sub);

            $customCategory->save();

            $response->status = true;
            $response->category_id = $category_id;
            $response->category_name = $category->getName();
            $response->amount = $amount;
            $response->apply_subcat = $apply_sub;
            $response->message = "Category {$response->category_name} has been " . ($new ? "added " : "updated ") . "to customer's catalog.";

            if ($new) {
                Mage::dispatchEvent('customcatalog_add_category', array('category'=>$category, 'customer'=>$customer));
            }
        }

        $this->sendResponse($response);
    }

    public function removeAction()
    {
        $request = Mage::app()->getRequest();
        $category_id = intval($request->getParam('category_id'));
        $customerId = $request->getParam('customer_id');
        if(!$customerId) {
            $customerId = Mage::registry('current_customer')->getId();
        }

        $category = Mage::getModel('catalog/category')->load($category_id);
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $response = new StdClass;
        if(!$customer->getId()) {
            $response->status = false;
            $response->message = 'Customer invalid';
            $this->sendResponse($response);
            return;
        }

        if(!$category->getId()) {
            $response->status = false;
            $response->message = 'Category invalid';
            $this->sendResponse($response);
            return;
        }

        $response->category_id = $category_id;

        $customCategory = Mage::getModel('customcatalog/category');

        if (Mage::getResourceModel("customcatalog/category")->loadByCategory($customCategory, $customer, $category)){
            $customCategory->delete();
            Mage::dispatchEvent('customcatalog_remove_category', array('category'=>$category, 'customer'=>$customer));
        }

        $response->message = "Category " . $category->getName() . " is removed from customer's catalog.";
        $response->status = true;

        $this->sendResponse($response);

    }

    public function detailsAction()
    {
        $request = Mage::app()->getRequest();
        $category_id = intval($request->getParam('cat_id'));
        $customerId = intval($request->getParam('customer_id'));

        if($customerId == 0) {
            $customerId =Mage::registry('current_customer')->getId();
        }

        $category = Mage::getModel('catalog/category')->load($category_id);
        $customer = Mage::getModel('customer/customer')->load($customerId);

        $response = new StdClass;

        if(!$customer->getId()) {
            $response->status = false;
            $response->message = 'Customer invalid';
            $this->sendResponse($response);
            return;
        }

        if (!$category->getId()) {
            $response->status = false;
            $response->message = 'Category not found.';
            $this->sendResponse($response);
            return;
        }

        $response->status = false;
        $response->category_name = $category->getName();

        $customCategory = Mage::getModel('customcatalog/category');

        if (Mage::getResourceModel("customcatalog/category")->loadByCategory($customCategory, $customer, $category)){
            $response->status = true;
            $response->amount = $customCategory->getDiscountAmount();
            $response->apply_subcat = $customCategory->getApplySubcategories();
        }

        $this->sendResponse($response);
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_customer_edit_tab_categoryGrid')->toHtml()
        );
    }

    private function sendResponse($response)
    {
        header("Content-Type: application/json");
        print json_encode($response);
    }

}