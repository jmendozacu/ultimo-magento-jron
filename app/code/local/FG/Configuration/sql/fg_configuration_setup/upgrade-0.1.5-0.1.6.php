<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// activate iDEAL only for nl
$config->saveConfig('payment/sofort_ideal/active', '0');
$config->saveConfig('payment/sofort_ideal/active', '1', 'store', 3);

$installer->endSetup();

