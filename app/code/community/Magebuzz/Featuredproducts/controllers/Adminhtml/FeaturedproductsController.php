<?php

class Magebuzz_Featuredproducts_Adminhtml_FeaturedproductsController extends Mage_Adminhtml_Controller_Action
{

	protected function _initProduct()
    {
        
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

    	
            if ($setId = (int) $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
                    
        $product->setData('_edit_mode', true);
        
        Mage::register('product', $product);
       
        return $product;
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('featuredproducts/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
        $this->_initProduct();
		$this->loadLayout()->_setActiveMenu('catalog/featuredproduct');
		$this->_addContent($this->getLayout()->createBlock('featuredproducts/adminhtml_edit'));
        $this->renderLayout();
	}
	
	public function manageAction() {
		$this->_initProduct();
		$this->loadLayout()->_setActiveMenu('catalog/featuredproduct');
		$this->_addContent($this->getLayout()->createBlock('featuredproducts/adminhtml_edit'));
        $this->renderLayout();
	}
	
	public function gridAction()
	{
		$this->getResponse()->setBody($this->getLayout()->createBlock('featuredproducts/adminhtml_edit_grid')->toHtml());
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('featuredproducts/featuredproducts')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('featuredproducts_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('featuredproducts/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('featuredproducts/adminhtml_featuredproducts_edit'))
				->_addLeft($this->getLayout()->createBlock('featuredproducts/adminhtml_featuredproducts_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('featuredproducts')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		$data = $this->getRequest()->getPost(); 
        $collection = Mage::getModel('catalog/product')->getCollection();
		$storeId        = $this->getRequest()->getParam('store', 0);
		
		         
        parse_str($data['featured_products'], $featured_products);
		
		
        $collection->addIdFilter(array_keys($featured_products));
        
		try {
		foreach($collection->getItems() as $product)
		{
			
			$product->setData('magebuzz_featured_product',$featured_products[$product->getEntityId()]);
			$product->setStoreId($storeId);		
			$product->save();	
		} 	

		$this->_getSession()->addSuccess($this->__('Featured product was successfully saved.'));
		$this->_redirect('*/*/index', array('store'=> $this->getRequest()->getParam('store')));	

		} catch (Exception $e){
		$this->_getSession()->addError($e->getMessage());
		$this->_redirect('*/*/index', array('store'=> $this->getRequest()->getParam('store')));
		}
	}
	
	protected function _validateSecretKey()
	{
		return true;
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('admin/catalog/featuredproduct');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('featuredproducts/featuredproducts');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $featuredproductsIds = $this->getRequest()->getParam('featuredproducts');
        if(!is_array($featuredproductsIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($featuredproductsIds as $featuredproductsId) {
                    $featuredproducts = Mage::getModel('featuredproducts/featuredproducts')->load($featuredproductsId);
                    $featuredproducts->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($featuredproductsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $featuredproductsIds = $this->getRequest()->getParam('featuredproducts');
        if(!is_array($featuredproductsIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($featuredproductsIds as $featuredproductsId) {
                    $featuredproducts = Mage::getSingleton('featuredproducts/featuredproducts')
                        ->load($featuredproductsId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($featuredproductsIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'featuredproducts.csv';
        $content    = $this->getLayout()->createBlock('featuredproducts/adminhtml_featuredproducts_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'featuredproducts.xml';
        $content    = $this->getLayout()->createBlock('featuredproducts/adminhtml_featuredproducts_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}