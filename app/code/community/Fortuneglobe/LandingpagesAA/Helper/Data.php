<?php

class Fortuneglobe_LandingpagesAA_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Return product collection to be displayed by our list block
	 *
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	public function getProductCollection()
	{
		$url = explode( "?", "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" );
		$url = explode( "/filter/", $url[0] );
		$url = explode( "/", $url[0] );

		$search_term     = array_pop( $url );
		$search_category = array_pop( $url );

		$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
		$layer          = Mage::getSingleton( 'catalog/layer' );
		$layer->setCurrentCategory( $rootCategoryId )->setIsAnchor( 1 );

		Mage::app()->setCurrentStore( Mage::app()->getStore() );

		$productCollection = $layer->getProductCollection()
		                           ->addAttributeToSelect( '*' )
		                           ->addAttributeToFilter( 'type_id', array( 'eq' => "configurable" ) )
		                           ->addAttributeToFilter( 'seo_categories', array( 'like' => "%" . $search_term . "%" ) );

		$toolbar = Mage::getBlockSingleton( 'catalog/product_list' )->getToolbarBlock();
		$toolbar->setCollection( $productCollection );

		return $productCollection;
	}
}