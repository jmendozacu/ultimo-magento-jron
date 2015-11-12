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
 *
 * @category	Customweb
 * @package		Customweb_SaferpayCw
 * @version		1.3.251
 */

class Customweb_SaferpayCw_Helper_Data extends Mage_Core_Helper_Abstract
{
	private static $container = null;

	public function log($message, $level = null, $file = '', $forceLog = false) {
		if (Mage::getStoreConfig('saferpaycw/general/debug_log') == '1') {
			Mage::log($message, $level, $file, $forceLog);
		}
	}

	public function logException(Exception $e) {
		if (Mage::getStoreConfig('saferpaycw/general/debug_log') == '1') {
			Mage::logException($e);
		}
	}

	/**
	 * @return Customweb_DependencyInjection_Container_Default
	 */
	public function createContainer() {
		if (self::$container === null) {
			if (!function_exists('cw_class_loader')) {
				function cw_class_loader($className) {
					return Varien_Autoload::instance()->autoload($className);
				}
				Customweb_Core_Util_Class::registerClassLoader('cw_class_loader');
			}

			$packages = array(
			0 => 'Customweb_Saferpay',
 			1 => 'Customweb_Payment_Authorization',
 		);
			$packages[] = 'Customweb_SaferpayCw_Model_';
			$packages[] = 'Customweb_Mvc_Template_Php_Renderer';
			$packages[] = 'Customweb_Payment_SettingHandler';

			$provider = new Customweb_DependencyInjection_Bean_Provider_Editable(new Customweb_DependencyInjection_Bean_Provider_Annotation(
					$packages
			));
			$provider->addObject(Customweb_Core_Http_ContextRequest::getInstance())
				->addObject($this->getAssetResolver());
			self::$container = new Customweb_DependencyInjection_Container_Default($provider);
		}

		return self::$container;
	}

	public function getAssetResolver() {
		return new Customweb_Asset_Resolver_Composite(array(
			Mage::getModel('saferpaycw/asset_skinResolver'),
			Mage::getModel('saferpaycw/asset_jsResolver'),
			Mage::getModel('saferpaycw/asset_templateResolver'),
			new Customweb_Asset_Resolver_Simple(Mage::getBaseDir('media') . '/customweb/saferpaycw/assets/', Mage::getBaseUrl('media') . '/customweb/saferpaycw/assets/')
		));
	}

	protected function getAuthorizationAdapterFactory() {
		$container = $this->createContainer();
		$factory = $container->getBean('Customweb_Payment_Authorization_IAdapterFactory');

		if (!($factory instanceof Customweb_Payment_Authorization_IAdapterFactory)) {
			throw new Exception("The payment api has to provide a class which implements 'Customweb_Payment_Authorization_IAdapterFactory' as a bean.");
		}

		return $factory;
	}

	public function getAuthorizationAdapter($authorizationMethodName) {
		return $this->getAuthorizationAdapterFactory()->getAuthorizationAdapterByName($authorizationMethodName);
	}

	public function getAuthorizationAdapterByContext(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		return $this->getAuthorizationAdapterFactory()->getAuthorizationAdapterByContext($orderContext);
	}

	/**
	 * This function serialize the given object to store it in the database.
	 *
	 * @param Object $object
	 * @return String A base64 representation of the object
	 */
	public function serialize($object)
	{
		return base64_encode(serialize($object));
	}

	/**
	 * @param unknown $string
	 * @return Customweb_Payment_Authorization_ITransaction
	 */
	public function unserialize($string)
	{

		// Detect if it is base 64 decoded
		if (!strstr($string, ':')) {
			$string = base64_decode($string);
		}

		set_error_handler(array(
			$this,
			'unserializationErrorHandler'
		));
		try {
			$object = unserialize($string);
		} catch (Exception $e) {
			// Give a second try with UTF-8 Decoding (legacy code)
			$object = unserialize(utf8_decode($string));
		}
		restore_error_handler();
		return $object;
	}

	public function unserializationErrorHandler($errno, $errstr, $errfile, $errline)
	{
		throw new Exception($errstr);
	}

