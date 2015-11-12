<?php

final class MagentoImage
{
  public static function mainImage( AlvineImage $image )
  {
    return [
      'disabled'       => 0,
      'position'       => 1,
      'label'          => $image->getName(),
      'filename'       => $image->getName(),
      'url'            => $image->getUrl(),
      'mediaAttribute' => true
    ];
  }

  public static function backImage( AlvineImage $image )
  {
    return [
      'disabled'       => 0,
      'position'       => 2,
      'label'          => $image->getName(),
      'filename'       => $image->getName(),
      'url'            => $image->getUrl()
    ];
  }

  public static function detailImage( AlvineImage $image )
  {
    return [
      'disabled'       => 0,
      'position'       => 99,
      'label'          => $image->getName(),
      'filename'       => $image->getName(),
      'url'            => $image->getUrl()
    ];
  }
}