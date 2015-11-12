<?php

class Iksanika_OneStepCheckout_Model_Source_Skin
{
    public function toOptionArray()
    {
        $options = array(
            array('label'=>'Generic OneStepCheckout skin', 'value'=>'generic'),
            array('label'=>'Magento look and feel', 'value'=>'magento'),
        );

        return $options;
    }
}