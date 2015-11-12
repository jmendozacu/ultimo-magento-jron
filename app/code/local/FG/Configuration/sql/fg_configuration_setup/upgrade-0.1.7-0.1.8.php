<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$config = new Mage_Core_Model_Config();

$config->saveConfig('customer/address/prefix_show', 'opt');

$installer->endSetup();

