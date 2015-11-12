<?php
//Für das anlegen des Cookie Attributes bei Bestellungen siehe Affiliate
$setup = new Mage_Catalog_Model_Resource_Setup();
$setup->startSetup();
//$setup->removeAttribute(Mage_Catalog_Model_Category::ENTITY, "seo_text");
//EAN
$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, "seo_text", array(
    'group'         => 'General',
    'input'         => 'textarea',
    'type'          => 'text',
    'label'         => 'SEO Text',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'wysiwyg_enabled' => true,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

$setup->endSetup();

?>