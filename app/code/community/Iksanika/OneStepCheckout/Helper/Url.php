<?php

class Iksanika_OneStepCheckout_Helper_Url 
    extends Mage_Checkout_Helper_Url
{
    
    
    /**
     * Retrieve checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return (!Mage::helper('onestepcheckout')->isRewriteCheckoutLinksEnabled()) ? 
                parent::getCheckoutUrl() : 
                $this->_getUrl('onestepcheckout', array('_secure' => true));
    }
    
    
}