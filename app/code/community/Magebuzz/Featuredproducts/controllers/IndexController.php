<?php
class Magebuzz_Featuredproducts_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/featuredproducts?id=15 
    	 *  or
    	 * http://site.com/featuredproducts/id/15 	
    	 */
    	/* 
		$featuredproducts_id = $this->getRequest()->getParam('id');

  		if($featuredproducts_id != null && $featuredproducts_id != '')	{
			$featuredproducts = Mage::getModel('featuredproducts/featuredproducts')->load($featuredproducts_id)->getData();
		} else {
			$featuredproducts = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($featuredproducts == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$featuredproductsTable = $resource->getTableName('featuredproducts');
			
			$select = $read->select()
			   ->from($featuredproductsTable,array('featuredproducts_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$featuredproducts = $read->fetchRow($select);
		}
		Mage::register('featuredproducts', $featuredproducts);
		*/
		$template = Mage::getConfig()->getNode('global/page/layouts/'.Mage::getStoreConfig("featuredproducts/general/layout").'/template');
			
		$this->loadLayout();     
		
		$this->getLayout()->getBlock('root')->setTemplate($template);
		
		$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbsBlock->addCrumb('featured_products', array(
			'label'=>Mage::helper('featuredproducts')->__(Mage::helper('featuredproducts')->getPageLabel()),
			'title'=>Mage::helper('featuredproducts')->__(Mage::helper('featuredproducts')->getPageLabel()),
		));
		
		$this->renderLayout();
    }
}