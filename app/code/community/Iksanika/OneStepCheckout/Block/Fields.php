<?php

class Iksanika_OneStepCheckout_Block_Fields 
    extends Iksanika_OneStepCheckout_Block_Checkout
{
    public function _construct()
    {
        $this->setSubTemplate(true);
        parent::_construct();
    }
}