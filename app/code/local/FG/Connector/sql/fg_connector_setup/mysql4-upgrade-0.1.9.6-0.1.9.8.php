<?php
//Für das anlegen des Cookie Attributes bei Bestellungen siehe Affiliate
$setup = new Mage_Sales_Model_Resource_Setup();
$setup->startSetup();
//EAN

$setup->addAttribute('quote', "customer_source", array(
   'type'                       => 'text',
   'visible'                    => true,
   'backend_type'               => 'text',
   'frontend_input'             => 'text',
   'label'                      => 'Costumer Source',
   'input'                      => 'text',
   'required'                   => false,
   'user_defined'               => true,
   'default'                    => NULL,
   'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
   'searchable'                 => false,
   'is_configurable'            => false
));

$setup->endSetup();

?>