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

//prüfen ob bereits einer der neuen Bestellzuständ vorhanden ist
$select = $this->getConnection()->select();
$select->from($statusTable);
$currentStatusTable = $this->getConnection()->fetchAll($select);
foreach ($currentStatusTable as $currentStatus) {
  if(in_array($currentStatus['status'], $newData)){
    //Falls bereits einer der Zustände vorhanden ist, wurde das Setup schon ausgeführt 
    //daher brechen wir hier ab und fügen keine Zustände hinzu
    $isNew = false;
    break;
  }
}
if($isNew){
  $installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
}

//Für das anlegen der Attribute wird ein anderes Model benötigt
$setup = new Mage_Catalog_Model_Resource_Setup();
$setup->startSetup();

//EAN
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
   'searchable'                 => true,
   'is_configurable'            => false,
));

//number - Artikelnnummer des Kunden
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
   'apply_to'                   => 'simple,configurable',
   'searchable'                 =>  true,
   'is_configurable'            => false,
));

//iid - Alvine IID des Stammproduktes
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
   'apply_to'                   => 'simple,configurable',
   'searchable'                 => true,
   'is_configurable'            => false,
));

//Attribute für die Hauptgröße, S, M , L oder bei BHs 75, 80
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "primary_size", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Größe',
    'input'                     => 'select',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 4,
    'group'                     => 'General',
    'apply_to'                  => 'simple',
    'is_configurable'           => true,
    'searchable'                => true,
    'filterable'                => true,
));

//Attribute für die ergänzende zweite Größe, nur bei BHs genutzt, A, B, C ...
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "secondary_size", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Korbgröße',
    'input'                     => 'select',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 5,
    'group'                     => 'General',
    'apply_to'                  => 'simple',
    'filterable'                => true,
    'is_configurable'           => true,
));

//Farbe
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "color", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Farbe',
    'input'                     => 'select',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 6,
    'group'                     => 'General',
    'apply_to'                  => 'simple',
    'searchable'                => true,
    'filterable'                => true,
    'is_configurable'           => true,
));

//Filterfarbe - wird nicht auf der Produktseite angezeigt, aber im Gallery-Filter
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "filter_color", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Filterfarbe',
    'input'                     => 'select',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 7,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => true,
    'filterable'                => true,
    'is_configurable'           => false,
));

//Pflegeanleitung
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "care_instructions", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Pflegeanleitung',
    'input'                     => 'multiselect',
    'backend'                   => 'eav/entity_attribute_backend_array',
    'frontend'                  => '',
    'source'                    => '',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'                => 8,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => false,
    'filterable'                => false,
    'is_configurable'           => false,
));

//Filtermaterial - ebenfalls nur zum Suchergebnisse filtern
//Bereits beim Seup vorkonfíguriert mit allen Optionen
//ACHTUNG: Bei jedem Aufruf des Skripts werden die Optionen hinzugefügt
//Dies kann zu doppelten Optionen führen
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "filter_material", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Filtermaterial',
    'input'                     => 'select',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 9,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => false,
    'filterable'                => false,
    'is_configurable'           => false,
    'option'                    => array(
      'value' => array(
        'Baumwolle'=>array(
          0=>'Baumwolle',
          1=>'Baumwolle',
          2=>'cotton',
          3=>'katoen',
        ),
        'Lyocell'=>array(
          0=>'Lyocell',
          1=>'Lyocell',
          2=>'lyocell',
          3=>'lyocell',
        ),
        'Milchfaser'=>array(
          0=>'Milchfaser',
          1=>'Milchfaser',
          2=>'milk fiber',
          3=>'melkvezel',
        ),
        'Modal'=>array(
          0=>'Modal',
          1=>'Modal',
          2=>'modal',
          3=>'modaal',
        ),
        'Polyamid'=>array(
          0=>'Polyamid',
          1=>'Polyamid',
          2=>'polyamide',
          3=>'polyamide',
        ),
        'Polyester'=>array(
          0=>'Polyester',
          1=>'Polyester',
          2=>'polyester',
          3=>'polyester',
        ),
        'Spitze'=>array(
          0=>'Spitze',
          1=>'Spitze',
          2=>'lace',
          3=>'kant',
        ),
        'Viskose'=>array(
          0=>'Viskose',
          1=>'Viskose',
          2=>'viscose',
          3=>'viscose',
        ),
        'Wolle'=>array(
          0=>'Wolle',
          1=>'Wolle',
          2=>'wool',
          3=>'wol',
        ),
        'Frottee'=>array(
          0=>'Frottee',
          1=>'Frottee',
          2=>'terry',
          3=>'terry',
        ),
        'Schurwolle'=>array(
          0=>'Schurwolle',
          1=>'Schurwolle',
          2=>'virgin wool',
          3=>'scheerwol',
        ),
      )
    )
));

//Material 1
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "material1", array(
    'type'                      => 'text',
    'visible'                   => true,
    'label'                     => 'Material1',
    'input'                     => 'textarea',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'                => 10,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => true,
    'filterable'                => true,
    'is_configurable'           => false,
));

//Material2
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "material2", array(
    'type'                      => 'text',
    'visible'                   => true,
    'label'                     => 'Material2',
    'input'                     => 'textarea',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'                => 11,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => true,
    'filterable'                => true,
    'is_configurable'           => false,
));

//Sellingpoints
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "sellingpoints", array(
    'type'                      => 'text',
    'visible'                   => true,
    'label'                     => 'Sellingpoints',
    'input'                     => 'textarea',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'                => 12,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => true,
    'filterable'                => true,
    'is_configurable'           => false,
));

//Produktfamilie - Alvine Attribute über welches die Produkte gruppiert werden
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

//Storefinder - Produkt im Storefinder anzeigen oder nicht
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "storefinder", array(
    'type'                      => 'int',
    'visible'                   => true,
    'label'                     => 'Storefinder',
    'input'                     => 'boolean',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 13,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => false,
    'filterable'                => false,
    'is_configurable'           => false,
));

//Badge - Auszeihnung, Siegel etc
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "badge", array(
    'type'                      => 'varchar',
    'visible'                   => true,
    'label'                     => 'Badge-Auszeichnung',
    'input'                     => 'multiselect',
    'backend'                   => 'eav/entity_attribute_backend_array',
    'frontend'                  => '',
    'source'                    => '',
    'required'                  => false,
    'user_defined'              => true,
    'default'                   => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'                => 14,
    'group'                     => 'General',
    'apply_to'                  => 'simple,configurable',
    'searchable'                => false,
    'filterable'                => false,
    'is_configurable'           => false,
));

//Style - Serie
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, "style", array(
   'type'                       => 'varchar',
   'visible'                    => true,
   'label'                      => 'style',
   'input'                      => 'text',
   'required'                   => false,
   'user_defined'               => true,
   'default'                    => '',
   'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
   'sort_order'                 => 15,
   'group'                      => 'General',
   'apply_to'                   => 'simple,configurable',
   'searchable'                 =>  true,
   'is_configurable'            => false,
));

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'scid', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Staging Category ID',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->endSetup();