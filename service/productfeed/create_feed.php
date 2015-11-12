<?php
gc_enable();
set_time_limit( 0 );
ini_set( "max_execution_time", 0 );

require dirname( dirname( __DIR__ ) ) . '/app/Mage.php';
require __DIR__ . '/SimpleProduct.php';
require __DIR__ . '/ConfigurableProduct.php';
require __DIR__ . '/TaxCalculation.php';
require __DIR__ . '/Category.php';
require __DIR__ . '/CsvWriter.php';
require __DIR__ . '/LanguageMapper.php';

$options        = getopt( null, [ "language:" ] );

if(empty($options))
{// fallback
	$options["language"] = "de";
}

$skippedProducts= 0;
$mapper         = new LanguageMapper($options["language"]);
$feedFilename   = sprintf( "%s/Mey_%s_%s.csv", "/opt/productexport", $mapper->getLabel(), date( "Y-m-d_H-m-s" ) );
$start          = microtime( true );
$data           = [ ];
$brokenProducts = [ ];
$gc             = gc_enabled();

Mage::app()->setCurrentStore( Mage::getModel( 'core/store' )->load( $mapper->getStoreId() ) );

$taxCalc     = new TaxCalculation( Mage );
$open        = count( $products );
$dataWritten = false;
$writer      = new CsvWriter( $feedFilename );

