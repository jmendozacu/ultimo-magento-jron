<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// extension settings
$config->saveConfig('payment/saferpaycw_directdebits/authorizationMethod', "HiddenAuthorization");
$config->saveConfig('payment/saferpaycw_creditcard/authorizationMethod', "HiddenAuthorization");

$installer->endSetup();

