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

class Customweb_SaferpayCw_ConfigsaferpaycwController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Return an instance of the helper.
	 *
	 * @return Customweb_SaferpayCw_Helper_Data
	 */
	protected function getHelper()
	{
		return Mage::helper('SaferpayCw');
	}

	public function indexAction()
	{
		$this->updateStoreHierarchy();
		
        $this->loadLayout();

        $this->_title('Saferpay');

        $this->_setActiveMenu('system/saferpaycw');

        $this->renderLayout();
	}
	
	public function saveAction()
	{
		$session = Mage::getSingleton('adminhtml/session');
		
		$this->updateStoreHierarchy();
		
		$form = $this->getCurrentForm();
		
		$params = $this->getRequest()->getParams();
		if (!isset($params['button'])) {
			$session->addError(Mage::helper('adminhtml')->__('No button returned.'));
		}
		$pressedButton = null;
		foreach ($form->getButtons() as $button) {
			if ($button->getMachineName() == $params['button']) {
				$pressedButton = $button;
			}
		}
		
		if ($pressedButton === null) {
			$session->addError(Mage::helper('adminhtml')->__('Could not find pressed button.'));
		}
		
		$storeHierarchy = Mage::getModel('saferpaycw/configurationAdapter')->getStoreHierarchy();
		foreach ($form->getElements() as $element) {
			if ($storeHierarchy != null && $element->isGlobalScope()) {
				$form->removeElement($element);
			}
		}
		
		$this->getFormAdapter()->processForm($form, $pressedButton, $params);
		
		$session->addSuccess(Mage::helper('adminhtml')->__('The configuration has been saved.'));
				
		$this->_redirect('*/*/index', array(
			'_current' => true
		));
	}
	
	private function updateStoreHierarchy()
	{
		$websiteCode = $this->getRequest()->getParam('website');
		$storeCode   = $this->getRequest()->getParam('store');
		
		$storeHierarchy = null;
		$storeId = Mage::app()->getDefaultStoreView()->getId();
		if ($websiteCode != null || $storeCode != null) {
			$storeHierarchy = array();
			if ($websiteCode != null) {
				$website = Mage::getModel('core/website')->load($websiteCode);
				$storeHierarchy['website_'.$website->getId()] = $website->getName();
				$storeId = $website->getDefaultStore()->getId();
			}
			if ($storeCode != null) {
				$store = Mage::getModel('core/store')->load($storeCode);
				$storeHierarchy['store_'.$store->getId()] = $store->getName();
				$storeId = $store->getId();
			}
		}
		Customweb_SaferpayCw_Model_ConfigurationAdapter::setStoreId($storeId);
		Customweb_SaferpayCw_Model_ConfigurationAdapter::setStoreHierarchy($storeHierarchy);
	}
	
	/**
	 * @return Customweb_Payment_BackendOperation_Form_IAdapter
	 */
	private function getFormAdapter()
	{
		$container = Mage::helper('SaferpayCw')->createContainer();
		return $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
	}
	
	private function getCurrentForm()
	{
		$machineName = $this->getRequest()->getParam('tab');
		
		foreach ($this->getFormAdapter()->getForms() as $form) {
			if ($form->getMachineName() == $machineName) {
				return $form;
			}
		}
		
		throw new Exception(Customweb_Core_String::_("Could not find form with form name '@name'.")->format(array('@name' => $machineName)));
	}
}
