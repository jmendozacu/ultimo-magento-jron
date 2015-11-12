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

//require_once 'Customweb/Saferpay/OnSiteAdapter.php';
//require_once 'Customweb/Saferpay/Configuration.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';

//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapper.php';
//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
//require_once 'Customweb/Saferpay/Authorization/Server/Adapter.php';
//require_once 'Customweb/Saferpay/Authorization/Hidden/Adapter.php';
//require_once 'Customweb/Saferpay/Authorization/PaymentPage/Adapter.php';

/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Moto_Adapter extends Customweb_Saferpay_OnSiteAdapter
implements Customweb_Payment_Authorization_Moto_IAdapter {

	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
		$this->setConfigurationAdapter($configuration);
		$this->isMoto = true;
		
	}
	
	public function getAdapterPriority() {
		return 1000;
	}
	
	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	
	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		$adapter = $this->getAdapterInstanceByPaymentMethod($orderContext->getPaymentMethod());
		return $adapter->isAuthorizationMethodSupported($orderContext);
	}
	
	public function validateTransaction(Customweb_Payment_Authorization_ITransaction $transaction) {
		return true;
	}
	
	public function createTransaction(Customweb_Payment_Authorization_Moto_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Saferpay_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$orderContext = $transaction->getTransactionContext()->getOrderContext();
		$adapter = $this->getAdapterInstanceByPaymentMethod($orderContext->getPaymentMethod());
		$transaction->setMotoAuthorizationMethodName($adapter->getAuthorizationMethodName());
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}
	
	public function getParameters(Customweb_Payment_Authorization_ITransaction $transaction){
		$adapter = $this->getAdapterInstanceByTransaction($transaction);
		
		if($adapter instanceof Customweb_Saferpay_Authorization_Hidden_Adapter)
		{
			return $adapter->getHiddenFormFields($transaction);
		}
		else{
			return $adapter->getParameters($transaction, array());
		}
	}
	
	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction){
		$adapter = $this->getAdapterInstanceByTransaction($transaction);
		return $adapter->getFormActionUrl($transaction, array());
	}
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,$paymentCustomerContext){
		$adapter = $this->getAdapterInstanceByPaymentMethod($orderContext->getPaymentMethod());
		return $adapter->getVisibleFormFields($orderContext, $aliasTransaction, $failedTransaction, $paymentCustomerContext);
	}
	

	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		$adapter = $this->getAdapterInstanceByTransaction($transaction);
		return $adapter->processAuthorization($transaction, $parameters);
	}
	

	public function finalizeAuthorizationRequest(Customweb_Payment_Authorization_ITransaction $transaction){
		$adapter = $this->getAdapterInstanceByTransaction($transaction);
		return $adapter->finalizeAuthorizationRequest($transaction);
	}
	
	
	/**
	 * @param Customweb_Payment_Authorization_IPaymentMethod $paymentMethod
	 * @throws Exception
	 * @return Customweb_Payment_Authorization_IAdapter
	 */
	protected function getAdapterInstanceByPaymentMethod(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod) {
		$configuredAuthorizationMethod = $paymentMethod->getPaymentMethodConfigurationValue('authorizationMethod');
		$adapter = null;
		switch (strtolower($configuredAuthorizationMethod)) {
				
			// In case the server mode is choosen, we stick to the hidden, for simplicity.
			case strtolower(Customweb_Saferpay_Authorization_Server_Adapter::AUTHORIZATION_METHOD_NAME):
			case strtolower(Customweb_Saferpay_Authorization_Hidden_Adapter::AUTHORIZATION_METHOD_NAME):
				$adapter = new Customweb_Saferpay_Authorization_Hidden_Adapter($this->getConfigurationAdapter(), $this->container);
				break;
	
			case strtolower(Customweb_Saferpay_Authorization_PaymentPage_Adapter::AUTHORIZATION_METHOD_NAME):
				$adapter = new Customweb_Saferpay_Authorization_PaymentPage_Adapter($this->getConfigurationAdapter(), $this->container);
				break;
			default:
				throw new Exception(Customweb_I18n_Translation::__("Could not find an adapter for the authoriztion method !methodName.", array('!methodName' => $configuredAuthorizationMethod)));
		}
		
		$adapter->setIsMoto(true);
		return $adapter;
	}
	
	/**
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 * @return Customweb_Payment_Authorization_IAdapter
	 */
	protected function getAdapterInstanceByTransaction(Customweb_Saferpay_Authorization_Transaction $transaction) {
		return $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getMotoAuthorizationMethodName());
	}
	
	/**
	 * @return Customweb_Payment_Authorization_IAdapterFactory
	 */
	protected function getAdapterFactory() {
		return $this->container->getBean('Customweb_Payment_Authorization_IAdapterFactory');
	}
	
}
