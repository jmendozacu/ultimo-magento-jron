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

//require_once 'Customweb/Saferpay/Authorization/PaymentPage/ParameterBuilder.php';
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';
//require_once 'Customweb/Http/Url.php';
//require_once 'Customweb/Saferpay/Authorization/AbstractRedirectionAdapter.php';
//require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';


/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_PaymentPage_Adapter extends Customweb_Saferpay_Authorization_AbstractRedirectionAdapter
implements Customweb_Payment_Authorization_PaymentPage_IAdapter {
	
	protected function createParameterBuilder($transaction) {
		return new Customweb_Saferpay_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getConfiguration(), $this->container);
	}
	
	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function createTransaction(Customweb_Payment_Authorization_PaymentPage_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Saferpay_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}

	public function isHeaderRedirectionSupported(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		if($transaction->isUseExistingAlias()){
			return false;
		}
		return strlen($this->getRedirectionUrl($transaction, $formData)) <= self::MAX_ALLOWED_URL_LENGTH;
	}

	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$completeUrl = $this->getRedirectionUrl($transaction,$formData);
		$url = new Customweb_Http_Url($completeUrl);
		return $url->getBaseUrl() . $url->getPath();
	}

}