	public function loadTransactionByPayment($orderPaymentId)
	{
		$transaction = Mage::getModel('saferpaycw/transaction')->load($orderPaymentId, 'order_payment_id');
		if ($transaction !== null && $transaction->getId()) {
			return $transaction;
		}

		$order = Mage::getModel('sales/order_payment')->load($orderPaymentId)->getOrder();
		$transaction = $this->migrateTransaction($order);
		if ($transaction !== null && $transaction->getId()) {
			return $transaction;
		}

		throw new Exception('The transaction could not have been loaded.');
	}

	public function loadTransactionByOrder($orderId)
	{
		$transaction = Mage::getModel('saferpaycw/transaction')->load($orderId, 'order_id');
		if ($transaction !== null && $transaction->getId()) {
			return $transaction;
		}

		$order = Mage::getModel('sales/order')->load($orderId);
		$transaction = $this->migrateTransaction($order);
		if ($transaction !== null && $transaction->getId()) {
			return $transaction;
		}

		throw new Exception('The transaction could not have been loaded.');
	}

	public function loadTransaction($transactionId)
	{
		$transaction = Mage::getModel('saferpaycw/transaction')->load($transactionId);
		if ($transaction === null || !$transaction->getId()) {
			return null;
		}
		return $transaction;
	}

	public function loadTransactionByExternalId($transactionId)
	{
		$transaction = Mage::getModel('saferpaycw/transaction')->load($transactionId, 'transaction_external_id');
		if ($transaction === null || !$transaction->getId()) {
			return null;
		}
		return $transaction;
	}

	public function loadTransactionByPaymentId($paymentId)
	{
		$transaction = Mage::getModel('saferpaycw/transaction')->load($paymentId, 'payment_id');
		if ($transaction === null || !$transaction->getId()) {
			return null;
		}
		return $transaction;
	}

	protected function migrateTransaction(Mage_Sales_Model_Order $order)
	{
		if ($order !== null && $order->getPayment() !== false) {
			$additionalData = $order->getPayment()->getAdditionalData();
			if (!empty($additionalData)) {
				$transactionObject = $this->unserialize($additionalData);
				$transaction = Mage::getModel('saferpaycw/transaction');
				$transaction->setOrderId($order->getId());
				$transaction->setOrderPaymentId($order->getPayment()->getId());
				$transaction->setTransactionObject($transactionObject);

				$alias = Mage::getModel('saferpaycw/aliasdata')->load($order->getId(), 'order_id');
				if ($alias !== null && $alias->getAliasId()) {
					$transaction->setAliasActive(true);
				} else {
					$transaction->setAliasActive(false);
				}

				$transaction->save();

				return $transaction;
			}
		}
	}

	public function loadOrderByTransactionId($transactionId)
	{
		$transaction = Mage::getModel('sales/order_payment_transaction')->loadByTxnId($transactionId);
		return $transaction->getOrder();
	}

	/**
	 * Retrieves the stored customer payment context for the given customer or for the current
	 * customer if no customer id is given.
	 *
	 * @param string $customerId
	 * @return Customweb_Payment_Authorization_IPaymentCustomerContext
	 */
	public function getPaymentCustomerContext($customerId = null)
	{
		$id = ($customerId != null) ? $customerId : $this->getCurrentCustomerId();

		return Customweb_SaferpayCw_Model_PaymentCustomerContext::getByCustomerId($id);
	}

	private function getCurrentCustomerId()
	{
		return Mage::getSingleton('customer/session')->getCustomer()->getId();
	}

	public function getConfigurationValue($key)
	{
		$configAdapter = new Customweb_SaferpayCw_Model_ConfigurationAdapter();
		return $configAdapter->getConfigurationValue($key);
	}

	public function isAliasManagerActive()
	{
		return $this->getConfigurationValue('alias_manager') != 'inactive';
	}

	public function getTooltip($block, $message)
	{
		static $includeJavascript = true;
		static $idCount = 0;
		$idCount++;
		$html = "";
		$message = str_replace("'", "\'", $message);
		$toolTipId = "tooltip" . $idCount;
		if ($includeJavascript) {
			$includeJavascript = false;
			$html .= "<script language=\"javascript\" type=\"text/javascript\" >
					function showTooltip(div, desc)
					{
					 div.style.display = 'inline';
					 div.style.position = 'absolute';
					 div.style.width = '300px';
					 div.style.backgroundColor = '#EFFCF0';
					 div.style.border = 'solid 1px black';
					 div.style.padding = '10px';
					 div.innerHTML = '<div style=\"padding-left:10; padding-right:5 width: 300px;\">' + desc + '</div>';
					}

					function hideTooltip(div)
					{
					 div.style.display = 'none';
					}
					</script>";
		}
		$html .= "<img onMouseOut=\"hideTooltip(" . $toolTipId . ")\" onMouseOver=\"showTooltip(" . $toolTipId . ", '" . $message . "')\" src=\"" . $block->getSkinUrl('images/fam_help.gif') . "\" width=\"16\" height=\"16\" border=\"0\">
				<div style=\"display:none\" id=\"" . $toolTipId . "\"></div>";

		return $html;
	}

