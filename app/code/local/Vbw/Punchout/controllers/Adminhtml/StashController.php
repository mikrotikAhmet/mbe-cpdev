<?php
/**
 * Created by JetBrains PhpStorm.
 * User: PO2Go-Dev
 * Date: 8/22/13
 * Time: 1:25 PM
 * To change this template use File | Settings | File Templates.
 */
class Vbw_Punchout_Adminhtml_StashController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Sales');
        return $this;
    }

   public function indexAction() {
       $this->_title($this->__('Sales'))->_title($this->__('Punchout Stash'));
       $this->loadLayout();
       $this->_setActiveMenu('sales/sales');
       $this->_addContent($this->getLayout()->createBlock('vbw_punchout/adminhtml_sales_stash'));
       $this->renderLayout();
  }
   public function gridAction() {
       $this->loadLayout();
       $this->getResponse()->setBody(
           $this->getLayout()->createBlock('vbw_punchout/adminhtml_sales_stash_grid')->toHtml()
        );
   }

    //edit, save, delete based on tutorial at
    // http://codegento.com/2011/02/grids-and-forms-in-the-admin-panel/

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('vbw_punchout/sales_quote_stash');
        if ($id){
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getLastSetData(true);
                if (!empty($data)) {
                    $model->setData($data);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('vbw_punchout')->__('Stash does not exist.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        Mage::register('stash_data', $model);

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('vbw_punchout/adminhtml_sales_stash_edit'));
        $this->renderLayout();

        $this->renderLayout();
    }



}
?>