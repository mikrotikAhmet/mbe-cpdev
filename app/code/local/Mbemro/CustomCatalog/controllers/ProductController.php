<?php
/**
 * Mbemro Custom Product controller.
 *
 * @category Mbemro
 * @package Mbemro_CustomCatalog
 * @version 1.0.0
 * @author Sofija Blazevski <sofi@cp-dev.com>
 */
class Mbemro_CustomCatalog_ProductController extends Mbemro_CustomCatalog_BaseController
{

    public function indexAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }

    public function searchAction()
    {
        $keyword = $this->getRequest()->getParam('keyword');
        if (!empty($keyword)) {
            Mage::register('keyword', $keyword);
        }
        $this->indexAction();
    }

    public function addAction()
    {
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if($product->getId()) {
                $customProduct = Mage::getModel('customcatalog/product');

                $customProduct->setProductId($productId);
                $customProduct->setCustomerId(Mage::getSingleton('customer/session')->getId());
                $customProduct->setStoreId(Mage::app()->getStore()->getId());
                $customProduct->save();
                $session = Mage::getSingleton('catalog/session')->addSuccess(
                    $this->__('The product %s has been added to your catalog.', Mage::helper('core')->escapeHtml($product->getName()))
                );
                Mage::dispatchEvent('customcatalog_add_product', array('product_id'=>$productId));

                $url = $session->getRedirectUrl(true);
                if ($url) {
                    $this->getResponse()->setRedirect($url);
                } else {
                    $this->_redirectReferer(Mage::helper('customcatalog/catalog')->getAddUrl($product));
                }
            }
        }

        if (!$this->getRequest()->getParam('isAjax', false)) {
            $this->_redirectReferer();
        }

    }

    /**
     * @use Mbemro_CustomCatalog_Model_Resource_Product::loadByProduct
     */

    public function removeAction()
    {
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            //if product is found
            if ($product->getId()){
                //find catalog reference
                $productName = $product->getName();
                $customProduct = Mage::getModel('customcatalog/product');
                if (Mage::getResourceModel("customcatalog/product")->loadByProduct($customProduct, $product)){
                    $customProduct->delete();
                    Mage::getSingleton('catalog/session')->addSuccess(
                        $this->__('The product %s has been removed from your catalog.', $product->getName())
                    );
                    Mage::dispatchEvent('customcatalog_remove_product', array('product'=>$product));
                }

            }
        }

        if (!$this->getRequest()->getParam('isAjax', false)) {
            $this->_redirectReferer();
        }

    }

    private function customReferrer($defaultUrl = '')
    {
        $referrerUrl = Mage::getSingleton('catalog/session')->getCustomCatalogReferrer();
        if (empty($referrerUrl)){
            $referrerUrl = $this->_getRefererUrl();
        }

        if (empty($referrerUrl)) {
            $referrerUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
        }

        return $referrerUrl;
    }

    public function editAction()
    {
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            //if product is found
            if ($product->getId()){
                //find catalog reference
                //$productName = $product->getName();
                $customProduct = Mage::getModel('customcatalog/product');
                if (Mage::getResourceModel("customcatalog/product")->loadByProduct($customProduct, $product)){
                    Mage::getSingleton('catalog/session')->setCustomCatalogReferrer('');
                    $referrer = $this->customReferrer();
                    Mage::getSingleton('catalog/session')->setCustomCatalogReferrer($referrer);

                    Mage::register('product', $product);
                    Mage::register('myproduct', $customProduct);
                    $this->loadLayout();
                    $this->renderLayout();
                } else {

                    Mage::getSingleton('catalog/session')->addError(
                        $this->__('The product %s is not in your catalog.', $product->getName())
                    );

                    //todo for ajax...
                    if (!$this->getRequest()->getParam('isAjax', false)) {
                        $this->_redirectReferer();
                    }
                }
            }
        }

    }

    public function saveAction()
    {
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            //if product is found
            if ($product->getId()){
                //find catalog reference
                $customProduct = Mage::getModel('customcatalog/product');
                if (Mage::getResourceModel("customcatalog/product")->loadByProduct($customProduct, $product)){
                    $request = $this->getRequest();
                    $customProduct->setPartNumber($request->getParam('part_number'));
                    $customProduct->setNotes($request->getParam('notes'));
                    $customProduct->save();
                    $session = Mage::getSingleton('catalog/session')->addSuccess(
                        $this->__('Your data for product %s have been saved.', Mage::helper('core')->escapeHtml($product->getName()))
                    );

                    $referrerUrl = $this->customReferrer();
                    $this->getResponse()->setRedirect($referrerUrl );

                } else {

                    Mage::getSingleton('catalog/session')->addError(
                        $this->__('The product %s is not in your catalog.', $product->getName())
                    );

                    if (!$this->getRequest()->getParam('isAjax', false)) {
                        $this->_redirectReferer();
                    }

                }
            }

        }
    }

}
