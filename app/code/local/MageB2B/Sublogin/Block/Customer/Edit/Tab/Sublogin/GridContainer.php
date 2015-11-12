<?php

class
MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_GridContainer
extends
Mage_Adminhtml_Block_Widget_Grid_Container
implements
Varien_Data_Form_Element_Renderer_Interface
{
public
function
__construct()
{
$this->_controller
=
'customer_edit_tab_sublogin';
$this->_blockGroup
=
'sublogin';
$this->_headerText
=
Mage::helper('sublogin')->__('Sublogins');
$this->_addButtonLabel
=
Mage::helper('sublogin')->__('Add new Sublogin');
parent::__construct();
$this->_updateButton('add',
'onclick',
'
          window.open(\''.$this->getUrl('adminhtml/sublogin/new',
array('cid'=>Mage::registry('current_customer')->getId())).'\', \'_blank\');
          window.focus();');
}


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
setElement(Varien_Data_Form_Element_Abstract
$_8d42409e40e4019fe1ad3970dab02ce54792984f)
{
$this->_element
=
$_8d42409e40e4019fe1ad3970dab02ce54792984f;
return
$this;
}

public
function
getElement()
{
return
$this->_element;
}
public
function
setDisplay($_461e9bac5c124e13e943d74294b5b3c23e91e59a)
{
$this->setIdField($_461e9bac5c124e13e943d74294b5b3c23e91e59a['idfield']);
$this->setDisplayFields($_461e9bac5c124e13e943d74294b5b3c23e91e59a['fields']);
$this->setAddbutton($_461e9bac5c124e13e943d74294b5b3c23e91e59a['addbutton']);
return
$this;
}
}
