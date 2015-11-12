<?php

class Iksanika_OneStepCheckout_Model_Source_Ajaxbilling
{
    
    
    public function toOptionArray()
    {
        $colors = array('Country', 'Postcode', 'State/region', 'City');
        $temp = array();

        foreach($colors as $color)	
        {
            $temp[] = array('label' => $color, 'value' => strtolower($color));
        }

        return $temp;
    }
}