<?php

class
MageB2B_Sublogin_Model_Rewrite_CustomerAddressCollection
extends
Mage_Customer_Model_Resource_Address_Collection
{

public
function
setCustomerFilter($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c)
{
if
($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
{
$this->addAttributeToFilter('parent_id',
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId());
}
else
{
$this->addAttributeToFilter('parent_id',
'-1');
}
$_4e45fa4f3249d9c3cc1314ed89922593008cf117
=
Mage::helper('sublogin')->getCurrentSublogin();

if
($_4e45fa4f3249d9c3cc1314ed89922593008cf117)
{
if
($_4e45fa4f3249d9c3cc1314ed89922593008cf117->getData('address_ids')
!=
null)
{
$_cf6123648704b8b9dd6c46b9d0941ce27c5101d8
=
explode(',',$_4e45fa4f3249d9c3cc1314ed89922593008cf117->getData('address_ids'));
$this->addAttributeToFilter('entity_id',
array('in'
=>
$_cf6123648704b8b9dd6c46b9d0941ce27c5101d8));
Mage::getSingleton('customer/session')->getCustomer()->setDefaultBilling(reset($_cf6123648704b8b9dd6c46b9d0941ce27c5101d8));
Mage::getSingleton('customer/session')->getCustomer()->setDefaultShipping(reset($_cf6123648704b8b9dd6c46b9d0941ce27c5101d8));
}
}
return
$this;
}
}