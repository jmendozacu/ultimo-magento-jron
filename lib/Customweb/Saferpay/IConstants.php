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

/**
 * This interface provides some constants for the Saferpay service.
 *
 * @author Severin Klingler
 *         	   		  	 	 	
 */
interface Customweb_Saferpay_IConstants {

	const URL_CREATE_PAY_INIT = 'CreatePayInit.asp';
	const URL_VERIFY_PAY_CONFRIM  = 'verifypayconfirm.asp';
	const URL_VERIFY_ENROLLMENT	= 'VerifyEnrollment.asp';
	const URL_EXECUTE = 'Execute.asp';
	const URL_PAY_COMPLETE = 'PayComplete.asp';


	const TEST_ACCOUNT_ID = '99867-94913159';
	const TEST_ACCOUNT_PASSWORD = 'XAjc3Kna';
	const SAFERPAYTEST_PROVIDER_ID = "90";


	const SEND_ADDRESS_MODE_NONE = 'none';
	const SEND_ADDRESS_MODE_DELIVERY = 'delivery';
	const SEND_ADDRESS_MODE_BILLING = 'billing';

	/**
	 * Liability status
	 */
	const ECI_NO_LIABILITY_SHIFT = 0;
	const ECI_LIABILITY_SHIFT_CUSTOMER_ENROLLED = 1;
	const ECI_LIABILITY_SHIFT_CUSTOMER_NOT_ENROLLED = 2;

	/**
	 * Technical stuff
	 */
	const MAX_ALLOWED_URL_LENGTH = 2000;
}