<?php

class mageSOAPAdvanced
{

	const STAGING_CATEGORY_ID_BLUESIGN = 22;

	const STAGING_CATEGORY_ID_PRESALES = 21;

	const STAGING_CATEGORY_ID_GIFT     = 20;

	const STAGING_CATEGORY_ID_ACTION   = 19;

	const STAGING_CATEGORY_ID_LOOKS    = 17;

	const ROOT_MEYSHOP                 = 2;

	const ROOT_MEYSTORY                = 35570;

	const ROOT_MEYB2B                  = 36988;

	function __construct()
	{
		define('MAGENTO', realpath( dirname( __FILE__ ) ));
		require_once MAGENTO . '/../app/Mage.php';
		Mage::app();
	}

	public function reindexAll()
	{
		$indexers = Mage::getSingleton( 'index/indexer' )->getProcessesCollection();
		foreach ( $indexers as $indexer )
		{
			$indexer->reindexEverything();
		}

		return true;
	}

	public function reindexStock()
	{
		$indexer = $process = Mage::getSingleton( 'index/indexer' )->getProcessByCode( 'cataloginventory_stock' );
		$indexer->reindexEverything();

		return true;
	}

	public function cloaksimpleproducts()
	{
		$storelist   = array_keys( Mage::app()->getStores() );
		$storelist[] = Mage_Core_Model_App::ADMIN_STORE_ID;
		foreach ( $storelist as $storeid )
		{
			Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $storeid ) );
			$products = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToSelect( 'visibility' )
			                ->addAttributeToFilter( 'type_id', array( 'in' => array( 'simple' ) ) )
			                ->addAttributeToFilter( 'visibility', array( 'eq' => 4 ) );
			foreach ( $products as $product )
			{
				$product->setStoreId( $storeid );
				$product->setVisibility( "1" );
				$product->getResource()->saveAttribute( $product, 'visibility' );
			}
		}
		return "Simple Products are now hidden again.";
	}

	public function indexModeManuell()
	{
		$processCollection = Mage::getSingleton( 'index/indexer' )->getProcessesCollection();
		$processCollection->walk( 'setMode', array( Mage_Index_Model_Process::MODE_MANUAL ) );
		$processCollection->walk( 'save' );

		return true;
	}

	public function indexModeLive()
	{
		$processCollection = Mage::getSingleton( 'index/indexer' )->getProcessesCollection();
		$processCollection->walk( 'setMode', array( Mage_Index_Model_Process::MODE_REAL_TIME ) );
		$processCollection->walk( 'save' );

		return true;
	}

	public function getNumberList()
	{
		$products = Mage::getModel( 'catalog/product' )->getCollection();
		// For Bugix
		//$products = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToFilter('type_id', array('in' => array('configurable')));
		$products->addAttributeToSelect( 'number' )->addAttributeToSelect( 'color_code' );
		$numbers = [ ];
		foreach ( $products as $product )
		{
			$number      = $product->getNumber();
			$colorcode   = $product->getColorCode();
			$numbercolor = $number . "-" . $colorcode;
			if ( $number && !in_array( $numbercolor, $numbers ) )
			{
				$numbers[] = $numbercolor;
			}
		}

		return $numbers;
	}

	public function deletespecials()
	{
		$products = Mage::getModel( 'catalog/product' )
		                ->getCollection()
		                ->addAttributeToSelect( array( 'special_price', 'special_to_date', 'status' ) )
		                ->addAttributeToFilter( 'special_price', array( 'notnull' => true ) )
		                ->addAttributeToFilter( 'status', array( 'eq' => 1 ) );

		$numbers = array();
		$now     = new DateTime();
		$time    = $now->getTimestamp();
		Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( Mage_Core_Model_App::ADMIN_STORE_ID ) );
		foreach ( $products as $product )
		{
			if ( $time <= strtotime( $product->getSpecialToDate() ) && $product->getSpecialToDate() != "" )
			{
				/* Stock Set Variante
				$stockItem = Mage::getModel('cataloginventory/stock_item')->load($product->getId(), 'product_id');
				$stockItem->setData('is_in_stock', 0);
				$stockItem->setTypeId( $product->getTypeId() );
				$stockItem->setQty(0);
				$stockItem->save();
				*/

				/* Deaktivierungsvariante */
				$product->setStatus( 2 );
				$product->getResource()->saveAttribute( $product, 'status' );
				$numbers[] = $product->getStatus();
				/* Ende */
			}
			else
			{
			}
		}

		return $numbers;
	}

	public function getUrlList()
	{
		$storelist = array_keys( Mage::app()->getStores() );
		$urllist   = [ ];

		foreach( $storelist as $storeid )
		{
			$urllist[ $storeid ] = [];
		}

		// Get all Crawlable Urls for all Stores
		foreach ( $storelist as $storeid )
		{

			Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $storeid ) );

			//Get all ProductUrls
			$products = Mage::getModel( 'catalog/product' )
			                ->getCollection()
			                ->addAttributeToFilter( 'type_id', array( 'in' => array( 'simple' ) ) )
			                ->addAttributeToSelect( 'url_seo_key' );

			foreach ( $products as $product )
			{
				$tmp = Mage::getBaseUrl() . $product->getUrlSeoKey();
				if ( $tmp && !in_array( $tmp, $urllist[ $storeid ] ) )
				{
					$urllist[ $storeid ][] = $tmp;
				}
			}

			// Get all Category-Paths
			$categories = Mage::getModel( 'catalog/category' )
			                  ->getCollection()
			                  ->addAttributeToSelect( 'url_path' )
			                  ->addFieldToFilter( 'is_active', array( 'eq' => '1' ) )
			                  ->addFieldToFilter( 'children_count', array( 'lteq' => '0' ) );

			foreach ( $categories as $category )
			{
				$tmp = Mage::getBaseUrl() . $category->getUrlPath();
				if ( $tmp && !in_array( $tmp, $urllist[ $storeid ] ) )
				{
					$urllist[ $storeid ][] = $tmp;
				}
			}
		}

		return $urllist;
	}

	public function getConfigList()
	{
		$products   = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToFilter(
			'type_id', array( 'in' => array( 'configurable' ) )
		);
		$productids = [ ];
		foreach ( $products as $product )
		{
			$id = $product->getId();
			if ( $id )
			{
				$productids[] = $id;
			}
		}
		$productids = array_unique($productids);

		return $productids;
	}

	public function getSimpleList()
	{
		$products   = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToFilter(
			'type_id', array( 'in' => array( 'simple' ) )
		);
		$productids = [ ];
		foreach ( $products as $product )
		{
			$id = $product->getId();
			if($id){
				$productids[] = $id;
			}
		}
		$productids = array_unique($productids);

		return $productids;
	}

	public function updateorderstatus($orderdata){
		foreach ( $orderdata as $orderstatus )
		{
			$order = Mage::getModel( 'sales/order' )->getCollection()->addAttributeToFilter( 'increment_id', $orderstatus['increment_id'] )->getFirstItem();
			$order->setStatus($orderstatus['status']);
			$order->save();
		}
		return "Success";
	}

	public function getImageList()
	{
		$products = Mage::getModel( 'catalog/product' )->getCollection();
		$products->addAttributeToSelect( 'number' );
		$products->addAttributeToSelect( 'image' );
		$returnData = [ ];
		$images     = [ ];
		$numbers    = [ ];
		foreach ( $products as $product )
		{
			$number = $product->getNumber();
			if ( $number )
			{
				$numbers[] = $number;
			}
			$currentImages = $this->getProductImages( $product );
			if ( sizeof( $currentImages ) )
			{
				$images = array_merge( $images, $this->getProductImages( $product ) );
			}
		}

		return array(
			'images'  => $images,
			'numbers' => array_unique( $numbers ),
		);
	}

	public function getProductImages( $product )
	{
		$currentProduct = Mage::getModel( 'catalog/product' )->load( $product->getId() );
		$galleryData    = $currentProduct->getMediaGalleryImages();
		//$mainImage = Mage::getModel('catalog/product_media_config')->getMediaUrl( $currentProduct->getImage() );
		$images = [ ];
		foreach ( $galleryData as $_image )
		{
			$url      = $_image->getUrl();
			$label    = $_image->getLabel();
			$pathInfo = pathinfo( $url );
			$images[] = array(
				'url'      => $url,
				'filename' => $pathInfo['basename'],
				'number'   => $currentProduct->getNumber(),
				'label'    => $label,
				//'dirname' => $pathInfo['dirname'],
				//'position' => $_image->getPosition(),
				//'md5_file' => md5_file($url),
			);
		}

		return $images;
	}

	public function assignImages( $imagesData )
	{
		//$imagesData = json_decode($imagesData, true);
		foreach ( $imagesData as $number => $currentImageData )
		{
			$main    = $currentImageData['main'];
			$back    = $currentImageData['back'];
			$gallery = $currentImageData['detail'];
			if ( isset($main) && $main !== "" )
			{
				$mainImageData = array(
					'disabled'       => 0,
					'position'       => 1,
					'label'          => $main['filename'],
					'filename'       => $main['filename'],
					'url'            => $main['url'],
					'mediaAttribute' => true,
				);
				$this->addImageByNumber( $number, $mainImageData );
			}
			if ( isset($back) && $back !== "" )
			{
				$backImageData = array(
					'disabled' => 0,
					'position' => 2,
					'label'    => $back['filename'],
					'filename' => $back['filename'],
					'url'      => $back['url'],
				);
				$this->addImageByNumber( $number, $backImageData );
			}
			if ( isset($gallery) )
			{
				for ( $i = 0, $iLength = sizeof( $gallery ); $i < $iLength; $i++ )
				{
					$currentImage     = $gallery[ $i ];
					$galleryImageData = array(
						'disabled' => 0,
						'position' => 99,
						'label'    => $currentImage['filename'],
						'filename' => $currentImage['filename'],
						'url'      => $currentImage['url'],
					);
					$this->addImageByNumber( $number, $galleryImageData );
				}
			}
		}
		//return $imagesData;
	}

	private function addImageByNumber( $number, $imageData )
	{
		$tmp          = explode( "-", $number );
		$pronumber    = $tmp[0];
		$procolorcode = $tmp[1];
		Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( Mage_Core_Model_App::ADMIN_STORE_ID ) );
		$products = Mage::getModel( 'catalog/product' )
		                ->getCollection()
		                ->addAttributeToSelect( 'number' )
		                ->addAttributeToSelect( 'color_code' )
		                ->addAttributeToSelect( 'url_key' )
		                ->addAttributeToSelect( 'name' )
		                ->addAttributeToSelect( 'style' )
		                ->addAttributeToSelect( 'visibility' )
		                ->addAttributeToSelect( 'media_gallery' )
			//Bugfix OLD Data
			//->addAttributeToFilter('number', array('eq' => $pronumber.'-'.$procolorcode));
		                ->addAttributeToFilter( 'number', array( 'eq' => $pronumber ) )
		                ->addAttributeToFilter( 'color_code', array( 'eq' => $procolorcode ) );
		// For Bugfix
		//$products->addAttributeToFilter('type_id', array('in' => array('configurable')));
		foreach ( $products as $product )
		{
			$this->importImageToMagento( $imageData, $product );
		}
	}

	private function importImageToMagento( $imageData, $product )
	{
		/*  FIRSTLOAD EXCEPTION BEGIN */
		//$filepath = Mage::getBaseDir('media') . DS . 'import'. DS . $imageData['filename']; //path for temp storage folder: ./media/import/
		//$filepath   = str_replace("\\","\\\\", $filepath);  // Weg wenn Online?
		$filepath    = '/var/www/mey/shared/public/media/import' . DS
		               . $imageData['filename']; //path for temp storage folder: ./media/import/ ONLINE
		$fileContent = file_get_contents( trim( $imageData['url'] ) );
		//var_dump($filepath);

		if ( $fileContent )
		{
			file_put_contents( $filepath, $fileContent ); //store the image from external url to the temp storage folder
			$mediaAttribute = null;
			if ( isset($imageData['mediaAttribute']) )
			{
				$mediaAttribute = array(
					'thumbnail',
					'small_image',
					'image'
				);
			}

			/*    FIRSTLOAD EXCEPTION END */

			/*  Comment after Firstload Begin */ /*
    $filepath = '/var/www/mey/shared/public/media/import'. DS . $imageData['filename']; //path for temp storage folder: ./media/import/ ONLINE
    //var_dump($filepath);

      file_put_contents($filepath, $fileContent); //store the image from external url to the temp storage folder

    if(copy('/var/www/mey/shared/public/media/import/tmp'. DS . $imageData['filename'], $filepath)){
      $mediaAttribute = null;
      if(isset($imageData['mediaAttribute'])){
        $mediaAttribute = array (
          'thumbnail',
          'small_image',
          'image'
        );
      }


/*  Comment after Firstload End */

			/**
			 * Add image to media gallery
			 *
			 * @param string       $file               file path of image in file system
			 * @param string|array $mediaAttribute     code of attribute with type 'media_image',
			 *                                         leave blank if image should be only in gallery
			 * @param boolean      $move               if true, it will move source file
			 * @param boolean      $exclude            mark image as disabled in product page view
			 */
			Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( Mage_Core_Model_App::ADMIN_STORE_ID ) );

			//Check ob altes Bild gelöscht werden muss
			$currentImages = $this->getProductImages( $product );
			for ( $i = 0, $iLength = sizeof( $currentImages ); $i < $iLength; $i++ )
			{
				$currentImageLabel = $currentImages[ $i ]['label'];
				if ( $imageData['filename'] == $currentImageLabel )
				{ // Remove Old Image sadly not from disk
					$mediaApi = Mage::getModel( "catalog/product_attribute_media_api" );
					$items    = $mediaApi->items( $product->getId() );
					$key      = $this->recursive_array_search( $currentImageLabel, $items );
					$mediaApi->remove( $product->getId(), $items[ $key ]['file'] );
					unlink( '../media/catalog/product' . $items[ $key ]['file'] );
					break;
				}
			}

			$product->addImageToMediaGallery( $filepath, $mediaAttribute, true, false );
			$gallery               = $product->getData( 'media_gallery' );
			$lastImage             = array_pop( $gallery['images'] );
			$lastImage['label']    = $imageData['label'];
			$lastImage['position'] = $imageData['position'];
			$lastImage['disabled'] = $imageData['disabled'];

			array_push( $gallery['images'], $lastImage );
			$product->setData( 'media_gallery', $gallery );
			if ( $product->getTypeId() == 'simple' )
			{
				$product->setVisibility( "1" );
			}
			elseif ( $product->getTypeId() == 'configurable' )
			{
				$stockItem = Mage::getModel( 'cataloginventory/stock_item' )->load( $product->getId(), 'product_id' );
				$stockItem->setData( 'manage_stock', 1 );
				$stockItem->setData( 'is_in_stock', 1 );
				$stockItem->setTypeId( $product->getTypeId() );
				$stockItem->save();
			}
			$product->save();
		}
	}

	public function prepareurlrewrites( $productids )
	{

		// Delete all old ID_Paths from previous Run
            ini_set('memory_limit', '-1');
		// Get all ID-Paths and Request-Paths until now
		$existurlrewrites = Mage::getModel( 'core/url_rewrite' )->getCollection()->addFieldToSelect( 'id_path' );
                
		$idPaths          = array();
		foreach ( $existurlrewrites as $rule )
		{
			$idPaths[] = $rule->getIdPath();
		}
		$idPaths = array_unique( $idPaths );
             
		// Get Color-Collection
		$colorCollection = Mage::getModel( 'catalog/product' )->getResource()->getAttribute( "color" );
		foreach ( $productids as $id )
		{
			try
			{
				$currentproduct = Mage::getModel( 'catalog/product' )
				                      ->getCollection()
				                      ->addAttributeToSelect(
					                      array( 'number', 'style', 'color_code', 'color', 'url_seo_key' )
				                      )
				                      ->addAttributeToFilter( 'entity_id', array( 'eq' => $id ) )
				                      ->addAttributeToFilter( 'type_id', array( 'in' => array( 'simple' ) ) )
				                      ->getFirstItem();

				if ( count( $currentproduct ) > 0 )
				{
					$newentrys  = $this->setUrlRewrite( $currentproduct, $idPaths, $colorCollection );
					$debugarray = array();
					if ( count( $newentrys['idpaths'] ) > 0 )
					{
						foreach ( $newentrys['idpaths'] as $entry )
						{
							$idPaths[] = $entry;
						}
						$colorproducts = Mage::getModel( 'catalog/product' )
						                     ->getCollection()
						                     ->addAttributeToSelect(
							                     array( 'number', 'style', 'color_code', 'color', 'url_seo_key' )
						                     )
						                     ->addAttributeToFilter(
							                     'number', array( 'eq' => $currentproduct->getNumber() )
						                     )
						                     ->addAttributeToFilter(
							                     'color_code', array( 'eq' => $currentproduct->getColorCode() )
						                     );
						foreach ( $colorproducts as $product )
						{
							$product->setStoreId( 0 );
							$product->setUrlSeoKey( "SeoURL" );
							$product->getResource()->saveAttribute( $product, 'url_seo_key' );
							foreach ( $newentrys['requestpath'] as $storeid => $requestpath )
							{
								$product->setStoreId( $storeid );
								$product->setUrlSeoKey( $requestpath );
								$product->getResource()->saveAttribute( $product, 'url_seo_key' );
							}
						}
					}
				}
			}
			catch ( Exception $e )
			{
				Mage::log(
					$e->getMessage(),
					null,
					'fg-soaperror.log'
				);
			}
		}
	}

	// Set URL Rewrites for all Stores
	private function setUrlRewrite( $product, $idpaths, $colorCollection )
	{

		$id = $product->getId();
		$storelist   = array_keys( Mage::app()->getStores() );
		$parentIds   = Mage::getResourceSingleton( 'catalog/product_type_configurable' )->getParentIdsByChild($id);

		$resultarray = array("idpaths" => [] );
		if ( count( $parentIds ) > 0 )
		{
			// Search for Parent for Frontend
			$validparent = 0;
			$parents = Mage::getModel( 'catalog/product' )
			    ->getCollection()
			    ->addAttributeToSelect( array('look_part') )
				->addAttributeToFilter( 'entity_id', array('in' => $parentIds) )
				->addAttributeToFilter( 'status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED) );
			$hasValidParent = false;
			foreach ( $parents as $parent )
			{
				if($parent->getLookPart() != 1 ){
					$validparent = $parent->getId();
					$hasValidParent = true;
					break;
				}

			}

			if( !$hasValidParent )
			{
				Mage::log("[setUrlRewrite]: $id has no valid parents",null,'fg-rewrite.log');
			}


			// Set All Int Values

			$style      = $product->getStyle();
			$style      = $this->createSlug( $style );
			$number     = $product->getNumber();
			$colorcode  = $product->getColorCode();
			$colorvalue = $product->getColor();

			foreach ( $storelist as $storeid )
			{
				Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $storeid ) );

				$currentproduct = Mage::getModel( 'catalog/product' )
				                      ->getCollection()
				                      ->addAttributeToSelect( 'name' )
				                      ->addAttributeToFilter( 'entity_id', array( 'eq' => $product->getId() ) )
				                      ->getFirstItem();

				$name = $currentproduct->getName();
				$name = $this->createSlug( $name );
				if ( $colorCollection->usesSource() )
				{
					$tmp   = $colorCollection->setStoreId( $storeid )->getSource()->getOptionText( $colorvalue );
					$color = $this->createSlug( $tmp );
				}
				if($style != ""){
					$requestpath = $style . '-' . $name . '-' . $color . '-' . $number . '-' . $colorcode;
					$idpath      = $storeid . 'userproduct/' . $style . '-' . $name . '-' . $color . '-' . $number . '-' . $colorcode;
				}else {
					$requestpath = $name . '-' . $color . '-' . $number . '-' . $colorcode;
					$idpath      = $storeid . 'userproduct/' . $name . '-' . $color . '-' . $number . '-' . $colorcode;
				}

				if ( !in_array( $idpath, $idpaths ) )
				{
					Mage::getModel( 'core/url_rewrite' )
					    ->setIsSystem( 0 )
					    ->setStoreId( $storeid )
					    ->setIdPath( $idpath )
					    ->setTargetPath( 'catalog/product/view/id/' . $validparent )
					    ->setRequestPath( $requestpath )
					    ->save();
					$resultarray['idpaths'][]               = $idpath;
					$resultarray['requestpath'][ $storeid ] = $requestpath;
				}
			}
		}
		else
		{
			Mage::log("[setUrlRewrite]: $id has no parents",null,'fg-rewrite.log');
		}

		return $resultarray;
	}

	//Initialize Categories-Names Attributes
	public function initcatproducts()
	{
		$storelist = array_keys( Mage::app()->getStores() );
		$admintore = array(Mage_Core_Model_App::ADMIN_STORE_ID);
		$storelist = array_merge( $admintore, $storelist );

		foreach ( $storelist as $storeid )
		{
			Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $storeid ) );
			$products = Mage::getModel( 'catalog/product' )
			                ->getCollection()
			                ->addAttributeToSelect( '*' )
			                ->addAttributeToFilter( 'type_id', array( 'in' => array( 'configurable' ) ) );
			foreach ( $products as $product )
			{
				if ( $product->getSeoCategories() != "placeholder" )
				{
					$product->setStoreId( $storeid );
					$product->setSeoCategories( "placeholder" );
					$product->getResource()->saveAttribute( $product, 'seo_categories' );
				}
			}
		}
			return "Produkte initialisiert";
	}

	// Add Categorie-Names to Variants for Google Landingpage
	public function addcatnamestoproducts(){

		$storelist = array_keys( Mage::app()->getStores() );
		$admintore = array(Mage_Core_Model_App::ADMIN_STORE_ID);
		$storelist = array_merge( $admintore, $storelist );

		foreach ( $storelist as $storeid )
		{
			Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $storeid ) );
			$products = Mage::getModel( 'catalog/product' )
			                ->getCollection()
			                ->addAttributeToSelect( 'seo_categories' )
			                ->addAttributeToFilter( 'type_id', array( 'in' => array( 'configurable' ) ) )
			                ->addAttributeToFilter( 'seo_categories', array( 'eq' => "placeholder" ) )
			;

			foreach ( $products as $product )
			{
				//Get Categories
				$categories = $product->getCategoryCollection()->addAttributeToSelect( 'name' );
				$catarray = array();
				foreach ($categories as $category){
					if($category->getName() != "")
						$catarray[] = $this->createSlug($category->getName());
				}
				$catarray = array_unique($catarray);
				$catstring = implode(",",$catarray);

				$product->setStoreId( $storeid );
				$product->setSeoCategories( $catstring );
				$product->getResource()->saveAttribute( $product, 'seo_categories' );
			}
		}
		print_r("Kategorienamen in Konfig Artikeln aktualisiert");
	}

    /**
     * Disable configurable products with no enabled children.
     */
    public function disableConfigurableProductsWithNoEnabledChildren()
    {
        $configurableProductCollection = Mage::getResourceModel('catalog/product_collection');
        $configurableProductCollection->addAttributeToSelect('entity_id');
        $configurableProductCollection->addAttributeToFilter('type_id', array('eq' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE));

        foreach ($configurableProductCollection as $product) {
            $childProductIds = Mage::getModel('catalog/product_type_configurable')
                ->getUsedProductIds($product);

            $childrenCollection = Mage::getResourceModel('catalog/product_collection');
            $childrenCollection->addAttributeToSelect('entity_id');
            $childrenCollection->addAttributeToFilter('entity_id', array('in' => $childProductIds));
            $childrenCollection->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));

            $status = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            if ($childrenCollection->getSize() == 0) {
                $status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            }
            Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), 0, $status);
        }

        // Disable empty categories (i.e. with no enabled products in them)
        Mage::getModel('mey_afterimport/observer')->disableEmptyCategories();
    }

	public function showStock()
	{
		$products = Mage::getModel( 'catalog/product' )->getCollection();
		$products->addAttributeToFilter( 'type_id', 'simple' );
		$products->addAttributeToSelect( 'ean' );

		$stockData = [ ];
		foreach ( $products as $product )
		{
			$currentStockItem = Mage::getModel( 'cataloginventory/stock_item' )->loadByProduct( $product->getId() );
			$stockData[]      = array(
				'ean' => $product->getEan(),
				'qty' => (int)$currentStockItem->getQty(),
			);
		}

		return $stockData;
	}

	public function setStock( $stockList )
	{
		//Stockdaten anhand der EAN setzen
		$return = [ ];
		for ( $i = 0, $iLength = sizeof( $stockList ); $i < $iLength; $i++ )
		{
			$ean = $stockList[ $i ]['ean'];
			$qty = $stockList[ $i ]['count'];

			$products = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToSelect( 'ean' )
			                ->addAttributeToFilter( 'ean', array( 'eq' => $ean ) );

			if ( !isset($products) || sizeof( $products ) == 0 )
			{
				continue;
			}

			$product = $products->getFirstItem();
			//überprüfen ob Produkt vorhanden
			$currentStockItem = Mage::getModel( 'cataloginventory/stock_item' )->loadByProduct( $product->getId() );
			$currentData      = array(
				'manage_stock'            => $currentStockItem->getManageStock(),
				'is_in_stock'             => $currentStockItem->getIsInStock(),
				'use_config_manage_stock' => $currentStockItem->getUseConfigManageStock(),
				'qty'                     => $currentStockItem->getQty(),
			);
			$needSave         = false;

			if ( $currentData['manage_stock'] != 1 )
			{
				$currentStockItem->setData( 'manage_stock', 1 );
				$needSave = true;
			}
			if ( $currentData['use_config_manage_stock'] != 0 )
			{
				$currentStockItem->setData( 'use_config_manage_stock', 0 );
				$needSave = true;
			}
			if ( $currentData['qty'] != $qty )
			{
				$currentStockItem->setData( 'qty', $qty );
				$needSave = true;
				if ( $qty <= 0 )
				{
					$currentStockItem->setData( 'is_in_stock', 0 );
				}
				else
				{
					$currentStockItem->setData( 'is_in_stock', 1 );
				}
			}

			if ( $needSave )
			{
				$currentStockItem->save();
				$return[] = $ean;
			}
		}
		$this->reindexStock();

		return $return;
	}

	public function geturlkeyjson( $filepath = '' ){

		$resultarray = array();
		$storelist   = Mage::app()->getStores();

		foreach ( $storelist as $store )
		{
			Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $store->getId() ) );
			$products = Mage::getModel( 'catalog/product' )->getCollection()->addAttributeToSelect( array('url_seo_key','iid') )
							->addAttributeToFilter( 'type_id', array( 'eq' => 'simple' ) )
							->addAttributeToFilter( 'status', array( 'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED ) );

			foreach ( $products as $product )
			{
				if($product->getUrlSeoKey() != ''){
					$resultarray[$store->getCode()][$product->getIid()] = $product->getUrlSeoKey();
				}
			}
		}
		
		return $resultarray;
	}

	public function getOrderByOID( $orderId )
	{
		$order = Mage::getModel( 'sales/order' )->getCollection()->addAttributeToFilter( 'increment_id', $orderId )
		             ->getFirstItem();

		$orderItems = $order->getAllItems();

		$couponId        = $order->getCouponCode();
		$customerId      = $order->getCustomerId();
		$shippingAddress = $order->getShippingAddress();
		$billingAddress  = $order->getBillingAddress();

		if ( isset($customerId) )
		{
			$customer      = Mage::getModel( 'customer/customer' )->getCollection()->addAttributeToSelect('customer_number')->addAttributeToSelect('purchase_price_list')->addAttributeToFilter(
				'entity_id', $customerId
			)->getFirstItem();
			$customerEmail = $customer->getEmail();
			$customerNumber = $customer->getCustomerNumber();
			$pricelistId = $customer->getPurchasePriceList() === null ? "191" : $customer->getPurchasePriceList();
		}
		else
		{
			$customerEmail = $order->getCustomerEmail();
		}

		$billingAddressStreet  = $billingAddress->getStreet();
		$shippingAddressStreet = $shippingAddress->getStreet();
		//$shippingCarrier = $order->getShippingAmount();

		// Check for Payment ID
		$payment = $order->getPayment()->getLastTransId();
		if($payment == ''){
			if($order->getPayment()->getData('additional_information')['sofort_transaction'] != ''){
				$payment = $order->getPayment()->getData('additional_information')['sofort_transaction'];
			}
		}

		$orderData = array(
			'increment_id'         => $orderId,
			'created_at'           => $order->getCreatedAt(),
			'updated_at'           => $order->getUpdatedAt(),
			'status'               => $order->getStatus(),
			'shipping_description' => $order->getShippingDescription(),
			'shipping_method'      => $order->getShippingMethod(),
			'payment_fee'          => ($order->getCodFee() * 10 * 10),
			'coupon_id'            => isset($couponId) ? $couponId : 0,
			'customer_firstname'   => $order->getCustomerFirstname(),
			'customer_lastname'    => $order->getCustomerLastname(),
			'customer_id'          => isset($customerId) ? (int)$customerId : 0,
			'customer_number'      => $customerNumber,
			'customer_email'       => $customerEmail,
			'customer_source'      => $order->getCustomerSource(),
			'pricelist_id'         => $pricelistId,
			'addressesEqual'       => Mage::helper("mey_b2b")->addressEqualTo($order->getBillingAddress(), $order->getShippingAddress()),
			'payment'              => $order->getPayment()->getData( 'method' ),
			'payment_trans_id'     => $payment,
			'subtotal'             => ($order->getSubtotal() * 10 * 10),
			'subtotal_incl_tax'    => ($order->getSubtotalInclTax() * 10 * 10),
			'tax_amount'           => ($order->getTaxAmount() * 10 * 10),
			'shipping_amount'      => ($order->getShippingAmount() * 10 * 10),
			'shipping_incl_tax'    => ($order->getShippingInclTax() * 10 * 10),
			'shipping_tax_amount'  => ($order->getShippingTaxAmount() * 10 * 10),
			'notice'               => ($order->getOnestepcheckoutCustomercomment()),

			'shipping_address'     => array(
				'address_id' => $shippingAddress->getId(),
				'lastname'   => $shippingAddress->getLastname(),
				'firstname'  => $shippingAddress->getFirstname(),
				'street'     => isset($shippingAddressStreet) && sizeof( $shippingAddressStreet ) >= 1
					? $shippingAddressStreet[0] : '',
				'street2'    => isset($shippingAddressStreet) && sizeof( $shippingAddressStreet ) == 2
					? $shippingAddressStreet[1] : '',
				'postcode'   => $shippingAddress->getPostcode(),
				'city'       => $shippingAddress->getCity(),
				'company'    => $shippingAddress->getCompany(),
				'country_id' => $shippingAddress->getCountryId(),
				'prefix'     => $shippingAddress->getGender(),
			),
			'billing_address'      => array(
				'address_id' => $billingAddress->getId(),
				'lastname'   => $billingAddress->getLastname(),
				'firstname'  => $billingAddress->getFirstname(),
				'street'     => isset($billingAddressStreet) && sizeof( $billingAddressStreet ) >= 1
					? $billingAddressStreet[0] : '',
				'street2'    => isset($billingAddressStreet) && sizeof( $billingAddressStreet ) == 2
					? $billingAddressStreet[1] : '',
				'postcode'   => $billingAddress->getPostcode(),
				'city'       => $billingAddress->getCity(),
				'company'    => $billingAddress->getCompany(),
				'country_id' => $billingAddress->getCountryId(),
				'prefix'     => $billingAddress->getGender(),
			)
		);

		$items     = [ ];
		$giftitems = [ ];
		foreach ( $orderItems as $orderItem )
		{
			if ( $orderItem->getProductType() == 'simple' )
			{
				// Get all Product Option-Options
				$options = $orderItem->getProduct()->getProductOptionsCollection();
				if ( $options != "" )
				{
					$optionsku = array();
					foreach ( $options as $option )
					{
						foreach ( $option->getValues() as $key => $value )
						{
							//Get Option SKUs
							$optionsku[ $value->getOptionTypeId() ] = array(
								"value_id" => $value->getSku(), "price" => $value->getPrice()
							);
						}
					}
					// Get Selected Option
					$selectedproductoption = $orderItem->getProductOptions();
					$selectedoptions       = $selectedproductoption["options"];
					$selectedskus          = array();
					foreach ( $selectedoptions as $key => $value )
					{
						// Get all Selected Option Values
						$selectedskus[] = $value["option_value"];
					}
				}
				$item = [ ];
				$product             = $orderItem->getProduct();
				$primarySize         = $this->getAttributeOptionAdminLabel(
					'primary_size', $product->getPrimarySize()
				);
				$secondarySize       = $this->getAttributeOptionAdminLabel(
					'secondary_size', $product->getSecondarySize()
				);
				$thirdSize           = $this->getAttributeOptionAdminLabel( 'third_size', $product->getThirdSize() );
				$item['size']        = $primarySize . $thirdSize . $secondarySize;
				$item['ean']         = $product->getEan();
				$item['iid']         = $product->getIid();
				$item['qty_ordered'] = $orderItem->getQtyToShip();

				$intMagentoPrice = (int)bcmul( $orderItem->getPrice(), 100 );

				$item['price_incl_tax'] = (int)bcmul( $orderItem->getOriginalPrice(), 100 );
				$item['price']          = $intMagentoPrice;

				if ( $loop = sizeof( $selectedskus ) > 0 )
				{
					for ( $i = 0; $i < $loop; $i++ )
					{
						$tmpindex                            = $i + 1;
						$tmpoptionarray                      = $optionsku[ $selectedskus[ $i ] ];
						$tmpvalue                            = $tmpoptionarray["value_id"];
						$item[ 'custom_option' . $tmpindex ] = $tmpvalue;
						$giftpriceincltax                    = $tmpoptionarray["price"] * 10 * 10;
						$giftprice
						                                     =
							round( $tmpoptionarray["price"] * 10 * 10 / 119, 2 ) * 10 * 10;
						//Reduce Product Price to Gift Price
						$item['price_incl_tax'] -= $giftpriceincltax;
						$item['price'] -= $giftprice;

						if ( !isset($giftitems[ $tmpvalue ]) )
						{
							$giftitems[ $tmpvalue ] = array(
								'size'           => "",
								'ean'            => "Geschenk",
								'iid'            => $tmpvalue,
								'qty_ordered'    => $orderItem->getQtyToShip(),
								'price_incl_tax' => $giftpriceincltax,
								'price'          => $giftprice
							);
						}
						else
						{
							$giftitems[ $tmpvalue ]["qty_ordered"]
								=
								intval( $giftitems[ $tmpvalue ]["qty_ordered"] ) + intval( $orderItem->getQtyToShip() );
						}
					}
				}
				else
				{
					$item['custom_option1'] = 0;
				}
				$items[] = $item;
			}
		}
		// Add all Custom Options to the Items
		foreach ( $giftitems as $value )
		{
			$items[] = $value;
		}

		$orderData['items'] = $items;

		return $orderData;
	}

	/**
	 * @param $path
	 *
	 * @return int
	 */
	protected function getRootCategoryId( $categoryData )
	{
		$root = $categoryData[0]['path'];

		switch ( $root )
		{
			case 'X':
				$rootId = self::ROOT_MEYSTORY;
				break;

			case 'B2BK':
			case 'B2BN':
			case 'B2BD':
			case 'B2BH':
			case 'B2BA1':
			case 'B2BA2':
			case 'B2BA3':
			case 'B2BA4':
				$rootId = self::ROOT_MEYB2B;
				break;

			default:
				$rootId = self::ROOT_MEYSHOP;
		}

		return $rootId;
	}

	/**
	 * @param   array $categoriesData
	 *
	 * @return  array
	 */
	public function updateCategories( $categoriesData )
	{
		// Kategorien anlegen
		// Jeder Eintrag im Array entspricht einem kompletten Pfad
		// alle Kategorien dieses Pfades müssen überprüft und bei Bedarf angelegt werden
		$returnData = [ ];

		$parentId = $this->getRootCategoryId( $categoriesData );

		foreach ( $categoriesData as $category )
		{
			$parentId = $this->checkCategory( $parentId, $category );
			if ( $parentId === null )
			{
				echo "ERROR\n";
			}
			else
			{
				$returnData[] = array(
					'SKID' => $category['SKID'],
					'mid'  => $parentId
				);
			}
		}

		return $returnData;
	}

	public function linkProductsToCategories( $categoriesData )
	{
		$products   = Mage::getResourceModel( 'catalog/product_collection' )->addAttributeToSelect( 'product_family' )
					->addAttributeToFilter( 'type_id', array( 'eq' => 'configurable' ) )
					->addAttributeToFilter( 'status', array( 'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED ) );
		$returnData = [ ];
		$productMap = [ ];

		foreach ( $products as $product )
		{
			$productId = $product->getId();
			$pfString  = $product->getProductFamily();
			$pfArray   = explode( ",", $pfString );
			for ( $i = 0, $iLength = sizeof( $pfArray ); $i < $iLength; $i++ )
			{
				$iid = $pfArray[ $i ];
				if ( isset($iid) && $iid !== '' )
				{
					$productMap[ $iid ] = $productId;
				}
			}
		}

		foreach ( $categoriesData as $data )
		{
			$scid        = $data['SCID'];
			$isVisible   = $this->isCategoryVisible( $data['SCID'] );
			$category    = Mage::getModel( 'catalog/category' )->getCollection()->addAttributeToSelect( 'scid' )
			                   ->addAttributeToFilter( 'scid', array( 'eq' => $scid ) )->getFirstItem();
			$productData = [ ];

			foreach ( $data['Products'] as $product )
			{
				if ( isset($product['IID']) )
				{
					$productId = $productMap[ $product['IID'] ];
					if ( isset($productId) )
					{
						$productData[ $productId ] = $product['GalPos'];
					}
				}
			}
			//$positions = $category->getProductsPosition();
			$category->setPostedProducts( $productData );
			$visible = array( 'include_in_menu' => $isVisible );
			$category->addData( $visible );
			$category->save();
			$returnData[] = $scid;
		}

		return $returnData;
	}

	public function linkCrossselling( $productData )
	{
		//an den Varianten einer IID, die konfiguierbaren Artikel der angegebenen Varianten verlinken, getParentId
		$configurableProducts = Mage::getResourceModel( 'catalog/product_collection' )->addAttributeToSelect( 'iid' )
		                            ->addAttributeToSelect( 'product_family' )->addAttributeToFilter(
				'type_id', array( 'eq' => 'configurable' )
			);
		$productIdMap         = [ ];
		$returnValue          = [ ];

		foreach ( $configurableProducts as $configurableProduct )
		{
			$iid           = $configurableProduct->getIid();
			$productId     = $configurableProduct->getId();
			$productFamily = $configurableProduct->getProductFamily();
			//$productIdMap[$iid] =
			if ( isset($iid) && isset($productId) && isset($productFamily) )
			{
				$familyIids = explode( ',', $productFamily );
				for ( $i = 0, $iLength = sizeof( $familyIids ); $i < $iLength; $i++ )
				{
					$productIdMap[ $familyIids[ $i ] ] = $productId;
				}
			}
		}

		//Abarbeiten der übertragenen Produkte
		for ( $i = 0, $iLength = sizeof( $productData ); $i < $iLength; $i++ )
		{
			$current      = $productData[ $i ];
			$iid          = $current['ALVIID'];
			$crossselling = $current['crossselling'];
			$linkData     = [ ];
			for ( $k = 0, $kLength = sizeof( $crossselling ); $k < $kLength; $k++ )
			{
				if ( isset($productIdMap[ $crossselling[ $k ] ]) )
				{
					$linkData[ $productIdMap[ $crossselling[ $k ] ] ] = array( 'position' => $k );
				}
			}

			$products = Mage::getResourceModel( 'catalog/product_collection' )->addAttributeToSelect( 'iid' )
			                ->addAttributeToFilter( 'iid', array( 'eq' => $iid ) )->addAttributeToFilter(
					'type_id', array( 'eq' => 'simple' )
				);
			foreach ( $products as $product )
			{
				$product->setUpSellLinkData( $linkData );
				$product->getLinkInstance()->getResource()->saveProductLinks($product, $linkData, Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL);
			}
			$returnValue[] = $iid;
		}

		return $returnValue;
	}

	private function categoryIdScidMap()
	{
		$map        = [ ];
		$categories = Mage::getModel( 'catalog/category' )->getCollection()->addAttributeToSelect( 'scid' );
		foreach ( $categories as $category )
		{
			$id   = $category->getId();
			$scid = $category->getScid();
			if ( isset($scid) && $scid !== '' )
			{
				$map[ $scid ] = $id;
			}
		}

		return $map;
	}

	private function isCategoryVisible( $scid )
	{
		return in_array(
			$scid, array(
				self::STAGING_CATEGORY_ID_BLUESIGN,
				self::STAGING_CATEGORY_ID_GIFT,
				self::STAGING_CATEGORY_ID_ACTION,
				self::STAGING_CATEGORY_ID_PRESALES,
				self::STAGING_CATEGORY_ID_LOOKS
			)
		) ? 0 : 1;
	}

	private function checkCategory( $parentId, $data )
	{
		//Eltern-Kategorie laden
		if ( !isset($data) || (isset($data) && !isset($data['SKID'])) )
		{
			return null;
		}

		$stores         = $this->getStores();
		$parentCategory = Mage::getModel( 'catalog/category' )
		                      ->getCollection()
		                      ->addAttributeToSelect( 'scid' )
		                      ->addAttributeToSelect( 'name' )
		                      ->addAttributeToFilter( 'entity_id', array( 'eq' => $parentId ) )
		                      ->getFirstItem();

		$children = Mage::getModel( 'catalog/category' )
		                ->getCollection()
		                ->addAttributeToSelect( 'scid' )
		                ->addAttributeToSelect( 'position' )
		                ->addAttributeToSelect( 'name' )
		                ->addAttributeToFilter( 'parent_id', array( 'eq' => $parentId ) );

		$isNew    = true;
		$updateId = null;
		foreach ( $children as $childCategory )
		{ // alle Kategorien durchlaufen und schauen ob die übergebene dabei ist, falls nicht neu, falls ja nicht neu + updateId
			$scid     = $childCategory->getScid();
			$position = $childCategory->getPosition();
			$name     = $childCategory->getName();

			if ( $scid == $data['SKID'] )
			{
				$isNew    = 0;
				$updateId = $childCategory->getId();
				break;
			}
		}

		if ( $isNew )
		{//Kategorie muss angelegt werden
			$category = new Mage_Catalog_Model_Category();
			$category->setPath( $parentCategory->getPath() );
		}

		if ( $updateId !== null )
		{
			$category = Mage::getModel( 'catalog/category' )->load( $updateId );
		}

		$isVisible = $this->isCategoryVisible( $data['SKID'] );

		if ( $category )
		{
			$isActive  = $data['active'] === "true" ? 1 : 0;
			$adminData = array(
				'scid'            => $data['SKID'],
				'is_active'       => $isActive,
				'is_anchor'       => 1,
				'include_in_menu' => $isVisible
			);

			// deutsche Werte als admin-Werte setzen
			if ( isset($data['name']) && isset($data['name']['de']) )
			{
				$adminData['name']    = $data['name']['de'];
				$adminData['url_key'] = $this->createSlug( $data['name']['de'] );
			}

			/*
			  if(isset($data['description']) && isset($data['description']['de']))
			  {
				$adminData['description'] = $data['description']['de'];
			  }

			  if(isset($data['seo_text']) && isset($data['seo_text']['de']))
			  {
				$adminData['seo_text'] = $data['seo_text']['de'];
			  }
			  */

			if ( isset($data['position']) )
			{
				$adminData['position'] = $data['position'] + 1;
			}

			$category->setStoreId( 0 );   // 0 = admin ID
			$category->addData( $adminData );
			$category->save();
			$category->setStoreId( $stores['de'] );  // admin Werte für deutschen Store übernehmen
			$category->save();
			// Sichbarkeit im deutschen Store setzen

			foreach ( [ 'en', 'nl' ] as $storeCode )
			{
				$storeData = [ ];
				//foreach (['name','description','seo_text'] as $key)
				foreach ( [ 'name' ] as $key )
				{
					if ( isset($data[ $key ]) && isset($data[ $key ][ $storeCode ]) )
					{
						$storeData[ $key ] = $data[ $key ][ $storeCode ];
					}
				}

				if ( isset($data['name'][ $storeCode ]) )
				{
					$storeData['url_key'] = $this->createSlug( $data['name'][ $storeCode ] );
				}

				$category->setStoreId( $stores[ $storeCode ] );
				$category->addData( $storeData );
				$category->setIncludeInMenu( $isVisible );
				$category->save();
			}

			$newId = $category->getId();
			unset($category);
		}

		return $newId;
	}

	protected function createSlug( $str )
	{
		$table = array(
			'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c',
			'Ć' => 'C', 'ć' => 'c',
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E',
			'É' => 'E',
			'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O', 'Ö' => 'Oe', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ý' => 'Y',
			'Þ' => 'B', 'ß' => 'Ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'ae', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e',
			'é' => 'e',
			'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o', 'õ' => 'o', 'ö' => 'oe', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y',
			'þ' => 'b',
			'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', '/' => '-', ' ' => '-', 'ü' => 'ue'
		);

		// remove duplicated spaces
		$str = preg_replace( array( '/\s{2,}/', '/[\t\n]/' ), ' ', $str );
		// normalize
		$str = strtolower( strtr( $str, $table ) );
		// remove unwanted chars
		$str = trim( preg_replace( '/[^a-z0-9\-]{1,}/', '', $str ), '-' );

		// cleanup
		return $str = preg_replace( '/[-]{2,}/', '', $str );
	}

	public function cleanUpProductFamilies( $familylist )
	{
		Mage::register( "isSecureArea", 1 );
		$deletequeue = [ ];
		foreach ( $familylist as $entry )
		{
			$product = Mage::getResourceModel( 'catalog/product_collection' )
			               ->addAttributeToSelect( array( 'number', 'product_family', 'color_code' ) )
			               ->addAttributeToFilter( 'product_family', array( 'eq' => $entry ) )
			               ->addAttributeToFilter( 'type_id', array( 'eq' => 'configurable' ) )
			               ->getFirstItem();

			if ( $product )
			{
				$key   = $product->getNumber() . '-' . $product->getColorCode();
				$value = $product->getProductFamily();
				try
				{
					$product->delete();
					$deletequeue['success'][ $key ] = $value;
				}
				catch ( Exception $e )
				{
					$deletequeue['failed'][ $key ] = $value;
				}
			}
		}

		return $deletequeue;
	}

	private function getStores()
	{
		$storesPlain = Mage::app()->getStores();
		$stores      = [ ];
		foreach ( $storesPlain as $store )
		{
			$stores[ $store->getCode() ] = $store->getId();
		}

		return $stores;
	}

	private function getAttributeOptionAdminLabel( $attributeName, $optionId )
	{
		//anhand einer Attribute Option ID zum Beispiel 260, das Admin Label laden zum Beispiel XXL
		$entityType                = Mage::getModel( 'eav/config' )->getEntityType( 'catalog_product' );
		$attributeModel            = Mage::getModel( 'eav/entity_attribute' )->loadByCode(
			$entityType, $attributeName
		);
		$attributeOptionCollection = Mage::getResourceModel( 'eav/entity_attribute_option_collection' )
		                                 ->setAttributeFilter( $attributeModel->getId() )
		                                 ->setStoreFilter( 0 )
		                                 ->load();
		foreach ( $attributeOptionCollection->toOptionArray() as $currentOption )
		{
			if ( $currentOption['value'] == $optionId )
			{
				return $currentOption['label'];
			}
		}
	}

	//Helper Function for Recursive Array Search
	private function recursive_array_search( $needle, $haystack )
	{
		foreach ( $haystack as $key => $value )
		{
			$current_key = $key;
			if ( $needle === $value OR (is_array( $value )
			                            && $this->recursive_array_search( $needle, $value ) !== false)
			)
			{
				return $current_key;
			}
		}

		return false;
	}
}
