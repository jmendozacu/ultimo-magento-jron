<?php
$setup = new Mage_Catalog_Model_Resource_Setup();
$setup->startSetup();

//Thrid Size
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "third_size", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Brustumfang',
    'input'        => 'select',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 1,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'filterable'   => true,
));

$setup->endSetup();

?>