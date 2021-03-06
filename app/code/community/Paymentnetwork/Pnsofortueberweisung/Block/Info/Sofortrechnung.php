<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Paymentnetwork
 * @package	Paymentnetwork_Sofortueberweisung
 * @copyright  Copyright (c) 2008 [m]zentrale GbR, 2010 Payment Network AG
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @version	$Id: Pnsofortueberweisung.php 2654 2011-12-09 11:08:14Z poser $
 */
require_once Mage::getModuleDir('', 'Paymentnetwork_Pnsofortueberweisung').'/Helper/library/sofortLib.php';
class Paymentnetwork_Pnsofortueberweisung_Block_Info_Sofortrechnung extends Mage_Payment_Block_Info
{
	private $sofort;
	/**
	 * Init default template for block
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('pnsofortueberweisung/info/sofortrechnung.phtml');
	  	
	}
	
	 /**
	 * Retrieve info model
	 *
	 * @return Mage_Sofortueberweisung_Model_Info
	 */
	public function getInfo()
	{
		$info = $this->getData('info');
		
		if (!($info instanceof Mage_Payment_Model_Info)) {
			Mage::throwException($this->__('Can not retrieve payment info model object.'));
		}
		return $info;
	}
	
	 /**
	 * Retrieve payment method model
	 *
	 * @return Mage_Payment_Model_Method_Abstract
	 */
	public function getMethod()
	{
		return $this->getInfo()->getMethodInstance();
	}
	
	public function toPdf()
	{
		//$pdf = $this->getMethod()->getInfoInstance()->getAdditionalInformation('sofortrechnung_invoice_url');
		//return Zend_Pdf::parse(file_get_contents($pdf));
		$this->setTemplate('pnsofortueberweisung/info/pdf/sofortueberweisung.phtml');
		return $this->toHtml();
	}
	
	public function _toHtml() {
		return parent::_toHtml();
	}
	
	public function getAmount() {
		return $this->sofort->getAmount();
	}

	public function getHolder() {
		return $this->sofort->getInvoiceBankHolder();
	}

	public function getAccountNumber() {
		return $this->sofort->getInvoiceBankAccountNumber();
	}
	
	public function getBankCode() {
		return $this->sofort->getInvoiceBankCode();
	}

	public function getBic() {
		return '';
	}
	
	public function getIban() {
		return '';
	}
	
	public function getReason1() {
		return $this->sofort->getInvoiceReason(1);
	}

	public function getReason2() {
		return $this->sofort->getInvoiceReason(2);
	}
	
	public function loadData() {
		$tid = $this->getMethod()->getInfoInstance()->getAdditionalInformation('sofort_transaction');
		if($tid) {
			$this->sofort = new SofortLib_TransactionData(Mage::getStoreConfig('payment/sofort/configkey'));
			$this->sofort->setTransaction($tid)->sendRequest();
			return !($this->sofort->isError()) && $this->sofort->getStatusReason() == 'not_credited_yet';
		}
		return false;
	}
   
}