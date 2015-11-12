<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

$config->saveConfig('google/googletagmanager/active', '1');
$config->saveConfig('google/googletagmanager/datalayertransactions', '1');
$config->saveConfig('google/googletagmanager/datalayervisitors', '1');
$config->saveConfig('google/googletagmanager/containerid', 'GTM-KXPNQB');

$installer->endSetup();

