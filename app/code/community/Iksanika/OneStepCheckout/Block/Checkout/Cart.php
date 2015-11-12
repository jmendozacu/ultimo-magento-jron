<?php



class Iksanika_OneStepCheckout_Block_Checkout_Cart 
    extends Mage_Checkout_Block_Cart
{
    
    
    
    public function getCheckoutUrl()
    {
        return (!$this->helper('onestepcheckout')->isRewriteCheckoutLinksEnabled())
                ? parent::getCheckoutUrl()
                : $this->getUrl('onestepcheckout', array('_secure'=>true));
    }
    
    
    
}
