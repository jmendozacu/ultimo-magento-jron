<?php

class Iksanika_OneStepCheckout_Model_Source_Registration
{
    
    
    public function toOptionArray()
    {
        $options = array(
            array('label'=>'Require registration/login', 'value'=>'require_registration'),
            array('label'=>'Disable registration/login', 'value'=>'disable_registration'),
            array('label'=>'Allow guests and logged in users', 'value'=>'allow_guest'),
            array('label'=>'Enable registration on success page', 'value'=>'registration_success'),
            array('label'=>'Auto-generate account for new emails', 'value'=>'auto_generate_account'),
        );

        return $options;
    }
}