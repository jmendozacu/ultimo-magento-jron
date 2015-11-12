<?php

class Codekunst_Saferpay_Helper_Data extends Customweb_SaferpayCw_Helper_Data {
    public function getFailUrl($transaction)
    {
        $frontentId =  'checkout/onepage/';

        // If the onestep checkout module is enabled redirect there
        if((Mage::helper('core')->isModuleEnabled('Idev_OneStepCheckout') || Mage::helper('core')->isModuleEnabled('Iksanika_OneStepCheckout'))
            && Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links')) {
            $frontentId =  'onestepcheckout';
        }

        // If the firecheckout module is enabled redirect there
        if(Mage::helper('core')->isModuleEnabled('TM_FireCheckout') && Mage::getStoreConfig('firecheckout/general/enabled')) {
            $frontentId =  'firecheckout';
        }

        $redirectionUrl = Customweb_Util_Url::appendParameters(
            Mage::getUrl($frontentId, array('_secure' => true)),
            array('loadFailed' => 'true')
        );

        $result = new StdClass;
        $result->url = $redirectionUrl;
        Mage::dispatchEvent('customweb_failure_redirection', array(
            'result' => $result,
            'transaction' => $transaction
        ));

        return $result->url;
    }
}
