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

//require_once 'Customweb/Saferpay/Method/CreditCardWrapper.php';
//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapperFactory.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapper.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/I18n/Translation.php';

//require_once 'Customweb/I18n/Translation.php';

class Customweb_Saferpay_AbstractAdapter implements Customweb_Saferpay_IConstants {
	/**
	 * Configuration object.
	 *
	 * @var Customweb_Saferpay_Configuration
	 */
	private $configuration;
	private $configurationAdapter;
	protected $isMoto = false;
	protected $container = null;

	public function __construct($configuration, Customweb_DependencyInjection_IContainer $container){
		$this->configuration = $configuration;
		$this->container = $container;
	}

	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		$method = Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapper($orderContext);
		$method->preValidate($orderContext, $paymentContext);
	}

	/**
	 * Returns the configuration object.
	 *          	   		  	 	 	
	 * 
	 * @return Customweb_Saferpay_Configuration
	 */
	public function getConfiguration(){
		return $this->configuration;
	}

	public function isTestMode(){
		return $this->getConfiguration()->isTestMode();
	}

	/**
	 * This method returns the base URL of Saferpay.
	 *
	 * @return The base URL
	 */
	protected final function getBaseUrl(){
		return 'https://www.saferpay.com/hosting/';
	}

	public function validate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext, array $formData){}

	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		// TODO: Move this into a dedicated authorization adpater
		return $orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing');
	}

	protected function parseRequestParameters($parameters){
		$data = $this->getIfExists($parameters, 'DATA');
		$signature = $this->getIfExists($parameters, 'SIGNATURE');
		
		$this->verifyResponse($data, $signature);
		
		$params = $this->getXmlParameters($data);
		$params['sfpCardCvc'] = $this->getIfExists($parameters, 'sfpCardCvc');
		$params['sfCecD'] = $this->getIfExists($parameters, 'sfCecD');
		$params[Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME] = $this->getIfExists($parameters, 
				Customweb_Saferpay_Method_PaymentMethodWrapper::FORM_KEY_OWNER_NAME);
		$params['transaction_id'] = $this->getIfExists($parameters, 'transaction_id');
		return $params;
	}

	/**
	 * Validates the parameters against the transaction
	 * 
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param array $parameters
	 * @return boolean True if parameters are valid.
	 */
	protected function validateParameters(Customweb_Payment_Authorization_ITransaction $transaction, $parameters){
		$isValid = true;
		
		if ($parameters['AMOUNT'] != number_format($transaction->getAuthorizationAmount(), 2, '', '')) {
			$isValid = false;
		}
		else if ($parameters['ACCOUNTID'] != $this->getConfiguration()->getAccountId()) {
			$isValid = false;
		}
		else if (!$this->getConfiguration()->isTestMode() && $parameters['PROVIDERID'] == self::SAFERPAYTEST_PROVIDER_ID) {
			$isValid = false;
		}
		else if ($parameters['CURRENCY'] != $transaction->getCurrencyCode()) {
			$isValid = false;
		}
		return $isValid;
	}

	/**
	 * This method checks that the custom parameters supplied by the transaction context have not been
	 * altered between sending them and receiving them again.
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param array $parameters
	 * @return boolean
	 */
	protected function validateCustomParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		// 		$customParametersBefore = $transaction->getTransactionContext()->getCustomParameters();
		// 		foreach($customParametersBefore as $key => $value){
		// 			if(!isset($parameters[$key])){
		// 				return false;
		// 			}
		// 			if($parameters[$key] != $value){
		// 				return false;
		// 			}
		// 		}
		return true;
	}

	/**
	 * Returns the $array[$key] if an element with key $key exists and
	 * $default otherwise.
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $default
	 */
	protected function getIfExists(array $array, $key, $default = ''){
		return isset($array[$key]) ? $array[$key] : $default;
	}

	/**
	 * This method returns the url to send payment confirmation verifications.
	 *
	 * @return String URL to the payment page without any parameter.
	 */
	protected function getVerifyPayConfirmUrl(){
		return $this->getBaseUrl() . self::URL_VERIFY_PAY_CONFRIM;
	}

	/**
	 * This method sends a verification request to Saferpay to verify that the parameters received
	 * are signed with the correct signature.
	 *
	 * @param string $data
	 * @param string $signature
	 */
	public function verifyResponse($data, $signature){
		$serviceUrl = $this->getVerifyPayConfirmUrl();
		$spPassword = $this->getAccountPassword();
		$requestUrl = $serviceUrl . '?ACCOUNTID=' . $this->getConfiguration()->getAccountId() . '&spPassword=' . $spPassword . '&DATA=' .
				 urlencode($data) . '&SIGNATURE=' . $signature;
		
		try {
			$response = Customweb_Saferpay_Util::sendRequest($requestUrl);
		}
		catch (Exception $e) {
			throw new Exception(
					Customweb_I18n_Translation::__('Saferpay response verification failed. Manipulation possible. Reason: !message', 
							array(
								'!message' => $e->getMessage() 
							)));
		}
	}

	/**
	 * This method returns the merchant id for the interaction.
	 *
	 * @return String Merchant ID
	 */
	protected final function getAccountPassword(){
		if ($this->getConfiguration()->isTestMode()) {
			return $this->getConfiguration()->getTestAccountPassword();
		}
		else {
			return $this->getConfiguration()->getAccountPassword();
		}
	}

	protected function performPayCompleteAction($parameters, $failMessage){
		$requestUrl = Customweb_Saferpay_Util::addParametersToUrl($this->getPayCompleteUrl(), $parameters);
		
		try {
			$response = Customweb_Saferpay_Util::sendRequest($requestUrl);
			return $this->parseServerResponse($response);
		}
		catch (Exception $e) {
			$message = $failMessage . "\n" . $e->getMessage();
			throw new Exception($message);
		}
	}

	/**
	 * This method returns the url to send the request for a new
	 * payment page url.
	 *
	 * @return String URL to request new payment page link
	 */
	protected function getCreatePayInitUrl(){
		return $this->getBaseUrl() . self::URL_CREATE_PAY_INIT;
	}

	/**
	 * This method returns the url to complete payments.
	 *
	 * @return String URL to the pay complete service.
	 */
	protected function getPayCompleteUrl(){
		return $this->getBaseUrl() . self::URL_PAY_COMPLETE;
	}

	/**
	 * This method returns the url to the verify enrollment url.
	 */
	protected function getVerifyEnrollmentUrl(){
		return $this->getBaseUrl() . self::URL_VERIFY_ENROLLMENT;
	}

	/**
	 * This method returns the url to execute authorizations.
	 */
	protected function getExecuteUrl(){
		return $this->getBaseUrl() . self::URL_EXECUTE;
	}

	/**
	 * Extracts the attributes of the root tag in the xml string.
	 * 
	 * @param string $xml The xml string to use
	 * @return array
	 */
	protected function getXmlParameters($xml){
		// In some cases the shop system may add slashes to the response. We check if
		// the response contains slashes, which escaps the quotes. If there are slashes, we strip
		// them away.
		if (strstr($xml, 'MSGTYPE=\"')) {
			$xml = stripslashes($xml);
		}
		preg_match_all('/([^[:space:]=]+)\="([^"]*)"/i', $xml, $result);
		
		$params = array();
		foreach ($result[1] as $key => $value) {
			$params[$value] = html_entity_decode($result[2][$key]);
		}
		
		return $params;
	}

	/**
	 *
	 * @param unknown_type $transaction
	 */
	protected function getPaymentMethodWrapper(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapper($orderContext);
	}

	protected function getPaymentMethodWrapperFromPaymentMethod(Customweb_Payment_Authorization_IPaymentMethod $method){
		return Customweb_Saferpay_Method_PaymentMethodWrapperFactory::getWrapperFromPaymentMethod($method);
	}

	protected function parseServerResponse($response){
		if (trim($response) == 'OK') {
			return true;
		}
		elseif (substr(trim($response), 0, 1) == '<') {
			$parameters = $this->getXmlParameters($response);
			if ($parameters['RESULT'] == 0 && $this->transaction == $parameters['ID']) {
				return true;
			}
			else {
				throw new Exception(Customweb_I18n_Translation::__("Capturing not possible. Capturing result '!result'"), 
						array(
							'!result' => $parameters['RESULT'] 
						));
			}
		}
		else {
			throw new Exception(
					Customweb_I18n_Translation::__("Capturing not possible. Technical error. Server response '!response'", 
							array(
								'!response' => $response 
							)));
		}
	}

	/**
	 * This method is called for all redirections to specific URL.
	 * The user will get
	 * redirected to this URL when the shop system calls self::processFurther().
	 *
	 * @param string $state STATE_INITIAL | STATE_3D_SECURE
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 * @param string $url
	 */
	protected function redirect($state, Customweb_Saferpay_Authorization_Transaction $transaction, $url){
		if ($state != null) {
			$transaction->setTransactionState($state);
		}
		$transaction->setNextRedirectUrl($url);
		
		$response = new Customweb_Core_Http_Response();
		$response->setLocation($url);
		
		return $response;
	}

	public function getFailedUrl($transaction){
		$url = "";
		if ($transaction->isMoto()) {
			$url = $transaction->getTransactionContext()->getBackendFailedUrl();
		}
		else {
			$url = $transaction->getTransactionContext()->getFailedUrl();
		}
		return Customweb_Util_Url::appendParameters($url, $transaction->getTransactionContext()->getCustomParameters());
	}

	public function getSuccessUrl($transaction){
		$url = "";
		if ($transaction->isMoto()) {
			$url = $transaction->getTransactionContext()->getBackendSuccessUrl();
		}
		else {
			$url = $transaction->getTransactionContext()->getSuccessUrl();
		}
		return Customweb_Util_Url::appendParameters($url, $transaction->getTransactionContext()->getCustomParameters());
	}

	protected function setConfigurationAdapter($adapter){
		$this->configurationAdapter = $adapter;
	}

	public function getConfigurationAdapter(){
		return $this->configurationAdapter;
	}

	public function setIsMoto($flag){
		$this->isMoto = $flag;
	}

	protected function isAskForCvc($aliasTransaction, $orderContext, $paymentCustomerContext){
		return $this->getConfiguration()->getCardVerificationMode() == 'cvc' || $aliasTransaction === null || $aliasTransaction === 'new' ||
				 !$this->isCardVerificationPossible($aliasTransaction, $orderContext, $paymentCustomerContext, array());
	}

	/**
	 * Stores the alias with a hash of the shipping address the alias was created with.
	 * This is used for fraud prevention as an alias can only be resused without
	 * entering the cvc when the shipping address did not change.
	 *
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 */
	protected function setAliasAddress(Customweb_Saferpay_Authorization_Transaction $transaction){
		$cardrefId = $transaction->getCardRefId();
		$orderContext = $transaction->getTransactionContext()->getOrderContext();
		
		$aliasContext = array(
			$cardrefId => $this->getShippingAddressHash($orderContext) 
		);
		$transaction->getPaymentCustomerContext()->updateMap($aliasContext);
	}

	protected function getShippingAddressHash(Customweb_Payment_Authorization_IOrderContext $orderContext){
		$addressString = $orderContext->getShippingFirstName() . '|' . $orderContext->getShippingLastName() . '|' . $orderContext->getShippingStreet() .
				 '|' . $orderContext->getShippingCity() . '|' . $orderContext->getShippingCountryIsoCode() . '|' . $orderContext->getShippingState();
		return hash('sha256', $addressString);
	}

	/**
	 * Check if we can verify the card/customer.
	 * If no CVC was supplied
	 * then the shipping address must not have changed.
	 *
	 * @param unknown $aliasTransaction
	 * @param unknown $orderContext
	 * @param unknown $paymentCustomerContext
	 * @param unknown $parameters
	 * @return boolean
	 */
	protected function isCardVerificationPossible($aliasTransaction, $orderContext, $paymentCustomerContext, $parameters){
		$method = $this->getPaymentMethodWrapperFromPaymentMethod($aliasTransaction->getPaymentMethod());
		if ($this->getConfiguration()->getCardVerificationMode() == 'cvc' && $method instanceof Customweb_Saferpay_Method_CreditCardWrapper) {
			return !empty($parameters['sfpCardCvc']);
		}
		else {
			if (empty($parameters['sfpCardCvc'])) {
				$currentShipping = $this->getShippingAddressHash($orderContext);
				$customerContext = $paymentCustomerContext->getMap();
				$cardrefId = $aliasTransaction->getCardRefId();
				return isset($customerContext[$cardrefId]) && $customerContext[$cardrefId] == $currentShipping;
			}
		}
		return true;
	}
}