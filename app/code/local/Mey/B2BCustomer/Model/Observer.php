<?php

class Mey_B2BCustomer_Model_Observer {
    public function propagateAddressToSubcustomers(Varien_Event_Observer $observer) {
        // Addresses from the subcustomer always get created on the main customer.
        // So all we have to do here is make sure that every subcustomer has all addresses of the main customer.
        $address = $observer->getEvent()->getCustomerAddress();

        $customer = Mage::getSingleton("customer/session")->getCustomer();
        $sublogins = Mage::getResourceModel("sublogin/sublogin_collection")->addFieldToFilter("entity_id", array("eq" => $customer->getId()));

        foreach($sublogins as $sublogin) {
            $addressIds = explode(",", $sublogin->getAddressIds());

            if(!in_array($address->getId(), $addressIds)) {
                $addressIds[] = $address->getId();
            }

            $sublogin->setAddressIds(implode(",", $addressIds))->save();
        }
    }
}
