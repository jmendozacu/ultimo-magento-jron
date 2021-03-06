<?php

class
MageB2B_Sublogin_Block_Admin_Budget_Grid
extends
Mage_Adminhtml_Block_Widget_Grid
{

public
function
__construct()
{
parent::__construct();
$this->setId('budgetGrid');
$this->setDefaultSort('budget_id');
$this->setDefaultDir('ASC');
$this->setSaveParametersInSession(true);
$this->setUseAjax(true);
}
public
function
getGridUrl()
{
return
$this->getUrl('*/*/grid',
array('_current'=>true));
}

protected
function
_prepareCollection()
{
$_205d95c001c842f933e3b55fa4e902d5d2fdd0af
=
Mage::getModel('sublogin/budget')->getCollection();
$_205d95c001c842f933e3b55fa4e902d5d2fdd0af->getSelect()->join(
array('sublogin'=>$_205d95c001c842f933e3b55fa4e902d5d2fdd0af->getTable('sublogin/sublogin')),
'main_table.sublogin_id = sublogin.id',
array('sublogin.email')
);
$this->setCollection($_205d95c001c842f933e3b55fa4e902d5d2fdd0af);
return
parent::_prepareCollection();
}

protected
function
_prepareColumns()
{
$this->addColumn('budget_id',
array(
'header'
=>
Mage::helper('sublogin')->__('ID'),
'align'
=>'right',
'width'
=>
'50px',
'index'
=>
'budget_id',
));
$this->addColumn('email',
array(
'header'
=>
Mage::helper('sublogin')->__('Sublogin'),
'align'
=>'right',
'width'
=>
'50px',
'index'
=>
'email',
));
$this->addColumn('year',
array(
'header'
=>
Mage::helper('sublogin')->__('Year'),
'index'
=>
'year',
));
$this->addColumn('month',
array(
'header'
=>
Mage::helper('sublogin')->__('Month'),
'index'
=>
'month',
));
$this->addColumn('day',
array(
'header'
=>
Mage::helper('sublogin')->__('Day'),
'index'
=>
'day',
));
$this->addColumn('per_order',
array(
'header'
=>
Mage::helper('sublogin')->__('Per Order Limit'),
'index'
=>
'per_order',
));
$this->addColumn('amount',
array(
'header'
=>
Mage::helper('sublogin')->__('Amount'),
'index'
=>
'amount',
));
return
parent::_prepareColumns();
}

public
function
getRowUrl($_15ded6f30a13ef74e4a6288cd249a3be115e0ad0)
{
return
$this->getUrl('*/*/edit',
array('id'
=>
$_15ded6f30a13ef74e4a6288cd249a3be115e0ad0->getId()));
}

protected
function
_prepareMassaction()
{
$this->setMassactionIdField('budget_id');
$this->getMassactionBlock()->setFormFieldName('budget_ids');
$this->getMassactionBlock()->addItem('delete',
array(
'label'
=>
Mage::helper('sublogin')->__('Delete'),
'url'
=>
$this->getUrl('*/*/massDelete'),
'confirm'
=>
Mage::helper('sublogin')->__('Are you sure?')
));
return
$this;
}
}