<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

// packstation settings
$config->saveConfig('shipping/packstation/disallowed_payment_methods', 'phoenix_cashondelivery,sofort_ideal');


$installer->endSetup();
