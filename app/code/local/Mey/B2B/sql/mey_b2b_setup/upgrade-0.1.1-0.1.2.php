<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->modifyColumn($installer->getTable('customerprices/prices'), 'customer_id', 'INT');

$installer->endSetup();
