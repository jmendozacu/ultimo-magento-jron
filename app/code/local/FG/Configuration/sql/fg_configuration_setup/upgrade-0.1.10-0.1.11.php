<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();
$helper = new Mage_Core_Helper_Data();

$config->saveConfig('payment/paypal_standard/active', '0');

$config->saveConfig('payment/paypal_express/active', '1');
$config->saveConfig('payment/paypal_express/title', 'PayPal');
$config->saveConfig('payment/paypal_express/payment_action', 'Sale');
$config->saveConfig('payment/paypal_express/allowspecific', '1');
$config->saveConfig('payment/paypal_express/specificcountry', 'BE,DK,DE,FR,IE,LU,NL,AT,CH,GB');
$config->saveConfig('payment/paypal_express/visible_on_cart', '0');
$config->saveConfig('payment/paypal_express/visible_on_product', '0');
$config->saveConfig('payment/paypal_express/information_text', 'Mit PayPal online bezahlen. Einfach sicherererer.');
$config->saveConfig('paypal/wpp/api_username', $helper->encrypt("paypal_api1.mey.de"));
$config->saveConfig('paypal/wpp/api_password', $helper->encrypt("GWZSS4W7QS6BY5TB"));
$config->saveConfig('paypal/wpp/api_signature', $helper->encrypt("AiPC9BjkCyDFQXbSkoZcgqH3hpacA6B-t.BL2ogQhf0c1xXRtu5KexvD"));

// payment method sort orders for de and en
$config->saveConfig('payment/paypal_express/sort_order', '2');
// payment method sort orders for nl
$config->saveConfig('payment/paypal_express/sort_order', '3', 'store', 3);

$installer->endSetup();

