<?php

class Fortuneglobe_LandingpagesAA_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
	/**
	 * Helper function to register the current router at the front controller.
	 *
	 * @param Varien_Event_Observer $observer The event observer for the controller_front_init_routers event
	 *
	 * @event controller_front_init_routers
	 */
	public function addSelectedProductRouter( $observer )
	{
		$front               = $observer->getEvent()->getFront();
		$AnalyticsUrlsRouter = new Fortuneglobe_LandingpagesAA_Controller_Router();
		$front->addRouter( 'landingpagesaa', $AnalyticsUrlsRouter );
	}

	/**
	 * Rewritten function of the standard controller. Tries to match the pathinfo on url parameters.
	 *
	 * @see Mage_Core_Controller_Varien_Router_Standard::match()
	 *
	 * @param Zend_Controller_Request_Http $request The http request object that needs to be mapped on Action
	 *                                              Controllers.
	 */
	public function match( Zend_Controller_Request_Http $request )
	{
		if ( !Mage::isInstalled() )
		{
			Mage::app()->getFrontController()->getResponse()
			    ->setRedirect( Mage::getUrl( 'install' ) )
			    ->sendResponse();
			exit;
		}

		$identifier = trim( $request->getPathInfo(), '/' );

		//URL Search after habbeda
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		//$identifier = trim($request->getPathInfo(), '/');

		if ( (strpos( $url, "/alle/" ) <= 0) && (strpos( $url, "/all/" ) <= 0) )
		{
			return false;
		}

		// if successfully gained url parameters, use them and dispatch ActionController action
		$request->setRouteName( 'landingpagesaa' )
		        ->setModuleName( 'landingpagesaa' )
		        ->setControllerName( 'index' )
		        ->setActionName( 'index' );
		//->setParam('test', "test");

		$urlpara = explode( "/filter/", $url );
		if ( !empty($urlpara[1]) )
		{

			$urlparas = explode( "?", $urlpara[1] );
			// Parse url params
			$params      = explode( '/', trim( $urlparas[0], '/' ) );
			$layerParams = array();
			$total       = count( $params );
			for ( $i = 0; $i < $total - 1; $i++ )
			{
				if ( isset($params[ $i + 1 ]) )
				{
					$layerParams[ $params[ $i ] ] = urldecode( $params[ $i + 1 ] );
					++$i;
				}
			}
			// Add post params to parsed ones from url
			// Usefull to easily override params
			$layerParams += $request->getPost();
			// Add params to request
			$request->setParams( $layerParams );

			// Save params in registry - used later to generate links
			Mage::register( 'layer_params', $layerParams );
		}

		$request->setRequestUri( $url );
		$request->setAlias(
			Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
			$identifier
		);

		return true;
	}

	function getFrontNameByRoute( $routeName )
	{
		var_dump( $routeName );
		die();
	}
}