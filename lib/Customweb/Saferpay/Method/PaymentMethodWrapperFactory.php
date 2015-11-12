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

//require_once 'Customweb/Saferpay/Method/CreditCardWrapper.php';
//require_once 'Customweb/Saferpay/Method/DirectDebitWrapper.php';
//require_once 'Customweb/Saferpay/Method/BillpayOpenInvoice.php';
//require_once 'Customweb/Saferpay/Method/BillpayDirectDebits.php';
//require_once 'Customweb/Saferpay/Method/OnlineBankingWrapper.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/Method/DefaultWrapper.php';
//require_once 'Customweb/Saferpay/Method/Masterpass.php';


final class Customweb_Saferpay_Method_PaymentMethodWrapperFactory {

	public static function getWrapper(Customweb_Payment_Authorization_IOrderContext $orderContext)
	{
		$wrapper = self::getWrapperFromPaymentMethod($orderContext->getPaymentMethod());
		$wrapper->setOrderContext($orderContext);
		return $wrapper;
	}
	
	public static function getWrapperFromPaymentMethod(Customweb_Payment_Authorization_IPaymentMethod $method)
	{
		$paymentMethodName = $method->getPaymentMethodName();
		
		switch(strtolower($paymentMethodName)){
			case 'mastercard':
			case 'visa':
			case 'americanexpress':
			case 'diners':
			case 'jcb':
			case 'saferpaytestcard':
			case 'laser':
			case 'lasercard':
			case 'bonuscard':
			case 'maestro':
			case 'myone':
			case 'creditcard':
				return new Customweb_Saferpay_Method_CreditCardWrapper($method);
			
			case 'postfinanceefinance':
			case 'postfinancecard':
			case 'paypal':
			case 'ideal':
			case 'clickandbuy':
			case 'eps':
				return new Customweb_Saferpay_Method_DefaultWrapper($method);

			case 'openinvoice':
				return new Customweb_Saferpay_Method_BillpayOpenInvoice($method);
				
			case 'mpass':
			case 'giropay':
			case 'directebanking':
				return new Customweb_Saferpay_Method_OnlineBankingWrapper($method);
	
			case 'directdebits':
				if($method->getPaymentMethodConfigurationValue('provider') == 'billpay'){
					return new Customweb_Saferpay_Method_BillpayDirectDebits($method);
				}
				else{
					return new Customweb_Saferpay_Method_DirectDebitWrapper($method);
				}
				break;
				
			case 'masterpass':
				return new Customweb_Saferpay_Method_Masterpass($method);
	
			default:
				throw new Exception(Customweb_I18n_Translation::__(
				"No method wrapper found for payment method '!method'.",
				array('!method' => $paymentMethodName)
				));
		}
	}
}