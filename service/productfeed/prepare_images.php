<?php
//***************************************************
// Produktbilder für den Produktdatenfeed erstellen

ini_set( 'memory_limit', '1600M' );
define( 'MAGENTO', realpath( dirname( __FILE__ ) ) );
require MAGENTO . '/../../app/Mage.php';
require __DIR__ . '/FeedImageHelper.php';
require __DIR__ . '/FeedImageManager.php';

$globalConfig   = include __DIR__ . "/config.php";
$feedImagePath  = $globalConfig["images_path"];
$imageConfig    = $globalConfig["images_to_create"];
$imagesNotFound = [ ];
$images         = [ ];
$stylesDone     = [ ];
$start          = microtime( true );
$bytes          = 0;

Mage::app()
    ->setCurrentStore(
	    Mage::getModel( 'core/store' )
	        ->load( Mage_Core_Model_App::ADMIN_STORE_ID )
    );

$builder = new FeedImageHelper();
$manager = new FeedImageManager( $feedImagePath );

$products = Mage::getModel( 'catalog/product' )->getCollection()
                ->addAttributeToSelect( [ "color_code", "number", "image", "small_image", "thumbnail" ] )
                ->addAttributeToFilter( 'type_id', array( 'eq' => 'simple' ) )
                ->addAttributeToFilter( 'status', array( 'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED ) );

foreach ( $products as $product )
{
	$style = $product->getNumber() . "-" . $product->getColorCode();

	if ( array_key_exists( $style, $stylesDone ) )
	{
		continue;
	}

	try
	{
		echo sprintf( "create images for product %s\n", $style );
		$bytes += $manager->storeImages( $product, $builder->createImages( $product, $imageConfig ) );
		echo "[success]\n";
	}
	catch ( Exception $e )
	{
		$imagesNotFound[] = $product->getId();
		echo sprintf( "[error] (%s)\n", $e->getMessage() );
	}

	$stylesDone[ $style ] = true;
}

if ( ($totalImagesNotFound = count( $imagesNotFound )) > 0 )
{
	file_put_contents( __DIR__ . "/imagesnotfound.log", implode( ", ", $imagesNotFound ) );
}

echo "$totalImagesNotFound images not found\n";
echo "duration: ", round( microtime( true ) - $start ), " sec\n";
echo "memory: ", round( memory_get_usage( false ) / 1024 / 1024 ), " mb\n\n";