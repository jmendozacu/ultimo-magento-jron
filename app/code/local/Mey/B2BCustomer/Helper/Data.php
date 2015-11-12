<?php

class Mey_B2BCustomer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return string
     */
    public function getPublicAccessKey() {
        return "33bnFvSBxT2Q";
    }

    /**
     * @param Varien_Object $customer
     * @return bool
     */
    public function processCustomerRegistration(Varien_Object $customer)
    {
        $newCustomer = null;
        try {
            $importHelper = Mage::helper("mey_b2b/import");

            $accountData = $importHelper->getAccountsData($customer->customer_number);

            if (!empty($accountData["EK-Preisliste"])) {
                // check price list availability
                foreach(explode(",", $accountData["EK-Preisliste"]) as $list) {
                    $data = $importHelper->getEKPriceListData($list);
                    if (!is_array($data)) {
                        // todo: show error page for the case that the purchase price list file was not found
                        throw new Exception("Purchase price file not found: " . $list);
                    }
                }
                if ($accountData["VK-Preisliste"] !== "0") {
                    // display both purchase price and retail price
                    $customer->display_price_type = "0";

                    $dataResult = $importHelper->getVKPriceListData($accountData["VK-Preisliste"]);
                    if (!is_array($dataResult)) {
                        $accountData["VK-Preisliste"] = "0";
                        $customer->display_price_type = "1";
                    }
                } else {
                    // display only purchase price
                    $customer->display_price_type = "1";
                }
            } else {
                // display only retail price
                $customer->display_price_type = "2";

                // check price list availability
                $dataResult = $importHelper->getVKPriceListData($accountData["VK-Preisliste"]);
                if (!is_array($dataResult)) {
                    // todo: show error page for the case that the purchase price list file was not found
                    throw new Exception("Retail price file not found: " . $accountData["VK-Preisliste"]);
                }
            }

            try {
                $newCustomer = $this->createCustomer($customer);
                $this->sendStaffNotificationEmail($newCustomer);
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton("customer/session")->addError(Mage::helper("mey_b2b")->__($e->getMessage()));
                return false;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton("customer/session")->addError(Mage::helper("mey_b2b")->__("Customer number not found. Please contact us for help."));
            return false;
        }

        return true;
    }

    /**
     * @param Varien_Object $customer
     * @return Mage_Customer_Model_Customer
     * @throws Exception
     */
    protected function createCustomer(Varien_Object $customer)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        // create customer
        $newCustomer = Mage::getModel("customer/customer");
        $newCustomer->setWebsiteId($websiteId)
            ->setStore($store)
            ->setGender($customer->gender)
            ->setFirstname($customer->prename)
            ->setLastname($customer->name)
            ->setCustomerNumber($customer->customer_number)
            ->setEmail($customer->email)
            ->setPassword($customer->password)
            ->setIsB2bApproved(false) //default
            ->setDisplayPriceType($customer->display_price_type)
            ->setPurchasePriceList(-1)
            ->setRetailPriceList(-1)
        ;

        $newCustomer->save();

        $customAddress = array(
            'firstname' => $customer->prename,
            'lastname' => $customer->name,
            'company' => $customer->company,
            'street' => array(
                '0' => $customer->street,
            ),
            'city' => $customer->city,
            'region_id' => '',
            'region' => '',
            'postcode' => $customer->zip,
            'country_id' => $customer->country,
            'telephone' => $customer->phone,
        );

        // create address for newly created customer
        $newAddress = Mage::getModel("customer/address");
        $newAddress->setData($customAddress)
            ->setCustomerId($newCustomer->getId())
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');

        $newAddress->save();

        return $newCustomer;
    }

    /**
     * @param $customer Mage_Customer_Model_Customer
     * @return mixed
     * @throws Mage_Core_Exception
     */
    protected function sendStaffNotificationEmail($customer)
    {
        $customer = Mage::getModel("customer/customer")->load($customer->getId());
        $key = Mage::helper("mey_b2bcustomer")->getPublicAccessKey();
        $approvalLink = Mage::getUrl("mey_b2bcustomer/index/approve", array("key" => $key, "id" => $customer->getId()));
        $disapprovalLink = Mage::getUrl("mey_b2bcustomer/index/disapprove", array("key" => $key, "id" => $customer->getId()));
        $fullSalutation = $customer->getGender() == "1" ? "Sehr geehrter Herr" : "Sehr geehrte Frau";
        $shortSalutation = $customer->getGender() == "1" ? "Herr" : "Frau";

        $address = $customer->getPrimaryBillingAddress();

        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate = Mage::getModel('core/email_template');

        $mailTemplate->setDesignConfig(array('area' => 'frontend'));

        $mailTemplate->sendTransactional(
            Mage::helper("mey_b2b")->getRegistrationEmailTemplate(),
            Mage::helper("mey_b2b")->getRegistrationSenderEmailIdentity(),
            Mage::helper("mey_b2b")->getRegistrationRecipientEmail(),
            null,
            array(
                "customer" => $customer,
                "address" => $address,
                "fullSalutation" => $fullSalutation,
                "shortSalutation" => $shortSalutation,
                "approvalLink" => $approvalLink,
                "disapprovalLink" => $disapprovalLink,
            ),
            Mage::helper("mey_b2b")->getStoreId()
        );

        if (!$mailTemplate->getSentSuccess()) {
            Mage::throwException("Registration mail could not be sent");
        }
        return $mailTemplate->getSentSuccess();
    }

    /**
     * @param $customer Mage_Customer_Model_Customer
     * @param $isApproved bool Send approval or disapproval
     * @throws Mage_Core_Exception
     */
    public function sendCustomerNotificationEmail($customer, $isApproved)
    {
        $address = $customer->getPrimaryBillingAddress();
        $fullSalutation = $customer->getGender() == "1" ? "Sehr geehrter Herr" : "Sehr geehrte Frau";
        $shortSalutation = $customer->getGender() == "1" ? "Herr" : "Frau";

        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate = Mage::getModel('core/email_template');

        $mailTemplate->setDesignConfig(array('area' => 'frontend'));

        // send this mail to staff members as well
        $mailTemplate->addBcc(Mage::helper("mey_b2b")->getRegistrationRecipientEmail());

        $mailTemplate->sendTransactional(
            $isApproved ? Mage::helper("mey_b2b")->getB2bRegistrationApprovedEmailTemplate() : Mage::helper("mey_b2b")->getB2bRegistrationDisapprovedEmailTemplate(),
            Mage::helper("mey_b2b")->getRegistrationSenderEmailIdentity(),
            $customer->getEmail(),
            $address->getFirstname() . " " . $address->getLastname(),
            array(
                "customer" => $customer,
                "address" => $address,
                "fullSalutation" => $fullSalutation,
                "shortSalutation" => $shortSalutation,
            ),
            Mage::helper("mey_b2b")->getStoreId()
        );

        return $mailTemplate->getSentSuccess();
    }

    /**
     * @param $customerNumber string
     * @return bool
     * @throws Exception
     */
    public function isCustomerBlocked($customerNumber) {
        $isBlocked = false;
        $dates = Mage::helper("mey_b2b/import")->getAccountsBlockedDates($customerNumber);
        if (count($dates) > 0) {
            $currentDate = new \DateTime();
            foreach ($dates as $date) {
                if ($date > $currentDate) {
                    $isBlocked = true;
                }
            }
        }
        return $isBlocked;
    }
}
