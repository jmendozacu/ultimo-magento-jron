<?php

class
MageB2B_Sublogin_Model_Rewrite_SalesQuote
extends
Mage_Sales_Model_Resource_Quote
{

public
function
loadByCustomerId($_a36006cfd57521d8b69cd2d3a65fbb5011ce09d8,
$_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d)
{
$_52e2091ba076be49cdee19801f9ae50c3cf39832
=
$this->_getReadAdapter();
$_05849e1fae48223ba6c5d89a1faca35ec555afc1
=
$this->_getLoadSelect('customer_id',
$_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d,
$_a36006cfd57521d8b69cd2d3a65fbb5011ce09d8)
->where('is_active = ?',
1)
->order('updated_at '
.
Varien_Db_Select::SQL_DESC)
->limit(1);

$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607
=
Mage::getSingleton('customer/session')->getSubloginEmail();
if
(!$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607)
{
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::getModel('customer/customer')->load($_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d);
$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607
=
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getEmail();
}
$_05849e1fae48223ba6c5d89a1faca35ec555afc1->where('customer_email = ?',
$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607);

$_461e9bac5c124e13e943d74294b5b3c23e91e59a
=
$_52e2091ba076be49cdee19801f9ae50c3cf39832->fetchRow($_05849e1fae48223ba6c5d89a1faca35ec555afc1);
if
($_461e9bac5c124e13e943d74294b5b3c23e91e59a)
{
$_a36006cfd57521d8b69cd2d3a65fbb5011ce09d8->setData($_461e9bac5c124e13e943d74294b5b3c23e91e59a);
}
$this->_afterLoad($_a36006cfd57521d8b69cd2d3a65fbb5011ce09d8);
return
$this;
}
}
