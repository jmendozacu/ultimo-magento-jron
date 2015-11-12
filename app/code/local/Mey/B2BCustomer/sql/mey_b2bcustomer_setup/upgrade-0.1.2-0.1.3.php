<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

// Attributes
$attributeSetup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $attributeSetup->getEntityTypeId('customer');
$attributeSetId   = $attributeSetup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $attributeSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "display_price_type", [
    "type"      => "varchar",
    "label"     => "Display Price Type",
    "input"     => "text",
    "visible"   => true,
    "required"  => false,
    "default"   => "0",
    "frontend"  => "",
    "unique"    => false,
    "note"      => "This value indicates which price type is displayed to the customer (B2B-Store only). (0=both, 1=purchase price, 2=retail price)"
]);

$attribute = Mage::getSingleton("eav/config")->getAttribute("customer", "display_price_type");

$attributeSetup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'display_price_type',
    '600' //sort_order
);

$attribute->setData("used_in_forms", ["adminhtml_customer"])
    ->setData("is_used_for_customer_segment", false)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 0)
    ->setData("sort_order", 600)
;

$attribute->save();

$installer->endSetup();
