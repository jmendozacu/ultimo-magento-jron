<?php
// helper-funcs
function getImageSet( array $articleImages )
{
  $set = new AlvineImageSet();

  foreach ($articleImages as $mode => $urlObj) 
  {
    switch($mode)
    {
      case AlvineImage::TYPE_MAIN:
        $set->setMainImage( new AlvineImage($urlObj["url"], $mode ));
        break;

      case AlvineImage::TYPE_BACK:
        $set->setBackImage( new AlvineImage($urlObj["url"], $mode ));
        break;

      case AlvineImage::TYPE_DETAIL:
        foreach ($urlObj as $imageData) 
        {
          $set->addDetailImage( new AlvineImage($imageData["url"], $mode ));
        }
        break;

      default: 
        throw new Exception("unexpected mode " . $mode . " given...");
    }
  }

  return $set;
}


function deleteImageSet( AlvineImageSet $set )
{
  $helper = new MagentoImageHelper();
  $mainImage = $set->getMainImage();
  $removesImages = 0;
  $products = $helper->fetchProducts($mainImage->getArticleNumber(), $mainImage->getColorCode());
  $helper->log(sprintf("%s Produkt(e) geladen", $products->count()));

  foreach ($products as $product) 
  {
    $removesImages += $helper->removeImages($product, $set->getNames());
  }

  return $removesImages;
}


function importImageSet( AlvineImageSet $set )
{
  $helper = new MagentoImageHelper();
  $mainImage = $set->getMainImage();

  $products = $helper->fetchProducts($mainImage->getArticleNumber(), $mainImage->getColorCode());
  $helper->log(sprintf("%s Produkt(e) geladen", $products->count()));

  foreach ($products as $product) 
  {
    $helper->addImageSet($product, $set);
  }
}


function readImageDataFromFile($filepath)
{
  $data = file_get_contents($filepath);
  if(false === $data)
  {
    throw new Exception("file not found: " . $filepath);
  }

  return json_decode($data, true);
}

function writeImageDataToFile($filepath, $data)
{
  return file_put_contents($filepath, json_encode($data));  
}