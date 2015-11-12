<?php

class Sandfox_RemovePaypalExpressReviewStep_Model_Observer
{
	public function controllerActionPredispatchPaypalExpressReview(Varien_Event_Observer $observer)
	{
		Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*/placeOrder'));
	}

	public function controllerActionPredispatchPaypalExpressPlaceOrder(Varien_Event_Observer $observer)
	{
		$requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
		Mage::app()->getRequest()->setPost('agreement', array_combine($requiredAgreements, $requiredAgreements));
	}
}
