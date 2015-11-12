<?php

/**
 *  * You are allowed to use this API in your web application.
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

//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapperFactory.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Saferpay/AbstractParameterBuilder.php';
//require_once 'Customweb/I18n/Translation.php';

abstract class Customweb_Saferpay_Authorization_AbstractRedirectionParameterBuilder extends Customweb_Saferpay_AbstractParameterBuilder {

	public function __construct(Customweb_Saferpay_Authorization_Transaction $transaction, Customweb_Saferpay_Configuration $configuration, Customweb_DependencyInjection_IContainer $container){
		parent::__construct($transaction->getTransactionContext(), $configuration, $container);
		$this->transaction = $transaction;
	}

	public function buildParameters(){
		$parameters = array_merge($this->getBasicParameters(), $this->getOrderParameters(), $this->getAddressParameters(), 
				$this->getReactionUrlParameters(), $this->getCustomizationParameters(), $this->getAdditionalPaymentPageParameters());
		
		$cssUrl = $this->getConfiguration()->getCssUrl();
		if (!empty($cssUrl)) {
			$parameters['CSSURL'] = $cssUrl;
		}
		
		return $parameters;
	}

	protected function getCustomizationParameters(){
		$parameters = array();
		$description = $this->getConfiguration()->getPaymentDescription($this->getOrderContext()->getLanguage());
		if (empty($description)) {
			$description = Customweb_I18n_Translation::__("Your order description");
		}
		$parameters['DESCRIPTION'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($description);
		if (strlen($this->getConfiguration()->getPaymentPageConfiguration()) > 0) {
			$parameters['VTCONFIG'] = $this->getConfiguration()->getPaymentPageConfiguration();
		}
		
		$parameters['DELIVERY'] = 'no';
		$parameters['LANGID'] = Customweb_Saferpay_Util::getCleanLanguageCode($this->getOrderContext()->getLanguage());
		if ($this->getTransactionContext()->getAlias() == 'new' ||
				 ($this->getTransactionContext()->getAlias() == null && $this->getTransactionContext()->createRecurringAlias())) {
			$parameters['CARDREFID'] = "new";
		}
		if ($this->getTransactionContext()->getAlias() != null && $this->getTransactionContext()->getAlias() != 'new') {
			$parameters['CARDREFID'] = $this->getTransactionContext()->getAlias()->getCardRefId();
		}
		
		return array_merge($parameters, $this->getSupportedPaymentMethods());
	}

	protected function getAddressParameters(){
		$parameters = array();
		$orderContext = $this->getOrderContext();
		
		if (!$orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('address_mode')) {
			return array();
		}
		
		$mode = $orderContext->getPaymentMethod()->getPaymentMethodConfigurationValue('address_mode');
		if ($mode === self::SEND_ADDRESS_MODE_DELIVERY) {
			$parameters['ADDRESS'] = strtoupper(self::SEND_ADDRESS_MODE_DELIVERY);
			$parameters['COMPANY'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingCompanyName());
			$parameters['FIRSTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingFirstName());
			$parameters['LASTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingLastName());
			$parameters['STREET'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingStreet());
			$parameters['ZIP'] = $orderContext->getShippingPostCode();
			$parameters['CITY'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getShippingCity());
			$parameters['COUNTRY'] = $orderContext->getShippingCountryIsoCode();
			$parameters['EMAIL'] = $orderContext->getCustomerEMailAddress();
		}
		else if ($mode === self::SEND_ADDRESS_MODE_BILLING) {
			$parameters['ADDRESS'] = strtoupper(self::SEND_ADDRESS_MODE_BILLING);
			$parameters['COMPANY'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingCompanyName());
			$parameters['FIRSTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingFirstName());
			$parameters['LASTNAME'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingLastName());
			$parameters['STREET'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingStreet());
			$parameters['ZIP'] = $orderContext->getBillingPostCode();
			$parameters['CITY'] = Customweb_Saferpay_Util::removeWrongEscaptedChars($orderContext->getBillingCity());
			$parameters['COUNTRY'] = $orderContext->getBillingCountryIsoCode();
			$parameters['EMAIL'] = $orderContext->getCustomerEMailAddress();
		}
		return $parameters;
	}

	protected function getSupportedPaymentMethods(){
		$paymentMethod = Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapper(
				$this->getTransactionContext()->getOrderContext());
		return $paymentMethod->getPaymentIdParameter();
	}

	protected function getAdditionalPaymentPageParameters(){
		$paymentMethod = Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapper(
				$this->getTransactionContext()->getOrderContext());
		return $paymentMethod->getAdditionalPaymentPageParameters();
	}
}