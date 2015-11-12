<?php
 
/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

// Attributes
$attributeSetup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $attributeSetup->getEntityTypeId('customer');
$attributeSetId   = $attributeSetup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $attributeSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "customer_number",  array(
    "type"     => "varchar",
    "label"    => "Customer Number",
    "input"    => "text",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""

));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "customer_number");

$attributeSetup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'customer_number',
    '400'  //sort_order
);

$used_in_forms=array();

$used_in_forms[]="adminhtml_customer";
$attribute->setData("used_in_forms", $used_in_forms)
    ->setData("is_used_for_customer_segment", false)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 0)
    ->setData("sort_order", 400)
;
$attribute->save();

$installer->endSetup();
