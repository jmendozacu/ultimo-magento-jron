<?php

class Mey_ValidateCustomerAddress_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_requiredFields = array(
        'firstname' => 'NotEmpty',
        'lastname' => 'NotEmpty',
        'street' => 'NotEmpty',
        'postcode' => 'NotEmpty',
        'city' => 'NotEmpty',
        'country_id' => 'NotEmpty',
    );

    /**
     * @param Mage_Customer_Model_Address $address
     * @return bool
     */
    public function addressIsValid($address) {
        $isValid = true;

        foreach($this->_requiredFields as $field => $validator) {
            if(!Zend_Validate::is($address->getData($field), $validator)) {
                $isValid = false;
            }
        }

        return $isValid;
    }
}
