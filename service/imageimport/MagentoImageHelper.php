<?php

final class MagentoImageHelper
{
  private $mediaApi;
  private $imageTempDir;
  private $mediaDir;

  public function __construct($tmpDir='/var/www/mey/shared/public/media/import/')
  {
    define('MAGENTO', realpath( dirname( __FILE__ ) ));
    require_once MAGENTO . '/../../app/Mage.php';
    Mage::app()
        ->setCurrentStore( Mage::getModel( 'core/store' )
        ->load( Mage_Core_Model_App::ADMIN_STORE_ID ) );

    $this->mediaApi = Mage::getModel( "catalog/product_attribute_media_api" );
    $this->imageTempDir = $tmpDir;
    $this->mediaDir = dirname(dirname(__DIR__)) . '/media/';
  }

  public function getMediaAttributes()
  {
    return ['thumbnail', 'small_image','image'];
  }

  public function storeImageToTempPath(AlvineImage $image, $skipIdExists=true)
  {
    $destination = $this->imageTempDir . $image->getName();

    if($skipIdExists && is_file($destination))
    {
      return filesize($destination);
    }

    $imageContent = @file_get_contents($image->getUrl());
    
    if($imageContent === false)
    {
      throw new Exception("unable to fetch image " . $image->getUrl());
    }

    return file_put_contents($destination, $imageContent);
  }

  public function getTempFilename(AlvineImage $image)
  {
    return $this->imageTempDir . $image->getName();
  }


  public function fetchProducts( $artNr, $colorCode )
  {
    return $products = Mage::getModel( 'catalog/product' )
            ->getCollection()
            ->addAttributeToSelect( 'number' )
            ->addAttributeToSelect( '*' )
            ->addAttributeToSelect( 'color_code' )
            ->addAttributeToSelect( 'url_key' )
            ->addAttributeToSelect( 'name' )
            ->addAttributeToSelect( 'style' )
            ->addAttributeToSelect( 'visibility' )
            ->addAttributeToSelect( 'media_gallery' )
            ->addAttributeToFilter( 'number', array( 'eq' => $artNr ) )
            ->addAttributeToFilter( 'color_code', array( 'eq' => $colorCode ) );
  }

  public function removeImages($product, array $imageNames)
  {
    $productId = $product->getId();
    $product_ = Mage::getModel("catalog/product")->load($product->getId());
    $images = $this->mediaApi->items( $productId );
    foreach ($images as $image) 
    {
      if(in_array($image["label"], $imageNames, true))
      {
        $path = $this->mediaDir . 'catalog/product' . $image['file'];
        self::log("entferne " . $path);
        unlink( $path );
        $this->mediaApi->remove( $productId, $image['file'] );
      }
    }
    $product_->save();
    return count($images);
  }

  private function getImportData(AlvineImage $image)
  {
    switch ($image->getType())
    {
      case AlvineImage::TYPE_BACK:
        return MagentoImage::backImage($image);
      case AlvineImage::TYPE_MAIN:
        return MagentoImage::mainImage($image);
      case AlvineImage::TYPE_DETAIL:
        return MagentoImage::detailImage($image);
      default:
        throw new Exception("unexpected type given: " . $image->getType());
    }
  }

  public function addImageSet($productFromCollection, AlvineImageSet $imageSet)
  {
    $product = Mage::getModel("catalog/product")->load($productFromCollection->getId());
    $imagesToAdd = $imageSet->getImages();
    self::log( "aktuelles Produkt: " . $product->getId());

    foreach ($imagesToAdd as $image) 
    {
      self::log("bearbeite\t" . $image->getType());
      $this->storeImageToTempPath($image);

      if($image->getType() === AlvineImage::TYPE_MAIN)
      {
        $product->addImageToMediaGallery( $this->getTempFilename($image), $this->getMediaAttributes(), false, false );        
      }
      else
      {
        $product->addImageToMediaGallery( $this->getTempFilename($image), null, false, false );
      }

      $gallery = $product->getData( 'media_gallery' );
      $galleryImage = array_pop($gallery["images"]);
      $imageData = $this->getImportData($image);
      $galleryImage['label']    = $imageData['label'];
      $galleryImage['position'] = $imageData['position'];
      $galleryImage['disabled'] = $imageData['disabled'];
      $gallery["images"][] = $galleryImage;
      $product->setData( 'media_gallery', $gallery);
    }


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

  public static function log($msg)
  {
    echo $msg, PHP_EOL;
  }
}
