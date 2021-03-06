<?php
class
MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_Grid
extends
Mage_Adminhtml_Block_Widget_Grid
implements
Varien_Data_Form_Element_Renderer_Interface
{

public
function
render(Varien_Data_Form_Element_Abstract
$_8d42409e40e4019fe1ad3970dab02ce54792984f)
{
$this->setElement($_8d42409e40e4019fe1ad3970dab02ce54792984f);
return
'</table>'.$this->toHtml();

}

public
function
__construct()
{
parent::__construct();
$this->setId('sublogin_grid');

$this->setUseAjax(true);
}

public
function
getGridUrl()
{
return
$this->_getData('grid_url')
?
$this->_getData('grid_url')
:
$this->getUrl('adminhtml/sublogin/grid',
array('_current'=>true,
'id'=>Mage::registry('current_customer')->getId()));
}

protected
function
_prepareCollection()
{
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::registry('current_customer');
$_205d95c001c842f933e3b55fa4e902d5d2fdd0af
=
Mage::getModel('sublogin/sublogin')->getCollection()
->addFieldToFilter('entity_id',
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
->addOrder('id',
'ASC');
$this->setCollection($_205d95c001c842f933e3b55fa4e902d5d2fdd0af);
return
parent::_prepareCollection();
}

protected
function
_prepareColumns()
{
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::registry('current_customer');
$_d2389373c4d226ef389a44a6ea431427c26dd7a9
=
Mage::Helper('sublogin')->getGridFields($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c);
foreach
($_d2389373c4d226ef389a44a6ea431427c26dd7a9
as
$_54323be853e934686a9fca46e2f91c8ead26624e)
{

if
(in_array($_54323be853e934686a9fca46e2f91c8ead26624e['name'],
array('address_ids',
'password',
'days_to_expire')))
continue;
$_9b3528ee820bc51621ab89082ce3f9eab6325b1e
=
array(
'header'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['label'],
'sortable'
=>
true,
'width'
=>
60,
'index'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['name']
);
if
($_54323be853e934686a9fca46e2f91c8ead26624e['type']
==
'select')
{
$_9b3528ee820bc51621ab89082ce3f9eab6325b1e['type']
=
'options';
$_9b3528ee820bc51621ab89082ce3f9eab6325b1e['options']
=
$_54323be853e934686a9fca46e2f91c8ead26624e['options'];
}
if
($_54323be853e934686a9fca46e2f91c8ead26624e['type']
==
'checkbox')
{
$_9b3528ee820bc51621ab89082ce3f9eab6325b1e['type']
=
'options';
$_9b3528ee820bc51621ab89082ce3f9eab6325b1e['options']
=
array(
0=>Mage::helper('sublogin')->__('No'),
1=>Mage::helper('sublogin')->__('Yes'),
);
}
$this->addColumn($_54323be853e934686a9fca46e2f91c8ead26624e['name'],
$_9b3528ee820bc51621ab89082ce3f9eab6325b1e);
}
$this->addColumn('action',
array(
'header'
=>
Mage::helper('sublogin')->__('Action'),
'sortable'
=>
true,
'width'
=>
60,
'filter'
=>
false,
'sortable'
=>
false,
'renderer'
=>
'sublogin/customer_edit_tab_sublogin_editRenderer',
));
}
protected
function
_prepareMassaction()
{
return;

$this->setMassactionIdField('value_id');
$this->getMassactionBlock()->setFormFieldName('sublogin');
$this->getMassactionBlock()->addItem('delete',
array(
'label'
=>
Mage::helper('sublogin')->__('Delete'),
'url'
=>
$this->getUrl('adminhtml/sublogin/gridMassDelete',
array('type'=>$this->getUseCase(),
'id'=>$this->getModel()->getId())),
'confirm'
=>
Mage::helper('sublogin')->__('Are you sure?')
));
return
$this;
}
}
