<?php

class Mey_Csrf_Model_Observer {
    public function disableCsrf(Varien_Event_Observer $observer) {
        $events = array(
            'checkout_cart_add',
            'checkout_cart_addgroup',
            'checkout_cart_updatepost',
            'codekunst_looks_cart_add',
            'review_product_post',
            'sendfriend_product_sendmail',
            'wishlist_index_add',
            'wishlist_index_allcart',
            'wishlist_index_update',
            'wishlist_index_cart',
            'wishlist_index_send',
            'catalog_product_compare_add',
            'customer_account_createb2bpost',
            'customer_account_loginpost',
        );
        $route = $observer->getEvent()->getControllerAction()->getFullActionName();

        if (in_array(strtolower($route), $events)) {
            $key = Mage::getSingleton('core/session')->getFormKey();
            Mage::app()->getRequest()->setParam('form_key', $key);
        }
    }
}
