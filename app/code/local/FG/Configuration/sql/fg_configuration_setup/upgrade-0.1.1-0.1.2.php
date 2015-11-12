<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// onestepcheckout settings
$config->saveConfig('carriers/dhlint/content_type', 'D');
$config->saveConfig('onestepcheckout/general/rewrite_checkout_links', '1');
$config->saveConfig('onestepcheckout/general/default_country', 'DE');
$config->saveConfig('onestepcheckout/general/default_shipping_if_one', '1');
$config->saveConfig('onestepcheckout/general/hide_shipping_method', '0');
$config->saveConfig('onestepcheckout/general/hide_payment_method', '0');
$config->saveConfig('onestepcheckout/general/checkout_title', 'Bestellung abschließen');
$config->saveConfig('onestepcheckout/general/checkout_description', 'Bitte geben Sie Ihre Adress- und Zahlungsdaten an, um die Bestellung abzuschließen.');
$config->saveConfig('onestepcheckout/general/skin', 'magento');
$config->saveConfig('onestepcheckout/general/enable_different_shipping', '1');
$config->saveConfig('onestepcheckout/general/enable_different_shipping_hide', '1');
$config->saveConfig('onestepcheckout/general/show_custom_options', '1');
$config->saveConfig('onestepcheckout/general/display_tax_included', '1');
$config->saveConfig('onestepcheckout/general/display_full_tax', '1');
$config->saveConfig('onestepcheckout/exclude_fields/exclude_region', '1');
$config->saveConfig('onestepcheckout/registration/registration_mode', 'allow_guest');
$config->saveConfig('onestepcheckout/ajax_update/enable_ajax_save_billing', '1');
$config->saveConfig('onestepcheckout/ajax_update/ajax_save_billing_fields', 'country,postcode');
$config->saveConfig('onestepcheckout/ajax_update/enable_update_payment_on_shipping', '1');
$config->saveConfig('onestepcheckout/direct_checkout/redirect_to_cart', '0');
$config->saveConfig('onestepcheckout/terms/enable_default_terms', '1');
$config->saveConfig('onestepcheckout/terms/enable_textarea', '1');
$config->saveConfig('onestepcheckout/terms/enable_terms', '0');

// amazon settings
$config->saveConfig('checkoutbyamazon/signup/info_active', '1');
$config->saveConfig('checkoutbyamazon/general/active', '1');
$config->saveConfig('checkoutbyamazon/general/marketplace', 'de_DE');
$config->saveConfig('checkoutbyamazon/general/merchant_id', 'A19LBWKW6DWUDZ');
$config->saveConfig('checkoutbyamazon/general/new_order_status', '1');
$config->saveConfig('checkoutbyamazon/general/sandbox_mode', '1');

// cashondelivery settings
$config->saveConfig('payment/phoenix_cashondelivery/active', '1');
$config->saveConfig('payment/phoenix_cashondelivery/display_zero_fee', '0');
$config->saveConfig('payment/phoenix_cashondelivery/title', 'Nachnahme');
$config->saveConfig('payment/phoenix_cashondelivery/order_status', 'processing');
$config->saveConfig('payment/phoenix_cashondelivery/inlandcosts', '4.9');
$config->saveConfig('payment/phoenix_cashondelivery/active', '1');

// sofort iDEAL settings
$config->saveConfig('payment/sofort_ideal/active', '1');
$config->saveConfig('payment/sofort_ideal/configkey', '75631:186034:949ad535a3aeab66a8741d0f942c0a27');
$config->saveConfig('payment/sofort_ideal/order_status_holding', 'holded');
$config->saveConfig('payment/sofort_ideal/order_status_waiting', 'pending');
$config->saveConfig('payment/sofort_ideal/order_status', 'processing');


$installer->endSetup();

