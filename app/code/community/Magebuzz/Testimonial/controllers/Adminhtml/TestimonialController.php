<?php

class Magebuzz_Testimonial_Adminhtml_TestimonialController extends Mage_Adminhtml_Controller_action {

	const XML_PATH_EMAIL_SELECT_TEMPLATE_AFTER_APPROVE = 'testimonial/email_configuration/select_template_approve';

	public function _getTestimonial() {
	
		return Mage::getSingleton('testimonial/testimonial');
	}
	
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('testimonial/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction();
		$this->renderLayout();
		
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('testimonial/testimonial')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('testimonial_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('testimonial/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manager Testimonial'), Mage::helper('adminhtml')->__('Manager Testimonial'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('testimonial/adminhtml_testimonial_edit'))
				->_addLeft($this->getLayout()->createBlock('testimonial/adminhtml_testimonial_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('testimonial')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
        $model = Mage::getModel('testimonial/testimonial');
		if ($data = $this->getRequest()->getPost()) {
			if(isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != '') {
                try
                {
                    $path = Mage::getBaseDir().DS.'media/magebuzz/avatar'.DS;
                    $fname = $_FILES['avatar']['name'];
                    $fname = str_replace(' ', '_', $fname);
                    $uploader = new Varien_File_Uploader('avatar');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','png','gif'));
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $destFile = $path.$fname;
                    $fname  = $model->getNewFileName($destFile);
                    $uploader->save($path,$fname);
                }
                catch (Exception $e)
                {
                    echo 'Error Message: '.$e->getMessage();
                }
		        //this way the name is saved in DB
	  			$data['avatar'] = $_FILES['avatar']['name'];
			}else{
                unset($data['avatar']);
            }

			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));

            if(isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != ''){
                $model->setAvatarName($fname);
                $model->setAvatarPath($path);
            }
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('testimonial')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('testimonial')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('testimonial/testimonial');
				 
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
        $testimonialIds = $this->getRequest()->getParam('testimonial');
        if(!is_array($testimonialIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($testimonialIds as $testimonialId) {
                    $testimonial = Mage::getModel('testimonial/testimonial')->load($testimonialId);
                    $testimonial->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($testimonialIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction() {
        $testimonialIds = $this->getRequest()->getParam('testimonial');
        if(!is_array($testimonialIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($testimonialIds as $testimonialId) {
					$current_status= $this->_getTestimonial()->load($testimonialId)->getStatus();
					$updated_status= $this->getRequest()->getParam('status');
					if ((Mage::getStoreConfig('testimonial/email_configuration/send_email_after_approve_testimonial', Mage::app()->getStore())=="1")and ($current_status=="3" and $updated_status=="1") ) {
						$to = array('email'=>$this->_getTestimonial()->load($testimonialId)->getEmail(), 'name'=>$this->_getTestimonial()->load($testimonialId)->getName());
						$this->sendemailAction($to, $templateConfigPath=self::XML_PATH_EMAIL_SELECT_TEMPLATE_AFTER_APPROVE);
						Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Admin has just sent the email to customer for approving their testimonials'));
				
	
					}
					
					$testimonial = $this->_getTestimonial()->load($testimonialId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
				}
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($testimonialIds))
                );
			}catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction() {
        $fileName   = 'testimonial.csv';
        $content    = $this->getLayout()->createBlock('testimonial/adminhtml_testimonial_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'testimonial.xml';
        $content    = $this->getLayout()->createBlock('testimonial/adminhtml_testimonial_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
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
	
	public function sendemailAction($to, $templateConfigPath) {
		if(!$to) return;
		$translate=Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$mailTemplate=Mage::getModel('core/email_template');
		$template=Mage::getStoreConfig($templateConfigPath, Mage::app()->getStore()->getId());
		$sendTo=array();
		foreach($to as $recipient) {
			if(is_array($recipient)) {
				$sendTo[]=$recipient;
			}
			else {
				$sendTo[]=array(
					'email'=>$recipient,
					'name'=>null
				);	
			}
		
		}
		foreach ($sendTo as $recipient ) {
			$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
			->sendTransactional(
			$template,
			Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, Mage::app()->getStore()->getId()),
			$recipient['email'],
			$recipient['name'],
			array('customer_name'       =>$this->_getTestimonial()->load($testimonialId)->getName(),
					'customer_email'    =>$this->_getTestimonial()->load($testimonialId)->getEmail(),
					'address'           =>$this->_getTestimonial()->load($testimonialId)->getAddress(),
					'website'           =>$this->_getTestimonial()->load($testimonialId)->getWebsite(),
					'company'           =>$this->_getTestimonial()->load($testimonialId)->getCompany(),
					'testimonial'       =>$this->_getTestimonial()->load($testimonialId)->getTestimonial()
			      )
			);
		}
		$translate->setTranslateInline(true);
		return $this;
	}
}