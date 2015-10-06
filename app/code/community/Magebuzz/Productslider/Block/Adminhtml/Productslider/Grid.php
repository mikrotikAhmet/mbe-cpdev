<?php

class Magebuzz_Productslider_Block_Adminhtml_Productslider_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
	  parent::__construct();
	  $this->setId('productsliderGrid');
	  $this->setUseAjax(true);
	  $this->setDefaultSort('productslider_id');
	  $this->setDefaultDir('ASC');
	  $this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
	  $collection = Mage::getModel('productslider/productslider')->getCollection();
	  $this->setCollection($collection);
	  return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
	  $this->addColumn('productslider_id', array(
		  'header'    => Mage::helper('productslider')->__('ID'),
		  'align'     =>'right',
		  'width'     => '50px',
		  'index'     => 'productslider_id',
	  ));

	  $this->addColumn('title', array(
		  'header'    => Mage::helper('productslider')->__('Title'),
		  'align'     =>'left',
		  'index'     => 'title',
	  ));

	  /*
	  $this->addColumn('content', array(
			'header'    => Mage::helper('productslider')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
	  ));
	  */

	  $this->addColumn('status', array(
		  'header'    => Mage::helper('productslider')->__('Status'),
		  'align'     => 'left',
		  'width'     => '80px',
		  'index'     => 'status',
		  'type'      => 'options',
		  'options'   => array(
			  1 => 'Enabled',
			  2 => 'Disabled',
		  ),
	  ));
	  
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('productslider')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('productslider')->__('Edit'),
						'url'       => array('base'=> '*/*/edit'),
						'field'     => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('productslider')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('productslider')->__('XML'));
	  
	  return parent::_prepareColumns();
	}

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('productslider_id');
        $this->getMassactionBlock()->setFormFieldName('productslider');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('productslider')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('productslider')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('productslider/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('productslider')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('productslider')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

	public function getRowUrl($row)
	{
	  return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=> true));
	}  

}