<?php

require_once 'MageB2B/Sublogin/controllers/AccountController.php';

class Mey_B2B_AccountController extends MageB2B_Sublogin_AccountController {
    public function createB2BAction() {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function loginAction() {
        if(Mage::app()->getWebsite()->getId() != Mage::helper("mey_b2b")->getWebsiteId()) {
            parent::loginAction();
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('root')->setTemplate('mey_b2b/page/account.phtml');

        $this->renderLayout();
    }

    public function loginPostAction() {
        if(Mage::app()->getWebsite()->getId() != Mage::helper("mey_b2b")->getWebsiteId()) {
            parent::loginPostAction();
            return;
        }

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password']) && !empty($login['customer_number'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if(!$session->getCustomer()->getIsB2bApproved()) {
                        $this->_getSession()->logout()
                            ->renewSession();
                        $session->addError($this->__('Your account is not yet approved.'));
                        $this->_redirect('*/*/');
                        return;
                    }
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                    $session->setCustomerNumber($login['customer_number']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login, customer number and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    public function createB2BPostAction() {
        $post = $this->getRequest()->getPost();
        if ($post) {
            if(!$this->_validateFormKey()) {
                $this->_redirect('*/*/');
                return;
            }
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (! Zend_Validate::is(trim($post['customer_number']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['company']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['gender']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['prename']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['street']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['country']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['zip']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['city']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['phone']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['password']) , 'NotEmpty')) {
                    $error = true;
                }

                if (! Zend_Validate::is(trim($post['confirmation']) , 'NotEmpty')) {
                    $error = true;
                }

                if ($post['password'] !== $post['confirmation']) {
                    $error = true;
                }

                Mage::getSingleton("customer/session")->setRegistrationFormData($this->getRequest()->getPost());

                if ($error) {
                    Mage::throwException("Registration validation error");
                }

                // create customer and process matching pricelist
                $result = Mage::helper("mey_b2bcustomer")->processCustomerRegistration($postObject);

                if (!$result) {
                    $this->_redirectUrl($this->_getRefererUrl());
                    return;
                }

                Mage::getSingleton("customer/session")->setRegistrationFormData(null);
                Mage::getSingleton("customer/session")->setEmail($postObject->getEmail());

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('mey_b2b')->__('Registration successful! Your account is preprocessed and will be ready shortly.'));

                $this->_redirectUrl(Mage::getUrl("*/*/createB2Bsuccess"));
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('customer/session')->addError(Mage::helper('mey_b2b')->__('Please fill all required fields or contact us for help.'));
                $this->_redirectUrl($this->_getRefererUrl());
            }
        } else {
            $this->_redirectUrl($this->_getRefererUrl());
        }
        return;
    }

    public function createB2BsuccessAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
}
