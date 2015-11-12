<?php

/**
 *  * You are allowed to use this API in your web application.
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
//require_once 'Customweb/Payment/Authorization/Method/Sepa/Mandate.php';
//require_once 'Customweb/Form/Control/Html.php';
//require_once 'Customweb/Saferpay/Method/OnlineBankingWrapper.php';
//require_once 'Customweb/Form/Element.php';
//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Form/Control/HiddenInput.php';
//require_once 'Customweb/Form/HiddenElement.php';

class Customweb_Saferpay_Method_DirectDebitWrapper extends Customweb_Saferpay_Method_OnlineBankingWrapper {
	private static $MANDATE_ID_SCHEMA = '{year}{month}{day}-{random}';
	
	// Same implementation as the OnlineBankingWrapper for the moment.
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $isMoto = false){
		$owner = $this->getOrderContext()->getBillingFirstName() . ' ' . $this->getOrderContext()->getBillingLastName();
		
		$elements = array();
		if ($aliasTransaction !== null && $aliasTransaction != 'new') {
			$ownerControl = new Customweb_Form_Control_HiddenInput(self::FORM_KEY_OWNER_NAME, $owner);
			$ownerElement = new Customweb_Form_HiddenElement($ownerControl);
			$elements[] = $ownerElement;
			$params = $aliasTransaction->getAuthorizationParameters();
			$panControl = new Customweb_Form_Control_Html(self::FORM_KEY_CARD_KONTO, $params['PAN']);
			$panElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Bank account number.'), $panControl);
			$panElement->setRequired(false);
			$elements[] = $panElement;
		}
		else {
			$elements[] = Customweb_Form_ElementFactory::getIbanNumberElement('sfpIBAN');
		}
		
		return array_merge($this->getMandateElements($orderContext), $elements);
	}

	private function getMandateElements(Customweb_Payment_Authorization_IOrderContext $orderContext){
		$customer = $orderContext->getBillingAddress()->getFirstName() . ' ' . $orderContext->getBillingAddress()->getLastName();
		$mandateId = Customweb_Payment_Authorization_Method_Sepa_Mandate::generateMandateId(self::$MANDATE_ID_SCHEMA);
		
		$mandateIdControl = new Customweb_Form_Control_HiddenInput('MANDATEID', $mandateId);
		$mandateIdElement = new Customweb_Form_HiddenElement($mandateIdControl);
		
		$mandateTextControl = new Customweb_Form_Control_Html('mandate_text', $this->getMandateText($mandateId, $customer));
		$mandateTextElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Mandate text'), $mandateTextControl);
		$mandateTextElement->setRequired(false);
		
		return array(
			$mandateIdElement,
			$mandateTextElement 
		);
	}

	private function getMandateText($mandateReference, $customer){
		$creditorId = $this->getPaymentMethodConfigurationValue('creditor_identifier');
		$merchantName = $this->getPaymentMethodConfigurationValue('mandate_name');
		$date = date('Y-m-d H:i');
		
		//@formatter:off
		return Customweb_I18n_Translation::__("Gläubiger-Identifikationsnummer: !CREDITOR_ID<br/>
Mandatsreferenz: !MANDATE_REFERENCE<br/>
Datum: !DATE<br/>
<br/>
Ich !CUSTOMER ermächtige !MERCHANT_NAME widerruflich, lt. meinen vorherigen Eingaben den oben genannten Betrag und die von mir zukünftig zu entrichtenden Zahlungen bei Fälligkeit von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von auf mein Konto gezogenen Lastschriften einzulösen.<br/>
<br/>
Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.<br/>
Hiermit bestätige ich das SEPA-Lastschriftmandat", array(
								'!CREDITOR_ID' => $creditorId,
								'!CUSTOMER' => $customer,
								'!MANDATE_REFERENCE' => $mandateReference,
								'!DATE' => $date,
								'!MERCHANT_NAME' => $merchantName,
							))
		;
		//@formatter:on
	}
}
