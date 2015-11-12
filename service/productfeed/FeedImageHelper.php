<?php

class FeedImageHelper
{
	/**
	 * @param      $product
	 * @param      $imagesToCreate
	 *    [
	 *    "size0" => ["height" => 2000,"width" => 1400,"type" => "image" | "small_image" | "thumbnail", "name" => "size0"]
	 *    ]
	 *
	 * @return array
	 *  [
	 *    "size0" => "http://some.magento.url"
	 *  ]
	 * @throws      Exception
	 */
	public function createImages( $product, array $imagesToCreate )
	{
		$urls = [ ];

		foreach ( $imagesToCreate as $image )
		{
			$url = Mage::helper( 'catalog/image' )
			           ->init( $product, $image['type'], null )
			           ->resize( $image['width'], $image['height'] )
			           ->__toString();

			$key = $image['name'];

			if ( $this->isPlaceholder( $url ) )
			{
				throw new Exception( sprintf("image not found (%s, %s)",$image['type'], $url) );
			}

			$urls[ $key ] = $url;
		}

		return $urls;
	}

	private function isPlaceholder( $url )
	{
		return strpos( $url, "placeholder" ) !== false;
	}
}