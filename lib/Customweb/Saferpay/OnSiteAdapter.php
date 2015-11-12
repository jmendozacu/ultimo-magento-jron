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
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Saferpay/AbstractAdapter.php';
//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapperFactory.php';
//require_once 'Customweb/Saferpay/Authorization/Hidden/ParameterBuilder.php';
//require_once 'Customweb/Saferpay/BackendOperation/Adapter/CaptureAdapter.php';
//require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';

//require_once 'Customweb/I18n/Translation.php';

class Customweb_Saferpay_OnSiteAdapter extends Customweb_Saferpay_AbstractAdapter {

	/**
	 * This method processes the response from the secure card data service that
	 * should get us a card alias to use further on.
	 *
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 * @param array $parameters
	 */
	protected function processScdResponse(Customweb_Saferpay_Authorization_Transaction $transaction, $parameters){
		if ($transaction->isUseExistingAlias()) {
			$transaction->setCardRefId($transaction->getTransactionContext()->getAlias()->getCardRefId());
			$transaction->setOwnerName($parameters[Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME]);
			
			if ($this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->is3DSecureSupported()) {
				$transaction->setCardExpiryDate($parameters['sfpCardExpiryMonth'], $parameters['sfpCardExpiryYear']);
				if (!$this->getConfiguration()->isUseMotoAccountForAlias()) {
					return $this->request3DSecure($transaction, $parameters);
				}
			}
			return $this->requestAuthorization($transaction);
		}
		else {
			$parameters = $this->parseRequestParameters($parameters);
			if ($parameters['RESULT'] == 0) {
				$transaction->setCardRefId($parameters['CARDREFID']);
				$transaction->setTruncatedPAN($parameters['CARDMASK']);
				$transaction->setOwnerName($parameters[Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME]);
				
				if ($this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->is3DSecureSupported()) {
					$transaction->setCardExpiryDate($parameters['EXPIRYMONTH'], $parameters['EXPIRYYEAR']);
					return $this->request3DSecure($transaction, $parameters);
				}
				
				return $this->requestAuthorization($transaction);
			}
			else {
				$userMessage = Customweb_Saferpay_Util::getUserErrorMessage($parameters['DESCRIPTION']);
				$backendMessage = Customweb_I18n_Translation::__("Server responded '!response'", array(
					'!response' => $parameters['DESCRIPTION'] 
				));
				$transaction->setAuthorizationFailed(new Customweb_Payment_Authorization_ErrorMessage($userMessage, $backendMessage));
				$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
			}
		}
	}

	/**
	 * This method checks the enrollment of the credit card in the 3D secure program.
	 * If the card is enrolled the user is redirected to the 3D secure verification page
	 * otherwise the transaction is authorized without 3D secure.
	 *
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 */
	protected function request3DSecure(Customweb_Saferpay_Authorization_Transaction $transaction, $parameters){
		$builder = new Customweb_Saferpay_Authorization_Hidden_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
		$sendParameters = $builder->buildVerifyEnrollmentParameters();
		
		$additionalParameters = array();
		if (isset($parameters['sfpCardCvc']) && !empty($parameters['sfpCardCvc'])) {
			$sendParameters['MPI_PA_BACKLINK'] = Customweb_Util_Url::appendParameters($sendParameters['MPI_PA_BACKLINK'], 
					array(
						'sfCecD' => $transaction->encrypt($parameters['sfpCardCvc']) 
					));
			$additionalParameters['CVC'] = $parameters['sfpCardCvc'];
		}
		
		$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getVerifyEnrollmentUrl(), $sendParameters);
		$response = Customweb_Saferpay_Util::sendRequest($requestUrl);
		$parameters = $this->getXmlParameters($response);
		
