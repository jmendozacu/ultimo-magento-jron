<?php

class Iksanika_OneStepCheckout_Block_Links 
    extends Mage_Core_Block_Template
{
    public function addCheckoutLink()
    {
        if (!$this->helper('checkout')->canOnepageCheckout()) 
        {
            return $this;
        }

        $parentBlock = $this->getParentBlock();

        if (!is_object($parentBlock)) 
        {
            $text = $this->__('Checkout');
            $parentBlock->addLink($text, 'onestepcheckout', $text, true, array('_secure'=>true), 60, null, 'class="top-link-onestepcheckout"');
        }
        return $this;
    }

}
