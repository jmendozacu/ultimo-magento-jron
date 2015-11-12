<?php

final class AlvineImage
{
  const TYPE_MAIN = "main";
  const TYPE_BACK = "back";
  const TYPE_DETAIL = "detail";

  private $url;
  private $name;
  private $articleNumber;
  private $colorCode;
  private $fileExtension;
  private $type;


  public function __construct( $url, $type )
  {
    $this->setUrl($url, $type);
  }

  public function setUrl( $url, $type )
  {
    $this->url = $url;
    $this->name = basename($url);
    $this->type = $type;

    $parts = explode("-", pathinfo($this->name, PATHINFO_FILENAME));

    if(count($parts) < 2)
    {
      throw new Exception("invalid name given: " . $this->name);
    }

    $this->articleNumber = $parts[0];
    $this->colorCode = (int) $parts[1];
    $this->fileExtension = pathinfo($this->name, PATHINFO_EXTENSION);
    return $this;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getMode()
  {
    return $this->mode;
  }

  public function getColorCode()
  {
    return $this->colorCode;
  }

  public function getArticleNumber()
  {
    return $this->articleNumber;
  }

  public function getUrl()
  {
    return $this->url;
  }
}