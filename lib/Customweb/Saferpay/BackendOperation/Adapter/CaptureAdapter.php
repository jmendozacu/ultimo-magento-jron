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
//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
//require_once 'Customweb/Util/Invoice.php';
//require_once 'Customweb/Saferpay/BackendOperation/Adapter/Capture/ParameterBuilder.php';

/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_BackendOperation_Adapter_CaptureAdapter extends Customweb_Saferpay_AbstractAdapter implements Customweb_Payment_BackendOperation_Adapter_Service_ICapture {

	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		parent::__construct(new Customweb_Saferpay_Configuration($configuration), $container);
	}

	public function capture(Customweb_Payment_Authorization_ITransaction $transaction){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$items = $transaction->getUncapturedLineItems();
		$this->partialCapture($transaction, $items, true);
	}
	
	public function partialCapture(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
			if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		
		// Check the transaction state
		$transaction->partialCaptureByLineItemsDry($items, true);
		
		$builder = new Customweb_Saferpay_BackendOperation_Adapter_Capture_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
		$builder->setAmount($amount);
		$parameters = $builder->buildParameters();
		$this->performPayCompleteAction($parameters, Customweb_I18n_Translation::__('The transaction could not be captured.'));
		
		$transaction->partialCaptureByLineItems($items, true);
		
		
	}
		
}