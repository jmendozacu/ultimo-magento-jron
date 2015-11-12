<?php
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2015 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Form/Intention/Intention.php';
//require_once 'Customweb/Form/Intention/NullIntention.php';

final class Customweb_Saferpay_IntentionFactory{
	
	private static $companyName = null;
	private static $gender = null;
	
	/**
	 * This method returns the company name element intention.
	 *         	   		  	 	 	
	 * @return Customweb_Form_Intention_Intention
	 */
	public static function getCompanyNameIntention() {
		if (self::$companyName === null) {
			self::$companyName = new Customweb_Form_Intention_Intention('company-name');
		}
		return self::$companyName;
	}
	
	public static function getGenderIntention(){
		if (self::$gender === null) {
			self::$gender = new Customweb_Form_Intention_Intention('gender');
		}
		return self::$gender;
	}
}