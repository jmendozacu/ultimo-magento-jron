<?php
include 'header.php';
include 'mageSOAP.php';
include 'mageSOAPAdvanced.php';
class SOAPConnector {
  private $soapUrl;
  private $soapClient;
  private $user = 'SOAPTESTER';
  private $pwd = '86c8c7153234f8c1c0c34c502a7ec1fb';
  private $multiCallStackSize = 500;

  function __construct( $soapUrl ) {
    if ( $soapUrl ) {
      $this->soapUrl = $soapUrl;
    }
    else {
      exit( "Missing Connect URL" );
    }
  }

  public function doRequest( $action, $data ) {
    switch ( $action ) {
    case 'products':
      $returnValue = $this->products( $data );
      break;
    case 'orders':// !!!ACHTUNG!!! NOCH UNGETESTET
      if ( $data && $data['data'] && count( $data['data'] ) ) {
        $type = isset( $data['data']['type'] ) ? $data['data']['type'] : null;
        $returnValue = $this->orders( $type, $data['data'] );
      }
      else {
        $returnValue = $this->orders();

      }
      break;

    case 'info':
      $returnValue = $this->login();
      break;
    case 'advanced':
      if ( $data && $data['data'] ) {
        $type = isset( $data['data']['type'] ) ? $data['data']['type'] : null;
        if ( $type ) {
          if ( isset( $data['data']['data'] ) ) {
            $returnValue = $this->advanced( $type, $data['data']['data'] );
          }
          else {
            $returnValue = $this->advanced( $type );
          }
        }
        else {
          $returnValue = array( "Error" => "Missing advanced tyoe" );
        }
      }
      break;

    case 'categories':
      $returnValue = $this->categories();
      break;

    default:
      $returnValue = array( "Error" => "Missing or unknown Action" );
      break;
    }
    $jsonString = json_encode( $returnValue, JSON_PRETTY_PRINT );
    return $jsonString;
  }

  private function products( $data ) {
    if ( isset( $data['data']['type'] ) ) {
      $type = $data['data']['type'];
      switch ( $type ) {
      case 'master':
        $this->login();
        $returnValue = $this->soapClient->getMasterProducts();
        break;

      case 'variants':
        $this->login();
        $returnValue = $this->soapClient->getVariants();
        break;

      case 'createMaster':
        if ( isset( $data['data']['data'] ) ) {
          $this->login();
          $returnValue = $this->soapClient->createMasterProducts( $data['data']['data'] );
          //$returnValue = array( "Success" => "Create Products");
        }
        else {
          $returnValue = array( "Error" => "No data for create Master" );
        }
        break;

      case 'createVariants':
        if ( isset( $data['data']['data'] ) ) {
          $this->login();
          $returnValue = $this->soapClient->createVariants( $data['data']['data'] );
          //$returnValue = array( "Success" => "Create Products");
        }
        else {
          $returnValue = array( "Error" => "No data for create Variants" );
        }
        break;

      case 'link':
        if ( isset( $data['data']['data'] ) ) {
          $this->login();
          $returnValue = $this->soapClient->linkVariants( $data['data']['data'] );
        }
        else {
          $returnValue = array( "Error" => "No data for link" );
        }
        break;
        case 'finalizeLink':
          $returnValue = array();
          $this->advanced('disableConfigurableProductsWithNoEnabledChildren');
          break;

      default:
        $returnValue = array( "Error" => "Unknown type $type for action products" );
        break;
      }
    }
    else {
      $returnValue = array( "Error" => "Missing type for Action products" );
    }
    return $returnValue;
  }

  private function login() {
    $this->soapClient = new mageSOAP( $this->soapUrl, $this->user, $this->pwd, $this->multiCallStackSize );
    return $this->soapClient->info();
  }

  private function orders( $type, $data ) {
    switch ( $type ) {
    case 'create':
      if ( isset( $data ) && isset( $data['data'] ) ) {
        $this->login();
        $orderData = json_decode( $data['data'], true );
        if ( isset( $orderData ) && isset( $orderData['orders'] ) && isset( $orderData['orders']['order'] ) ) {
          $this->soapClient->createOrder( $orderData['orders']['order'] );
        }
      }
      break;
    }
  }

  private function advanced( $type, $data = null ) {
    $mageSOAPAdvanced = new mageSOAPAdvanced();
    
    switch ( $type ) {
    case 'reindex':
      $mageSOAPAdvanced->reindexAll();
      echo "Reindex done\n";
      break;

    case 'images':
      return $mageSOAPAdvanced->getImageList();
      break;

    case 'cloaksimple':
      return $mageSOAPAdvanced->cloaksimpleproducts();
      break;

    case 'addcatnamestoproducts':
	  return $mageSOAPAdvanced->addcatnamestoproducts();
	  break;

    case 'firstaddcatnamestoproducts':
	    return $mageSOAPAdvanced->initcatproducts();
	    break;

    case 'orderstatusupdate':
	    return $mageSOAPAdvanced->updateorderstatus( $data );
	    break;

    case 'createurlkeys':
      return $mageSOAPAdvanced->createurlkeys( $data );
      break;

    case 'createurlrewrite':
    
      return $mageSOAPAdvanced->prepareurlrewrites( $data );
      break;

    case 'addImages':
      if ( isset( $data ) ) {
        return $mageSOAPAdvanced->assignImages( $data );
      }
      break;

    case 'numbers':
      return $mageSOAPAdvanced->getNumberList();
      break;

    case 'urllist':
      return $mageSOAPAdvanced->getUrlList();
      break;

    case 'configprods':
      return $mageSOAPAdvanced->getConfigList();
      break;

    case 'simpleprods':
      return $mageSOAPAdvanced->getSimpleList();
      break;

    case 'showStock':
      return $mageSOAPAdvanced->showStock();
      break;

    case 'deletespecials':
      return $mageSOAPAdvanced->deletespecials();
      break;

    case 'setStock':
      if ( isset( $data ) ) {
        return $mageSOAPAdvanced->setStock( $data );
      }
      break;

    case 'geturlkeylist':
	    return $mageSOAPAdvanced->geturlkeyjson( $data['path'] );
	    break;

    case 'orderByOID':
      if ( isset( $data ) && isset($data['increment_id'])) {
        return $mageSOAPAdvanced->getOrderByOID( $data['increment_id'] );
      }
      break;

    case 'categories':
      if ( isset( $data )) {
        return $mageSOAPAdvanced->updateCategories( $data );
      }
      break;

    case 'linkProductsToCategories':
      if ( isset( $data )) {
        return $mageSOAPAdvanced->linkProductsToCategories( $data );
      }
      break;

      case 'linkCrossselling':
      if ( isset( $data )) {
        return $mageSOAPAdvanced->linkCrossselling( $data );
      }
      break;

      case 'cleanUpProductFamilies':
      if ( isset( $data )) {
        return $mageSOAPAdvanced->cleanUpProductFamilies( $data );
      }
      break;

      case 'disableConfigurableProductsWithNoEnabledChildren':
        return $mageSOAPAdvanced->disableConfigurableProductsWithNoEnabledChildren();
      break;
    }
  }

  private function categories() {
    $type = 'tree';
    switch ( $type ) {
    case 'tree':
        $this->login();
          $tree = $this->soapClient->getCategoryTree();
          return $tree;
      break;
    }
  }
};
