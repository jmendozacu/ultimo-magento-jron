<?php
class
MageB2B_Sublogin_Block_Admin_Project238
extends
Mage_Adminhtml_Block_System_Config_Form_Field
{
protected
function
_getElementHtml(Varien_Data_Form_Element_Abstract
$_8d42409e40e4019fe1ad3970dab02ce54792984f)
{
$this->setElement($_8d42409e40e4019fe1ad3970dab02ce54792984f);
$_7e23d5ac854af6c14a773f4575e12a0e2b975da8
=
$this->getUrl('adminhtml/sublogin/project238');
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f
=
$this->getLayout()->createBlock('adminhtml/widget_button')
->setType('button')
->setClass('scalable')
->setLabel('Run setup')
->setOnClick("setLocation('$_7e23d5ac854af6c14a773f4575e12a0e2b975da8')");
if
($this->_checkIfAlreadyInstalled())
{
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f->setClass('scalable disabled');
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f->setAttribute('disabled',
'disabled');
}
return
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f->toHtml();
}

protected
function
_checkIfAlreadyInstalled()
{
$_7580b9ddf3e33cc40c713c4f578c461cf3580591
=
Mage::getModel('catalog/resource_eav_attribute')->loadByCode('customer','kcp');
if
($_7580b9ddf3e33cc40c713c4f578c461cf3580591->getData('attribute_code'))
return
true;
return
false;
}
}