<?php

class Mey_Amazon_Model_Observer {
    public function clearAmazonData(Varien_Event_Observer $observer) {
        $events = array(
            'onestepcheckout_index_index',
        );
        $route = $observer->getEvent()->getControllerAction()->getFullActionName();

        if (in_array($route, $events)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if($quote && $quote->getId()) {
                $address = $quote->getBillingAddress();
                $this->_cleanAddress($address);

                $address = $quote->getShippingAddress();
                $this->_cleanAddress($address);

                if(strstr($quote->getCustomerEmail(), 'service@mey-shop.com')) {
                    $quote->setCustomerEmail('');
                    $quote->save();
                }
            }
        }
    }

    /**
     * @param $address Mage_Sales_Model_Quote_Address
     */
    protected function _cleanAddress(&$address) {
        if(strstr($address->getStreet1(), '-- PROCESSING --')) {
            $address->setStreet(array());
            $address->save();
        }
    }
}