	public function getSuccessUrl($transaction)
	{
		$result = new StdClass;
		$result->url = Mage::getUrl('checkout/onepage/success', array('_secure' => true));
		Mage::dispatchEvent('customweb_success_redirection', array(
			'result' => $result,
			'transaction' => $transaction
		));
		return $result->url;
	}

	public function getFailUrl($transaction)
	{
		$frontentId =  'checkout/onepage/';

		// If the onestep checkout module is enabled redirect there          	   		  	 	 	
		if(Mage::helper('core')->isModuleEnabled('Idev_OneStepCheckout') && Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links')) {
			$frontentId =  'onestepcheckout';
		}

		// If the firecheckout module is enabled redirect there
		if(Mage::helper('core')->isModuleEnabled('TM_FireCheckout') && Mage::getStoreConfig('firecheckout/general/enabled')) {
			$frontentId =  'firecheckout';
		}

		// If the magestore onestep checkout module is enabled redirect there
		if(Mage::helper('core')->isModuleEnabled('Magestore_Onestepcheckout') && Mage::getStoreConfig('onestepcheckout/general/active')) {
			$frontentId =  'onestepcheckout';
		}

		$redirectionUrl = Customweb_Util_Url::appendParameters(
			Mage::getUrl($frontentId, array('_secure' => true)),
			array('loadFailed' => 'true')
		);

		$result = new StdClass;
		$result->url = $redirectionUrl;
		Mage::dispatchEvent('customweb_failure_redirection', array(
			'result' => $result,
			'transaction' => $transaction
		));

		return $result->url;
	}

	/**
	 * @param string $transactionId
	 * @return string
	 */
	public function waitForNotification($transaction)
	{
		if (Mage::getStoreConfig('saferpaycw/general/wait_for_success') != '1') {
			$transaction->getOrder()->getPayment()->getMethodInstance()->success($transaction, $_REQUEST);
			return $this->getSuccessUrl($transaction);
		}

		$transactionId = $transaction->getId();

		$maxTime = min(array(Customweb_Util_System::getMaxExecutionTime() - 4, 30));
		$startTime = microtime(true);
		while(true){
			if (microtime(true) - $startTime >= $maxTime) {
				break;
			}

			$transaction = Mage::getModel('saferpaycw/transaction')->load($transactionId);
			if ($transaction == null || !$transaction->getId() || $transaction->getTransactionObject() == null) {
				continue;
			}
			if ($transaction->getTransactionObject()->isAuthorized()) {
				$transaction->getOrder()->getPayment()->getMethodInstance()->success($transaction, $_REQUEST);
				return $this->getSuccessUrl($transaction);
			}
			if ($transaction->getTransactionObject()->isAuthorizationFailed()) {
				$errorMessages = $transaction->getTransactionObject()->getErrorMessages();
				$messageToDisplay = nl2br(end($errorMessages));
				reset($errorMessages);

				$transaction->getOrder()->getPayment()->getMethodInstance()->fail($transaction, $_REQUEST);
				return $this->getFailUrl($transaction);
			}

			sleep(1);
		}

		$transaction->getOrder()->getPayment()->getMethodInstance()->success($transaction, $_REQUEST);
		Mage::getSingleton('core/session')->addError($this->__('There has been a problem during the processing of your payment. Please contact the shop owner to make sure your order was placed successfully.'));
		return $this->getSuccessUrl($transaction);
	}

	public function getStatusStates($status)
	{
		$states = array();
		$collection = Mage::getResourceModel('sales/order_status_collection');
		$collection->joinStates();
		$collection->getSelect()->where('state_table.status=?', $status);
		foreach ($collection as $state) {
			$states[] = $state;
		}
		return $states;
	}
}
