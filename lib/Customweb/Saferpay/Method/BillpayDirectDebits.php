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

//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Saferpay/Method/DirectDebitWrapper.php';
//require_once 'Customweb/Saferpay/ElementFactory.php';
//require_once 'Customweb/Saferpay/Method/ValidateDateOfBirth.php';


class Customweb_Saferpay_Method_BillpayDirectDebits extends Customweb_Saferpay_Method_DirectDebitWrapper{
	private $autorizationAttributes = array(
			'spPassword','ACCOUNTID', 'ACTION', 'AMOUNT', 'CURRENCY', 'ORDERID', 'PAYMENTTYPE',
			'NAME', 'ACCOUNTNUMBER', 'BANK_CODE_NUMBER', 'GENDER', 'FIRSTNAME', 'LASTNAME',
			'STREET', 'ADDRESSADDITION', 'ZIP', 'CITY', 'COUNTRY', 'EMAIL', 'PHONE', 'DATEOFBIRTH',
			'LANGID', 'COMPANY', 'IP', 'LEGALFORM', 'DELIVERY_GENDER', 'DELIVERY_FIRSTNAME',
			'DELIVERY_LASTNAME', 'DELIVERY_STREET', 'DELIVERY_ADDRESSADDITION', 'DELIVERY_ZIP',
			'DELIVERY_CITY', 'DELIVERY_COUNTRY', 'DELIVERY_PHONE'
	);
	
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,
			$isMoto = false){
		$elements = array();
	
		$owner = $this->getOrderContext()->getBillingFirstName() . ' ' . $this->getOrderContext()->getBillingLastName();
	
		if($orderContext->getBillingCompanyName() == null){
			$elements[] = Customweb_Saferpay_ElementFactory::getCompanyElement('COMPANY');
		}
		if($this->getGender($orderContext->getBillingGender(), $orderContext->getBillingCompanyName()) == null){
			$elements[] = Customweb_Saferpay_ElementFactory::getGenderElement('GENDER');
		}
	
		if($orderContext->getBillingDateOfBirth() == null){
			$dobElement = Customweb_Saferpay_ElementFactory::getDateOfBirthElement('dob-day','dob-month','dob-year');
			$elements[] = $dobElement;
		}
	
		if($this->getGender($orderContext->getShippingGender(),"") == null){
			$elements[] = Customweb_Saferpay_ElementFactory::getGenderElement('DELIVERY_GENDER');
		}
	
		$elements[] = Customweb_Form_ElementFactory::getAccountOwnerNameElement('NAME', $owner);
		$elements[] = Customweb_Form_ElementFactory::getAccountNumberElement('ACCOUNTNUMBER');
		$elements[] = Customweb_Form_ElementFactory::getBankCodeElement('BANK_CODE_NUMBER');
	
	
		$elements[] = $this->getGeneralTermsElement("generalTerms");
	
		return $elements;
	}
	
	public function getHiddenFormFields(){
		$fields = array();
		$fields['LEGALFORM'] = $this->guessLegalForm();
		return $fields;
	}
	
	public function is3DSecureSupported(){
		return false;
	}
	
	public function getAdditionalPaymentPageParameters(){
		$parameters = array();
		$operator = new Customweb_Saferpay_Method_Operator_Billpay();
		
		$parameters = $operator->addSpecificPaymentPageParameters($this->getOrderContext(), $parameters);
		if($this->getPaymentMethodConfigurationValue('approval') == 'yes'){
			$parameters['PROVIDERSET'] = '1218';
		}

		return $parameters;
	}
	
	public function getAdditionalCaptureParameters(){
		return array();
	}
	
	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $parameters){
		$parameters['PAYMENTTYPE'] = 'DDG';
		$parameters = array_merge($parameters,$this->getAdditionalPaymentPageParameters());
		$parameters['DATEOFBIRTH'] = $parameters['dob-year'] . $parameters['dob-month'] . $parameters['dob-day'];
	
		$filteredParameters = array();
		foreach($this->autorizationAttributes as $att){
			if(isset($parameters[$att])){
				$filteredParameters[$att] = $parameters[$att];
			}
		}
	
		return $filteredParameters;
	}
	
	public function isAliasManagerSupported(){
		return false;
	}
	
	/**
	 * This method tries to determine the legal form of the company based on the company
	 * name. This is possible for companies that carry their (well known) legal
	 * form in their company name.
	 *
	 * @return string | NULL
	 */
	private function guessLegalForm(){
		$name = $this->getOrderContext()->getBillingCompanyName();
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
	
	
	
	/**
	 * This method creates a general terms and conditions element.
	 *
	 * @param string $fieldName The field name of the account number element
	 * @return Customweb_Form_IElement
	 */
	protected function getGeneralTermsElement($fieldName) {
	
		$content = Customweb_I18n_Translation::__(
			"I agree that my personal data are transmitted to
			<a href='https://billpay.de/endkunden' target='blank'> Billpay GmbH</a> in order to  perfrom 
			an idenity and solvency check. Billpay's <a href=\"!link\">Privacy Policy</a> applies.",
			array('!link' => $this->getGeneralTermsLink())
		);
	
	
		$terms  = new Customweb_Form_Control_Html('generalTermsText',$content);
		$checkbox = new Customweb_Form_Control_SingleCheckbox($fieldName,'accepted',
				Customweb_I18n_Translation::__('I agree.'));
		$checkbox->addValidator(new Customweb_Form_Validator_NotEmpty($checkbox, Customweb_I18n_Translation::__("You have to agree to the general terms.")));
		$control = new Customweb_Form_Control_MultiControl('generalTermsController',array($terms,$checkbox));
	
		$element = new Customweb_Form_Element(
				Customweb_I18n_Translation::__('Billpay General Terms'),
				$control,
				Customweb_I18n_Translation::__('By continuing the checkout you agree to the general terms of this agreement.')
		);
		$element->setElementIntention($this->getGeneralTermsIntention());
	
		return $element;
	}
	
	protected function getGeneralTermsIntention(){
		return new Customweb_Form_Intention_Intention('general-terms');
	}
	
	protected function getGeneralTermsLink(){
		$orderContext = $this->getOrderContext();
		$country = $orderContext->getBillingCountryIsoCode();
		$language = $orderContext->getLanguage();
	
		$agb  = null;
		$lang = 'en';
	
		switch(strtolower($country)){
			case 'ch':
				$agb = 'agb-ch';
				break;
			case 'at':
				$agb = 'agb-at';
				break;
			default:
				$agb = 'agb';
		}
	
		switch(substr($lang, 0,2)){
			case 'fr':
				$lang = 'fr';
				break;
			case 'en':
				$lang = 'en';
				break;
			default:
				$lang = 'de';
		}
	
		$link = 'https://www.billpay.de/kunden/' . $agb . '?lang=' . $lang . '/#datenschutz';
		return $link;
	}
}