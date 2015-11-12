<?php

class Codekunst_Packstation_Model_Observer
{
    private $notSupportedPaymentMethods = array();

    public function __construct()
    {
        $this->notSupportedPaymentMethods = explode(',', Mage::getStoreConfig('shipping/packstation/disallowed_payment_methods'));
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkPaymentOption(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();
        /* @var $order Mage_Sales_Model_Order */

        $strPaymentMethod = $order->getPayment()->getMethod();

        if (in_array($strPaymentMethod, $this->notSupportedPaymentMethods))
        {
            $strStreet = strtolower(implode('', $order->getShippingAddress()->getStreet()));

            if (preg_match('/packstation/', $strStreet) !== 0)
            {
                Mage::getSingleton('core/session')->addError(Mage::helper('codekunst_packstation')->__('Packstation is not allowed with the selected payment method.'));
                Mage::app()->getResponse()->setRedirect(Mage::getUrl('onestepcheckout'), array('_secure' => true))->sendResponse();
                exit();
            }
        }

        return $this;
    }
}
