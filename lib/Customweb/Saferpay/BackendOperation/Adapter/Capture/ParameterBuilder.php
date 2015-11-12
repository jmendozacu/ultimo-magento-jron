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

//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapperFactory.php';
//require_once 'Customweb/Saferpay/AbstractParameterBuilder.php';

class Customweb_Saferpay_BackendOperation_Adapter_Capture_ParameterBuilder extends Customweb_Saferpay_AbstractParameterBuilder{
	private $partialAmount;
	
	/**
	 * 
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param Customweb_Saferpay_Configuration $configuration
	 */
	public function __construct(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Saferpay_Configuration $configuration,  Customweb_DependencyInjection_IContainer $container){
		parent::__construct($transaction->getTransactionContext(), $configuration, $container); 
		$this->setTransaction($transaction);
	}
	
	public function setAmount($amount){
		if($amount <= $this->transaction->getAuthorizationAmount()){
			$this->partialAmount = number_format($amount, 2, '', '');
		}
	}
	
	public function buildParameters() {
		return $this->getCaptureParameters();
	}	
	
	protected function getCaptureParameters(){
		$parameters = array_merge(
				$this->getBasicParameters(), 
				$this->getActionParameters('Settlement'),
				$this->getAdditionalMethodParameters()
		);
		if($this->partialAmount != null){
			$parameters['AMOUNT'] = $this->partialAmount;
		}
		return $parameters;
	}
	
	private function getAdditionalMethodParameters(){
		$paymentMethod = Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapper($this->getOrderContext());
		return $paymentMethod->getAdditionalCaptureParameters();
	}
	
	
}