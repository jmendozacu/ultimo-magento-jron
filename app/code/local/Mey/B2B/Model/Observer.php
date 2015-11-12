<?php

class Mey_B2B_Model_Observer
{
    const XML_SALES_NEW_ORDER_BCC = "mey_b2b/email/sales_new_order_bcc";

    public function checkForLogin(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getWebsite()->getId() == Mage::helper("mey_b2b")->getWebsiteId()) {
            $allow = array(
                'customer_account_login',
                'customer_account_forgotpassword',
                'customer_account_resetpassword',
                'customer_account_loginpost',
                'customer_account_forgotpasswordpost',
                'customer_account_resetpasswordpost',
                'customer_account_createb2b',
                'customer_account_createb2bpost',
                'customer_account_createb2bsuccess',
                'mey_b2bcustomer_index_approve',
                'mey_b2bcustomer_index_disapprove',
                'turpentine_esi_getblock',
            );

            $allowCms = array(
                "/newsletter/",
                "/newsletter-abmelden/",
            );

            $disallowRoute = false;

            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                if ($observer->getControllerAction()->getFullActionName() === "cms_page_view") {
                    $pathInfo = Mage::app()->getFrontController()->getRequest()->getOriginalPathInfo();
                    if (!in_array($pathInfo, $allowCms)) {
                        $disallowRoute = true;
                    }
                } else if (!in_array(strtolower($observer->getControllerAction()->getFullActionName()), $allow)) {
                    $disallowRoute = true;
                }
            }

            if ($disallowRoute) {
                $fullRequestUrl = Mage::app()->getRequest()->getScheme() . "://" . Mage::app()->getRequest()->getHttpHost() . Mage::app()->getRequest()->getRequestUri();
                Mage::getSingleton("customer/session")->setBeforeAuthUrl($fullRequestUrl);
                Mage::app()->getResponse()->setRedirect(Mage::helper("customer")->getLoginUrl())->sendResponse();
                exit;
            }
        }
    }

    public function setPaymentAndShippingMethods(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $helper = Mage::helper('onestepcheckout/checkout');
        if (Mage::app()->getWebsite()->getId() == Mage::helper("mey_b2b")->getWebsiteId()) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if (empty($shippingMethod)) {
                $helper->saveShippingMethod("freeshipping_freeshipping");
                
                if ($quote->isVirtual()) {
                    $quote->getBillingAddress()->setPaymentMethod("mey_b2bpayment");
                } else {
                    $quote->getShippingAddress()->setPaymentMethod("mey_b2bpayment");
                }
                $quote->getPayment()->importData(array("method" => "mey_b2bpayment"));
            }
        }
    }

    public function addBccToSaleEmail(Varien_Event_Observer $observer) {
        /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
        $mailer = $observer->getEvent()->getMailer();
        /** @var Mey_B2B_Model_Sales_Order $order */
        $order = $observer->getEvent()->getOrder();

        if(Mage::app()->getStore($mailer->getStoreId())->getWebsiteId() != Mage::helper("mey_b2b")->getWebsiteId()) {
            return;
        } else {
            if(!Mage::helper("mey_b2b")->addressEqualTo($order->getBillingAddress(), $order->getShippingAddress())) {
                $bcc = Mage::getStoreConfig(self::XML_SALES_NEW_ORDER_BCC, $mailer->getStoreId());
                if(!empty($bcc)) {
                    $mailer->addBcc($bcc);
                }
            }
        }
    }
}
