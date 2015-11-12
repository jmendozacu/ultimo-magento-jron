<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

$config->saveConfig('checkoutbyamazon/general/sandbox_mode', '0');

$config->saveConfig('saferpaycw/general/operation_mode', 'live');
$config->saveConfig('saferpaycw/general/password', 'Nu2sKd5HDs7RRo4y');

$config->saveConfig('payment/codekunst_payglobe/test', '0');
$config->saveConfig('payment/codekunst_payglobe/client_id', 'ZcpYtk4lQ2dpGu3/mfEdTbrO5cWxNbwE');
$config->saveConfig('payment/codekunst_payglobe/client_secret', 'Y0bEj0eJDlLKLCm6hXpf5IZWRO3Q3taQgiihVuFLNv51qiBgecUMh/E3zMU8');

$config->saveConfig('payment/paypal_standard/sandbox_flag', '0');
$installer->endSetup();

