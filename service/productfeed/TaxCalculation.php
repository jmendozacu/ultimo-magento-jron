<?php

class TaxCalculation
{

	private $request;

	private $calculation;

	public function __construct( $magento, $storeId = "de" )
	{
		$store             = Mage::app()->getStore( $storeId );
		$this->calculation = Mage::getModel( 'tax/calculation' );
		$this->request     = $this->calculation->getRateRequest( null, null, null, $store );
	}

	public function getTaxRate( $product )
	{
		return sprintf(
			"%.2f", $this->calculation->getRate( $this->request->setProductClassId( $product->getTaxClassId() ) )
		);
	}

	public function getTaxAmount( $priceWithTax, $taxRate )
	{
		$dividedValue = $taxRate + 100;

		return round( $priceWithTax * $taxRate / $dividedValue, 2 );
	}

	public function getPriceWithoutTax( $priceWithTax, $taxRate )
	{
		$dividedValue = 1 + ($taxRate / 100);

		return round( $priceWithTax / $dividedValue, 2 );
	}
}