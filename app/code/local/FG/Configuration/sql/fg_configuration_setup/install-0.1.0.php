<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// extension settings
$config->saveConfig('saferpaycw/general/account_id', "97884-17788281");
$config->saveConfig('saferpaycw/general/order_id_schema', "Bestellung_{id}");
$config->saveConfig('saferpaycw/general/description', "Ihre Bestellung bei mey");

// direct debits
$config->saveConfig('payment/saferpaycw_directdebits/active', "1");
$config->saveConfig('payment/saferpaycw_directdebits/title', "Lastschrift");
$config->saveConfig('payment/saferpaycw_directdebits/description', "Zahlung per Lastschrift");
$config->saveConfig('payment/saferpaycw_directdebits/allowspecific', "1");
$config->saveConfig('payment/saferpaycw_directdebits/specificcountry', "DE");
$config->saveConfig('payment/saferpaycw_directdebits/Currency', "EUR");

// credit card
$config->saveConfig('payment/saferpaycw_creditcard/active', "1");
$config->saveConfig('payment/saferpaycw_creditcard/title', "Kreditkarte");
$config->saveConfig('payment/saferpaycw_creditcard/description', "Zahlung per Kreditkarte");
$config->saveConfig('payment/saferpaycw_creditcard/allowspecific', "1");
$config->saveConfig('payment/saferpaycw_creditcard/specificcountry', "DE");
$config->saveConfig('payment/saferpaycw_creditcard/Currency', "EUR");

$installer->endSetup();

