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

class Customweb_SaferpayCw_Model_Observer
{
	private $timeout = 0;

	public function initCart(Varien_Event_Observer $observer)
	{
		if (Mage::getStoreConfig('saferpaycw/general/cancel_existing_orders')) {
			$cart = $observer->getCart();
			$customer = Mage::getSingleton('customer/session')->getCustomer();

			$query = 'SELECT product_id FROM sales_flat_quote_item WHERE quote_id = ' . $cart->getQuote()->getId();
			$resource = Mage::getSingleton('core/resource');
			$conn = $resource->getConnection('core_read');
			$productIds = $conn->query($query)->fetchAll();

			$orders = Mage::getResourceModel('sales/order_collection')
				->addAttributeToSelect('*')
				->addAttributeToFilter('customer_id', $customer->getId())
				->addAttributeToFilter('status', Customweb_SaferpayCw_Model_Method::SAFERPAYCW_STATUS_PENDING)
				->load();

			if (count($orders) > 0 && count($productIds) > 0) {
				foreach ($productIds as $productId) {
					$product = Mage::getModel('catalog/product')->load($productId);
					if (!$product->isSalable()) {
						foreach ($orders as $order) {
							$order->cancel();

							$order->setIsActive(0);
							$order->addStatusToHistory(Customweb_SaferpayCw_Model_Method::SAFERPAYCW_STATUS_CANCELED, Mage::helper('SaferpayCw')->__('Order cancelled, because the customer was too long in the payment process of Saferpay.'));
							$order->save();
						}
						break;
					}
				}
			}
		}
	}

	public function placeOrder(Varien_Event_Observer $observer)
	{
		$order = $observer->getOrder();
		try {
			if (strpos($order->getPayment()->getMethodInstance()->getCode(), 'saferpaycw') === 0) {
				Mage::register('cw_order_id', $order->getId());

				if (Mage::registry('cw_is_moto') == null) {
					$transaction = $order->getPayment()->getMethodInstance()->createTransaction($order);
					if ($order->getId()) {
						$order->getPayment()->getMethodInstance()->updateTransaction($transaction, $order);
					}
					Mage::register('cstrxid', $transaction->getTransactionId());
				}
			}
		} catch (Exception $e) {}
	}

	public function saveOrder(Varien_Event_Observer $observer)
	{
		$order = $observer->getOrder();
		try {
			if (strpos($order->getPayment()->getMethodInstance()->getCode(), 'saferpaycw') === 0) {
				$sessionTransactionId = Mage::registry('cstrxid');
				if (Mage::registry('cw_is_moto') == null && !empty($sessionTransactionId)) {
					$transaction = Mage::helper('SaferpayCw')->loadTransaction($sessionTransactionId);
					if ($transaction != null && $transaction->getId()) {
						$order->getPayment()->getMethodInstance()->updateTransaction($transaction, $order);
					}
				}
			}
		} catch (Exception $e) {}
	}

	public function capturePayment(Varien_Event_Observer $observer)
	{

	}

	public function cancelOrder(Varien_Event_Observer $observer)
	{
		$order = $observer->getOrder();
		if (strpos($order->getPayment()->getMethodInstance()->getCode(), 'saferpaycw') === 0) {
			$order->addStatusHistoryComment(Mage::helper('SaferpayCw')->__('Transaction cancelled successfully'));
		}
	}

	public function invoiceView(Varien_Event_Observer $observer)
	{
		$block = $observer->getBlock();
		$invoice = $observer->getInvoice();

		if (strpos($invoice->getOrder()->getPayment()->getMethodInstance()->getCode(), 'saferpaycw') === 0) {
			$transaction = Mage::helper('SaferpayCw')->loadTransactionByOrder($invoice->getOrder()->getId());

			if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/edit')
				&& $invoice->canCapture()
				&& $transaction->getTransactionObject()->isCapturePossible()
				&& $transaction->getTransactionObject()->isPartialCapturePossible()) {
				$block->addButton('edit', array(
					'label'     => Mage::helper('sales')->__('Edit'),
					'class'     => 'go',
					'onclick'   => 'setLocation(\''.$block->getUrl('*/editsaferpaycw/index', array('invoice_id'=>$invoice->getId())).'\')'
				));
			}
		}
	}

	public function loadCustomerQuoteBefore(Varien_Event_Observer $observer)
	{
		if (Mage::registry('saferpaycw_external_checkout_login') === true) {
			$customerQuote = Mage::getModel('sales/quote')
				->setStoreId(Mage::app()->getStore()->getId())
				->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

			if ($customerQuote->getId() && Mage::getSingleton('checkout/session')->getQuoteId() && Mage::getSingleton('checkout/session')->getQuoteId() != $customerQuote->getId()) {
				$customerQuote->delete();
			}
		}
	}

	public function collectExternalCheckoutWidgets(Varien_Event_Observer $observer)
	{
		$event = $observer->getEvent();
		$widgets = $event->getWidgets();
		$widgets = array_merge($widgets, Mage::getModel('saferpaycw/externalCheckoutWidgets')->getWidgets());
		$observer->getEvent()->setWidgets($widgets);
	}

	public function registerTranslationResolver(Varien_Event_Observer $observer)
	{
		Customweb_I18n_Translation::getInstance()->addResolver(new Customweb_SaferpayCw_Model_TranslationResolver());
	}
}
