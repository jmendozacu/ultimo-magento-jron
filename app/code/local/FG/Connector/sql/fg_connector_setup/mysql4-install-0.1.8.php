<?php
$installer = $this;
//Bestellzustände hinzufügen
$statusTable        = $installer->getTable('sales/order_status');

$newData = ['faulty','partially_open','partially_processing','readytoship','partially_readytoship','delivered','partially_delivered','received','partially_received'];
$isNew = true;
$data = array(
    array('status' => 'faulty', 'label' => 'Faulty'),
    array('status' => 'partially_open', 'label' => 'Partially Open'),
    array('status' => 'partially_processing', 'label' => 'Partially Proccessing'),
    array('status' => 'readytoship', 'label' => 'Ready to Ship'),
    array('status' => 'partially_readytoship', 'label' => 'Partially Ready to Ship'),
    array('status' => 'delivered', 'label' => 'Delivered'),
    array('status' => 'partially_delivered', 'label' => 'Partially Delivered'),
    array('status' => 'received', 'label' => 'Received'),
    array('status' => 'partially_received', 'label' => 'Partially Received')

);
$select = $this->getConnection()->select();
$select->from($statusTable);
$currentStatusTable = $this->getConnection()->fetchAll($select);
foreach ($currentStatusTable as $currentStatus) {
  if(in_array($currentStatus['status'], $newData)){
    $isNew = false;
    break;
  }
}
if($isNew){
  $installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
}

//EAN als Attribute hinzufügen
$setup = new Mage_Catalog_Model_Resource_Setup();
$setup->startSetup();
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "ean", array(
   'type'                       => 'varchar',
   'visible'                    => true,
   'label'                      => 'EAN',
   'input'                      => 'text',
   'required'                   => false,
   'user_defined'               => true,
   'default'                    => '',
   'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
   'sort_order'                 => 1,
   'group'                      => 'General',
   'apply_to'                   => 'simple',
       'searchable'   => true,
));
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "number", array(
   'type'                       => 'varchar',
   'visible'                    => true,
   'label'                      => 'number',
   'input'                      => 'text',
   'required'                   => false,
   'user_defined'               => true,
   'default'                    => '',
   'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
   'sort_order'                 => 2,
   'group'                      => 'General',
   'apply_to'                   => 'simple',
   'searchable'   => true,
));
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "iid", array(
   'type'                       => 'int',
   'visible'                    => true,
   'label'                      => 'IID',
   'input'                      => 'text',
   'required'                   => false,
   'user_defined'               => true,
   'default'                    => '',
   'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
   'sort_order'                 => 3,
   'group'                      => 'General',
   'apply_to'                   => 'simple',
  'searchable'   => true,
));
//Attribute Größe anlegen
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "primary_size", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Größe',
    'input'        => 'select',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 4,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'is_configurable' => true,
    'searchable'   => true,
    'filterable'   => true,
));
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "secondary_size", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Korbgröße',
    'input'        => 'select',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'filterable'   => true,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "color", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Farbe',
    'input'        => 'select',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => true,
    'filterable'   => true,
    'is_configurable' => true,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "filter_color", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Filterfarbe',
    'input'        => 'select',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => true,
    'filterable'   => true,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "care_instructions", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Pflegeanleitung',
    'input'        => 'multiselect',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => false,
    'filterable'   => false,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "filter_material", array(
    'type'         => 'varchar',
    'visible'      => true,
    'label'        => 'Filtermaterial',
    'input'        => 'select',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => false,
    'filterable'   => false,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "material1", array(
    'type'         => 'text',
    'visible'      => true,
    'label'        => 'Material1',
    'input'        => 'textarea',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => true,
    'filterable'   => true,
    'is_configurable' => true,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "material2", array(
    'type'         => 'text',
    'visible'      => true,
    'label'        => 'Material2',
    'input'        => 'textarea',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => true,
    'filterable'   => true,
    'is_configurable' => true,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "sellingpoints", array(
    'type'         => 'text',
    'visible'      => true,
    'label'        => 'Sellingpoints',
    'input'        => 'textarea',
    'required'     => false,
    'user_defined' => true,
    'default'      => '',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'   => 5,
    'group'        => 'General',
    'apply_to'     => 'simple',
    'searchable'   => true,
    'filterable'   => true,
    'is_configurable' => true,
));

$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "product_family", array(
   'type'                       => 'varchar',
   'visible'                    => true,
   'label'                      => 'ProductFamily',
   'input'                      => 'text',
   'required'                   => false,
   'user_defined'               => true,
   'default'                    => '',
   'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
   'sort_order'                 => 1,
   'group'                      => 'General',
   'apply_to'                   => 'configurable',
));
$setup->endSetup();
