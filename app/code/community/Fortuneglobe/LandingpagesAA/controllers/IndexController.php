<?php

class Fortuneglobe_LandingpagesAA_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		Mage::dispatchEvent(
			'catalog_controller_category_init_before',
			array(
				'controller_action' => $this
			)
		);
		//$current_category = Mage::getModel('catalog/category');
		//$current_category->
		//$current_category->setProductCollection(Mage::helper('landingpagesaa')->getProductCollection());
		Mage::register( 'is_landingpage', true );
		$this->loadLayout();
		$update = $this->getLayout()->getUpdate();
		$update->addHandle( 'default' );
		$update->addHandle( 'catalog_category_layered' );
		$this->addActionLayoutHandles();

		//$category = Mage::getSingleton( 'catalog/category' )->_initCatagory();
		//$update->addHandle($category->getLayoutUpdateHandle());
		//$update->addHandle('CATEGORY_' . $category->getId());

		$this->_initLayoutMessages( 'catalog/session' );
		$this->_initLayoutMessages( 'checkout/session' );

		// Custom Catalin SEO Controller
		if ( $this->getRequest()->isAjax() )
		{
			$update->addHandle( 'catalog_category_layered_ajax_layer' );
		}
		$this->loadLayoutUpdates();
		$rootCategory = 2;
		try
		{
			Mage::dispatchEvent(
				'catalog_controller_category_init_after',
				array(
					'category'          => $rootCategory,
					'controller_action' => $this
				)
			);
		}
		catch ( Mage_Core_Exception $e )
		{
			Mage::logException( $e );

			return;
		}

		// return json formatted response for ajax
		if ( $this->getRequest()->isAjax() )
		{
			$listing = $this->getLayout()->getBlock( 'product_list' )->toHtml();
			//print_r($listing); die();
			$layer = $this->getLayout()->getBlock( 'catalog.leftnav' )->toHtml();

			// Fix urls that contain '___SID=U'
			$urlModel = Mage::getSingleton( 'core/url' );
			$listing  = $urlModel->sessionUrlVar( $listing );
			$layer    = $urlModel->sessionUrlVar( $layer );

			$response = array(
				'listing' => $listing,
				'layer'   => $layer
			);

			$this->getResponse()->setHeader( 'Content-Type', 'application/json', true );
			$this->getResponse()->setBody( json_encode( $response ) );
		}
		else
		{
			$this->renderLayout();
		}
		//$this->renderLayout();
	}
}