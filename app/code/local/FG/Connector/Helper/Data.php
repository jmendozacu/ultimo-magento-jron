<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
class FG_Connector_Helper_Data extends Mage_Core_Helper_Abstract{


  public function getImageUrl($sku, $width = null, $height = null)
  {
    $returnValue = "";
    $serverPath = isset($_SERVER['HTTPS']) ? "https://"  . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']). "/": "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']). "/";
    $imageServerPath =  Mage::getStoreConfig('fg_options/messages/fg_imageurl');
    $imagePath = $this->checkImage($sku, $width, $height);
    if(isset($imagePath)){
      $returnValue = $imagePath;
    }
    else{
      $returnValue = isset($width) && isset($height) ? $serverPath . $imageServerPath . "/" . "placeholder_" . $width . "_" . $height . ".jpg" : $serverPath . $imageServerPath . "/placeholder.jpg";
    }
    return $returnValue;
  }

  public function checkImage($sku, $width = null, $height = null)
  {
    $returnValue = "";
    $imagePath =  Mage::getStoreConfig('fg_options/messages/fg_imageurl');
    $serverPath = isset($_SERVER['HTTPS']) ? "https://"  . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']). "/": "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']). "/";
    if(isset($sku)){
      $sku = strtolower($sku);
      $fileName = isset($width) && isset($height) ? $sku . "_" . $width . "_" . $height : $sku;
      $checkPath = $serverPath . $imagePath . "/" . $fileName . ".jpg";
      $fileHeaders = @get_headers($checkPath);
      if (preg_match("|200|", $fileHeaders[0])) {
        $returnValue = $checkPath;
      } else {
        $returnValue = $this->resize($imagePath . "/" . $sku, $width, $height);
      }
    }
    
    return $returnValue;
  }

  private function resize($url, $width = null, $height = height, $quality = 80)
  {
    $resizeUrl = $url . "_" . $width . "_" . $height . ".jpg";
    $sourceUrl = $url . ".jpg";
    $returnUrl = null;
    $serverPath = isset($_SERVER['HTTPS']) ? "https://"  . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']). "/": "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']). "/";
    $imageData = getimagesize( $serverPath . $url . ".jpg" );
    if(is_array($imageData)){
      $resizeImage;
      $originalImage = imagecreatefromjpeg( $url . ".jpg" );
      $originalRatio = number_format($imageData[0] / $imageData[1],2);
      $newRatio = number_format($width / $height,2);
      if($newRatio != $originalRatio){
        if($imageData[0] / $width > $imageData[1] / $height){
          $newHeight = intval($height * $originalRatio);
          $resizeImage = imagecreatetruecolor( $newHeight, $height );
          imagecopyresampled( $resizeImage, $originalImage, 0, 0, 0, 0, $width, $newHeight, $imageData[0], $imageData[1] );
        }
        else{
          $newWidth = intval($width * $originalRatio);
          $resizeImage = imagecreatetruecolor( $newWidth, $height );
          imagecopyresampled( $resizeImage, $originalImage, 0, 0, 0, 0, $newWidth, $height, $imageData[0], $imageData[1] );
        }
      }
      else{
        $resizeImage = imagecreatetruecolor( $width, $height );
        imagecopyresampled( $resizeImage, $originalImage, 0, 0, 0, 0, $width, $height, $imageData[0], $imageData[1] );
      }
      imagejpeg( $resizeImage, $resizeUrl, $quality  );
        $returnUrl = $serverPath . $resizeUrl;
        imagedestroy($resizeImage);
        imagedestroy($originalImage);
    }
    return $returnUrl;
  }
}