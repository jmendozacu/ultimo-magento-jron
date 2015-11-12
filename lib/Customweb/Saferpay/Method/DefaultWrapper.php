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

//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Saferpay/Method/PaymentMethodWrapper.php';


class Customweb_Saferpay_Method_DefaultWrapper extends Customweb_Saferpay_Method_PaymentMethodWrapper{
	
	const FORM_KEY_CARD_NUMBER = 'sfpCardNumber';
	const FORM_KEY_CARD_CVC = 'sfpCardCvc';
	const FORM_KEY_CARD_EXPIRY_MONTH = 'sfpCardExpiryMonth';
	const FORM_KEY_CARD_EXPIRY_YEAR = 'sfpCardExpiryYear';
	
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,
			$isMoto = false){
		
		$elements = array();
		return $elements;
	}
	
	public function getHiddenFormFields(){
		return array();
	}
	
	public function is3DSecureSupported(){
		return true;
	}
	
	public function getAdditionalPaymentPageParameters(){
		return array();
	}
	
	public function getAdditionalCaptureParameters(){
		return array();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Customweb_Saferpay_Method_PaymentMethodWrapper::getAuthorizationParameters()
	 */
	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $parameters){
		// We send always the expiration date instead of updating them in the database of the PSP.
		$parameters['EXP'] = $transaction->getCardExpiryDate();
		return $parameters;
	}
	
	public function isEciMeaningless() {
		return true;
	}



}
