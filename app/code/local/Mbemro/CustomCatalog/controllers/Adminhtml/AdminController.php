<?php
 
class Mbemro_CustomCatalog_Adminhtml_AdminController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Customer Catalog'));
        $this->loadLayout();
        // $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_customer_edit_tab_action'));
        $this->_addContent($this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_customer_edit_tab_product'));
        $this->renderLayout();
    }
 
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_customer_edit_tab_grid')->toHtml() 
            //. $this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_customer_edit_tab_product')->toHtml()
        );
    }

    public function priceAction()
    {
    	$request = Mage::app()->getRequest();
    	$sku = $request->getParam('sku');
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
    	$response = new StdClass;
    	if ($product->getId()) {
    		$response->status = true;
    		$response->price = $product->getPrice();
    	}    

        header("Content-Type: application/json");
        print json_encode($response);	
    }

    public function saveAction()
    {
    	$request = Mage::app()->getRequest();
    	$sku = $request->getParam('sku');
    	$part_number = ($request->getParam('part_number'));
    	$notes = ($request->getParam('notes')); 
    	$price = ($request->getParam('price'));
    	$customer_id = (int)trim($request->getParam('customer_id'));
        $customer = Mage::getModel('customer/customer')->load($customer_id);

    	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
    	$response = new StdClass;
    	if($product->getId()) {
    			$productId = $product->getId(); 
    			//find catalog reference
                $productName = $product->getName();
                $customProduct = Mage::getModel('customcatalog/product');
                $new = true;
                if (Mage::getResourceModel("customcatalog/product")->loadByProduct($customProduct, $product, $customer)){
    
                	$new = false;
      
                } 

                $customProduct->setProductId($productId);
                $customProduct->setCustomerId($customer_id);
                $customProduct->setStoreId(Mage::app()->getStore()->getId());
                $customProduct->setPartNumber($part_number);
                $customProduct->setNotes($notes);
                $customProduct->setCustomPrice($price);
                
                $customProduct->save();

                $response->product_id = $productId;
                $response->sku = $sku;
                $response->name = $productName;
                $response->part_number = $part_number;
                $response->custom_price = $price;
                $response->price = $product->getPrice();
                $response->notes = $notes;
                $response->message = "Product $productName has been " . ($new ? "added " : "updated ") . "to customer's catalog.";


                if ($new) {
                	Mage::dispatchEvent('customcatalog_add_product', array('product_id'=>$productId));
                }

            }

        header("Content-Type: application/json");
        print json_encode($response);
    }

    public function removeAction()
    {
    	$request = Mage::app()->getRequest();
    	$sku = $request->getParam('sku');
    	$customer_id = $request->getParam('customer_id');
        $customer = Mage::getModel('customer/customer')->load($customer_id);
    	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
    	$response = new StdClass;
    	$response->message = "Product not found.";
    	$response->sku = $sku;

    	if($product->getId()) {
    			$productId = $product->getId(); 
    			//find catalog reference
                $productName = $product->getName();
                $customProduct = Mage::getModel('customcatalog/product');
                if (Mage::getResourceModel("customcatalog/product")->loadByProduct($customProduct, $product, $customer)){
                    $customProduct->delete();
                    Mage::dispatchEvent('customcatalog_remove_product', array('product'=>$product, 'customer_id'=>$customer_id));
                }

                $response->message = "Product " . $productName . " is removed from customer's catalog.";
                $response->status = true;
        }

        header("Content-Type: application/json");
        print json_encode($response);
    	
    }

    public function enableAction()
    {
    	$request = Mage::app()->getRequest();
    	$customer_id = intval($request->getParam('customer_id'));
    	$store_id = 0;

    	$response = new StdClass;
		$enabled = false;    	
    	if (Mage::getResourceModel("customcatalog/customer")->isCustomerEnabled($customer_id, $store_id)) {
    		$enabled = true;
    	}

    	if (!$enabled) {
            $customCustomer = Mage::getModel('customcatalog/customer');
            $customCustomer->setStoreId($store_id);
            $customCustomer->setCustomerId($customer_id);
            $customCustomer->save();
            $enabled = true;
        }

		$response->status = $enabled;

        header("Content-Type: application/json");
        print json_encode($response);

    }

    public function disableAction()
    {
    	$request = Mage::app()->getRequest();
    	$customer_id = intval($request->getParam('customer_id'));
    	$store_id = 0;

    	$response = new StdClass;
		$customCustomer = Mage::getModel('customcatalog/customer');
    	if (Mage::getResourceModel("customcatalog/customer")->loadRecord($customCustomer, $customer_id, $store_id)) {
    		$customCustomer->delete();
    	}

    	$response->status = true;
        header("Content-Type: application/json");
        print json_encode($response);
    	
    }
 
    public function exportInchooCsvAction()
    {
        $fileName = 'orders_inchoo.csv';
        $grid = $this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
 
    public function exportInchooExcelAction()
    {
        $fileName = 'orders_inchoo.xml';
        $grid = $this->getLayout()->createBlock('mbemro_customcatalog/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}