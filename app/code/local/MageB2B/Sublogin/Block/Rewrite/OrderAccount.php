<?php

class
MageB2B_Sublogin_Block_Rewrite_OrderAccount
extends
Mage_Adminhtml_Block_Sales_Order_Create_Form_Account
{

protected
function
_addAttributesToForm($_a8c5747baec2d53dc80fd53f721245f3ab24680d,
Varien_Data_Form_Abstract
$_fb05a43927cff438da7fdbd15552ff3227bd4b96)
{
parent::_addAttributesToForm($_a8c5747baec2d53dc80fd53f721245f3ab24680d,
$_fb05a43927cff438da7fdbd15552ff3227bd4b96);

$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
$this->getCustomer();

if
(!$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
return;
foreach($_a8c5747baec2d53dc80fd53f721245f3ab24680d
as
$_7580b9ddf3e33cc40c713c4f578c461cf3580591)
{

if
($_7580b9ddf3e33cc40c713c4f578c461cf3580591->getAttributeCode()
==
'email')
{


$_6351c259e862f79c63cc667ddd48f376530a4a2e
=
array($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getEmail()=>$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getEmail());

$_205d95c001c842f933e3b55fa4e902d5d2fdd0af
=
Mage::getModel('sublogin/sublogin')->getCollection()->addFieldToFilter('entity_id',
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId());
foreach
($_205d95c001c842f933e3b55fa4e902d5d2fdd0af
as
$_4e45fa4f3249d9c3cc1314ed89922593008cf117)
$_6351c259e862f79c63cc667ddd48f376530a4a2e[$_4e45fa4f3249d9c3cc1314ed89922593008cf117->getEmail()]
=
$_4e45fa4f3249d9c3cc1314ed89922593008cf117->getEmail();
$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607
=
Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getData('customer_email');

$_fb05a43927cff438da7fdbd15552ff3227bd4b96->removeField($_7580b9ddf3e33cc40c713c4f578c461cf3580591->getAttributeCode());

$_fb05a43927cff438da7fdbd15552ff3227bd4b96->addField($_7580b9ddf3e33cc40c713c4f578c461cf3580591->getAttributeCode(),
'select',
array(
'name'
=>
$_7580b9ddf3e33cc40c713c4f578c461cf3580591->getAttributeCode(),
'label'
=>
$_7580b9ddf3e33cc40c713c4f578c461cf3580591->getStoreLabel(),
'class'
=>
$_7580b9ddf3e33cc40c713c4f578c461cf3580591->getFrontend()->getClass(),
'required'
=>
$_7580b9ddf3e33cc40c713c4f578c461cf3580591->getIsRequired(),
'options'
=>
$_6351c259e862f79c63cc667ddd48f376530a4a2e,
'value'
=>
$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607,
));
}
}
}
}
