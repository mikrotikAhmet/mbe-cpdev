<?php
class Mbemro_Autoexport_Helper_Data extends Mage_Core_Helper_Abstract
{
	const logFile = 'autoexport.log';

	public function getTime(){
		return new DateTime();
	}
	
	public function getLastExportedId(){
		
		$lastOrderId = null;
		$lastCustomerId = null;
		
		$collection = Mage::getModel('autoexport/autoexportrecords')->getCollection()
		->addFieldToFilter('passed', '1');
		
		if($collection->count()){
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			
			$select = $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
			->columns(array('MAX(last_exported_order_id) as lastexporderid','MAX(last_exported_customer_id) as lastexpcustomerid'));
			
			$data = $read->fetchAll($select);
			
			$lastOrderId = isset($data[0]['lastexporderid']) ? $data[0]['lastexporderid'] : null;
			$lastCustomerId = isset($data[0]['lastexpcustomerid']) ? $data[0]['lastexpcustomerid'] : null;

		}
		
		return array($lastOrderId, $lastCustomerId);
	}

	public function getExportedOrders(){
		
		$orders = array();
		$status = false;
		$filepath = '';

		$time = $this->getTime()->getTimestamp();
		
		list($lastOrderId, $lastCustomerId) = $this->getLastExportedId();
		
		$collectionOrders = Mage::getModel('sales/order')->getCollection();

		if($lastOrderId){
			$collectionOrders->addFieldToFilter('entity_id', array('gt' => $lastOrderId));
		}
		
		foreach ($collectionOrders as $order){
			$orders[] = $order->getId();
		}

		try{
			// $file = Mage::getModel('bluejalappeno_orderexport/export_csv')->exportOrders($orders);
			$file = Mage::getModel('bluejalappeno_orderexport/export_edi850')->exportOrders($orders);

			$filepath = Mage::getBaseDir('export') . DS . $file;
			$status = true;
		}catch (Mage_Core_Exception $e) {
			// die('Mage_Core_Exception: ' . $e->getMessage());
			Mage::log($e->getMessage(), null, self::logFile);
		}
		catch(Exception $e){
			// die('Exception: ' . $e->getMessage());
			Mage::log($e->getMessage(), null, self::logFile);
		}
		
		return array($status, $orders, $filepath);
	}

	public function doAutoExport(){

		if(!$this->getEnabled()) return;
		
		$modelAutoExportRecords = Mage::getModel('autoexport/autoexportrecords');

		list($statusOrder, $ordersIds, $filepathOrder) = $this->getExportedOrders();

		$datetime = $this->getTime()->format('Y-m-d H:i:s');

		$modelAutoExportRecords->setExportedOrders(implode(',', $ordersIds));
		$modelAutoExportRecords->setLastExportedOrderId(empty($ordersIds) ? NULL : max($ordersIds));
		$modelAutoExportRecords->setExportedAt($datetime);
		$modelAutoExportRecords->setPassed(1);
		$modelAutoExportRecords->save();
	}
	
	public function doManualExport(){
		if(!$this->getEnabled()) return;

		$modelAutoExportRecords = Mage::getModel('autoexport/autoexportrecords');
		$session = Mage::getSingleton('adminhtml/session');
	
		list($statusOrder, $ordersIds, $filepathOrder) = $this->getExportedOrders();

		
		$datetime = $this->getTime()->format('Y-m-d H:i:s');
		
		$modelAutoExportRecords->setExportedOrders(implode(',', $ordersIds));
		$modelAutoExportRecords->setLastExportedOrderId(empty($ordersIds) ? NULL : max($ordersIds));
		$modelAutoExportRecords->setExportedAt($datetime);
		$modelAutoExportRecords->setPassed(1);
		$modelAutoExportRecords->save();
		
		if($statusOrder){
			$session->addSuccess($this->__('Records exported sucessfuly.'));
		}else{
			$session->addError($this->__('There was an error during records export.'));
		}

	}

	
	public function getEnabled() {
		return Mage::getStoreConfig('autoexportsection/autoexportgroup/active');
	}


}
