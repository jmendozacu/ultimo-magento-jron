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

//require_once 'Customweb/Saferpay/AbstractAdapter.php';
//require_once 'Customweb/Saferpay/OnSiteAdapter.php';
//require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';
//require_once 'Customweb/Saferpay/Configuration.php';
//require_once 'Customweb/Saferpay/Util.php';

/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Server_Adapter extends Customweb_Saferpay_OnSiteAdapter 
implements Customweb_Payment_Authorization_Server_IAdapter{
	
	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
		$this->setConfigurationAdapter($configuration);
	}

	public function getAdapterPriority() {
		return 400;
	}
	
	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	public function createTransaction(Customweb_Payment_Authorization_Server_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Saferpay_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,
			$paymentCustomerContext){
		return $this->getPaymentMethodWrapper($orderContext)->getVisibleFormFields($orderContext, $aliasTransaction, $failedTransaction);
	}
	
	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return $this->getPaymentMethodWrapper($orderContext)->isAuthorizationMethodSupported('server');
	}
	
	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		if(!$this->validateCustomParameters($transaction, $parameters) &&
			$transaction->getTransactionState() != Customweb_Saferpay_Authorization_Transaction::STATE_INITIAL){
			$reason = Customweb_I18n_Translation::__("Custom parameters have been altered. Fraud possible, aborting.");
			$transaction->setAuthorizationFailed($reason);
		}
		
		if($transaction->isAuthorizationFailed()){
			$this->redirect(null, $transaction, $transaction->getTransactionContext()->getFailedUrl());
		}
		elseif($transaction->isAuthorized()){
			$this->redirect(null, $transaction, $transaction->getTransactionContext()->getSuccessUrl());
		}
		else{
			if($this->getPaymentMethodWrapper($transaction->getTransactionContext()->getOrderContext())->isAliasManagerSupported()){
				$this->processAuthorizationUsingAlias($transaction, $parameters);
			}
			else{
				$this->processAuthorizationWithoutAlias($transaction, $parameters);
			}
		}
		return $this->finalizeAuthorizationRequest($transaction);
	}
	
	protected function processAuthorizationUsingAlias(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		switch($transaction->getTransactionState()){
			case Customweb_Saferpay_Authorization_Transaction::STATE_INITIAL:
				$this->processFormData($transaction, $parameters);
				break;
			case Customweb_Saferpay_Authorization_Transaction::STATE_ALIAS_EXISTS:
				$this->processScdResponse($transaction, $parameters);
				break;
			case Customweb_Saferpay_Authorization_Transaction::STATE_3D_SECURE:
				$parameters = $this->parseRequestParameters($parameters);
				$this->process3DSecureResponse($transaction, $parameters);
				break;
			default:
				$this->redirect(null, $transaction, $transaction->getTransactionContext()->getFailedUrl());
		}
	}
	
	protected function processAuthorizationWithoutAlias(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		switch($transaction->getTransactionState()){
			case Customweb_Saferpay_Authorization_Transaction::STATE_INITIAL:
				$this->requestAuthorization($transaction,$parameters);
				break;
		}
	}

	public function finalizeAuthorizationRequest(Customweb_Payment_Authorization_ITransaction $transaction){
		$response = new Customweb_Core_Http_Response();
		$response->setLocation($transaction->getNextRedirectUrl());
		return $response;
	}
	
	private function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction){
		$builder = new Customweb_Saferpay_Authorization_Hidden_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
		$parameters = $builder->buildRegisterCardParameters();
		$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getCreatePayInitUrl(), $parameters);
		return Customweb_Saferpay_Util::sendRequest($requestUrl);
	}
	
	protected function processFormData(Customweb_Saferpay_Authorization_Transaction $transaction, $parameters){
		if($transaction->isUseExistingAlias()){
			$transaction->setTransactionState(Customweb_Saferpay_Authorization_Transaction::STATE_ALIAS_EXISTS);
			$parameters = array_merge($parameters,$transaction->getTransactionContext()->getCustomParameters());
			$this->processAuthorization($transaction, $parameters);
		}
		else{
			$this->createAlias($transaction, $parameters);
		}
	}
	
	protected function createAlias($transaction,$parameters){
		$url = $this->getFormActionUrl($transaction);
		$response = Customweb_Saferpay_Util::sendFormData($url,$parameters);
			
		if(isset($response['location'])){
			$transaction->setTransactionState(Customweb_Saferpay_Authorization_Transaction::STATE_ALIAS_EXISTS);
			$transaction->setNextRedirectUrl($response['location']);
		}
		else{
			$reason = Customweb_I18n_Translation::__(
					"Server responded with unexpected headers '!response'",
					array('!response' => print_r($response,true))
			);
			$transaction->setAuthorizationFailed($reason);
			$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
		}
	}
}