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


//require_once 'Customweb/Saferpay/AbstractParameterBuilder.php';


class Customweb_Saferpay_Authorization_Hidden_ParameterBuilder extends Customweb_Saferpay_AbstractParameterBuilder {

	private $additionalParameters = array();
	
	
	public function __construct(Customweb_Saferpay_Authorization_Transaction $transaction, Customweb_Saferpay_Configuration $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct($transaction->getTransactionContext(), $configuration, $container);
		$this->transaction = $transaction;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Customweb_Saferpay_AbstractParameterBuilder::buildParameters()
	 */
	public function buildParameters($additionalParameters = null) {
		$this->additionalParameters = $additionalParameters;
		$parameters = array_merge(
			$this->getBasicParameters(),
			$this->getServiceParameters(),
			$this->getOrderParameters(),
			$this->getAuthorizationParameters()
		);
		
		return $parameters;
	}
	
	
	public function buildVerifyEnrollmentParameters(){
		return array_merge(
			$this->getBasicParameters(),
			$this->getServiceParameters(),
			$this->getMPIParameters()		
		);
	}
	
	public function buildRegisterCardParameters(){
		return $this->getRegisterCardParameters();
	}
	
	public function buildCardUpdateParameters(){
		$parameters = $this->getServiceParameters();
		
		$parameters['MSGTYPE'] = 'UpdateCard';
		$parameters['ACCOUNTID'] = $this->getAccountId();
		$parameters['CARDREFID'] = $this->getTransaction()->getCardRefId();
		$parameters['EXP'] = $this->getTransaction()->getCardExpiryDate();
		
		return $parameters;
	}
	
	protected function getAuthorizationParameters(){
		$parameters = $this->additionalParameters;
		$parameters['ACTION'] = 'Debit';
		if($this->getTransaction()->getMpiSessionId()){
			$parameters['MPI_SESSIONID'] = $this->getTransaction()->getMpiSessionId();
		}
		
		$parameters = $this->getPaymentMethodWrapper()->getAuthorizationParameters($this->getTransaction(),$parameters);
		
		return $parameters;
	}
	
	protected function getMPIParameters(){
		$parameters = array();
		$parameters['MSGTYPE'] = 'VerifyEnrollment';
		$parameters['MPI_PA_BACKLINK'] = $this->getHiddenAuthorizationUrl();
		$parameters['EXP'] = $this->getTransaction()->getCardExpiryDate();
		$parameters['CURRENCY'] = $this->getOrderContext()->getCurrencyCode();
		
		return $parameters;
	}
	
	protected function getRegisterCardParameters(){
		$parameters =  array();
		$parameters['ACCOUNTID'] = $this->getAccountId();
		$parameters['CARDREFID'] = 'new';
		$parameters['SUCCESSLINK'] = $this->getHiddenAuthorizationUrl();
		$parameters['FAILLINK']    = $this->getHiddenAuthorizationUrl();
		return $parameters;
	}
	
	private function getHiddenAuthorizationUrl() {
		return $this->container->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", 'index', array('cw_transaction_id' => $this->getTransaction()->getExternalTransactionId()));
	}
	
	
// 	/**
// 	 * Generates the next unique card reference ID for the use with secure card data service of
// 	 * Saferpay.
// 	 *
// 	 * @return int
// 	 */
// 	protected function getNextCardRefId()
// 	{
// 		$transactionString = $this->getTransactionContext()->getExternalTransactionId();
// 		$dateTime = date('Y-m-d_H:i:s');
// 		$cardRefId = $transactionString . '_' . $dateTime;
// 		return $cardRefId;
// 	}
}
