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
//require_once 'Customweb/Saferpay/Method/OpenInvoiceWrapper.php';
//require_once 'Customweb/Saferpay/ElementFactory.php';
//require_once 'Customweb/Saferpay/Method/ValidateDateOfBirth.php';
//require_once 'Customweb/Saferpay/Method/Operator/Billpay.php';

class Customweb_Saferpay_Method_BillpayOpenInvoice extends Customweb_Saferpay_Method_OpenInvoiceWrapper{
	
	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext) {
		parent::preValidate($orderContext, $paymentContext);
		$this->checkAddresses($orderContext);
		
		return true;
	}
	
	/**
	 * This method throws an exception in case the shipping and billing address do not match.
	 * 
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @throws Exception
	 */
	protected function checkAddresses(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		
		if ($orderContext->getBillingCity() != $orderContext->getShippingCity()) {
			throw new Exception(Customweb_I18n_Translation::__(
				"The billing and shipping address do not match."
			));
		}
		if ($orderContext->getBillingPostCode() != $orderContext->getShippingPostCode()) {
			throw new Exception(Customweb_I18n_Translation::__(
				"The billing and shipping address do not match."
			));
		}
		if ($orderContext->getBillingStreet() != $orderContext->getShippingStreet()) {
			throw new Exception(Customweb_I18n_Translation::__(
				"The billing and shipping address do not match."
			));
		}
		if ($orderContext->getBillingFirstName() != $orderContext->getShippingFirstName()) {
			throw new Exception(Customweb_I18n_Translation::__(
				"The billing and shipping address do not match."
			));
		}
		if ($orderContext->getBillingLastName() != $orderContext->getShippingLastName()) {
			throw new Exception(Customweb_I18n_Translation::__(
				"The billing and shipping address do not match."
			));
		}
		if ($orderContext->getBillingCountryIsoCode() != $orderContext->getShippingCountryIsoCode()) {
			throw new Exception(Customweb_I18n_Translation::__(
				"The billing and shipping address do not match."
			));
		}
		
		return true;
	}
	
	public function getAdditionalCaptureParameters(){
		$parameters = array();
		if(strlen($this->getPaymentMethodConfigurationValue('pob_delay')) > 0){
			$parameters['POB_DELAY'] = $this->getPaymentMethodConfigurationValue('pob_delay');
		}
	
		return $parameters;
	}
	
	public function getAdditionalPaymentPageParameters(){
		$this->checkAddresses($this->getOrderContext());
		
		$parameters = array();
		$operator = new Customweb_Saferpay_Method_Operator_Billpay();
		
		$parameters = $operator->addSpecificPaymentPageParameters($this->getOrderContext(), $parameters);
		if($this->getPaymentMethodConfigurationValue('approval') == 'yes'){
			$parameters['PROVIDERSET'] = '1219';
		}
	
		return $parameters;
	}

	public function extractPaymentInformation(array $parameters){
		$substitutes = array(
			'POB_ACCOUNTHOLDER' => Customweb_I18n_Translation::__('Account Holder'),
			'POB_ACCOUNTNUMBER' => Customweb_I18n_Translation::__('Account Number'),
			'POB_BANKCODE'=> Customweb_I18n_Translation::__('Bank Code'),
			'POB_BANKNAME'=> Customweb_I18n_Translation::__('Bank Name'),
			'POB_PAYERNOTE'=> Customweb_I18n_Translation::__('Payment Note')
		);
		$information = array();
	
		foreach ($parameters as $key => $value){
			if(array_key_exists($key, $substitutes)){
				$information[] = array(
					'label' => $substitutes[$key],
					'value' => $value
				);
			}
		}
	
		return $information;
	}
}