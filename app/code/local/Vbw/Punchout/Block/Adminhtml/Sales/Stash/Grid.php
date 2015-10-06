<?php
/**
 * Class Vbw_Punchout_Block_Adminhtml_Sales_Stash_Grid
 *
 * Stash Grid for Punchout
 * @author Christy Carwile (PunchOut2Go)
 * @version 1.0 - December 2013
 */
class Vbw_Punchout_Block_Adminhtml_Sales_Stash_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('vbw_stash_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('vbw_punchout/sales_quote_stash')->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('quote_id')
            ->addFieldToSelect('item_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('stash')
            ->addFieldToSelect('request')
            ->addFilter('item_id', 0);
            //->addFieldToFilter('stash', Array('neq'=>null));

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        //Renderers
        $unserialize = 'Vbw_Punchout_Block_Adminhtml_Sales_Stash_Renderer_Unserialize';

        //Helper
        $helper = Mage::helper('vbw_punchout');

        //Construct Columns
        $this->addColumn('id',
            array(
                'header'=> $helper->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
            ));
        $this->addColumn('store_id',
            array(
                'header'=> $helper->__('Store'),
                'index' => 'store_id',
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store'
            ));


        $this->addColumn('quote_id',
            array(
                'header'=> $helper->__('Quote ID'),
                'width' => '100px',
                'index' => 'quote_id',
            ));

       /**  $this->addColumn('item_id',         //If we're filtering so these are always 0, why display them?
            array(
                'header'=> $helper->__('Item ID'),
                'width' => '80px',
                'index' => 'item_id',
            )); **/

        $this->addColumn('customer_id',
            array(
                'header'=> $helper->__('Customer'),
                'width' => '80px',
                'index' => 'customer_id',
                'renderer' => 'Vbw_Punchout_Block_Adminhtml_Sales_Stash_Renderer_Customer'
            ));

       /** $this->addColumn('request',          //Because we're filtering by item_id = 0, this will never have a value?
            array(
                'header'=> $helper->__('Line Item'),
                'width' => '200px;',
                'index' => 'request',
                'renderer' => $unserialize
            )); **/

        $this->addColumn('stash',
            array(
                'header'=> $helper->__('Stash'),
                'width' => '200px',
                'index' => 'stash',
                'renderer' => $unserialize
            ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('View'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ));

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
