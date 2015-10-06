<?php
class Mbemro_Orderconfirmation_IndexController extends Mage_Core_Controller_Front_Action
{
     /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

	 /**
     * Order Confirmation index action
     */
    public function indexAction()
    {
		if(!Mage::getSingleton('customer/session')->getCustomer()->getCorpDepSupervisor()){
		    $this->_forward('noRoute');
            return false;
		}
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Orders waiting for approval'));	
        $this->renderLayout();	
    }
	
	 /**
     * Order Confirmation view order action
     */
    public function viewAction()
    {
	    $orderId = (int) $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canAccessOrder($order)) {
            Mage::register('current_order', $order);
			$this->loadLayout();
			$this->renderLayout();	
        } else {
            $this->_forward('noRoute');
			return false;
        }
	
    }	
	
	 /**
     * Order Confirmation confirm order action
     */
    public function confirmAction()
    {
	    $orderId = (int) $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }
		
		$order = Mage::getModel('sales/order')->load($orderId);
        if ($this->_canAccessOrder($order)) {
			$comment = 'Confirmed by '.Mage::getSingleton('customer/session')->getCustomer()->getName();		
			$order->setState(Mage_Sales_Model_Order::STATE_NEW, true, $comment)->save();
			$message = "Order ".$order->getRealOrderId() ." is confirmed";
		
			Mage::getSingleton('core/session')->addSuccess($message);
		} else {
            $this->_forward('noRoute');
			return false;
        }
		
        $this->_redirect('*/*/');		
    }
	
	 /**
     * Order Confirmation - put order onhold state
     */
    public function holdAction()
    {
	    $orderId = (int) $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }
		
		$order = Mage::getModel('sales/order')->load($orderId);
        if ($this->_canAccessOrder($order)) {
			$comment = 'Holded by '.Mage::getSingleton('customer/session')->getCustomer()->getName();
			$order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, $comment)->save();
			$message = "Order ".$order->getRealOrderId() ." is on hold";
		
			Mage::getSingleton('core/session')->addSuccess($message);
		} else {
            $this->_forward('noRoute');
			return false;
        }
		
        $this->_redirect('*/*/');		
    }	

    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
            ) {
            return true;
        }
        return false;
    }

    /**
     * Check if user is supervisor and have access to modify order status
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canAccessOrder($order)
    {
		if(($order->getState() != 'holded') && ($order->getState() != 'new')) return false;
		$customerUser = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
		$customerOrder = Mage::getModel('customer/customer')->load($order->getCustomerId());
		if(!$customerUser->getCorpDepSupervisor()) return false;
		if($customerUser->getCorpCompanyDd() != $customerOrder->getCorpCompanyDd()) return false;		
		if($customerUser->getCorpDepartmentDd() != $customerOrder->getCorpDepartmentDd()) return false;
        return true;
    }	
}
?>