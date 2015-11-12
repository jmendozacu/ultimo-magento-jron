<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database for tables setup
 */
$installer->startSetup();

$table = $installer->getConnection()->query("ALTER TABLE `{$installer->getTable("customerprices_prices")}` ADD UNIQUE( `customer_id`, `product_id`, `store_id`, `qty`);");

/**
 * Prepare database after tables setup
 */
$installer->endSetup();