<?php
class Fortuneglobe_CheckoutPopup_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout(false);
		$this->renderLayout();
	}
}