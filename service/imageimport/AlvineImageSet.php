<?php

final class AlvineImageSet
{
  private $mainImage;
  private $backImage;
  private $detailImages;
  private $names;


  public function __construct()
  {
    $this->detailImages = [];
    $this->names = [];
  }

  public function addDetailImage(AlvineImage $image)
  {
    $this->detailImages[] = $image;
    $this->names[] = $image->getName();
    return $this;
  }

  public function setMainImage(AlvineImage $image)
  {
    $this->mainImage = $image;
    $this->names[] = $image->getName();
    return $this;
  }

  public function setBackImage(AlvineImage $image)
  {
    $this->backImage = $image;
    $this->names[] = $image->getName();
    return $this;
  }

  public function getImages()
  {
    $images = [];

    if($this->hasMainImage())
    {
      $images[] = $this->mainImage;
    }

    if($this->hasBackImage())
    {
      $images[] = $this->backImage;
    }

    if($this->hasDetailImages())
    {
      $images = array_merge($this->detailImages, $images);
    }

    return $images;
  }

  public function getNames()
  {
    return $this->names;
  }

  public function getMainImage()
  {
    return $this->mainImage;
  }

  public function getBackImage()
  {
    return $this->backImage;
  }

  public function getDetailImages()
  {
    return $this->detailImages;
  }

  public function hasMainImage()
  {
    return ! is_null($this->mainImage);
  }

  public function hasBackImage()
  {
    return ! is_null($this->backImage);
  }

  public function hasDetailImages()
  {
    return ! empty($this->detailImages);
  }
}