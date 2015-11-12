<?php

class Mey_B2B_Model_Sales_Quote extends Iksanika_OneStepCheckout_Model_Sales_Quote {
    public function validateMinimumAmount($multishipping = false) {
        if (Mage::app()->getWebsite()->getId() == Mage::helper("mey_b2b")->getWebsiteId()) {
            if(is_array(Mage::app()->getRequest()->getParam("billing")) && !array_key_exists("use_for_shipping", Mage::app()->getRequest()->getParam("shipping"))) {
                return true;
            }
        }

        return parent::validateMinimumAmount($multishipping);
    }
}
