<?php

/**
 * Fortuneglobe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Fortuneglobe
 * @package     Fortuneglobe_OptivoNewsletter
 * @copyright   Copyright (c) 2014 Fortuneglobe (http://www.fortuneglobe.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * OptivoNewsletter database setup
 *
 * @category   Fortuneglobe
 * @package    Fortuneglobe_OptivoNewsletter
 * @author     Fortuneglobe Magento Developer
 */

$installer = $this;
$installer->startSetup();

// add additional columns to "newsletter_subscriber" table
	$installer->run('ALTER TABLE  `newsletter_subscriber` ADD ( subscriber_gender INT(1) NULL, subscriber_title VARCHAR(255) NULL, subscriber_firstname VARCHAR(255) NULL, subscriber_lastname VARCHAR(255) NULL, subscriber_street VARCHAR(255) NULL, subscriber_country VARCHAR(255) NULL, subscriber_zipcode VARCHAR(255) NULL, subscriber_city VARCHAR(255) NULL, subscriber_telephone VARCHAR(255) NULL, subscriber_dayofbirth VARCHAR(255) NULL, subscriber_signip VARCHAR(255) NULL, subscriber_signtimestamp VARCHAR(255) NULL, subscriber_activateip VARCHAR(255) NULL, subscriber_activatetimestamp VARCHAR(255) NULL );');

$installer->endSetup();