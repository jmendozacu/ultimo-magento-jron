<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$attribute  = array(
    'type' => 'int',
    'label'=> 'Is look category?',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => false,
    'visible_on_front' => false,
    'required' => false,
    'user_defined' => true,
    'default' => 0,
    'group' => 'General Information',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false
);
$installer->addAttribute('catalog_category', 'is_look', $attribute);

$installer->endSetup();
