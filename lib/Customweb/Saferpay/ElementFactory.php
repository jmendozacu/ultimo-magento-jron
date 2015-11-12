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

//require_once 'Customweb/Saferpay/ElementFactory.php';
//require_once 'Customweb/Saferpay/IntentionFactory.php';

/**
 * This class provides method for creating elements that are used by Saferpay.
 *
 * @author Severin Klinglerl
 *         	   		  	 	 	
 */
final class Customweb_Saferpay_ElementFactory{
	
	
	public static function getCompanyElement($fieldName, $defaultCompanyName = '') {
		$control = new Customweb_Form_Control_TextInput($fieldName, $defaultCompanyName);
		$control->setAutocomplete(true);
		$element = new Customweb_Form_Element(
				Customweb_I18n_Translation::__('Company name'),
				$control,
				Customweb_I18n_Translation::__('Please enter the name of your company if you order for your company.')
		);
		$element->setElementIntention(Customweb_Saferpay_IntentionFactory::getCompanyNameIntention());
		
		return $element;
	}
	
	public static function getGenderElement($fieldName){
		$gender = array(
				'none' => Customweb_I18n_Translation::__('Gender'),
				'm' => Customweb_I18n_Translation::__('Male'),
				'f' => Customweb_I18n_Translation::__('Female'), 
				'c' => Customweb_I18n_Translation::__('Company')
		);
		
		$control = new Customweb_Form_Control_Select($fieldName, $gender, "");
		$element = new Customweb_Form_Element(
				Customweb_I18n_Translation::__('Gender'),
				$control,
				Customweb_I18n_Translation::__('Select your gender.')
		);
		$element->setElementIntention(Customweb_Saferpay_IntentionFactory::getGenderIntention());
		
		return $element;
	}
	
	/**
	 * This method creates an element to select the date of birth
	 * @param string $fieldName The field name of the date of birth element
	 */
	public static function getDateOfBirthElement($dayFieldName,$monthFieldName,$yearFieldName, $defaultDay='', $defaultMonth='', $defaultYear=''){
	
		$days = array(
				'none' => Customweb_I18n_Translation::__('Day'),
				'01' => '01', '02' => '02', '03' => '03', '04' => '04',
				'05' => '05', '06' => '06', '07' => '07', '08' => '08',
				'09' => '09', '10' => '10', '11' => '11', '12' => '12',
				'13' => '13', '14' => '14', '15' => '15', '16' => '16',
				'17' => '17', '18' => '18', '19' => '19', '20' => '20',
				'21' => '21', '22' => '22', '23' => '23', '24' => '24',
				'25' => '25', '26' => '26', '27' => '27', '28' => '28',
				'29' => '29', '30' => '30', '31' => '31'
		);
	
		$months = array(
				'none' => Customweb_I18n_Translation::__('Month'),
				'01' => '01', '02' => '02', '03' => '03', '04' => '04',
				'05' => '05', '06' => '06', '07' => '07', '08' => '08',
				'09' => '09', '10' => '10', '11' => '11', '12' => '12',
		);
	
		$years = array('none' => Customweb_I18n_Translation::__('Year'));
		$current = intval(date('Y'));
		for($i = 0; $i < 130; $i++) {
			$years[$current] = $current;
			$current--;
		}
	
		$dayControl = new Customweb_Form_Control_Select($dayFieldName, $days, $defaultDay);
		$dayControl->addValidator(new Customweb_Form_Validator_NotEmpty($dayControl, Customweb_I18n_Translation::__("Please select the day on which you were born.")));
	
		$monthControl = new Customweb_Form_Control_Select($monthFieldName, $months, $defaultMonth);
		$monthControl->addValidator(new Customweb_Form_Validator_NotEmpty($monthControl, Customweb_I18n_Translation::__("Please select the month in which you were born.")));
	
		$yearControl = new Customweb_Form_Control_Select($yearFieldName, $years, $defaultYear);
		$yearControl->addValidator(new Customweb_Form_Validator_NotEmpty($yearControl, Customweb_I18n_Translation::__("Please select the year in which you were born.")));
		$yearControl->addValidator(new Customweb_Saferpay_Method_ValidateDateOfBirth($yearControl,'dob-day','dob-month','dob-year'));
	
		$control = new Customweb_Form_Control_MultiControl('dayOfBirth', array(
				$dayControl,
				$monthControl,
				$yearControl,
		));
	
		$element = new Customweb_Form_Element(
				Customweb_I18n_Translation::__('Date of birth'),
				$control,
				Customweb_I18n_Translation::__('Select your date of birth.')
		);
		$element->setElementIntention(Customweb_Form_Intention_Factory::getDateOfBirthIntention());
	
		return $element;
	}
	
	
}