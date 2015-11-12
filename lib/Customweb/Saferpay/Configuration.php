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

//require_once 'Customweb/Saferpay/IConstants.php';

class Customweb_Saferpay_Configuration {
	
	/**
	 *         	   		  	 	 	
	 * @var Customweb_Payment_IConfigurationAdapter
	 */
	private $configurationAdapter = null;

	public function __construct(Customweb_Payment_IConfigurationAdapter $configurationAdapter) {
		$this->configurationAdapter = $configurationAdapter;
	}


	/**
	 * Returns whether the gateway is in test mode or in live mode.
	 *         	   		  	 	 	
	 * @return boolean True if the system is in test mode. Else return false.
	 */
	public function isTestMode()
	{
		// We check against the 'live' value, because in some system the configuration
		// page is not stored and hence it contains nothing.
		// If the account id is set, the operation mode will be also set!
		return $this->getConfigurationValue('operation_mode') != 'live';
	}

	/**
	 * This method returns the account id for the interaction.
	 *
	 * @return String account ID
	 */
	public final function getAccountId() {

		if($this->isTestMode()) {
			$accountId = $this->getTestAccountId();
		}
		else{
			$accountId = $this->getConfigurationValue('account_id');
		}

		$accountId = trim($accountId);
		if (empty($accountId)) {
			throw new Exception(Customweb_I18n_Translation::__("The given account id is empty. Please check the Saferpay settings."));
		}
		
		return $accountId;
	}

	public function getTestAccountId()
	{
		return Customweb_Saferpay_IConstants::TEST_ACCOUNT_ID;
	}

	public function getAccountPassword()
	{
		if($this->isTestMode()){
			$password = $this->getTestAccountPassword();
		}
		else{
			$password = $this->getConfigurationValue('password');
		}
		
		return trim($password);
	}

	public function getMotoAccountId(){
		if($this->isTestMode()) {
			$accountId = $this->getTestAccountId();
		}
		else{
			$accountId = $this->getConfigurationValue('moto_account_id');
		}
		
		$accountId = trim($accountId);
		if (empty($accountId)) {
			throw new Exception(Customweb_I18n_Translation::__("The given MoTo account id is empty. Please check the Saferpay settings."));
		}
		
		return $accountId;
	}

	public function getMotoAccountPassword(){
		if($this->isTestMode()){
			$password = $this->getTestAccountPassword();
		}
		else{
			$password = $this->getConfigurationValue('moto_password');
		}
		
		return $password;
	}

	public function getTestAccountPassword()
	{
		return Customweb_Saferpay_IConstants::TEST_ACCOUNT_PASSWORD;
	}

	public function isUseMotoAccountForAlias(){
		$result = false;
		
		return $result;
	}

	public function getCardVerificationMode(){
		$result = 'cvc';
		
		return $result;
	}

	public function getShopOwnerEmailAddress(){
		return $this->getConfigurationValue('shop_email');
	}

	public function getOrderIdSchema(){
		return $this->getConfigurationValue('order_id_schema');
	}

	public function getPaymentPageConfiguration(){
		return $this->getConfigurationValue('payment_page_configuration_name');
	}
	
	public function getCssUrl() {
		return trim($this->getConfigurationValue('css_url'));
	}

	/**
	 * Should transaction be marked as uncertain, which has no liability shift?
	 *
	 * @return boolean
	 */
	public function isMarkLiabilityShiftTransactions(){
		return strtolower($this->getConfigurationValue('liability_shift')) == 'uncertain';
	}

	public function getPaymentDescription($language){
		return $this->getConfigurationValue('description', $language);
	}

	protected function getConfigurationValue($key, $language = null) {
		return $this->configurationAdapter->getConfigurationValue($key, $language);
	}

	/**
	 *
	 * @return Customweb_Payment_IConfigurationAdapter
	 */
	public function getConfigurationAdapter() {
		return $this->configurationAdapter;
	}
}
