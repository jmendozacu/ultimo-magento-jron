<?php

class Mey_B2B_Block_Form_Register extends Mage_Core_Block_Template {
    /**
     * Retrieve form data
     *
     * @return Varien_Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $formData = Mage::getSingleton('customer/session')->getRegistrationFormData(true);
            $data = new Varien_Object();
            if ($formData) {
                $data->addData($formData);
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    public function getPostActionUrl() {
        return $this->getUrl("customer/account/createB2BPost");
    }
}
