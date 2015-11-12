<?php

class Iksanika_OneStepCheckout_Block_Checkout_Onepage_Link 
    extends Mage_Checkout_Block_Onepage_Link
{
    
    
    
    public function getCheckoutUrl()
    {
        return (!$this->helper('onestepcheckout')->isRewriteCheckoutLinksEnabled())
                ? parent::getCheckoutUrl()
                : $this->getUrl('onestepcheckout', array('_secure'=>true));
    }
    
    
    
}
