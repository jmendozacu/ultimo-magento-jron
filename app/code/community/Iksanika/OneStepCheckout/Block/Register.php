<?php

class Iksanika_OneStepCheckout_Block_Register 
    extends Mage_Checkout_Block_Onepage_Abstract    
{

    
    
    public function show()
    {
        $lastOrderId        =   $this->getOnepage()->getCheckout()->getLastOrderId();
        $order              =   Mage::getModel('sales/order')->load($lastOrderId);
        $registration_mode  =   Mage::getStoreConfig('onestepcheckout/registration/registration_mode');

        return ($lastOrderId && !$this->_isLoggedIn() && !$this->_isEmailRegistered($order->getCustomerEmail()) && $registration_mode == 'registration_success')
                ? true
                : false;
    }

    
    
    protected function _isEmailRegistered($email)
    {
        $model = Mage::getModel('customer/customer');
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

        return ($model->getId() == NULL) ? false : true;
    }

    
    
    protected function _isLoggedIn()
    {
        $helper = $this->helper('customer');
        return $helper->isLoggedIn() ? true : false;
    }

    
    
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
}
