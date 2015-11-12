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

//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Util/Url.php';

abstract class Customweb_Saferpay_AbstractParameterBuilder implements Customweb_Saferpay_IConstants {
	
	private $transactionContext;
	private $configuration;
	private $adapter;
	protected $container = null;
	
	/**
	 *         	   		  	 	 	
	 * @var Customweb_Saferpay_Authorization_Transaction
	 */
	protected $transaction;
	
	public function __construct(Customweb_Payment_Authorization_PaymentPage_ITransactionContext $transactionContext, Customweb_Saferpay_Configuration $configuration, Customweb_DependencyInjection_IContainer $container) {
		$this->transactionContext = $transactionContext;
		$this->configuration = $configuration;
		$this->container = $container;
	}
	
	
	protected function getReactionUrlParameters() {
		$notificationUrl = $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "index", array('cw_transaction_id' => $this->getTransaction()->getExternalTransactionId()));
		$cancelUrl = $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "cancel", array('cw_transaction_id' => $this->getTransaction()->getExternalTransactionId()));
		$failUrl = $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "fail", array('cw_transaction_id' => $this->getTransaction()->getExternalTransactionId()));
		$parameters = array();
		$parameters['SUCCESSLINK'] = Customweb_Util_Url::appendParameters($this->getSuccessUrl(), $this->getTransactionContext()->getCustomParameters());
		$parameters['NOTIFYURL']   = $notificationUrl;
		$parameters['FAILLINK']    = $failUrl;
		$parameters['BACKLINK']    = $cancelUrl;
		return $parameters;
	}
	
	protected function getOrderParameters() {
		$parameters = array();
		$parameters['CURRENCY']  = $this->getTransactionContext()->getOrderContext()->getCurrencyCode();
		$parameters['ORDERID']   = $this->getTransactionAppliedSchema();
		$parameters['ACCOUNTID'] = $this->getAccountId();
		return $parameters;
	}
	
	protected function getBasicParameters(){
		$parameters = array();
		// Why is the currency not added directly by this method?
		// Because the currency is not always needed where the amount is needed (i.e. capturing)
		$parameters['AMOUNT']    = number_format($this->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals(), 2, '', '');
		$parameters['ACCOUNTID'] = $this->getAccountId();
		return $parameters;
	}
	
	protected function getCancellationParamters(){
		$parameters = array_merge($this->getBasicParameters(), $this->getActionParameters('Cancel'));
		
		return $parameters;
	}
	
	
	
	protected function getActionParameters($action){
		$parameters = array();
		$parameters['spPassword'] = $this->getAccountPassword();
		$parameters['ID'] = $this->getTransaction()->getPaymentId();
		$parameters['ACTION'] = $action;
		return $parameters;
	}
	
	protected function getServiceParameters(){
		$parameters = array();
		if($this->getTransaction()->getCardRefId()){
			$parameters['CARDREFID'] = $this->getTransaction()->getCardRefId();
		}
		$parameters['ACCOUNTID'] = $this->getAccountId();
		$parameters['spPassword'] = $this->getAccountPassword();
		return $parameters;
	}
	
	/**
	 * 
	 * @return Customweb_Saferpay_Authorization_Transaction
	 */
	protected function getTransaction(){
		return $this->transaction;
	}
	protected function setTransaction($transaction){
		$this->transaction = $transaction;
	}
	
	
	
	/**
	 * @return Customweb_Saferpay_Configuration
	 */
	protected function getConfiguration(){
		return $this->configuration;
	}
	
	/**
	 * @return Customweb_Payment_Authorization_ITransactionContext
	 */
	protected function getTransactionContext(){
		return $this->transactionContext;
	}
	
	protected function getOrderContext(){
		return $this->transactionContext->getOrderContext();
	}
	
	protected function getAccountId(){
		if($this->getTransaction()->isMoto()){
			return $this->getConfiguration()->getMotoAccountId();
		}
		elseif($this->getTransaction()->isUseExistingAlias() && $this->getConfiguration()->isUseMotoAccountForAlias()){
			return $this->getConfiguration()->getMotoAccountId();
		}
		elseif ($this->isRecurringTransaction()) {
			return $this->getConfiguration()->getMotoAccountId();
		}
		else{
			return $this->getConfiguration()->getAccountId();
		}
	}
	
	protected function getAccountPassword(){
		if($this->getTransaction()->isMoto()){
			return $this->getConfiguration()->getMotoAccountPassword();
		}
		elseif($this->getTransaction()->isUseExistingAlias() && $this->getConfiguration()->isUseMotoAccountForAlias()){
			return $this->getConfiguration()->getMotoAccountPassword();
		}
		elseif ($this->isRecurringTransaction()) {
			return $this->getConfiguration()->getMotoAccountPassword();
		}
		else{
			return $this->getConfiguration()->getAccountPassword();
		}	
	}
	
	protected function isRecurringTransaction() {
		$transactionContext = $this->getTransactionContext();
		if ($transactionContext instanceof Customweb_Payment_Authorization_Recurring_ITransactionContext && $transactionContext->getInitialTransaction() !== null) {
			return true;
		}
		else {
			return false;
		}
	}
	
	protected function getSuccessUrl(){
		if($this->getTransaction()->isMoto()){
			return $this->getTransactionContext()->getBackendSuccessUrl();
		}
		else{
			return $this->getTransactionContext()->getSuccessUrl();
		}
	}
	
	protected function getFailedUrl(){
		if($this->getTransaction()->isMoto()){
			return $this->getTransactionContext()->getBackendFailedUrl();
		}
		else{
			return $this->getTransactionContext()->getFailedUrl();
		}
	}
	

	/**
	 * @return string
	 */
	protected final function getTransactionAppliedSchema()
	{
		$schema = $this->getConfiguration()->getOrderIdSchema();
		$id = $this->getTransaction()->getExternalTransactionId();
		
		return Customweb_Payment_Util::applyOrderSchema($schema, $id, 80);
	}
	
	/**
	 * 
	 * @return Customweb_Saferpay_Method_PaymentMethodWrapper
	 */
	protected function getPaymentMethodWrapper(){
		return Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapper($this->getTransaction()->getTransactionContext()->getOrderContext());
	}
}