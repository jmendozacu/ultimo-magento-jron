<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Change specific config data for b2b website
$websiteId = Mage::getResourceModel("core/website_collection")->addFieldToFilter("code", "b2b")->getFirstItem()->getId();

$_config = new Mage_Core_Model_Config();
$_options = array(
    "trans_email/ident_general/name" => "MeyB2B - Service",
    "trans_email/ident_general/email" => "service@meyb2b.com",
    "customer/startup/redirect_dashboard" => 0,
);
foreach( $_options as $_path => $_value ) {
    $_config->saveConfig( $_path, $_value, 'websites', $websiteId );
}

$installer->endSetup();