<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// payglobe settings
$config->saveConfig('payment/codekunst_payglobe/active', '1');
$config->saveConfig('payment/codekunst_payglobe/title', 'Kauf auf Rechnung');
$config->saveConfig('payment/codekunst_payglobe/client_id', 'uTnzg5ba2qR5Dftp5MKUS1Y/TBYJzJ0JwTIkICYkAofbpHBH53ZhkePqumDm');
$config->saveConfig('payment/codekunst_payglobe/client_secret', 'pZkHqvCF3jVYpNh1r6X7C6wM8w0SGX9plkx3OZ8xz7qQ8xpUbERoX/IzzxIZ');
$config->saveConfig('payment/codekunst_payglobe/test', '1');
$config->saveConfig('payment/codekunst_payglobe/debug', '0');
$config->saveConfig('customer/address/dob_show', 'req');
$config->saveConfig('customer/address/gender_show', 'req');


$installer->endSetup();
