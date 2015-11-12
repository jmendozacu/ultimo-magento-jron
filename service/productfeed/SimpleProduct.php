<?php

final class SimpleProduct
{

	const DEPARTMENT_ID_WOMEN = "18436";

	private $product;

	private $stockItem;

	public function __construct( $magento, $product )
	{
		$this->product   = $product;
		$this->stockItem = $magento::getModel( 'cataloginventory/stock_item' )->loadByProduct( $product->getId() );
	}

	public function getId()
	{
		return $this->product->getId();
	}

	public function getColorAsText()
	{
		return $this->product->getAttributeText( 'color' );
	}

	public function getFilterColorAsText()
	{
		return $this->product->getAttributeText( 'filter_color' );
	}

	public function getNumber()
	{
		return $this->product->getNumber();
	}

	public function getEan()
	{
		return $this->product->getEan();
	}

	public function getName()
	{
		return $this->product->getName();
	}

	public function getStyle()
	{
		return sprintf( "%s-%s", $this->product->getNumber(), $this->product->getColorCode() );
	}

	public function isAvailable()
	{
		return $this->stockItem->getIsInStock();
	}

	public function getStock()
	{
		return round( $this->stockItem->getQty() );
	}

	public function getDepartment()
	{
		return $this->product->getAttributeText( 'department' );
	}

	public function getSizeLabel()
	{
		if ( false == $sizeLabel = $this->product->getAttributeText( 'primary_size' ) )
		{ // BH
			$sizeLabel = sprintf(
				"%s%s",
				$this->product->getAttributeText( 'third_size' ),
				$this->product->getAttributeText( 'secondary_size' )
			);
		}

		return $sizeLabel;
	}

	public function getShippingTime()
	{
		return $this->product->getDeliveryTime();
	}

	public function getShortDescription()
	{
		return $this->product->getShortDescription();
	}

	public function getLongDescription()
	{
		return $this->product->getDescription();
	}

	public function getDetails()
	{
		return $this->product->getSellingpoints();
	}

	public function getCareInstructionAsHtml()
	{
		$html               = "";
		$care               = [ ];
		$careInstructionIds = $this->product->getCareInstructions();
		$careInstructionIds = explode( ",", $careInstructionIds );
		$model              = Mage::getModel( 'catalog/product' );
		$attr               = $model->getResource()->getAttribute( "care_instructions" );

		foreach ( $careInstructionIds as $id )
		{
			$care[] = $attr->getSource()->getOptionText( $id );
		}

		if ( count( $care ) )
		{
			$html = sprintf(
				"<ul><li>%s</li></ul>", implode( "</li><li>", $care )
			);
		}

		return $html;
	}

	public function getMaterial()
	{
		$materials = [ ];

		if ( $material = $this->product->getMaterial1() )
		{
			$materials[] = $material;
		}

		if ( $material = $this->product->getMaterial2() )
		{
			$materials[] = $material;
		}

		return count( $materials ) ? implode( ",", $materials ) : "";
	}

	public function isCurrentlyInSale()
	{
		if ( null === $this->product->getSpecialPrice() )
		{
			return false;
		}

		return new DateTime( $this->product->getSpecialToDate() ) >= new DateTime();
	}

	public function getPrice()
	{
		return $this->product->getPrice();
	}

	public function getActualPrice()
	{
		return ($this->isCurrentlyInSale( $this->product )) ? $this->product->getSpecialPrice()
			: $this->product->getPrice();
	}

	public function isNew()
	{
		$today = new DateTime();

		return new DateTime( $this->product->getNewsToDate() ) >= $today
		       && new DateTime( $this->product->getNewsFromDate() ) <= $today;
	}

	public function getShippingPrice()
	{
		return ($this->getActualPrice() < 50) ? '3.95' : '0.00';
	}

	public function getMagentoProduct()
	{
		return $this->product;
	}

	public function getBrand()
	{
		return $this->product->getBrand();
	}

	public function getUrl()
	{

		return Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_LINK ) . $this->product->getUrlSeoKey();
	}

	public function getImages( $magento )
	{
		$productImages = [ ];
		$basePath      = Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA );
		$baseLength    = strlen( $basePath );
		$product       = $magento::getModel( 'catalog/product' )->load( $this->product->getId() );
		$images        = $product->getMediaGalleryImages();
		foreach ( $images as $image )
		{
			$productImages[] = substr( $image->getUrl(), $baseLength );
		}

		return $productImages;
	}

	public function getGender()
	{
		return $this->product->getDepartment() != self::DEPARTMENT_ID_WOMEN ? "male" : "female";
	}
}