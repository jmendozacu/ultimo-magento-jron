<?php

class Mey_B2BCustomer_Model_CustomerStatus
{
    const LOG_FILE = "customer_status_update.log";

    public function updateCustomersBlockedState()
    {
        Mage::log("Gathering approved accounts to update B2B blocked state...", Zend_Log::INFO, self::LOG_FILE);

        Mage::app()->setCurrentStore(Mage::helper("mey_b2b")->getStore());

        // get all approved customers (objects) without price lists
        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect(array("customer_number", "is_b2b_approved", "is_b2b_blocked"))
            ->addAttributeToFilter("is_b2b_approved", true);

        Mage::log("Done. Found: " . count($customerCollection), Zend_Log::INFO, self::LOG_FILE);

        foreach ($customerCollection as $customer) {
            try {
                $isBlocked = Mage::helper("mey_b2bcustomer")->isCustomerBlocked($customer->getCustomerNumber());
                $customer->setIsB2bBlocked($isBlocked);
                $customer->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        Mage::log("Successfully updated all approved B2B customer's blocked state.", Zend_Log::INFO, self::LOG_FILE);
    }
}