<?php

class Category
{
	const ROOT_CATEGORY_ID_MEY       = "2";

	const ROOT_CATEGORY_ID_MEY_STORY = "35570";

	const ROOT_CATEGORY_ID_MEY_B2B   = "36988";

	protected $category;

	protected $excludeList;

	protected $ids;

	public function __construct( $categoryId )
	{
		$this->category    = Mage::getModel( 'catalog/category' )->load( $categoryId );
		$this->excludeList = [ self::ROOT_CATEGORY_ID_MEY_B2B, self::ROOT_CATEGORY_ID_MEY_STORY ];
		$this->ids         = explode( "/", substr($this->category->getPath(), 1) );
	}

	public function getCategoryTree( )
	{
		return $this->buildCategoryTree( $this->category );
	}

	protected function buildCategoryTree( $category )
	{
		$tree = "";
		$pId  = $category->getParentId();

		if ( $pId )
		{
			$category = Mage::getModel( 'catalog/category' )->load( $pId );
			$tree     = $this->buildCategoryTree( $category );
		}

		if ( self::ROOT_CATEGORY_ID_MEY == $category->getId()
		     || self::ROOT_CATEGORY_ID_MEY_STORY == $category->getId()
		     || self::ROOT_CATEGORY_ID_MEY_B2B == $category->getId()
		)
		{
			return "";
		}

		return strlen( $tree ) > 0 ? $tree . " / " . $category->getName() : $category->getName();
	}

	public function isExcluded()
	{
		return count( array_intersect( $this->ids, $this->excludeList ) ) > 0;
	}

	public function getName()
	{
		return $this->category->getName();
	}

	public function isVisible()
	{
		return $this->category->getIncludeInMenu() != "0";
	}
}