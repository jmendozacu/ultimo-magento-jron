<?php

class Mey_B2BCustomer_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Approval: Create job that applies price list and sends out approval email afterwards
     */
    public function approveAction()
    {
        $key = $this->getRequest()->getParam("key");
        $entityId = $this->getRequest()->getParam("id");

        if ($key !== Mage::helper("mey_b2bcustomer")->getPublicAccessKey()) {
            $this->norouteAction();
            return;
        }

        $customer = Mage::getModel("customer/customer")->load($entityId);

        if (!$customer->getId()) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('mey_b2b')->__('Account was not found.'));
            $this->norouteAction();
            return;
        }

        if ($customer->getIsB2bApproved()) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('mey_b2b')->__('Account already approved.'));
            $this->norouteAction();
            return;
        }

        $customer->setIsB2bApproved(true);
        $customer->save();

        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('mey_b2b')->__('Account is now approved. The customer will receive an email notification.'));

        $this->_redirectUrl(Mage::getBaseUrl());
        return;
    }

    /**
     * Disapproval: Do not create job and send out disapproval email immediately
     */
    public function disapproveAction()
    {
        $key = $this->getRequest()->getParam("key");
        $entityId = $this->getRequest()->getParam("id");

        if ($key !== Mage::helper("mey_b2bcustomer")->getPublicAccessKey()) {
            $this->norouteAction();
            return;
        }

        $customer = Mage::getModel("customer/customer")->load($entityId);

        if (!$customer->getId()) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('mey_b2b')->__('Account was not found.'));
            $this->norouteAction();
            return;
        }

        if ($customer->getIsB2bApproved()) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('mey_b2b')->__('Account already approved.'));
            $this->norouteAction();
            return;
        }

        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('mey_b2b')->__('Account denied. The customer will receive an email notification.'));

        Mage::helper("mey_b2bcustomer")->sendCustomerNotificationEmail($customer, false);

        $this->_redirectUrl(Mage::getBaseUrl());
        return;
    }
}