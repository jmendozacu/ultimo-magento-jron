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

class Customweb_Saferpay_BackendOperation_Adapter_Cancel_ParameterBuilder extends Customweb_Saferpay_AbstractParameterBuilder{
	
	/**
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param Customweb_Saferpay_Configuration $configuration
	 */
	public function __construct(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Saferpay_Configuration $configuration, Customweb_DependencyInjection_IContainer $container){
		parent::__construct($transaction->getTransactionContext(), $configuration, $container);
		$this->transaction = $transaction;
	}
	
	public function buildParameters() {
		return $this->getCancellationParameters();
	}
	
	protected function getCancellationParameters(){
		$parameters = array_merge(
				$this->getActionParameters('Cancel'),
				$this->getBasicParameters()	
		);
		return $parameters;
	}	
}