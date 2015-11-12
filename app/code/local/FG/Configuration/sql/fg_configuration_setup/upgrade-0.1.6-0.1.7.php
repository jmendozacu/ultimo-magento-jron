<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// only show mastercard and visa as brands
$config->saveConfig('payment/saferpaycw_creditcard/credit_card_brands', 'mastercard,visa');

$config->saveConfig('payment/codekunst_payglobe/information_text', 'Auf Rechnung bequem nach Erhalt der Ware zahlen.');
$config->saveConfig('payment/phoenix_cashondelivery/customtext', 'Sie zahlen direkt bei Erhalt der Ware an den Postboten.');
$config->saveConfig('payment/paypal_standard/information_text', 'Mit PayPal online bezahlen. Einfach sicherererer.');

$config->saveConfig('payment/sofort_ideal/information_text', 'Wanneer u betaalt met iDEAL kunt u de betaling direct voldoen via uw eigen bank. Aangesloten banken zijn ABN AMRO, ASN Bank, Friesland Bank, ING, Knab, Rabobank, SNS Bank, SNS Regio Bank en Triodos Bank.<br />U bent dan dus direct in de vertrouwde betaalomgeving van uw eigen bank.', 'store', 3);

// payment method sort orders for de and en
$config->saveConfig('payment/saferpaycw_creditcard/sort_order', '1');
$config->saveConfig('payment/paypal_standard/sort_order', '2');
$config->saveConfig('payment/codekunst_payglobe/sort_order', '3');
$config->saveConfig('payment/bankpayment/sort_order', '4');
$config->saveConfig('payment/phoenix_cashondelivery/sort_order', '5');

// payment method sort orders for nl
$config->saveConfig('payment/saferpaycw_creditcard/sort_order', '1', 'store', 3);
$config->saveConfig('payment/sofort_ideal/sort_order', '2', 'store', 3);
$config->saveConfig('payment/paypal_standard/sort_order', '3', 'store', 3);
$config->saveConfig('payment/bankpayment/sort_order', '4', 'store', 3);
$config->saveConfig('payment/phoenix_cashondelivery/sort_order', '5', 'store', 3);

$installer->endSetup();

