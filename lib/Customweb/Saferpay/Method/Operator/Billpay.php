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

//require_once 'Customweb/Saferpay/Method/Operator/Abstract.php';


class Customweb_Saferpay_Method_Operator_Billpay extends Customweb_Saferpay_Method_Operator_Abstract{
	
	private $orderContext = null;
	
	public function addSpecificPaymentPageParameters(Customweb_Payment_Authorization_IOrderContext $orderContext, array $parameters){
		$this->orderContext = $orderContext;
		$this->addIfValueNotNull($parameters, 'COMPANY', Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingCompanyName()));
		$this->addIfValueNotNull($parameters, 'LEGALFORM', $this->guessLegalForm());
		$this->addIfValueNotNull($parameters, 'GENDER',
				$this->getGender($orderContext->getBillingGender(),$orderContext->getBillingCompanyName())
		);
		$parameters['FIRSTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingFirstName());
		$parameters['LASTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingLastName());
		$parameters['STREET'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingStreet());
		$parameters['ZIP'] = $orderContext->getBillingPostCode();
		$parameters['CITY'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingCity());
		$parameters['COUNTRY'] = $orderContext->getBillingCountryIsoCode();
		$parameters['EMAIL'] = $orderContext->getCustomerEMailAddress();
		$this->addIfValueNotNull($parameters, 'PHONE', $orderContext->getBillingPhoneNumber());
		
		if($orderContext->getBillingDateOfBirth() != null){
			$this->addIfValueNotNull($parameters, 'DATEOFBIRTH', $orderContext->getBillingDateOfBirth()->format('Ymd'));
		}
			
		$this->addIfValueNotNull($parameters, 'DELIVERY_GENDER',
				$this->getGender($orderContext->getShippingGender(),"")
		);
		$parameters['DELIVERY_FIRSTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingFirstName());
		$parameters['DELIVERY_LASTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingLastName());
		$parameters['DELIVERY_STREET'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingStreet());
		$parameters['DELIVERY_ZIP'] = $orderContext->getShippingPostCode();
		$parameters['DELIVERY_CITY'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingCity());
		$parameters['DELIVERY_COUNTRY'] = $orderContext->getShippingCountryIsoCode();
		$this->addIfValueNotNull($parameters, 'DELIVERY_PHONE', $orderContext->getShippingPhoneNumber());
		
		$parameters['LANGID'] = substr($orderContext->getLanguage(),0,2);
		//$parameters['IP'] = Customweb_Saferpay_Util::getClientIpAddress();
		
		return $parameters;
	}
	
	/**
	 * This method tries to determine the legal form of the company based on the company
	 * name. This is possible for companies that carry their (well known) legal
	 * form in their company name.
	 *
	 * @return string | NULL
	 */
	private function guessLegalForm(){
		$name = $this->orderContext->getBillingCompanyName();
		if(strlen($name) > 0){
			if(preg_match("/\bag\b/i", $name) || preg_match("/\bltd\b/i", $name) || preg_match("/\bplc\b/i", $name)){
				return 'ag';
			}
			elseif(preg_match("/\bgmbh\b/i", $name)){
				return 'gmbh';
			}
			else{
				return 'misc';
			}
		}
		else{
			return null;
		}
	
	}
	
	/**
	 * Adds the key value pair to the array if $value is not null or empty
	 * @param array $array
	 * @param misc $key
	 * @param misc $value
	 */
	private function addIfValueNotNull(&$array,$key,$value){
		if($value != null && $value != ""){
			$array[$key] = $value;
		}
	}
	
	
	/**
	 * This method tries to determine the gender based on possible
	 * values of the gender or company strings.
	 *
	 * @param string $gender
	 * @param string $company
	 */
	private function getGender($gender, $company)
	{
		if($gender == 'male'){
			return 'm';
		}
		elseif($gender == 'female'){
			return 'f';
		}
		elseif(strlen($company) > 0){
			return 'c';
		}
		else{
			return null;
		}
	}
}