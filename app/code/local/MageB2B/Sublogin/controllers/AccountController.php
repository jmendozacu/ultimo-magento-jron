<?php

require_once("Mage/Customer/controllers/AccountController.php");
class
MageB2B_Sublogin_AccountController
extends
Mage_Customer_AccountController
{
public
function
preDispatch()
{
parent::preDispatch();
$_726da6d3022af6314a6aa35c59981f2654ef1b69
=
$this->getRequest()->getActionName();
if
($_726da6d3022af6314a6aa35c59981f2654ef1b69
==
'refreshsublogin')
{
$this->_getSession()->setNoReferer(true);
$this->setFlag('',
'no-dispatch',
false);
}
}

public
function
resetPasswordAction()
{
$_1193a032c60aa21cb0323d5e50afd4c3965510f1
=
(string)
$this->getRequest()->getQuery('token');
$_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d
=
(int)
$this->getRequest()->getQuery('id');

$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607
=
$this->getRequest()->getQuery('email');

$this->_getSession()->setData('resetPasswordEmail',
$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607);
$_4c08962d83c4a8d0b8e15398c5db95ab58b3cf62
=
Mage::getModel('customer/customer')->load($_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d);
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::getModel('customer/customer')
->setWebsiteId($_4c08962d83c4a8d0b8e15398c5db95ab58b3cf62->getWebsiteId())
->loadByEmail($_ff0a3525eb1a63ce44e7951a0b522829a4d2f607);

if
(!$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
$_4c08962d83c4a8d0b8e15398c5db95ab58b3cf62;

try
{

$this->_validateResetPasswordLinkToken($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c,
$_1193a032c60aa21cb0323d5e50afd4c3965510f1);

$this->loadLayout();

$this->getLayout()->getBlock('resetPassword')
->setCustomerId($_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d)
->setResetPasswordLinkToken($_1193a032c60aa21cb0323d5e50afd4c3965510f1);
$this->renderLayout();
}
catch
(Exception
$_096e526fb587435687d8b326c166325c87efa974)
{
$this->_getSession()->addError(Mage::helper('customer')->__('Your password reset link has expired.'));
$this->_redirect('*/*/');
}
}

public
function
resetPasswordPostAction()
{
$_1193a032c60aa21cb0323d5e50afd4c3965510f1
=
(string)
$this->getRequest()->getQuery('token');
$_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d
=
(int)
$this->getRequest()->getQuery('id');
$_506da6907f960f50cad09ca45512519f91515237
=
(string)
$this->getRequest()->getPost('password');
$_a12b972da4e44447d3534c51bf5e9b5c2f596030
=
(string)
$this->getRequest()->getPost('confirmation');

$_ff0a3525eb1a63ce44e7951a0b522829a4d2f607
=
$this->_getSession()->getData('resetPasswordEmail');
$_4c08962d83c4a8d0b8e15398c5db95ab58b3cf62
=
Mage::getModel('customer/customer')->load($_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d);
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::getModel('customer/customer')
->setWebsiteId($_4c08962d83c4a8d0b8e15398c5db95ab58b3cf62->getWebsiteId())
->loadByEmail($_ff0a3525eb1a63ce44e7951a0b522829a4d2f607);

if
(!$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
$_4c08962d83c4a8d0b8e15398c5db95ab58b3cf62;
try
{
$this->_validateResetPasswordLinkToken($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c,
$_1193a032c60aa21cb0323d5e50afd4c3965510f1);

}
catch
(Exception
$_096e526fb587435687d8b326c166325c87efa974)
{
$this->_getSession()->addError(Mage::helper('customer')->__('Your password reset link has expired.'));
$this->_redirect('*/*/');
return;
}
$_9dcc3b3207ac666e4c174411da26b17170543b06
=
array();
if
(iconv_strlen($_506da6907f960f50cad09ca45512519f91515237)
<=
0)
{
array_push($_9dcc3b3207ac666e4c174411da26b17170543b06,
Mage::helper('customer')->__('New password field cannot be empty.'));
}




$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setPassword($_506da6907f960f50cad09ca45512519f91515237);
if
(version_compare(Mage::getVersion(),
'1.9.1.0')
<
0)

{
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setConfirmation($_a12b972da4e44447d3534c51bf5e9b5c2f596030);
}
else
{
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setPasswordConfirmation($_a12b972da4e44447d3534c51bf5e9b5c2f596030);
}
$_b7542ebd2d63afe458fada9dae566393ebbae000
=
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->validate();
if
(is_array($_b7542ebd2d63afe458fada9dae566393ebbae000))
{
$_9dcc3b3207ac666e4c174411da26b17170543b06
=
array_merge($_9dcc3b3207ac666e4c174411da26b17170543b06,
$_b7542ebd2d63afe458fada9dae566393ebbae000);
}
if
(!empty($_9dcc3b3207ac666e4c174411da26b17170543b06))
{
$this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
foreach
($_9dcc3b3207ac666e4c174411da26b17170543b06
as
$_d6efd9fa1f666cfead3d8f61ea3ffc81d285726b)
{
$this->_getSession()->addError($_d6efd9fa1f666cfead3d8f61ea3ffc81d285726b);
}
$this->_redirect('*/*/resetpassword',
array(
'id'
=>
$_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d,
'token'
=>
$_1193a032c60aa21cb0323d5e50afd4c3965510f1
));
return;
}
try
{

$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setRpToken(null);
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setRpTokenCreatedAt(null);
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->setConfirmation(null);
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->save();
$this->_getSession()->addSuccess(Mage::helper('customer')->__('Your password has been updated.'));
$this->_redirect('*/*/login');
}
catch
(Exception
$_096e526fb587435687d8b326c166325c87efa974)
{
$this->_getSession()->addException($_096e526fb587435687d8b326c166325c87efa974,
$this->__('Cannot save a new password.'));
$this->_redirect('*/*/resetpassword',
array(
'id'
=>
$_00bfc7a78eb28c3a15ee3f0a828d5a0f3f8d9b1d,
'token'
=>
$_1193a032c60aa21cb0323d5e50afd4c3965510f1
));
return;
}
}


protected
function
_validateResetPasswordLinkToken($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c,
$_1193a032c60aa21cb0323d5e50afd4c3965510f1)
{
if
(!is_string($_1193a032c60aa21cb0323d5e50afd4c3965510f1)
||
empty($_1193a032c60aa21cb0323d5e50afd4c3965510f1)
)
{
throw
Mage::exception('Mage_Core',
Mage::helper('customer')->__('Invalid password reset token.'));
}

if
(!$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
||
!$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getId())
{
throw
Mage::exception('Mage_Core',
Mage::helper('customer')->__('Wrong customer account specified.'));
}
$_1dabec2283d85c7ca0103a68c05263dcfb258e90
=
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->getRpToken();
if
(strcmp($_1dabec2283d85c7ca0103a68c05263dcfb258e90,
$_1193a032c60aa21cb0323d5e50afd4c3965510f1)
!=
0
||
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c->isResetPasswordLinkTokenExpired())
{
throw
Mage::exception('Mage_Core',
Mage::helper('customer')->__('Your password reset link has expired.'));
}
}


public
function
refreshsubloginAction()
{
$_7661e073459e96b1adbc480bebcb023560a54a58
=
Mage::getStoreConfig('sublogin/general/expire_interval');
$_ce376578098cf70af7021fca72fe4e15a4a98365
=
(int)
$this->getRequest()->getQuery('id');
$_1193a032c60aa21cb0323d5e50afd4c3965510f1
=
(string)
$this->getRequest()->getQuery('token');
$_4e45fa4f3249d9c3cc1314ed89922593008cf117
=
Mage::getModel('sublogin/sublogin')->load($_ce376578098cf70af7021fca72fe4e15a4a98365);
if
($_4e45fa4f3249d9c3cc1314ed89922593008cf117->getId())
{
if
($_4e45fa4f3249d9c3cc1314ed89922593008cf117->getRpToken()
==
$_1193a032c60aa21cb0323d5e50afd4c3965510f1)
{
$_4e45fa4f3249d9c3cc1314ed89922593008cf117->setExpireDate(strtotime(date("Y-m-d",
strtotime(date('Y-m-d')))
.
" + ".
$_7661e073459e96b1adbc480bebcb023560a54a58
.
" days"));
$_4e45fa4f3249d9c3cc1314ed89922593008cf117->setActive(1);
$this->_getSession()->addSuccess(Mage::helper('sublogin')->__('Successfully refreshed this account'));
}
else
$this->_getSession()->addError(Mage::helper('sublogin')->__('Your account refresh link has expired - try to login to get another email.'));
$_4e45fa4f3249d9c3cc1314ed89922593008cf117->setRpToken(null);
$_4e45fa4f3249d9c3cc1314ed89922593008cf117->setRpTokenCreatedAt(null);
$_4e45fa4f3249d9c3cc1314ed89922593008cf117->save();
}
else
$this->_getSession()->addError(Mage::helper('sublogin')->__('This account doesn\'t exist anymore and can therefore not be refreshed'));

$this->_redirect('*/*/login');
}
}
