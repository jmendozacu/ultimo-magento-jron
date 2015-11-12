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


//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';
//require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';


/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Recurring_Adapter extends Customweb_Saferpay_OnSiteAdapter
implements Customweb_Payment_Authorization_Recurring_IAdapter {
	
	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
		$this->setConfigurationAdapter($configuration);
	}

	public function getAdapterPriority() {
		return 1001;
	}
	
	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod){
		return $this->getPaymentMethodWrapperFromPaymentMethod($paymentMethod)->isRecurringPaymentSupported();
	}
	
	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}
	
	public function createTransaction(Customweb_Payment_Authorization_Recurring_ITransactionContext $transactionContext){
		$transaction = new Customweb_Saferpay_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}
	
	public function process(Customweb_Payment_Authorization_ITransaction $transaction){
		$initialTransaction = $transaction->getTransactionContext()->getInitialTransaction();
		$this->useInitialTransactionData($initialTransaction, $transaction);
		
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$this->requestAuthorization($transaction);
	}
	
	/**
	 * Copy important data from the initial transaction to the new transaction.
	 * 
	 * @param Customweb_Saferpay_Authorization_Transaction $initialTransaction
	 * @param Customweb_Saferpay_Authorization_Transaction $recurringTransaction
	 */
	protected function useInitialTransactionData(Customweb_Saferpay_Authorization_Transaction $initialTransaction, Customweb_Saferpay_Authorization_Transaction $recurringTransaction){
		$recurringTransaction->setCardRefId($initialTransaction->getCardRefId());
		$recurringTransaction->setCardExpiryDate($initialTransaction->getCardExpiryMonth(), $initialTransaction->getCardExpiryYear());
		$recurringTransaction->setOwnerName($initialTransaction->getOwnerName());
		$recurringTransaction->setTruncatedPAN($initialTransaction->getTruncatedPAN());
		$recurringTransaction->setMpiSessionId(null);
	}	
}