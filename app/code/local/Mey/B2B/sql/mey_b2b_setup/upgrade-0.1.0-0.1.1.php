<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Change specific config data for b2b website
$websiteId = Mage::getResourceModel("core/website_collection")->addFieldToFilter("code", "b2b")->getFirstItem()->getId();

$_config = new Mage_Core_Model_Config();
$_options = array(
    "design/head/default_robots" => "NOINDEX"
);
foreach( $_options as $_path => $_value ) {
    $_config->saveConfig( $_path, $_value, 'websites', $websiteId );
}

$installer->endSetup();
