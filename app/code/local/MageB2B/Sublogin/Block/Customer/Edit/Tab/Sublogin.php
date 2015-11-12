<?php

class
MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin
extends
Mage_Adminhtml_Block_Widget_Form
{
protected
$_addressCollection
=
null;
protected
function
_prepareForm()
{
$_fb05a43927cff438da7fdbd15552ff3227bd4b96
=
new
Varien_Data_Form();
$this->setForm($_fb05a43927cff438da7fdbd15552ff3227bd4b96);
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::registry('current_customer');
$_780559c4efd84bf65288edb8394a43a3aa300846
=
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->addFieldset('subloginconfiguration',
array('legend'=>Mage::helper('sublogin')->__('Sublogin Configuration')));
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setHtmlIdPrefix('_subloginconfiguration');
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setFieldNameSuffix('subloginconfiguration');
$_780559c4efd84bf65288edb8394a43a3aa300846->addField('can_create_sublogins',
'select',
array(
'label'
=>
Mage::helper('sublogin')->__('Can create sublogins'),
'after_element_html'
=>
'<br />'
.
Mage::helper('sublogin')->__('If allowed, customer can create sublogins in frontend area'),
'name'
=>
'can_create_sublogins',
'values'
=>
Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
));
if
(Mage::getStoreConfig('sublogin/projects/project266_active'))
{
$_780559c4efd84bf65288edb8394a43a3aa300846->addField('max_number_sublogins',
'text',
array(
'label'
=>
Mage::helper('sublogin')->__('Max. number of sublogins'),
'after_element_html'
=>
Mage::helper('sublogin')->__('Configure the max. amount of sublogins the customer can create. 0 is unlimited.'),
'name'
=>
'max_number_sublogins',
'readonly'
=>
true,
));
}
else
{
$_780559c4efd84bf65288edb8394a43a3aa300846->addField('max_number_sublogins',
'text',
array(
'label'
=>
Mage::helper('sublogin')->__('Max. number of sublogins'),
'after_element_html'
=>
Mage::helper('sublogin')->__('Configure the max. amount of sublogins the customer can create. 0 is unlimited.'),
'name'
=>
'max_number_sublogins',
));
}
$_64af779d81eb451f44e50893e14780ab31bf8125
=
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->addFieldset('sublogin',
array('legend'
=>
Mage::helper('sublogin')->__('Sublogins')));
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setHtmlIdPrefix('_sublogin');
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setFieldNameSuffix('sublogin');
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('sublogins',
'text',
array(
'name'=>'sublogins',
));


$_2ecc41b28041667049b648881d5ff17111568699
=
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->getElement('sublogins')->getName();
$_2289f66a254ac0ee1bb00efbd40a49e3e810d1f2
=
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->getElement('sublogins')->getHtmlId();
$_168f685b62f9bec4ef848eff88e14ca431705084
=
<<<EOH
<div style="width:100px">
                <img style="margin-top:1px;float:right"
                    id="{$_2289f66a254ac0ee1bb00efbd40a49e3e810d1f2}_row_{{index}}_expire_date_trig"
                    src="{$this->getSkinUrl('images/grid-cal.gif')}" />
                <input rel="{{index}}" class="input-text" type="text" value="{{expire_date}}"
                    name="{$_2ecc41b28041667049b648881d5ff17111568699}[{{index}}][expire_date]" id="{$_2289f66a254ac0ee1bb00efbd40a49e3e810d1f2}_row_{{index}}_expire_date"
                    readonly="readonly"
                    style="width:70px"
                    />
</div>
EOH;
$_d2389373c4d226ef389a44a6ea431427c26dd7a9
=
Mage::Helper('sublogin')->getGridFields($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c,
$_168f685b62f9bec4ef848eff88e14ca431705084);
if
(Mage::getStoreConfig('sublogin/general/edit_in_grid',
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getStoreId()))
{
$_205d95c001c842f933e3b55fa4e902d5d2fdd0af
=
Mage::getModel('sublogin/sublogin')->getCollection()
->addFieldToFilter('entity_id',
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
->addOrder('id',
'ASC');
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setSublogins($_205d95c001c842f933e3b55fa4e902d5d2fdd0af->getItems());
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->getElement('sublogins')->setRenderer(
Mage::getSingleton('core/layout')->createBlock('sublogin/tableinput')
->addAfterJs('mageb2b/sublogin/form.js')
->setDisplay(
array(
'idfield'
=>
'id',
'addbutton'
=>
$this->__('Add'),
'fields'
=>
$_d2389373c4d226ef389a44a6ea431427c26dd7a9,
))
);
}
else
{
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->getElement('sublogins')->setRenderer(
Mage::getSingleton('core/layout')->createBlock('sublogin/customer_edit_tab_sublogin_gridContainer')
);
}
if
(!$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setData('can_create_sublogins',
1);
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setValues($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getData());
$this->setForm($_fb05a43927cff438da7fdbd15552ff3227bd4b96);
return
$this;
}
}
