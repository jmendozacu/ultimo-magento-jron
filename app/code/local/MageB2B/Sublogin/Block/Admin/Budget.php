<?php

class
MageB2B_Sublogin_Block_Admin_Budget
extends
Mage_Adminhtml_Block_Widget_Grid_Container
{
public
function
__construct()
{
$this->_controller
=
'admin_budget';
$this->_blockGroup
=
'sublogin';
$this->_headerText
=
Mage::helper('sublogin')->__('Sublogin Budget');
$this->_addButtonLabel
=
Mage::helper('sublogin')->__('Add New');
parent::__construct();
}
}
