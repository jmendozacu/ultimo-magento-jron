<?php

class
MageB2B_Sublogin_Block_Admin_Acl_Edit_Tabs
extends
Mage_Adminhtml_Block_Widget_Tabs
{
public
function
__construct()
{
parent::__construct();
$this->setId('sublogin_acl_tabs');
$this->setDestElementId('edit_form');
$this->setTitle(Mage::helper('sublogin')->__('Add access control'));
}
protected
function
_beforeToHtml()
{
$_384d479d3c396afbfb7e832d2d450608e3145d55
=
Mage::registry('acl_data');
$this->addTab('form_section',
array(
'label'
=>
Mage::helper('sublogin')->__('Access Control'),
'title'
=>
Mage::helper('sublogin')->__('Access Control'),
'content'
=>
$this->getLayout()->createBlock('sublogin/admin_acl_edit_tab_form')->toHtml(),
));
return
parent::_beforeToHtml();
}
}
