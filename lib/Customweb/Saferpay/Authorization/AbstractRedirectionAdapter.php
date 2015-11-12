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

//require_once 'Customweb/Saferpay/Authorization/Hidden/Adapter.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';
//require_once 'Customweb/Saferpay/AbstractAdapter.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/ITransactionContext.php';
//require_once 'Customweb/Saferpay/Configuration.php';
//require_once 'Customweb/Core/Exception/CastException.php';
//require_once 'Customweb/Saferpay/BackendOperation/Adapter/CaptureAdapter.php';
//require_once 'Customweb/Payment/Authorization/Iframe/IAdapter.php';
//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';
//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapper.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Http/Url.php';



/**
 *
 * @author Thomas Hunziker
 *
 */
abstract class Customweb_Saferpay_Authorization_AbstractRedirectionAdapter extends Customweb_Saferpay_AbstractAdapter {

	protected abstract function createParameterBuilder($transaction);

	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container){
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
		$this->setConfigurationAdapter($configuration);
	}

	public function getAdapterPriority(){
		return 100;
	}

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}

	public function validateTransaction(Customweb_Payment_Authorization_ITransaction $transaction){
		return true;
	}

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $paymentCustomerContext){
		if($aliasTransaction != null){
			return $this->getPaymentMethodWrapper($orderContext)->getVisibleFormFields(
					$orderContext, $aliasTransaction,$failedTransaction,
					$this->isMoto || !$this->isAskForCvc($aliasTransaction,$orderContext, $paymentCustomerContext));
		} 
		else{
			return array();
		}
	}

	public function getRedirectionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		try {
			if($transaction->isUseExistingAlias()){
				return $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", 'index', array_merge($formData, array('cw_transaction_id' => $transaction->getExternalTransactionId())));
			}
			else{
				if ($transaction->getRedirectUrl() == null) {
					$builder = $this->createParameterBuilder($transaction);
					$parameters = $builder->buildParameters();
					
					$parameters = array_merge($parameters, 
							$this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->getAdditionalPaymentPageParameters());
					
					$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getCreatePayInitUrl(), $parameters);
					$transaction->setRedirectUrl(Customweb_Saferpay_Util::sendRequest($requestUrl));
				}
				return $transaction->getRedirectUrl();
			}			
			
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
			return $transaction->getFailedUrlWithCustomParameters();
		}
	}

	public function getParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$completeUrl = $this->getRedirectionUrl($transaction, $formData);
		$url = new Customweb_Http_Url($completeUrl);
		$parameters = $url->getQueryAsArray();
		
		foreach ($parameters as $key => $value) {
			$parameters[$key] = utf8_encode(stripcslashes(utf8_decode($value)));
		}
		
		return $parameters;
	}

	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		
		if($transaction->isUseExistingAlias()){
			$hiddenAdapter = new Customweb_Saferpay_Authorization_Hidden_Adapter($this->getConfiguration()->getConfigurationAdapter(), $this->getContainer());
			$result = $hiddenAdapter->processAuthorization($transaction, $parameters);
			if($transaction->getAuthorizationMethod() == Customweb_Payment_Authorization_Iframe_IAdapter::AUTHORIZATION_METHOD_NAME) {
				if($result == 'redirect:'.$transaction->getSuccessUrl() || $result == 'redirect:'.$transaction->getFailedUrl()) {
					return 'redirect:'. Customweb_Util_Url::appendParameters($transaction->getTransactionContext()->getIframeBreakOutUrl(), $transaction->getTransactionContext()->getCustomParameters());
				}
			}
			return $result;
		}
		
		if (!isset($parameters['DATA']) || empty($parameters['DATA'])) {
			return Customweb_Core_Http_Response::_("NO DATA parameter provided.")->setStatusCode(500);
		}
		
		if (!$this->validateCustomParameters($transaction, $parameters)) {
			$reason = Customweb_I18n_Translation::__("Custom parameters have been altered. Fraud possible, aborting.");
			$transaction->setAuthorizationFailed($reason);
			return 'redirect:' . $this->getFailedUrl($transaction);
		}
		
		try {
			$parameters = $this->parseRequestParameters($parameters);
		}
		catch (Exception $e) {
			$transaction->setAuthorizationParameters($parameters);
			$transaction->setAuthorizationFailed($e->getMessage());
			return 'redirect:' . $this->getFailedUrl($transaction);
		}
		
		$transaction->setPaymentInformation($this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->extractPaymentInformation($parameters));
		
		if ($this->validateParameters($transaction, $parameters)) {
			// Check transaction state
			$transaction->authorizeDry();
			
			if (isset($parameters['PAYMENTMETHOD']) && !empty($parameters['PAYMENTMETHOD'])) {
				$paymentMachineName = Customweb_Saferpay_Method_PaymentMethodWrapper::getPaymentMethodMachineNameByPaymentMethodId(
						$parameters['PAYMENTMETHOD']);
				$transaction->setEffectivePaymentMethodMachineName($paymentMachineName);
			}
			
			
			$transaction->setPaymentId($parameters['ID']);
			if (isset($parameters['ECI']) && $parameters['ECI'] != 0) {
				$transaction->setState3DSecure(Customweb_Payment_Authorization_DefaultTransaction::STATE_3D_SECURE_SUCCESS);
			}
			if ($this->getConfiguration()->isMarkLiabilityShiftTransactions()) {
				if ((!isset($parameters['ECI']) || $parameters['ECI'] == 0) &&
						 !$this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->isEciMeaningless()) {
					$transaction->setAuthorizationUncertain();
				}
			}
			
			if (isset($parameters['CARDREFID'])) {
				$transaction->setCardRefId($parameters['CARDREFID']);
			}
			if(isset($parameters['CARDMASK'])) {
				$transaction->setTruncatedPAN($parameters['CARDMASK']);
				$transaction->setAliasForDisplay($parameters['CARDMASK']);
				$this->setAliasAddress($transaction);
				$parameters['PAN'] = $parameters['CARDMASK'];
			}
			if(isset($parameters[Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME])) {
				$transaction->setOwnerName($parameters[Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME]);
			}
			//	$transaction->setOwnerName($parameters[Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME]);
			if(isset($parameters['EXPIRYMONTH']) && isset($parameters['EXPIRYYEAR'])){
				$transaction->setCardExpiryDate($parameters['EXPIRYMONTH'], $parameters['EXPIRYYEAR']);
			}
			
			$transaction->authorize(Customweb_I18n_Translation::__('Customer sucessfully returned from the Saferpay payment page.'));
			
			if ($transaction->getTransactionContext()->getCapturingMode() == null) {
				$capturingMode = $this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->getPaymentMethodConfigurationValue(
						'capturing');
			}
			else {
				$capturingMode = $transaction->getTransactionContext()->getCapturingMode();
			}
			$transaction->setAuthorizationParameters($parameters);
			if (!$transaction->isAuthorizationUncertain() &&
					 $capturingMode == Customweb_Payment_Authorization_ITransactionContext::CAPTURING_MODE_DIRECT) {
				$this->captureTransaction($transaction);
			}
		}
		else {
			$transaction->setAuthorizationParameters($parameters);
			$transaction->setAuthorizationFailed(
					Customweb_I18n_Translation::__('Possible fraud detected. Parameters send from Saferpay were not correct.'));
		}
		
		return $this->finalizeAuthorizationRequest($transaction);
	}

	public function finalizeAuthorizationRequest(Customweb_Payment_Authorization_ITransaction $transaction){
		/* @var $transaction Customweb_Saferpay_Authorization_Transaction */
		if ($transaction->isAuthorizationFailed()) {
			return 'redirect:' . $this->getFailedUrl($transaction);
		}
		
		if ($transaction->isAuthorized()) {
			return 'redirect:' . $this->getSuccessUrl($transaction);
		}
		
		return $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "index", 
				array(
					'cw_transaction_id' => $transaction->getExternalTransactionId() 
				));
	}

	protected function getContainer(){
		return $this->container;
	}

	protected function captureTransaction($transaction){
		$capturingAdapter = new Customweb_Saferpay_BackendOperation_Adapter_CaptureAdapter($this->getConfigurationAdapter(), 
				$this->container);
		$capturingAdapter->capture($transaction);
	}

}
