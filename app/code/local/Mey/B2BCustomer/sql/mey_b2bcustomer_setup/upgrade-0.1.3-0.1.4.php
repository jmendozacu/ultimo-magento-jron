<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

// Attributes
$attributeSetup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $attributeSetup->getEntityTypeId('customer');
$attributeSetId   = $attributeSetup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $attributeSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "retail_price_list", [
    "type"     => "varchar",
    "label"    => "Retail Price List",
    "input"    => "text",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""
]);

$attribute = Mage::getSingleton("eav/config")->getAttribute("customer", "retail_price_list");

$attributeSetup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'retail_price_list',
    '550' //sort_order
);

$attribute->setData("used_in_forms", ["adminhtml_customer"])
    ->setData("is_used_for_customer_segment", false)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 0)
    ->setData("sort_order", 550)
;

$attribute->save();

$installer->endSetup();
