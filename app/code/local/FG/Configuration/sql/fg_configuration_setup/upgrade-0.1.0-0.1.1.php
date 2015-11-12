<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// paypal settings
$config->saveConfig('paypal/general/merchant_country', "DE");
$config->saveConfig('paypal/general/business_account', "paypal@mey.de");

$config->saveConfig('payment/paypal_standard/active', "1");
$config->saveConfig('payment/paypal_standard/title', "PayPal");
$config->saveConfig('payment/paypal_standard/payment_action', "Sale");
$config->saveConfig('payment/paypal_standard/allowspecific', "1");
$config->saveConfig('payment/paypal_standard/sandbox_flag', "1");
$config->saveConfig('payment/paypal_standard/line_items_enabled', "1");
$config->saveConfig('payment/paypal_standard/specificcountry', "DE");

// deactivate default magento payment extension
$config->saveConfig('payment/ccsave/active', "0");
$config->saveConfig('payment/checkmo/active', "0");

// Phoenix bankpayment
$config->saveConfig('payment/bankpayment/bank_accounts', 'ca:6:{s:14:"account_holder";a:2:{i:0;s:0:"";i:1;s:16:"Mey Handels GmbH";}s:14:"account_number";a:2:{i:0;s:0:"";i:1;s:9:"0 166 280";}s:9:"sort_code";a:2:{i:0;s:0:"";i:1;s:11:"653 700 75 ";}s:9:"bank_name";a:2:{i:0;s:0:"";i:1;s:23:"Deutsche Bank, Albstadt";}s:4:"iban";a:2:{i:0;s:0:"";i:1;s:22:"DE45653700750016628000";}s:3:"bic";a:2:{i:0;s:0:"";i:1;s:11:"DEUTDESS653";}}');
$config->saveConfig('payment/bankpayment/active', "1");
$config->saveConfig('payment/bankpayment/title', "Vorkasse");
$config->saveConfig('payment/bankpayment/order_status', "pending_payment");
$config->saveConfig('payment/bankpayment/allowspecific', "1");
$config->saveConfig('payment/bankpayment/specificcountry', "DE");
$config->saveConfig('payment/bankpayment/show_bank_accounts_in_pdf', "1");
$config->saveConfig('payment/bankpayment/customtext', "Bei der Überweisung geben Sie bitte unbedingt Ihren Namen und Ihre Bestellnummer als Verwendungszweck an. Nach Eingang der vollständigen Rechnungssumme verlässt Ihre Bestellung unser Logistikzentrum nach maximal 2-4 Werktagen.");
$config->saveConfig('payment/bankpayment/show_customtext_in_pdf', "1");

$installer->endSetup();
