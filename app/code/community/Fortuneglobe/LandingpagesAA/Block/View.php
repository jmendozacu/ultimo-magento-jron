<?php

class Fortuneglobe_LandingpagesAA_Block_View extends Mage_Core_Block_Template
{

	public function __construct()
	{
		parent::__construct();
		$collection    = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToSelect( '*' );
		$url           = explode( "/", $_SERVER['REQUEST_URI'] );
		$controllerkey = array_keys( $url, "alle" );
		if ( count( $controllerkey ) == 1 && isset($url[ $controllerkey[0] + 1 ]) )
		{
			$param = $url[ $controllerkey[0] + 1 ];
		}
		else
		{
			$param = "all";
		}
		//$collection->addAttributeToFilter( 'style_seo', array( 'eq' => $param ));
		$this->setCollection( $collection );
	}

	public function getPagerHtml()
	{
		return $this->getChildHtml( 'pager' );
	}

	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		//Get Pager
		$pager = $this->getLayout()->createBlock( 'page/html_pager', 'custom.pager' );
		//$pager->setAvailableLimit( array( 5 => 5, 10 => 10, 20 => 20, 'all' => 'all' ) );
		$pager->setCollection( $this->getCollection() );
		$this->setChild( 'pager', $pager );

		//Get Art per Site ??

		//Get Filter Navigation ??

		//Last Step

		$this->getCollection()->load();

		return $this;
	}
}