$productIds = Mage::getModel( 'catalog/product' )->getCollection()
                  ->addAttributeToFilter( 'type_id', array( 'eq' => 'configurable' ) )
                  ->addAttributeToFilter( 'status', array( 'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED ) )
                  ->getAllIds();

$open = count( $productIds );

foreach ( $productIds as $id )
{
	$product             = Mage::getModel( 'catalog/product' )->load( $id );
	$websites            = $product->getWebsiteIds();

	if( !inMainStore($websites) )
	{
		$websites = implode(",", $websites);
		echo "[wrong website] skip $id, websites: $websites\n";
		$skippedProducts++;
		continue;
	}

	$data                = [ ];
	$categoryData        = [ ];
	$configProduct       = new ConfigurableProduct( $product );
	$categories          = $configProduct->getCategoryIds();
	$hasActiveCategories = false;

	echo sprintf(
		"(%s) %s %s\n",
		--$open,
		str_pad( $configProduct->getId(), 5 ),
		str_pad( $configProduct->getName(), 30 )
	);

	foreach ( $categories as $categoryId )
	{
		$category = new Category($categoryId);

		if ($category->isVisible() && strlen( $category->getName( ) ) > 0 && !$category->isExcluded( ))
		{
			if ( $hasActiveCategories )
			{
				$categoryData ['alternateCategoryId'][]   = $categoryId;
				$categoryData ['alternateCategoryName'][] = $category->getName();
				$categoryData ['alternateCategoryTree'][] = $category->getCategoryTree();
			}
			else
			{
				$categoryData ['defaultCategoryId']     = $categoryId;
				$categoryData ['defaultCategoryName']   = $category->getName();
				$categoryData ['defaultCategoryTree']   = $category->getCategoryTree( );
				$categoryData ['productType']           = $category->getName();
				$categoryData ['alternateCategoryId']   = [ ];
				$categoryData ['alternateCategoryName'] = [ ];
				$categoryData ['alternateCategoryTree'] = [ ];
			}

			$hasActiveCategories = true;
		}
	} // eo category

	if ( !$hasActiveCategories )
	{// diese Produkte sollten eigentlich nicht mehr aktiv sein
		$brokenProducts[] = [ $configProduct->getId(), $configProduct->getName() ];
		continue;
	}

	if ( count( $categoryData ['alternateCategoryId'] ) > 0 )
	{
		$categoryData ['alternateCategoryId']   = implode( "|", $categoryData['alternateCategoryId'] );
		$categoryData ['alternateCategoryName'] = implode( "|", $categoryData['alternateCategoryName'] );
		$categoryData ['alternateCategoryTree'] = implode( "|", $categoryData['alternateCategoryTree'] );
	}
	else
	{
		$categoryData ['alternateCategoryId']   = "";
		$categoryData ['alternateCategoryName'] = "";
		$categoryData ['alternateCategoryTree'] = "";
	}

	$children = $configProduct->getChilden( Mage );
	$product->clearInstance();

	foreach ( $children as $simpleProduct )
	{
		$exportProduct = new SimpleProduct( Mage, $simpleProduct );

		if ( !$exportProduct->isAvailable() )
		{
			$simpleProduct->clearInstance();
			continue;
		}

		$taxRate                  = $taxCalc->getTaxRate( $exportProduct->getMagentoProduct() );
		$data["productID"]        = $exportProduct->getNumber();
		$data["styleID"]          = $exportProduct->getStyle();
		$data["EAN"]              = $exportProduct->getEan();
		$data["size"]             = $exportProduct->getSizeLabel();
		$data["regularPrice"]     = sprintf( "%.2f", $exportProduct->getPrice() );
		$data["actualPrice"]      = sprintf( "%.2f", $exportProduct->getActualPrice() );
		$data["currency"]         = "EUR";
		$data["tax"]              = $taxCalc->getTaxAmount( $exportProduct->getActualPrice(), $taxRate );
		$data["taxRate"]          = $taxRate;
		$data["shipping"]         = $exportProduct->getShippingPrice();
		$data["shippingTime"]     = $mapper->getDeliveryText();
		$data["details"]          = $exportProduct->getDetails();
		$data["shortDescription"] = str_replace( array( "\t", "\n", "\r" ), "", $exportProduct->getShortDescription() );
		$data["longDescription"]  = str_replace( array( "\t", "\n", "\r" ), "", $exportProduct->getLongDescription() );
		$data["material"]         = $exportProduct->getMaterial();
		$data["care"]             = $exportProduct->getCareInstructionAsHtml();
		$data["imageBaseUrl"]     = Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA );
		// image0 - image9
		$images = $exportProduct->getImages( Mage );
		for ( $i = 0; $i < 10; $i++ )
		{
			$image                = isset($images[ $i ]) ? $images[ $i ] : "";
			$data[ "image" . $i ] = $image;
		}

		$data["size0"]                 = $data["image0"];
		$data["size1"]                 = "";
		$data["size2"]                 = "";
		$data["size3"]                 = "";
		$data["size4"]                 = "";
		$data["size5"]                 = "";
		$data["name"]                  = $exportProduct->getName();
		$data["brand"]                 = $exportProduct->getBrand();
		$data["color"]                 = $exportProduct->getColorAsText();
		$data["realColor"]             = $exportProduct->getFilterColorAsText();
		$data["pattern"]               = "";
		$data['defaultCategoryId']     = $categoryData['defaultCategoryId'];
		$data['defaultCategoryName']   = $categoryData['defaultCategoryName'];
		$data['defaultCategoryTree']   = $categoryData['defaultCategoryTree'];
		$data['alternateCategoryId']   = $categoryData['alternateCategoryId'];
		$data['alternateCategoryName'] = $categoryData['alternateCategoryName'];
		$data['alternateCategoryTree'] = $categoryData['alternateCategoryTree'];
		$date["onlineTo"]              = date( "Y-m-d", time() + 604800 );
		$data["available"]             = $exportProduct->isAvailable() ? "y" : "n";
		$data["stockLevel"]            = $exportProduct->getStock();
		$data["url"]                   = $exportProduct->getUrl();
		$data["isNew"]                 = $exportProduct->isNew() ? "y" : "n";
		$data["gender"]                = $exportProduct->getDepartment();
		$data["sex"]                   = $exportProduct->getGender();
		$data["ageGroup"]              = "adult";
		$data['productType']           = $categoryData['productType'];
		$data["measureUnit"]           = "";
		$data["measureReference"]      = "";
		$data["measureCapacity"]       = "";
		$data["pricePerUnit"]          = "";

		if ( !$dataWritten )
		{
			$dataWritten = true;
			$writer->writeHeadLine( $data );
		}
		$writer->writeDataLine( $data );
		$simpleProduct->clearInstance();
	}
	$children->clear();
	if ( $gc )
	{
		gc_collect_cycles();
	}
}

file_put_contents( __DIR__ . "/broken.json", json_encode( $brokenProducts ) );

if ( $gc )
{
	gc_disable();
}

$report  = "broken: " . count( $brokenProducts ) . " \n";
$report .= "skipped: " . $skippedProducts . " \n";
$report .= "duration: " . round( microtime( true ) - $start, 2 ) . " sec\n";
$report .= "memory: " . round( memory_get_usage( false ) / 1024 / 1024 ) . " mb";

echo "$report\n\n";

file_put_contents( __DIR__ . "/report_" . $feedFilename, $report );

function inMainStore(array $ids)
{
	$meyWebsiteId = 1;
	return in_array($meyWebsiteId, $ids);
}