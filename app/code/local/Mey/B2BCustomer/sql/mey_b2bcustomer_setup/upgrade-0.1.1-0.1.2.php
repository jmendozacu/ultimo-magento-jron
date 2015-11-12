<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

// Attributes
$attributeSetup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $attributeSetup->getEntityTypeId('customer');
$attributeSetId   = $attributeSetup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $attributeSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "is_b2b_approved", [
    "type"      => "int",
    "label"     => "Is B2B Approved?",
    "input"     => "select",
    "visible"   => false,
    "required"  => false,
    "default"   => 0,
    "source" => "eav/entity_attribute_source_boolean",
    "unique"    => false,
    "note"      => ""
]);

$attribute = Mage::getSingleton("eav/config")->getAttribute("customer", "is_b2b_approved");

$attributeSetup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'is_b2b_approved',
    '560' //sort_order
);

$attribute->setData("used_in_forms", ["adminhtml_customer"])
    ->setData("is_used_for_customer_segment", false)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 0)
    ->setData("sort_order", 560)
;

$attribute->save();

$installer->endSetup();
