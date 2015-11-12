<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'look_image', array(
    'group'             => 'Images',
    'type'              => 'varchar',
    'frontend'          => 'catalog/product_attribute_frontend_image',
    'label'             => 'Look Image',
    'input'             => 'media_image',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'default'           => '',
    'class'             => '',
    'source'            => '',
    'required'          => false
));

$installer->endSetup();
