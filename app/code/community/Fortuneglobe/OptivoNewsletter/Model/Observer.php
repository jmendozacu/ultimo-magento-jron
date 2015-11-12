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
 * NewsletterExtended module observer
 *
 * @category   Fortuneglobe
 * @package    Fortuneglobe_OptivoNewsletter
 * @author     Fortuneglobe Magento Developer
 */

class Fortuneglobe_OptivoNewsletter_Model_Observer
{
    public function newslettersubscriberchange(Varien_Event_Observer $observer)
	{
	
		$subscriber = $observer->getEvent()->getSubscriber();
        $data = Mage::app()->getRequest()->getParams();
		
		if (is_array($data) && isset($data['email'])) {
			$date = new DateTime();
			$subscriber->setSubscriberSignip($_SERVER['REMOTE_ADDR']);
			$subscriber->setSubscriberSigntimestamp($date->format('Y-m-d H:i:s'));
			if (isset($data['gender'])) $subscriber->setSubscriberGender($data['gender']);
			if (isset($data['title']))$subscriber->setSubscriberTitle($data['title']);
			if (isset($data['firstname']))$subscriber->setSubscriberFirstname($data['firstname']);
			if (isset($data['lastname'])) $subscriber->setSubscriberLastname($data['lastname']);
			if (isset($data['street']))$subscriber->setSubscriberStreet($data['street']);
			if (isset($data['country']))$subscriber->setSubscriberCountry($data['country']);
			if (isset($data['zipcode'])) $subscriber->setSubscriberZipcode($data['zipcode']);
			if (isset($data['city']))$subscriber->setSubscriberCity($data['city']);
			if (isset($data['telephone']))$subscriber->setSubscriberTelephone($data['telephone']);
			if (isset($data['day']) && isset($data['month']) && isset($data['year']))$subscriber->setSubscriberDayofbirth($data['day'].'. '.$data['month'].'. '.$data['year']);
		}
		return $this;
    }
}