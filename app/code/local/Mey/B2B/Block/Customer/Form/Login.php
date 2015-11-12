<?php
 
class Mey_B2B_Block_Customer_Form_Login extends Codekunst_Performance_Block_Form_Login {
    private $_customerNumber = -1;

    public function getCustomerNumber() {
        if (-1 === $this->_customerNumber) {
            $this->_customerNumber = Mage::getSingleton('customer/session')->getCustomerNumber(true);
        }
        return $this->_customerNumber;
    }
}
