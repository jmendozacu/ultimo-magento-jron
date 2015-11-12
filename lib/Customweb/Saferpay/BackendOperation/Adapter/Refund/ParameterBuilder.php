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

class Customweb_Saferpay_BackendOperation_Adapter_Refund_ParameterBuilder extends Customweb_Saferpay_AbstractParameterBuilder {

	public function __construct(Customweb_Saferpay_Authorization_Transaction $transaction, Customweb_Saferpay_Configuration $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct($transaction->getTransactionContext(), $configuration, $container);
		$this->transaction = $transaction;
	}

	public function buildParameters($amount){
		$parameters = array_merge(
				$this->getServiceParameters(),
				$this->getRefundParameters()
		);
		$refundNumber = count($this->getTransaction()->getRefunds())+1;
		$parameters['AMOUNT'] = number_format($amount, 2, '', '');
		$parameters['CURRENCY'] = $this->getTransaction()->getCurrencyCode();
		$parameters['ORDERID'] = Customweb_Util_String::substrUtf8($this->getTransactionAppliedSchema().'-refund'.$refundNumber, -80);
		$parameters['DESCRIPTION'] = Customweb_I18n_Translation::__(
				"Refunding Transaction '!transactionId'",
				array('!transactionId' => $this->getTransaction()->getExternalTransactionId())
		);
		return $parameters;
	}

	protected function getRefundParameters(){
		$parameters = array();
		$parameters['ACTION'] = 'Credit';
		$p = $this->getTransaction()->getAuthorizationParameters();
		if (isset($p['EXP'])) {
			$parameters['EXP'] = $p['EXP'];
		}
		$parameters['REFID'] = $p['ID'];
		return $parameters;
	}
}