<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'look_text_title', array(
    'group'             => 'General',
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'Look Text Title',
    'backend'           => '',
    'required'          => false,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'visible_on_front'  => false,
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'is_configurable'   => false,
));

$installer->addAttribute('catalog_product', 'look_text', array(
    'group'                     => 'General',
    'input'                     => 'textarea',
    'type'                      => 'text',
    'label'                     => 'Look Text',
    'backend'                   => '',
    'required'                  => false,
    'wysiwyg_enabled'           => false,
    'is_html_allowed_on_front'  => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => false,
    'visible_on_front'          => false,
    'user_defined'              => true,
    'searchable'                => false,
    'filterable'                => false,
    'comparable'                => false,
    'is_configurable'           => false,
));

$installer->endSetup();
