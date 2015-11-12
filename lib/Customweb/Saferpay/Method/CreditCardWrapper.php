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
//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapper.php';
//require_once 'Customweb/Payment/Authorization/Method/CreditCard/ElementBuilder.php';


class Customweb_Saferpay_Method_CreditCardWrapper extends Customweb_Saferpay_Method_PaymentMethodWrapper{
	
	const FORM_KEY_CARD_NUMBER = 'sfpCardNumber';
	const FORM_KEY_CARD_CVC = 'sfpCardCvc';
	const FORM_KEY_CARD_EXPIRY_MONTH = 'sfpCardExpiryMonth';
	const FORM_KEY_CARD_EXPIRY_YEAR = 'sfpCardExpiryYear';
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,
			$isMoto = false){
		
		$formBuilder = new Customweb_Payment_Authorization_Method_CreditCard_ElementBuilder();
			
		// Set field names
		$formBuilder
			->setCardHolderFieldName(self::FORM_KEY_OWNER_NAME)
			->setCardNumberFieldName(self::FORM_KEY_CARD_NUMBER)
			->setCvcFieldName(self::FORM_KEY_CARD_CVC)
			->setExpiryMonthFieldName(self::FORM_KEY_CARD_EXPIRY_MONTH)
			->setExpiryYearFieldName(self::FORM_KEY_CARD_EXPIRY_YEAR)
			->setExpiryYearNumberOfDigits(2);
			
		// Handle brand selection
		if (strtolower($this->getPaymentMethodName()) == 'creditcard') {
			$brands = $this->getPaymentMethodConfigurationValue('credit_card_brands');
			
			$formBuilder
			->setCardHandlerByBrandInformationMap($this->getPaymentInformationMap(), $brands, 'id')
			->setAutoBrandSelectionActive(true);
		}
		else {
			$formBuilder
			->setFixedBrand(true)
			->setSelectedBrand($this->getPaymentMethodName())
			->setCardHandlerByBrandInformationMap($this->getPaymentInformationMap(), $this->getPaymentMethodName(), 'id')
			;
		}
		
		$formBuilder->setCardHolderName($orderContext->getBillingFirstName() . ' ' . $orderContext->getBillingLastName());

		if($aliasTransaction !== null && $aliasTransaction !== 'new' && $aliasTransaction instanceof Customweb_Saferpay_Authorization_Transaction){
			$formBuilder->setSelectedExpiryMonth($aliasTransaction->getCardExpiryMonth());
			$formBuilder->setSelectedExpiryYear( $aliasTransaction->getCardExpiryYear());
			$params = $aliasTransaction->getAuthorizationParameters();
			$formBuilder->setMaskedCreditCardNumber($params['PAN']);
			$formBuilder->setCardHolderName($aliasTransaction->getOwnerName());
		}
		
		if ($isMoto) {
			$formBuilder->setCvcFieldName(null);
		}
		
		return $formBuilder->build();
	}
	
	public function getHiddenFormFields(){
		return array();
	}
	
	public function is3DSecureSupported(){
		return true;
	}
	
	public function getAdditionalPaymentPageParameters(){
		return array();
	}
	
	public function getAdditionalCaptureParameters(){
		return array();
	}
	
	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $parameters){
		// We send always the expiration date instead of updating them in the database of the PSP.
		$parameters['EXP'] = $transaction->getCardExpiryDate();
		return $parameters;
	}
	
	public function isEciMeaningless(){
		// Only mastercard and visa support 3D secure (and for test mode: saferpay test card)
		if ($this->getPaymentId() == 1) {
			return false;
		}
		else if ($this->getPaymentId() == 2) {
			return false;
		}
		else if ($this->getPaymentId() == 6) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the Saferpay id for this payment method.
	 *
	 * @return string ID of the payment method.
	 */
	public function getPaymentId(){
		if ($this->existsPaymentMethodConfigurationValue('credit_card_brands')) {
			$map = $this->getPaymentInformationMap();
			$ids = array();
			$cards = $this->getPaymentMethodConfigurationValue('credit_card_brands');
			
			foreach ($cards as $card) {
				$ids[] = $map[strtolower($card)]['parameters']['id'];
			}
			
			return implode(',', $ids);
		}
		else {
			return parent::getPaymentId();
		}
	}
	
	
	
}
