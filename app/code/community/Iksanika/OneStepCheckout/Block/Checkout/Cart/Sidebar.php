<?php

class Iksanika_OneStepCheckout_Block_Checkout_Cart_Sidebar 
    extends Mage_Checkout_Block_Cart_Sidebar
{
    
    
    
    /**
     * Get one page checkout page url
     *
     * @return bool
     */
    public function getCheckoutUrl()
    {
        return (!$this->helper('onestepcheckout')->isRewriteCheckoutLinksEnabled())
                ? parent::getCheckoutUrl()
                : $this->getUrl('onestepcheckout', array('_secure'=>true));
    }

    
    
}