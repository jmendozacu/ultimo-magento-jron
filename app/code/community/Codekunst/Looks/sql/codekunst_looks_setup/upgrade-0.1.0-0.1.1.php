<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'is_look', array(
    'type' => 'int',
    'label'=> 'Is look product?',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => false,
    'visible_on_front' => false,
    'required' => false,
    'user_defined' => true,
    'default' => 0,
    'group' => 'General',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'is_configurable' => false,
));

$installer->endSetup();
