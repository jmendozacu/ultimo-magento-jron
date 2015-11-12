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

class Customweb_SaferpayCw_Block_Adminhtml_BackendForm_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('saferpaycwbackendform_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle('Saferpay');
	}

	protected function _beforeToHtml()
	{
		$container = Mage::helper('SaferpayCw')->createContainer();
		if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
			/* @var $adapter Customweb_Payment_BackendOperation_Form_IAdapter */
			$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
			foreach ($adapter->getForms() as $form) {
				$form = new Customweb_Payment_BackendOperation_Form($form);
				if ($form->isProcessable()) {
					$form->setTargetUrl($this->getUrl('*/*/save', array('tab' => $form->getMachineName(), '_current' => true)))
						->setRequestMethod(Customweb_IForm::REQUEST_METHOD_POST);
				}
				$this->addTab($form->getMachineName(), array(
					'label'     => $form->getTitle(),
					'title'     => $form->getTitle(),
					'content'   => $this->getLayout()->createBlock('saferpaycw/adminhtml_backendForm_form')->setForm($form)->toHtml(),
				));
			}
		}
		
		$this->_updateActiveTab();
		
		return parent::_beforeToHtml();
	}
	
	protected function _updateActiveTab()
	{
		$tabId = $this->getRequest()->getParam('tab');
		if( $tabId ) {
			$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
			if($tabId) {
				$this->setActiveTab($tabId);
			}
		}
	}
}