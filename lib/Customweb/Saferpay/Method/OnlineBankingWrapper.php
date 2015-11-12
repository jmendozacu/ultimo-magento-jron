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


class Customweb_Saferpay_Method_OnlineBankingWrapper extends Customweb_Saferpay_Method_PaymentMethodWrapper{
	
	const FORM_KEY_CARD_BLZ = 'sfpCardBLZ';
	const FORM_KEY_CARD_KONTO = 'sfpCardKonto';
	
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext,
			$aliasTransaction,
			$failedTransaction,
			$isMoto = false){
		$owner = $this->getOrderContext()->getBillingFirstName() . ' ' . $this->getOrderContext()->getBillingLastName();
		
		$elements = array();
		$elements[] = Customweb_Form_ElementFactory::getAccountOwnerNameElement(self::FORM_KEY_OWNER_NAME, $owner);
		$elements[] = Customweb_Form_ElementFactory::getAccountNumberElement(self::FORM_KEY_CARD_KONTO);
		$elements[] = Customweb_Form_ElementFactory::getBankCodeElement(self::FORM_KEY_CARD_BLZ);
		
		return $elements;
	}
	
	public function getHiddenFormFields(){
		return array();
	}
	
	function is3DSecureSupported(){
		return false;
	}
	
	public function getAdditionalPaymentPageParameters(){
		return array();
	}
	
	public function getAdditionalCaptureParameters(){
		return array();
	}
	
	public function isEciMeaningless(){
		return true;
	}
}