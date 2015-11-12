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

//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/Http/Request.php';
//require_once 'Customweb/Http/Response.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Util/Html.php';

final class Customweb_Saferpay_Util {

	private function __construct() {
		// prevent any instantiation of this class
	}

	public static function getCleanLanguageCode($lang) {
		$supportedLanguages = array('de_DE','en_US','fr_FR','da_DK',
				'cs_CZ','es_ES','hr_HR','it_IT','hu_HU','nl_NL',
				'no_NO','pl_PL','pt_PT','ru_RU','ro_RO','sk_SK',
				'sl_SI','fi_FI','sv_SE','tr_TR','el_GR','ja_JP'
		);
		return substr(Customweb_Payment_Util::getCleanLanguageCode($lang,$supportedLanguages), 0, 2);
	}

	/**
	* Simply appends the parameters in the array as get parameters to the url
	* @param string $url An arbitrary url
	* @param array $parameters The array contains the parameters to append
	* @deprecated use instead Customweb_Util_Url::appendParameters
	*          	   		  	 	 	
	*/
	public static function addParametersToUrl($url, array $parameters)
	{
		return Customweb_Util_Url::appendParameters($url, $parameters);
	}
	
	/**
	 * Returns the client ip address even behind a proxy.
	 * 
	 * @return unknown
	 */
	public static function getClientIpAddress(){
		$ip = $_SERVER['REMOTE_ADDR'];
		if(isset($_SERVER["HTTP_X_REAL_IP"])){
			$ip = $_SERVER["HTTP_X_REAL_IP"];
		}
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		return $ip;
	}


	public static function sendFormData($url, $formData){
		$handler = new Customweb_Http_Response();
		$request = new Customweb_Http_Request($url);

		$request->setResponseHandler($handler)->setMethod("POST")
			->setBody($formData)
			->send();
		if($handler->getStatusCode() == 302){
			return $handler->getHeaders();
		}
		elseif($handler->getStatusCode() != 200)
		{
			throw new Exception('Saferpay Server response is: '
					. $handler->getStatusCode() . ' ' . $handler->getStatusMessage());
		}

		$response = trim($handler->getBody());

		return array('body' => $response);
	}

	/**
	 * Sends a request to one of the Saferpay service urls. We do some
	 * basic response validation here already.
	 *         	   		  	 	 	
	 * @param string $url
	 */
	public static function sendRequest($url, $method = "GET", $postParams = null)
	{
		$handler = new Customweb_Http_Response();
		$request = new Customweb_Http_Request($url);

		
		$request->setResponseHandler($handler)->setMethod($method)
			->setBody($postParams)
			->send();
		
		if($handler->getStatusCode() != 200)
		{
			throw new Exception('Saferpay Server response is: '
					. $handler->getStatusCode() . ' ' . $handler->getStatusMessage());
		}
		$response = trim($handler->getBody());

		$pos = strpos($response, ':');
		if($pos === false)
		{
			return $response;
		}

		$result = substr($response, 0, $pos);
		$data = substr($response,$pos+1);
		if($result != 'OK' && $result != 'ERROR')
		{
			return $response;
		}

		if($result == 'ERROR')
		{
			throw new Exception(Customweb_I18n_Translation::__(
					"An error occured: Saferpay response is: '!data'", array('!data' => trim($data))));
		}

		return $data;
	}

	/**
	 * Replaces parts of the IBAN string to mask the number.
	 *
	 * @param string $iban
	 * @return string
	 */
	public static function maskIban($iban){
		$start = substr($iban, 0, 4);
		$end = substr($iban, -4, 4);
		return str_pad($start, strlen($iban) - 8, 'X') . $end;
	}
	
	public static function getUserErrorMessage($originalMessage)
	{
		if (strpos($originalMessage, 'No CardType found for CustomerId') === 0
			|| $originalMessage == 'Invalid PAN.'
			|| $originalMessage == 'unknown card type') {
			return Customweb_I18n_Translation::__('Invalid credit card data: The given card number is not valid.');
		} elseif ($originalMessage == 'wrong CVC'
			|| $originalMessage == 'authorization failed (5): Authorisation refused') {
			return Customweb_I18n_Translation::__('Invalid credit card data: Please check your credit card data.');
		} elseif ($originalMessage == 'Account Data expired.') {
			return Customweb_I18n_Translation::__('Invalid credit card data: The expiration date has to be in the future.');
		} elseif (strpos($originalMessage, 'authorization failed (12): Invalid transaction') === 0) {
			return Customweb_I18n_Translation::__('The payment failed because either the card number, the CVC or the expiry date was invalid.');
		}

		return $originalMessage;
	}
	
	
	public static function removeWrongEscaptedChars($string) {
		return Customweb_Util_Html::convertSpecialCharacterToEntities(str_replace('Ä', 'Ae', $string));
	}
	
}