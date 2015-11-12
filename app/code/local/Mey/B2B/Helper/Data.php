<?php

class Mey_B2B_Helper_Data extends Mage_Core_Helper_Abstract {
    const XML_B2B_WEBSITE = "mey_b2b/general/website_id";
    const XML_PATH_REGISTRATION_EMAIL_RECIPIENT = "mey_b2b/email/registration_recipient_email";
    const XML_PATH_REGISTRATION_EMAIL_SENDER = "mey_b2b/email/registration_sender_email_identity";
    const XML_PATH_REGISTRATION_EMAIL_TEMPLATE = "mey_b2b/email/registration_email_template";
    const XML_PATH_B2B_REGISTRATION_APPROVED_EMAIL_TEMPLATE = "mey_b2b/email/b2b_approved_email_template";
    const XML_PATH_B2B_REGISTRATION_DISAPPROVED_EMAIL_TEMPLATE = "mey_b2b/email/b2b_disapproved_email_template";

    public function getRegistrationRecipientEmail()
    {
        return Mage::getStoreConfig(self::XML_PATH_REGISTRATION_EMAIL_RECIPIENT);
    }

    public function getRegistrationSenderEmailIdentity()
    {
        return Mage::getStoreConfig(self::XML_PATH_REGISTRATION_EMAIL_SENDER);
    }

    public function getRegistrationEmailTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_REGISTRATION_EMAIL_TEMPLATE);
    }

    public function getB2bRegistrationApprovedEmailTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_B2B_REGISTRATION_APPROVED_EMAIL_TEMPLATE);
    }

    public function getB2bRegistrationDisapprovedEmailTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_B2B_REGISTRATION_DISAPPROVED_EMAIL_TEMPLATE);
    }

    public function getWebsite() {
        return Mage::app()->getWebsite($this->getWebsiteId());
    }

    public function getWebsiteId() {
        return Mage::getStoreConfig(self::XML_B2B_WEBSITE);
    }

    public function getStore() {
        return Mage::getModel('core/store')->load("b2b_de");
    }

    public function getStoreId() {
        return $this->getStore()->getId();
    }

    /**
     * @param $address1 Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address
     * @param $address2 Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address
     * @return bool
     */
    public function addressEqualTo($address1, $address2) {
        return
            $address1->getFirstname() == $address2->getFirstname() &&
            $address1->getLastname() == $address2->getLastname() &&
            $address1->getStreet() == $address2->getStreet() &&
            $address1->getPostcode() == $address2->getPostcode() &&
            $address1->getCity() == $address2->getCity() &&
            $address1->getCountryId() == $address2->getCountryId();
    }
}
