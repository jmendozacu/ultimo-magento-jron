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
//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';

class Customweb_Saferpay_Authorization_Transaction extends Customweb_Payment_Authorization_DefaultTransaction {
	const STATE_INITIAL = 'initial';
	const STATE_ALIAS_EXISTS = "alias";
	const STATE_3D_SECURE = '3d-secure';
	private $check3DSecure;
	private $state;
	private $mpiSessionId;
	private $ownerName;
	private $cardExpiryDate;
	private $cardExpiryMonth;
	private $cardExpiryYear;
	private $nextRedirectUrl;
	private $truncatedPAN;
	private $cardRefId = null;
	private $key = null;
	private $motoAuthorizationMethodName = null;
	private $effectivePaymentMethodMachineName = null;
	private $paymentInformation;

	public function __construct(Customweb_Payment_Authorization_ITransactionContext $transactionContext){
		parent::__construct($transactionContext);
		
		$this->state = self::STATE_INITIAL;
		$this->setState3DSecure(self::STATE_3D_SECURE_FAILED);
		$this->key = Customweb_Util_Rand::getRandomString(32, '');
	}

	public function getKey(){
		return $this->key;
	}

	public function resetKey(){
		$this->key = Customweb_Util_Rand::getRandomString(32, '');
		return $this;
	}

	public function setCheck3DSecure($flag){
		$this->check3DSecure = $flag;
	}

	public function getCheck3DSecure($flag){
		return $this->check3DSecure;
	}

	public function setTransactionState($state){
		$this->state = $state;
	}

	public function getTransactionState(){
		return $this->state;
	}

	public function setMpiSessionId($id){
		$this->mpiSessionId = $id;
	}

	public function getMpiSessionId(){
		return $this->mpiSessionId;
	}

	public function setOwnerName($name){
		$this->ownerName = $name;
	}

	public function getOwnerName(){
		return $this->ownerName;
	}

	public function getCardExpiryDate(){
		return $this->cardExpiryDate;
	}

	public function getCardExpiryMonth(){
		return $this->cardExpiryMonth;
	}

	public function getCardExpiryYear(){
		return $this->cardExpiryYear;
	}

	public function setCardExpiryDate($month, $year){
		$this->cardExpiryMonth = $month;
		$this->cardExpiryYear = $year;
		$m = substr($month, -2);
		$y = substr($year, -2);
		return $this->cardExpiryDate = $m . $y;
	}

	public function setNextRedirectUrl($url){
		$this->nextRedirectUrl = $url;
	}

	public function getNextRedirectUrl(){
		return $this->nextRedirectUrl;
	}

	public function setTruncatedPAN($truncatedPan){
		$this->truncatedPAN = $truncatedPan;
	}

	public function getTruncatedPAN(){
		return $this->truncatedPAN;
	}

	public function isUseExistingAlias(){
		$alias = $this->getTransactionContext()->getAlias();
		return $alias !== null && $alias != 'new';
	}

	public function isCaptureClosable(){
		// We support only one capture per transaction, hence the first capture 
		// closes the transaction.          	   		  	 	 	
		return false;
	}

	public function getTransactionSpecificLabels(){
		$labels = array();
		
		$params = $this->getAuthorizationParameters();
		
		if (isset($params['PAN']) && !isset($params['IBAN'])) {
			$labels['cardnumber'] = array(
				'label' => Customweb_I18n_Translation::__('Card Number'),
				'value' => $params['PAN'] 
			);
		}
		if(isset($params['IBAN'])) {
			$labels['iban'] = array(
				'label' => Customweb_I18n_Translation::__('IBAN'),
				'value' => $params['IBAN']
			);
		}
		
		if ($this->cardExpiryMonth !== null) {
			$labels['card_expiry'] = array(
				'label' => Customweb_I18n_Translation::__('Card Expiry Date'),
				'value' => $this->getCardExpiryMonth() . '/' . $this->getCardExpiryYear() 
			);
		}
		
		if (isset($params['PROVIDERNAME'])) {
			$labels['card_type'] = array(
				'label' => Customweb_I18n_Translation::__('Card Type'),
				'value' => $params['PROVIDERNAME'] 
			);
		}
		
		if ($this->isMoto()) {
			$labels['moto'] = array(
				'label' => Customweb_I18n_Translation::__('Mail Order / Telephone Order (MoTo)'),
				'value' => Customweb_I18n_Translation::__('Yes') 
			);
		}
		
		$cardRefId = $this->getCardRefId();
		if (!empty($cardRefId)) {
			$labels['card_ref_id'] = array(
				'label' => Customweb_I18n_Translation::__('Card Reference ID'),
				'value' => $cardRefId 
			);
		}
		
		$labels['authorization_method'] = array(
			'label' => Customweb_I18n_Translation::__('Authorization Method'),
			'value' => $this->getAuthorizationMethod() 
		);
		
		if ($this->getEffectivePaymentMethodMachineName() !== null) {
			$labels['effective_method'] = array(
				'label' => Customweb_I18n_Translation::__('Effective Payment Method Name'),
				'value' => $this->getEffectivePaymentMethodMachineName(),
				'description' => Customweb_I18n_Translation::__(
						'In some cases the customer switch the payment method or select a more specific one. This label shows the effective one.') 
			);
		}
		
		return $labels;
	}

