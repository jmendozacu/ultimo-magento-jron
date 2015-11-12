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

class Customweb_SaferpayCw_Block_Form extends Mage_Payment_Block_Form
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('customweb/saferpaycw/form.phtml');
	}
	
	public function getContent()
	{
		
		$arguments = null;
		return Customweb_Licensing_SaferpayCw_License::run('qh3o7i11ub6ijtpr', $this, $arguments);
	}

	public function call_i1kbl6gv6rrkcio7() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}
	
	private function getPaymentForm($code, $method)
	{
		return '
			<div id="payment_description_' .  $code . '" class="cw_payment_description">
				' . ($method->getPaymentMethodConfigurationValue('show_image') ? '
					<img src="' . $this->getSkinUrl('images/saferpaycw/' . $method->getPaymentMethodName() . '.png') . '" /><br/>
				' : '') . $this->getMethodDescription() . '
			</div>
			' . $this->getAliasSelect() . '
			<input type="hidden" id="' . $code . '_authorization_method" value="' . $this->getAuthorizationMethod() . '" />
			<div id="payment_form_fields_' . $code . '">
				' . $this->getFormFields() . '
			</div>
		
			<script type="text/javascript">
				' . $this->getFormJavaScript() . '
			</script>';
	}

	public function getFormFields()
	{
		Mage::getSingleton('checkout/session')->setAliasId('new');
		return $this->getMethod()
			->generateVisibleFormFields(array(
				'alias_id' => 'new'
			));
	}

	public function getFormJavaScript()
	{
		return $this->getMethod()
			->generateFormJavaScript(array(
				'alias_id' => 'new'
			));
	}

	public function getProcessUrl()
	{
		return Mage::getUrl('SaferpayCw/process/process', array('_secure' => true));
	}

	public function getJavascriptUrl()
	{
		return Mage::getUrl('SaferpayCw/process/ajax', array('_secure' => true));
	}

	public function getHiddenFieldsUrl()
	{
		return Mage::getUrl('SaferpayCw/process/getHiddenFields', array('_secure' => true));
	}

	public function getVisibleFieldsUrl()
	{
		return Mage::getUrl('SaferpayCw/process/getVisibleFields', array('_secure' => true));
	}

	public function getAuthorizationMethod()
	{
		$adapter = $this->getMethod()->getAuthorizationAdapter(false)->getAuthorizationMethodName();
		$adapter = strtolower($adapter);
		$adapter = str_replace('authorization', '', $adapter);
		return $adapter;
	}

	public function getMethodDescription()
	{
		return $this->getMethod()
			->getPaymentMethodConfigurationValue('description', Mage::app()->getLocale()
				->getLocaleCode());
	}

	public function getAliasSelect()
	{
		$payment = $this->getMethod();
		$result = "";

		if ($payment->getPaymentMethodConfigurationValue('alias_manager') == 'active') {
			$aliasList = $payment->loadAliasForCustomer();

			if (count($aliasList)) {
				$alias = array(
					'new' => Mage::helper('SaferpayCw')->__('New card')
				);

				foreach ($aliasList as $key => $value) {
					$alias[$key] = $value;
				}

				// The onchange even listener is added here, because there seems to be a bug with prototype's observe
				// on select fields.          	   		  	 	 	
				$selectControl = new Customweb_SaferpayCw_Model_Select("alias_select", $alias, 'new', "cwpm_" . $payment->getCode() . ".loadAliasData(this)");
				$aliasElement = new Customweb_Form_Element(Mage::helper('SaferpayCw')->__("Saved cards:"), $selectControl, Mage::helper('SaferpayCw')->__("You may choose one of the cards you paid before on this site."));
				$aliasElement->setRequired(false);

				$renderer = new Customweb_SaferpayCw_Model_FormRenderer();
				$renderer->setNameSpacePrefix($payment->getCode());
				$result = $renderer->renderElements(array(
					0 => $aliasElement
				));
			}
		}

		return $result;
	}

}
