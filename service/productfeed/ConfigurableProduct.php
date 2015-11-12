<?php

class ConfigurableProduct
{

	protected $product;

	public function __construct( $magentoProduct )
	{
		$this->product = $magentoProduct;
	}

	public function getCategoryIds()
	{
		return $this->product->getCategoryIds();
	}

	public function getName()
	{
		return $this->product->getName();
	}

	public function getId()
	{
		return $this->product->getId();
	}

	public function getChilden( $magento )
	{
		$conf = Mage::getModel( 'catalog/product_type_configurable' )->setProduct( $this->product );

		return $conf->getUsedProductCollection()
		            ->addAttributeToSelect( "*" )
		            ->addFilterByRequiredOptions()
		            ->addAttributeToFilter(
			            'status', array( 'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED )
		            );
	}
}