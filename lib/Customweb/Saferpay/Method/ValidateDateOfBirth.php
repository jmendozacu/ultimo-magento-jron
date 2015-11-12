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

//require_once 'Customweb/Form/Validator/IValidator.php';

class Customweb_Saferpay_Method_ValidateDateOfBirth implements Customweb_Form_Validator_IValidator{
	
	private $control = null;
	
	
	public function __construct($control,$dayFieldName, $monthFieldName, $yearFieldName){
		$this->control = $control;
	}
	
	public function getCallbackJs(){
		$js =	'function (e, element) { 
					var day = document.getElementById("dob-day").value;
					var month = document.getElementById("dob-month").value;
					var year = document.getElementById("dob-year").value;
					
					if(day != "none" && month != "none" && year != "none"){
						var formElement = getFormElement(element);
						var dob = document.createElement("input");
						dob.type = "hidden";
						dob.name = "DATEOFBIRTH";
						dob.value = year + month + day;
						formElement.appendChild(dob);
					}
				};';
		return $js;
		
	}
	public function getControl(){
		return $this->control;
	}
}