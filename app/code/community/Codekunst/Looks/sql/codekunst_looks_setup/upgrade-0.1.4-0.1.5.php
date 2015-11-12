<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, 'bg_look_image');
$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, 'look_image');

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'look_part', array(
    'type' => 'int',
    'label' => 'Is product part of a look?',
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

$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'is_look', 'apply_to', Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'look_text_title', 'apply_to', Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'look_text', 'apply_to', Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'look_part', 'apply_to', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

$installer->endSetup();