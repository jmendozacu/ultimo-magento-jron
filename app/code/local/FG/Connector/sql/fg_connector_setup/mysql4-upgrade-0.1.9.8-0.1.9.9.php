<?php
$setup = new Mage_Sales_Model_Resource_Setup('core_setup');
$setup->startSetup();

$setup->addAttribute('order', 'do_export_to_alvine', array(
    'type'                       => 'boolean',
    'label'                      => 'Do export to Alvine',
    'input'                      => 'boolean',
    'required'                   => false,
    'user_defined'               => false,
    'default'                    => NULL,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => false,
));

$setup->endSetup();

$collection =  Mage::getResourceModel('sales/order_collection');
foreach($collection as $order) {
    $order->setData('do_export_to_alvine', 0);
    $order->getResource()->saveAttribute($order, 'do_export_to_alvine');
}
