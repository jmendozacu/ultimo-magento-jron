<?php

/**
 *  * You are allowed to use this API in your web application.
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

//require_once 'Customweb/Payment/Endpoint/Controller/Abstract.php';
//require_once 'Customweb/Core/Exception/CastException.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';



/**
 *
 * @author Mathis Kappeler
 * @Controller("process")
 *
 */
class Customweb_Saferpay_Endpoint_Process extends Customweb_Payment_Endpoint_Controller_Abstract {

	/**
	 * @Action("index")
	 */
	public function index(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());

		$isAuthorized = $transaction->isAuthorized();
		$parameters = $request->getParameters();
		$response = $adapter->processAuthorization($transaction, $parameters);
		
		return $response;
	}

	/**
	 * @Action("cancel")
	 */
	public function cancel(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		
		if (!$transaction->isAuthorizationFailed() && !$transaction->isAuthorized()) {
			
			$message = new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__("You have successfully cancelled the payment."), 
					Customweb_I18n_Translation::__("The customer cancelled the transaction."));
			$transaction->setAuthorizationFailed($message);
		}
		
		$url = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod())->getFailedUrl($transaction);
		
		return Customweb_Core_Http_Response::redirect($url);
	}
	
	/**
	 * Called when iframe/pp fails, as PSP does not send a notification
	 * @Action("fail")
	 */
	public function fail(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
	
		if (!$transaction->isAuthorizationFailed() && !$transaction->isAuthorized()) {
				
			$message = new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__("The payment could not be processed."),
					Customweb_I18n_Translation::__("The payment failed."));
			$transaction->setAuthorizationFailed($message);
		}
	
		$url = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod())->getFailedUrl($transaction);
	
		return Customweb_Core_Http_Response::redirect($url);
	}
}