	public function setAuthorizationParameters(array $parameters){
		return parent::setAuthorizationParameters(array_change_key_case($parameters, CASE_UPPER));
	}

	public function addAuthorizationParameters(array $parameters){
		$existing = $this->getAuthorizationParameters();
		foreach ($parameters as $key => $value) {
			$existing[$key] = $value;
		}
		$this->setAuthorizationParameters($existing);
	}

	public function getFailedUrlWithCustomParameters(){
		$baseUrl = "";
		if ($this->isMoto()) {
			$baseUrl = $this->getTransactionContext()->getBackendFailedUrl();
		}
		else {
			$baseUrl = $this->getTransactionContext()->getFailedUrl();
		}
		return Customweb_Util_Url::appendParameters($baseUrl, $this->getTransactionContext()->getCustomParameters());
	}

	/**
	 * This methods saves the redirecrect url for performance reasons
	 * so we don't have tho call the Saferpay service
	 * multiple times.
	 */
	public function setRedirectUrl($redirectUrl){
		// We store the URL only in a temporary storage, we do not need to persist it.
		if (!isset($GLOBALS['saferpay_redirection_urls'])) {
			$GLOBALS['saferpay_redirection_urls'] = array();
		}
		
		$GLOBALS['saferpay_redirection_urls'][$this->getExternalTransactionId()] = $redirectUrl;
		
		return $this;
	}

	public function getRedirectUrl(){
		if (isset($GLOBALS['saferpay_redirection_urls'][$this->getExternalTransactionId()])) {
			return $GLOBALS['saferpay_redirection_urls'][$this->getExternalTransactionId()];
		}
		else {
			return null;
		}
	}

	public function isMoto(){
		return $this->getAuthorizationMethod() == Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME;
	}

	public function getCardRefId(){
		return $this->cardRefId;
	}

	public function setCardRefId($cardRefId){
		$this->cardRefId = $cardRefId;
	}

	public function encrypt($string){
		if (empty($string)) {
			throw new Exception("An empty string provided.");
		}
		return base64_encode($this->getCipher()->encrypt($string));
	}

	public function decrypt($string){
		if (empty($string)) {
			throw new Exception("An empty string provided.");
		}
		return $this->getCipher()->decrypt(base64_decode($string));
	}

	/**
	 *
	 * @return Crypt_AES
	 */
	private function getCipher(){
		//require_once 'Crypt/AES.php';
		$cipher = new Crypt_AES(CRYPT_AES_MODE_CTR);
		$key = $this->getKey();
		if (empty($key)) {
			throw new Exception("No key was set.");
		}
		$cipher->setKey($key);
		return $cipher;
	}

	public function getTransactionData(){
		$parameters = $this->getAuthorizationParameters();
		$parameters['cardRefId'] = $this->getCardRefId();
		return $parameters;
	}

	public function getEffectivePaymentMethodMachineName(){
		return $this->effectivePaymentMethodMachineName;
	}

	public function setEffectivePaymentMethodMachineName($effectivePaymentMethodMachineName){
		$this->effectivePaymentMethodMachineName = $effectivePaymentMethodMachineName;
		return $this;
	}

	public function getMotoAuthorizationMethodName(){
		return $this->motoAuthorizationMethodName;
	}

	public function setMotoAuthorizationMethodName($motoAuthorizationMethodName){
		$this->motoAuthorizationMethodName = $motoAuthorizationMethodName;
		return $this;
	}

	public function getPaymentInformation(){
		if (empty($this->paymentInformation)) {
			return null;
		}
		return $this->formatPaymentInformation($this->paymentInformation);
	}

	public function setPaymentInformation($information){
		$this->paymentInformation = $information;
	}

	private function formatPaymentInformation(){
		$formatted = '';
		foreach ($this->paymentInformation as $entry) {
			$formatted .= '<b>' . $entry['label']. ':</b> ' . $entry['value'] . '<br/>';
		}
		return $formatted;
	}
}