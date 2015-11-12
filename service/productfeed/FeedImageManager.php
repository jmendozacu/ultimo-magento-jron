<?php

final class FeedImageManager
{
	private $feedImagePath;

	/**
	 * @param string $path
	 */
	public function __construct( $path = "" )
	{
		$this->feedImagePath = $path;
	}

	/**
	 * @param OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $product
	 * @param string                                                           $appendDir
	 *
	 * @return array
	 */
	public function getImageUrls( OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $product, $appendDir = "" )
	{
		$urls   = [ ];
		$path   = $this->getProductPath( $product );
		$images = scandir( $path );

		if ( !empty($appendDir) )
		{
			$appendDir = $this->ensurePathEndsWithDirectorySeparator( $appendDir );
		}

		foreach ( $images as $image )
		{
			if ( is_file( sprintf( "%s/%s", $path, $image ) ) )
			{
				$filename = pathinfo( $image, PATHINFO_FILENAME );
				$urls[ $filename ] = sprintf("%s%s/%s", $appendDir, $this->getStyleDir($product), $image);
			}
		}

		return $urls;
	}

	/**
	 * @param  string $path
	 *
	 * @return string
	 */
	private function ensurePathEndsWithDirectorySeparator( $path )
	{
		if ( substr( $path, -1 ) != DIRECTORY_SEPARATOR )
		{
			$path .= DIRECTORY_SEPARATOR;
		}

		return $path;
	}

	/**
	 * @param OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $productId
	 * @param array                                                            $imageMap
	 *
	 * @return int
	 */
	public function storeImages( OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $product, $imageMap )
	{
		$bytes = 0;

		$path = $this->getProductPath( $product );
		$this->createDirectoryIfNotExists( $path );

		foreach ( $imageMap as $feedField => $url )
		{
			$ext  = pathinfo( $url, PATHINFO_EXTENSION );
			$data = file_get_contents( $url );
			$bytes += file_put_contents( sprintf( "%s/%s.%s", $path, $feedField, $ext ), $data );
		}

		return $bytes;
	}

	/**
	 * @param string $dir
	 */
	private function createDirectoryIfNotExists( $dir )
	{
		if ( !is_dir( $dir ) )
		{
			mkdir( $dir );
		}
	}

	/**
	 * @param OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $productId
	 *
	 * @return string
	 */
	private function getProductPath( OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $product )
	{
		return sprintf( "%s%s", $this->feedImagePath, $this->getStyleDir( $product ) );
	}

	/**
	 * @param OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $product
	 *
	 * @return string
	 */
	private function getStyleDir( OrganicInternet_SimpleConfigurableProducts_Model_Catalog_Product $product )
	{
		return sprintf( "%s_%s", $product->getNumber(), $product->getColorCode() );
	}

}