		if ($this->is3DSecurePossible($transaction, $parameters)) {
			return $this->redirect(Customweb_Saferpay_Authorization_Transaction::STATE_3D_SECURE, $transaction, $parameters['MPI_PA_LINK']);
		}
		else {
			if (isset($parameters['MPI_SESSIONID'])) {
				$transaction->setMpiSessionId($parameters['MPI_SESSIONID']);
			}
			return $this->requestAuthorization($transaction, $additionalParameters);
		}
	}

	/**
	 * This method processes the 3D Secure response from Saferpay .
	 *
	 *
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 * @param array $parameters
	 */
	protected function process3DSecureResponse(Customweb_Saferpay_Authorization_Transaction $transaction, $parameters){
		$transaction->setMpiSessionId($parameters['MPI_SESSIONID']);
		$additional = array();
		if (isset($parameters['sfCecD']) && !empty($parameters['sfCecD'])) {
			$additional['CVC'] = $transaction->decrypt($parameters['sfCecD']);
		}
		
		// The card is enrolled, but the authorization failed. 
		if (isset($parameters['ECI']) && $parameters['ECI'] == '0') {
			$message = Customweb_I18n_Translation::__("The 3-D Secure authentication failed.");
			$transaction->setAuthorizationFailed($message);
			$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
			return;
		}
		
		return $this->requestAuthorization($transaction, $additional);
	}

	/**
	 * This method issues an autorization request for the transaction.
	 * If the capturing mode is "Direct" the transaction gets captured after
	 * a sucessfull authorization.
	 *
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 */
	protected function requestAuthorization(Customweb_Saferpay_Authorization_Transaction $transaction, array $additionalParameters = array()){
		if (!isset($additionalParameters['CVC'])) {
			$isAliasWithAddressCheck = $transaction->isUseExistingAlias() && $this->getConfiguration()->getCardVerificationMode() != 'cvc';
			$isCreditCard = $this->getPaymentMethodWrapperFromPaymentMethod($transaction->getPaymentMethod()) instanceof Customweb_Saferpay_Method_CreditCardWrapper;
			$isMoto = strtolower($transaction->getAuthorizationMethod()) ==
					 strtolower(Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME);
			$isRecurring = strtolower($transaction->getAuthorizationMethod()) ==
					 strtolower(Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
			if (!$isAliasWithAddressCheck && !$isMoto && !$isRecurring && $isCreditCard) {
				$message = Customweb_I18n_Translation::__("No CVC provided.");
				$transaction->setAuthorizationFailed($message);
				$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
				return;
			}
		}
		// 		&& !isset($additionalParameters['IBAN'])
		$builder = new Customweb_Saferpay_Authorization_Hidden_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
		$sendParameters = $builder->buildParameters($additionalParameters);
		
		$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getExecuteUrl(), $sendParameters);
		$response = Customweb_Saferpay_Util::sendRequest($requestUrl);
		$parameters = $this->getXmlParameters($response);
		
		$transaction->resetKey();
		
		if ($parameters['RESULT'] == 0 && $this->isTransactionValidState($transaction)) {
			$transaction->setPaymentId($parameters['ID']);
			$transaction->setAuthorizationParameters($parameters);
			$transaction->authorize();
			
			if (isset($parameters['ECI']) && $parameters['ECI'] != self::ECI_NO_LIABILITY_SHIFT) {
				$transaction->setState3DSecure(Customweb_Saferpay_Authorization_Transaction::STATE_3D_SECURE_SUCCESS);
			}
			if ($this->getConfiguration()->isMarkLiabilityShiftTransactions() &&
					 $transaction->getAuthorizationMethod() != Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME &&
					 !$transaction->isUseExistingAlias() && !$transaction->isMoto()) {
				if ((!isset($parameters['ECI']) || $parameters['ECI'] == self::ECI_NO_LIABILITY_SHIFT) &&
				 !$this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->isEciMeaningless()) {
			$transaction->setAuthorizationUncertain();
		}
	}
	
	// In case the shop system request a new alias, we mark this transaction as a alias source.
	$transactionContext = $transaction->getTransactionContext();
	if (!($transactionContext instanceof Customweb_Payment_Authorization_Recurring_ITransactionContext &&
			 $transactionContext->getInitialTransaction() != null)) {
		
		if ($transaction->getTransactionContext()->getAlias() !== null || $transaction->getTransactionContext()->createRecurringAlias()) {
			if ($transaction->getTransactionContext()->getAlias() != 'new' && !isset($parameters['PAN'])) {
				$transaction->setAliasForDisplay($transaction->getTransactionContext()->getAlias()->getAliasForDisplay());
				$transaction->addAuthorizationParameters(array(
					'PAN' => $transaction->getTransactionContext()->getAlias()->getAliasForDisplay() 
				));
			}
			else {
				if (isset($parameters['PAN'])) {
					$transaction->setAliasForDisplay($parameters['PAN']);
				}
				else if (isset($parameters['IBAN'])) {
					$transaction->setAliasForDisplay(Customweb_Saferpay_Util::maskIban($parameters['IBAN']));
				}
				else {
					throw new Exception('PAN or IBAN must be set, none given.');
				}
			}
			$this->setAliasAddress($transaction);
		}
	}
	
	if ($transaction->getTransactionContext()->getCapturingMode() == null) {
		$capturingMode = $this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->getPaymentMethodConfigurationValue(
				'capturing');
	}
	else {
		$capturingMode = $transaction->getTransactionContext()->getCapturingMode();
	}
	if (!$transaction->isAuthorizationUncertain() && $capturingMode == Customweb_Payment_Authorization_ITransactionContext::CAPTURING_MODE_DIRECT) {
		$this->captureTransaction($transaction);
	}
	$this->redirect(null, $transaction, $this->getSuccessUrl($transaction));
}
else {
	$userMessage = Customweb_Saferpay_Util::getUserErrorMessage($parameters['AUTHMESSAGE']);
	$backendMessage = Customweb_I18n_Translation::__("Saferpay authorization responded with : !result '!authmessage'", 
			array(
				'!result' => $parameters['RESULT'],
				'!authmessage' => $parameters['AUTHMESSAGE'] 
			));
	$transaction->setAuthorizationFailed(new Customweb_Payment_Authorization_ErrorMessage($userMessage, $backendMessage));
	$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
}
}

/**
 * This method captures the full amount of the transaction.
 *
 * @param Customweb_Saferpay_Authorization_Transaction $transaction
 */
protected function captureTransaction(Customweb_Saferpay_Authorization_Transaction $transaction){
$capturingAdapter = new Customweb_Saferpay_BackendOperation_Adapter_CaptureAdapter($this->getConfigurationAdapter(), $this->container);
$capturingAdapter->capture($transaction);
}

/**
 * This method desides based on the response parameters from Saferpay if
 * 3D secure is possible.
 * 
 * @param array $parameters
 */
protected function is3DSecurePossible(Customweb_Saferpay_Authorization_Transaction $transaction, $parameters){
return !$transaction->isMoto() && $parameters['RESULT'] == 0 && $parameters['ECI'] == self::ECI_LIABILITY_SHIFT_CUSTOMER_ENROLLED;
}

/**
 * Checks if the transaction is in a valid state.
 */
// TODO : Does this really help?
protected function isTransactionValidState($transaction){
return strlen($transaction->getCardRefId()) > 0;
}
}