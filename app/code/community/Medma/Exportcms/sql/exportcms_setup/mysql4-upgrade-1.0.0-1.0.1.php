<?php
/**
 * Medma Exportcms Module.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Team
 * that is bundled with this package of Medma Infomatix Pvt. Ltd.
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Medma Support does not guarantee correct work of this package
 * on any other Magento edition except Magento COMMUNITY edition.
 * =================================================================
 */
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable(Medma_Exportcms_Model_Authenticate::MEDMA_DOMAIN_TABLE_NAME)} (
  `modules` int(11) unsigned NOT NULL auto_increment,  
  `domain_name` varchar(255),  
  `medma_module` varchar(255),
  PRIMARY KEY (`modules`)
) ENGINE = INNODB CHARSET=utf8;

");

$installer->endSetup(); 
?>
