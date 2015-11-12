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
//require_once 'Customweb/Saferpay/Configuration.php';
//require_once 'Customweb/Saferpay/Authorization/Hidden/ParameterBuilder.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';

//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapper.php';
//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Payment/Authorization/Hidden/IAdapter.php';


/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Hidden_Adapter extends Customweb_Saferpay_OnSiteAdapter
implements Customweb_Payment_Authorization_Hidden_IAdapter {
	
	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
		$this->setConfigurationAdapter($configuration);
	}

	public function getAdapterPriority() {
		return 200;
	}
	
	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return $this->getPaymentMethodWrapper($orderContext)->isAliasManagerSupported() &&
				$this->getPaymentMethodWrapper($orderContext)->isAuthorizationMethodSupported('hidden');
	}
	
	public function validateTransaction(Customweb_Payment_Authorization_ITransaction $transaction) {
		return true;
	}
	
	public function createTransaction(Customweb_Payment_Authorization_Hidden_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Saferpay_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}
	
	public function getHiddenFormFields(Customweb_Payment_Authorization_ITransaction $transaction){
		return array();
	}
	
	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction){
		if($transaction->isUseExistingAlias()){
			return $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", 'index', array('cw_transaction_id' => $transaction->getExternalTransactionId()));
		}
		else{
			$builder = new Customweb_Saferpay_Authorization_Hidden_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
			$parameters = $builder->buildRegisterCardParameters();
			$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getCreatePayInitUrl(), $parameters);
			
			return Customweb_Saferpay_Util::sendRequest($requestUrl);
		}
		
	}
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,$paymentCustomerContext){
		return $this->getPaymentMethodWrapper($orderContext)->getVisibleFormFields(
				$orderContext, $aliasTransaction,$failedTransaction, 
				$this->isMoto || !$this->isAskForCvc($aliasTransaction,$orderContext, $paymentCustomerContext));
	}
	
	public function isHiddenAuthorizationSupported(Customweb_Payment_Authorization_ITransaction $transaction){
		return true;
	}
	
	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		// Check if the CVC field is filled in in case of a alias transaction.
		if($transaction->getTransactionState() == Customweb_Saferpay_Authorization_Transaction::STATE_INITIAL &&
				$transaction->isUseExistingAlias() && 
				!$this->isCardVerificationPossible($transaction->getTransactionContext()->getAlias(),
						$transaction->getTransactionContext()->getOrderContext(),
						$transaction->getPaymentCustomerContext(), $parameters)){
			$message = Customweb_I18n_Translation::__("The CVC field is required.");
			$transaction->setAuthorizationFailed($message);
		}
		
		if(!$this->validateCustomParameters($transaction, $parameters)){
			$reason = Customweb_I18n_Translation::__("Custom parameters have been altered. Fraud possible, aborting.");
			$transaction->setAuthorizationFailed($reason);
		}
		
		if($transaction->isAuthorizationFailed()){
			$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
		}
		elseif($transaction->isAuthorized())
		{
			$this->redirect(null, $transaction, $this->getSuccessUrl($transaction));
		}
		else {
			switch($transaction->getTransactionState()){
				case Customweb_Saferpay_Authorization_Transaction::STATE_INITIAL:
					$this->processScdResponse($transaction, $parameters);
					break;
				case Customweb_Saferpay_Authorization_Transaction::STATE_3D_SECURE:

					if (!isset($parameters['DATA']) || empty($parameters['DATA'])) {
						return Customweb_Core_Http_Response::_("NO DATA parameter provided.")->setStatusCode(500);
					}
					
					$parameters = array_merge(
						$parameters,
						$this->parseRequestParameters($parameters)
					);
					$this->process3DSecureResponse($transaction, $parameters);
					break;
				default:
					$this->redirect(null, $transaction, $this->getFailedUrl($transaction));
			}
		}
		return $this->finalizeAuthorizationRequest($transaction);
	}
	
	public function finalizeAuthorizationRequest(Customweb_Payment_Authorization_ITransaction $transaction){
		return "redirect:" . $transaction->getNextRedirectUrl();
	}
	

}
