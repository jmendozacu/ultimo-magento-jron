<?php

class Fortuneglobe_LandingpagesAA_Helper_Filter extends Catalin_SEO_Helper_Data
{
	public function getFilterUrl( array $filters, $noFilters = false, array $q = array() )
	{
		if ( Mage::registry( 'is_landingpage' ) )
		{
			$query = array(
				'isLayerAjax'                                                  => null,
				// this needs to be removed because of ajax request
				Mage::getBlockSingleton( 'page/html_pager' )->getPageVarName() => null // exclude current page from urls
			);
			$query = array_merge( $query, $q );

			$suffix = Mage::getStoreConfig( 'catalog/seo/category_url_suffix' );
			$params = array(
				'_current'     => true,
				'_use_rewrite' => true,
				'_query'       => $query,
				'_escape'      => true,
			);

			$urlbase = Mage::getUrl( '*/*/*', $params );
			//Delete Filter Suffix
			$url = explode( "/filter/", $urlbase );

			$urlPath = '';
			if ( !$noFilters )
			{
				// Add filters
				$layerParams = $this->getCurrentLayerParams( $filters );
				foreach ( $layerParams as $key => $value )
				{
					// Encode and replace escaped delimiter with the delimiter itself
					$value = str_replace(
						urlencode( self::MULTIPLE_FILTERS_DELIMITER ), self::MULTIPLE_FILTERS_DELIMITER,
						urlencode( $value )
					);
					$urlPath .= "/{$key}/{$value}";
				}
			}

			// Skip adding routing suffix for links with no filters
			if ( empty($urlPath) )
			{
				return $url[0];
			}
			else
			{
				$urlPathwosuf = explode( '?', urldecode( $urlPath ) );
			}

			$urlParts = explode( '?', $urlbase );

			$urlParts[0] = substr( $urlParts[0], 0, strlen( $urlParts[0] ) - strlen( $suffix ) );
			// Add the suffix to the url - fixes when comming from non suffixed pages
			// It should always be the last bits in the URL
			$urlParts[0] .= $this->getRoutingSuffix();

			if ( !empty($urlParts[1]) )
			{
				if ( empty($url[1]) )
				{
					$urlbase = $url[0] . '?' . $urlParts[1];
				}
				else
				{
					$urlbase = $url[0] . '/filter' . $urlPathwosuf[0] . '?' . $urlParts[1];
				}
			}
			else
			{
				$urlbase = $url[0] . '/filter' . $urlPathwosuf[0] . $suffix;
			}

			return $urlbase;
		}
		else
		{
			return parent::getFilterUrl( $filters, $noFilters, $q );
		}
	}
}