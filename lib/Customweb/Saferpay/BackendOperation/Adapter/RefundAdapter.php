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
//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
//require_once 'Customweb/Util/Invoice.php';
//require_once 'Customweb/Saferpay/BackendOperation/Adapter/Capture/ParameterBuilder.php';
//require_once 'Customweb/Saferpay/BackendOperation/Adapter/Refund/ParameterBuilder.php';

/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_BackendOperation_Adapter_RefundAdapter extends Customweb_Saferpay_AbstractAdapter implements Customweb_Payment_BackendOperation_Adapter_Service_IRefund {

	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
	}
	
	public function refund(Customweb_Payment_Authorization_ITransaction $transaction){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$items = $transaction->getNonRefundedLineItems();
		return $this->partialRefund($transaction, $items, true);
	}
	
	public function partialRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		
		// Check the transaction state         	   		  	 	 	
		$transaction->refundByLineItemsDry($items, $close);

		$builder = new Customweb_Saferpay_BackendOperation_Adapter_Refund_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
		$sendParameters = $builder->buildParameters($amount);

		$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getExecuteUrl(), $sendParameters);
		$response = Customweb_Saferpay_Util::sendRequest($requestUrl);
		$parameters = $this->getXmlParameters($response);

		if($parameters['RESULT'] == 0)
		{
			$id = $parameters['ID'];
			$this->capture($transaction, $amount, $id);

			$refundItem = $transaction->refundByLineItems($items, $close, Customweb_I18n_Translation::__(
					"The total amount of the transaction was refunded. Refund transaction ID: '!paymentId' .",array('!paymentId' => $id)));
			$refundItem->setRefundId($id);

		}
		else{
			if ($parameters['RESULT'] == '75') {
				throw new Exception(Customweb_I18n_Translation::__('Refund of !amount failed because no card reference id was provided. This most likely because you try to refund a transaction authorized over the payment page. (Code: !result).',
						array('!amount' => $amount, '!result' => $parameters['RESULT']))
				);
			}
			else {
				throw new Exception(Customweb_I18n_Translation::__('Refund of !amount failed with code: !result.',
						array('!amount' => $amount, '!result' => $parameters['RESULT']))
				);
			}
			
		}
	}

	protected function capture($transaction, $amount, $id){
		$builder = new Customweb_Saferpay_BackendOperation_Adapter_Capture_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
		$parameters = $builder->buildParameters();
		$parameters['ID'] = $id;

		// No amount must be send in case of refunds.
		unset($parameters['AMOUNT']);

		$this->performPayCompleteAction($parameters,Customweb_I18n_Translation::__('The refund could not be captured.'));
	}
		
		
}