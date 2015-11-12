<?php

class Codekunst_Payglobe_Model_Payment_Method_Payglobe extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'codekunst_payglobe';
    protected $_infoBlockType = 'codekunst_payglobe/info_payglobe';
    protected $_formBlockType = 'codekunst_payglobe/form_payglobe';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway                   = false;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = true;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = false;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = false;
    protected $_canCancelInvoice            = false;


    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();
        /** @var $session Mage_Checkout_Model_Session */
        $session = Mage::getSingleton('checkout/session');
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $session->getQuote();
        if (is_null($quote)) {
            $quote = $this->getInfoInstance()->getQuote();
        }

        $dob = $quote->getCustomerDob();
        if(empty($dob)){
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Date of Birth is a required field.');
        }

        $gender = $quote->getCustomerGender();
        if(empty($errorMsg) && empty($gender)){
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Gender is a required field.');
        }

        if(empty($errorMsg) && !$this->_isQualified($quote)) {
            $errorMsg = $this->_getHelper()->__('You are not qualified for payment with this method.');
        }

        if(!empty($errorMsg)) {
            Mage::throwException($errorMsg);
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null) {
        $isAvailable = parent::isAvailable($quote);

        if($isAvailable) {
            // Allow if billing and shipping address are the same.
            $shippingAddress = $quote->getShippingAddress();
            if($shippingAddress->getSameAsBilling()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether current operation is order placement
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

    /**
     * Checks with the API if the customer is qualified to use this payment method.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    protected function _isQualified($quote) {
        $authorization = Mage::getModel('codekunst_payglobe/api_authorization')->getAuthorization($quote);
        return $authorization;
    }

    protected function _getHelper() {
        return Mage::helper('codekunst_payglobe');
    }
}
