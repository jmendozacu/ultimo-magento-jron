  <?php
class mageSOAP {
  private $proxy;
  private $session;
  private $url;
  private $user;
  private $apiKey;
  private $multiCallStackSize;
  private $currentAttributeSet;
  private $attributeInfo = [];
  private $storesInfo;
  private $attributes;

  //Konstruktor Methode
  function __construct( $initUrl, $initUser, $initKey, $multiCallStackSize ) 
  {
    if ( $initUrl && $initUser && $initKey && $multiCallStackSize ) 
    {
      $this->url    = $initUrl;
      $this->user   = $initUser;
      $this->apiKey = $initKey;
      $this->multiCallStackSize = $multiCallStackSize;
      define('MAGENTO', realpath( dirname( __FILE__ ) ));
      require_once MAGENTO . '/../app/Mage.php';
      Mage::app();
    }
    else 
    {
      exit( "missing login data" );
    }
  }

  //öffentliche Funktionen
  public function info()
  {
    return Mage::getModel('core/store_api')->info(1);
  }


  //Produkt Funktionen
  public function getProductList( $type = 'all', $additionalFilter = array() ) 
  {
    switch ( $type )
    {
    case 'master':
      $filter = array( 'type' => 'configurable' );
      return Mage::getModel('catalog/product_api')->items($filter);
      break;

    case 'variants':
      $filter = array_merge(array( 'type' => 'simple' ), $additionalFilter);
      return Mage::getModel('catalog/product_api')->items($filter);
      break;

    case 'all':
    default:
      return Mage::getModel('catalog/product_api')->items();
      break;
    }
  }

  public function addProductInfosToList( $productList ) 
  {
    $products = [];
    $api = Mage::getModel('catalog/product_api');
    //für jedes Produkt einen call zum laden der Produktinfos anlegen und anschließend einmal einen großen Call ausführen, Performancesteigerung
    foreach ( $productList as $currentProduct ) 
    {
      $attributes = array(
          'iid', 'ean', 'name', 'color', 'number', 'color_code', 'keywords', 'cat_hira_color', 'description', 'primary_size', 'secondary_size', 'third_size', 'filter_color', 'short_description', 'news_from_date', 'news_to_date', 'status',
          'brand', 'visibility', 'delivery_time', 'created_at', 'updated_at', 'price', 'special_price', 'special_from_date', 'special_to_date' );

      $products[] = $api->info($currentProduct['product_id'], null, $attributes);
    }
    return $products;
  }

  public function getMasterProducts() 
  {
    $returnValue = $this->getProductList( 'master' );
    $returnValue = $this->addProductInfosToList( $returnValue );
    return $returnValue;
  }

  public function createMasterProducts( $productList ) 
  {
    $returnList = [];
    $set = '4';
    $weight = '1';
    $callDataStack = [];
    $returnStack = [];
    $currentMasterProductFamilies = [];

    foreach ( $productList as $currentProduct ) 
    {
      //überprüfen ob bereits ein konfigurierbares Produkt für diese Familie vorhanden ist
      $productFamily = $currentProduct['product_family'];

      $filter = array( 'product_family' => $productFamily );
      $currentFamilyCheck = Mage::getModel('catalog/product_api')->items($filter);
      if ( sizeof( $currentFamilyCheck ) === 1  || in_array($productFamily, $currentMasterProductFamilies))
      {
        $returnStack[] = array(
          'product_family' => $currentProduct['product_family'],
          'sku'            => $currentProduct['sku']
        );
        continue;
      }
      else 
      {
        $sku = $currentProduct['sku'];
        $currentProduct['visibility'] = '4';
        $currentProduct['name'] = $currentProduct['name'];
        $currentProduct['description'] = '-';
        $currentProduct['short_description'] = '-';
        $currentProduct['price'] = '1';
        $currentProduct['tax_class_id'] = "1";
        $currentProduct['status'] = "1";

        //Produktdaten weiter aufbereiten und Defaultwerte ergänzen
        unset( $currentProduct['type'], $currentProduct['sku'] );
        $currentProduct['weight'] = $weight;
        $currentProduct['websites'] = $this->getWebsiteIdsForProduct($currentProduct);

        $returnList[] = Mage::getModel('catalog/product_api')->create('configurable', $set, $sku, $currentProduct);
        $callDataStack[] = array(
            'product_family' => $currentProduct['product_family'],
            'sku'            => $sku
        );

        $currentMasterProductFamilies[] = $currentProduct['product_family'];
      }
    }

    for ( $i=0, $iLength = sizeof( $returnList ); $i < $iLength ; $i++ )
    {
      $currentReturn = $returnList[$i];
      $currentReturnStackData = $callDataStack[$i];
      if ( !is_array( $currentReturn ) ) 
      {
        $returnStack[] = $currentReturnStackData;
      }      
    }
    return $returnStack;
  }

  public function getVariants() 
  {
    $returnValue = $this->getProductList( 'variants' );
    $returnValue = $this->addProductInfosToList( $returnValue );
    return $returnValue;
  }

  public function createVariants( $productList ) 
  {
    /*
        Es wird hier immer auf dem übergebenen Produkt weiter gearbeitet. Dies hat zur Folge,
        dass aus der Staging übergebene Attribute "durchgeschleust" werden, ohne hier namentlich
        aufzutauchen. 
        Dieses Verhalten ist bisher bekannt von:
          > cat_hira_color
          > keywords
          > news_from_date und news_to_date
          > special_from_date und special_to_date sowie special_price
    */
    $multiCallStack = [];
    $multiCalls = [];
    $returnList = [];
    $errorList = [];
    $callResponse = [];
    $set = '4';
    $weight = '1';
    $storesInfo = $this->getStores();

    foreach ( $productList as $currentProduct )
    {
      $responses = [];

      if(!array_key_exists('sku', $currentProduct) || empty($currentProduct['sku']))
      {
        $errorList[] = "Error : no sku";
        continue;
      }

      if(!array_key_exists('type', $currentProduct) || $currentProduct['type'] != 'simple')
      {
        $type = array_key_exists('type', $currentProduct) ? $currentProduct['type'] : 'NULL';
        $errorList[] = "Error : sku: ". $currentProduct['sku'] ." msg: invalid type: $type";
        continue;
      }

      $isValid = true;
      $type = $currentProduct['type'];
      $sku = $currentProduct['sku'];
      $mageProducts = $this->getProductList('variants', array('sku'=>$sku));
      $action = empty($mageProducts)? 'create' : 'update';

      //erforderliche Attribute überprüfen und gegebenenfalls Optionen anlegen
      //Hauptgrösse überprüfen und bei Bedarf anlegen, bisher nicht sprachabhängig gepflegt
      $currentProduct['color'] = $this->checkAttributeOption('color', $currentProduct['color'][0]);


      if (isset($currentProduct['care']))
      {
        $currentProduct['care_instructions'] = $this->checkAttributeOptionMulti( 'care_instructions', $currentProduct['care'] );
        unset( $currentProduct['care'] );
      }

      //Namen überprüfen

      if (isset($currentProduct['title']) && isset($currentProduct['title']['de']))
      {
        $currentProduct['name'] = $currentProduct['title']['de'];
      }

      foreach (['name', 'ean', 'price', 'sku', 'color', 'iid'] as $checkValue )
      {
        if ( !isset( $currentProduct[$checkValue] ) || $currentProduct[$checkValue] === false || $currentProduct[$checkValue] === '' )
        {
          $isValid = false;
          $sku = isset( $currentProduct['sku'] ) ? $currentProduct['sku'] : 'sku missing';
          $errorList[] = "Error : sku: $sku msg: $checkValue is not valid";
          continue;
        }
      }

      //Number zerlegen

      $splitNumber = explode("-", $currentProduct['number']);
      if(sizeof($splitNumber) != 2)
      {
        $errorList[] = "Error: sku: $sku msg: missing color_code";
        continue;
      }

      $currentProduct['number'] = $splitNumber[0];
      $currentProduct['color_code'] = $splitNumber[1];

      if ( $currentProduct['status'] == 'true' )
      {// sichtbar
        $currentProduct['status'] = '1';
      }
      else
      {// nicht sichtbar
        $currentProduct['status'] = '2';
      }

      //Storefinder-Flag
      if ( $currentProduct['storefinder'] == 'true' )
      {
        $currentProduct['storefinder'] = '1';
      }
      else
      {
        $currentProduct['storefinder'] = '0';
      }
      //optionale Attribute überprüfen
      if($currentProduct['primary_size'] != '')
      {
        $currentProduct['primary_size'] = $this->checkAttributeOption( 'primary_size', array( "admin" => $currentProduct['primary_size'] ) );
      }
      else
      {
        unset($currentProduct['primary_size']);
      }
      if ($currentProduct['secondary_size'] !== "")
      {
        $currentProduct['secondary_size'] = $this->checkAttributeOption( 'secondary_size', array( "admin" => $currentProduct['secondary_size'] ) );
      }
      else
      {
        unset($currentProduct['secondary_size']);
      }
      if ($currentProduct['third_size'] !== "")
      {
        $currentProduct['third_size'] = $this->checkAttributeOption( 'third_size', array( "admin" => $currentProduct['third_size'] ) );
      }
      else
      {
        unset($currentProduct['third_size']);
      }
      if (isset( $currentProduct['filter_color']) && !empty($currentProduct['filter_color']))
      {
        $currentProduct['filter_color'] = $this->checkAttributeAdminLabel( $currentProduct['filter_color'][0] );
        $currentProduct['filter_color'] = $this->checkAttributeOption( 'filter_color', $currentProduct['filter_color'] );
      }

      if (isset( $currentProduct['filter_material']))
      {
        $currentProduct['filter_material'] = $this->getAttributeOptionIDByAdminLabel('filter_material', $currentProduct['filter_material']);
      }

      if (isset( $currentProduct['department']))
      {
        $currentProduct['department'] = $this->getAttributeOptionIDByAdminLabel('department', $currentProduct['department']);
      }

      //Varianten(simple) - einzeln nicht sichtbar 1; Stammprodukte(configurable) überall sichtbar 4
      $currentProduct['visibility'] = '1';
      //Produktdaten weiter aufbereiten und Defaultwerte ergänzen
      unset( $currentProduct['type'], $currentProduct['sku'] );
      $currentProduct['weight'] = $weight;
      $currentProduct['websites'] = $this->getWebsiteIdsForProduct($currentProduct);
      $currentProduct['tax_class_id'] = "1";

      //Handling der Text
      $tmpDescription = $currentProduct['description'];
      $currentProduct['description'] = isset($tmpDescription['de']) ? $tmpDescription['de'] : "-";
      $tmpShortDescription = $currentProduct['shortdescription'];
      unset( $currentProduct['shortdescription'] );
      $currentProduct['short_description'] = isset( $tmpShortDescription['de'] ) ? $tmpShortDescription['de'] : "-";

      //Material1
      if( isset( $currentProduct['material1'] ) )
      {
        $tmpMaterial1 = $currentProduct['material1'];
        $currentProduct['material1'] = isset( $tmpMaterial1['de'] ) ? $tmpMaterial1['de'] : "";
      }

      //Material2
      if( isset( $currentProduct['material2'] ) )
      {
        $tmpMaterial2 = $currentProduct['material2'];
        $currentProduct['material2'] = isset( $tmpMaterial2['de'] ) ? $tmpMaterial2['de'] : "";
      }

      if(isset($currentProduct['brand']))
      {
        $tmpBrand = $currentProduct['brand'];
        $currentProduct['brand'] = $tmpBrand['de'];
      }

      //Sellingpoints

      $sellingPoints = [];
      if(isset($currentProduct['sellingpoint1'])) $sellingPoints[] = $currentProduct['sellingpoint1'];
      if(isset($currentProduct['sellingpoint2'])) $sellingPoints[] = $currentProduct['sellingpoint2'];
      if(isset($currentProduct['sellingpoint3'])) $sellingPoints[] = $currentProduct['sellingpoint3'];
      if(isset($currentProduct['sellingpoint4'])) $sellingPoints[] = $currentProduct['sellingpoint4'];
      if(isset($currentProduct['sellingpoint5'])) $sellingPoints[] = $currentProduct['sellingpoint5'];
      if(isset($currentProduct['sellingpoint6'])) $sellingPoints[] = $currentProduct['sellingpoint6'];
      if(isset($currentProduct['sellingpoint7'])) $sellingPoints[] = $currentProduct['sellingpoint7'];
      if(isset($currentProduct['sellingpoint8'])) $sellingPoints[] = $currentProduct['sellingpoint8'];
      if(isset($currentProduct['sellingpoint9'])) $sellingPoints[] = $currentProduct['sellingpoint9'];
      if(isset($currentProduct['sellingpoint10'])) $sellingPoints[] = $currentProduct['sellingpoint10'];
      if(isset($currentProduct['sellingpoint11'])) $sellingPoints[] = $currentProduct['sellingpoint11'];
      if(isset($currentProduct['sellingpoint12'])) $sellingPoints[] = $currentProduct['sellingpoint12'];
      if(isset($currentProduct['sellingpoint13'])) $sellingPoints[] = $currentProduct['sellingpoint13'];
      if(isset($currentProduct['sellingpoint14'])) $sellingPoints[] = $currentProduct['sellingpoint14'];
      if(isset($currentProduct['sellingpoint16'])) $sellingPoints[] = $currentProduct['sellingpoint16'];
      if(isset($currentProduct['sellingpoint17'])) $sellingPoints[] = $currentProduct['sellingpoint17'];
      if(isset($currentProduct['sellingpoint18'])) $sellingPoints[] = $currentProduct['sellingpoint18'];
      if(isset($currentProduct['sellingpoint19'])) $sellingPoints[] = $currentProduct['sellingpoint19'];
      if(isset($currentProduct['sellingpoint20'])) $sellingPoints[] = $currentProduct['sellingpoint20'];

      $concatSellingpoints = ['de' => '', 'en' => '', 'nl' => ''];
      $defaultSellingpointText = ['de' => '<li>Keine Details vorhanden</li>', 'en' => '<li>No details available</li>', 'nl' => '<li>Geen gegevens beschikbaar</li>'];

      foreach($sellingPoints as $currentSellingPoint)
      {
        if(!empty($currentSellingPoint['de']))
        {
          $concatSellingpoints['de'] .= '<li>'.$currentSellingPoint['de'].'</li>';
        }
        if(!empty($currentSellingPoint['en']))
        {
          $concatSellingpoints['en'] .= '<li>'.$currentSellingPoint['en'].'</li>';
        }
        if(!empty($currentSellingPoint['nl']))
        {
          $concatSellingpoints['nl'] .= '<li>'.$currentSellingPoint['nl'].'</li>';
        }
      }

      $currentProduct['sellingpoints'] = '<ul>';
      if(empty($concatSellingpoints['de']))
      {
        $currentProduct['sellingpoints'] .= $defaultSellingpointText['de'];
      }
      else
      {
        $currentProduct['sellingpoints'] .= $concatSellingpoints['de'];
      }
      $currentProduct['sellingpoints'] .= '</ul>';

      //badge, audience - Auszeichnungen, Gütesiegel etc, welche dann als Block angezeigt werden
      if ( isset( $currentProduct['audience'] ) )
      {
        $audienceIds = [];
        for ($i=0, $iLength = sizeof($currentProduct['audience']); $i < $iLength; $i++)
        {
          $badgeName = $currentProduct['audience'][$i];
          $audienceIds[] = $this->checkAttributeOption( 'badge', array( "admin" => $badgeName ) );
        }
        unset($currentProduct['audience'] );
        $currentProduct['badge'] = $audienceIds;
      }


      if ( $isValid )
      {
        if ( $action === 'create' )
        {
          $call = array( 'catalog_product.create', array( $type , $set , $sku, $currentProduct ) );
          $responses[] = Mage::getModel('catalog/product_api')->create($type, $set, $sku, $currentProduct);
          $multiCallStack[] = $call;
        }
        else if ( $action === 'update' )
        {
          $call = array( 'catalog_product.update', array( $sku, $currentProduct ) );
          $responses[] = Mage::getModel('catalog/product_api')->update($sku, $currentProduct);
          $multiCallStack[] = $call;
        }

        //Sprachabhängige Texte updaten
        foreach ( $storesInfo as $currentStore )
        {
          $storeCode = $this->getLanguageFromStoreCode($currentStore['code']);
          if ( $storeCode !== 'admin' && $storeCode !== 'de' )
          {
            $tmpData = array();

            if( isset($currentProduct['title']) && isset($currentProduct['title'][$storeCode]) )
            {
              $tmpData['name'] = $currentProduct['title'][$storeCode];
            }

            if ( isset( $tmpDescription ) && isset( $tmpDescription[$storeCode] ) )
            {
              $tmpData['description'] = $tmpDescription[$storeCode];
            }

            if ( isset( $tmpShortDescription ) && isset( $tmpShortDescription[$storeCode] ) )
            {
              $tmpData['short_description'] = $tmpShortDescription[$storeCode];
            }

            if ( isset( $tmpMaterial1 ) && isset( $tmpMaterial1[$storeCode] ) )
            {
              $tmpData['material1'] = $tmpMaterial1[$storeCode];
            }

            if ( isset( $tmpMaterial2 ) && isset( $tmpMaterial2[$storeCode] ) )
            {
              $tmpData['material2'] = $tmpMaterial2[$storeCode];
            }

            if ( isset( $tmpBrand ) && isset( $tmpBrand[$storeCode]))
            {
              $tmpData['brand'] = $tmpBrand[$storeCode];
            }

            $tmpData['sellingpoints'] = '<ul>';

            if(empty($concatSellingpoints[$storeCode]))
            {
              $tmpData['sellingpoints'] .= $defaultSellingpointText[$storeCode];
            }
            else
            {
              $tmpData['sellingpoints'] .= $concatSellingpoints[$storeCode];
            }

            $tmpData['sellingpoints'] .= '</ul>';

	          $storeCode = $currentStore['code'];

            $call = array( 'catalog_product.update', array( $sku, $tmpData, $storeCode ) );
            $responses[] = Mage::getModel('catalog/product_api')->update($sku, $tmpData, $storeCode);
            $multiCallStack[] = $call;
          }

        }

        //Bei NOS Attributen wird sofort das erforderliche Attribute gesetzt
        if($currentProduct['nos'] == "true")
        {
          $call = array('product_stock.update', array(
            $sku,
            array(
              'qty'          => 0,
              'is_in_stock'  => true,
              'manage_stock' => false,
              'use_config_manage_stock' => false
            )
          ));
          $responses[] = Mage::getModel('cataloginventory/stock_item_api')->update($sku, array(
              'qty'          => 0,
              'is_in_stock'  => true,
              'manage_stock' => false,
              'use_config_manage_stock' => false
            )
          );
          $multiCallStack[] = $call;
        }
        elseif($action === 'create')
        {
          $call = array('product_stock.update', array(
            $sku,
            array(
              'qty'          => 0,
              'is_in_stock'  => false,
              'manage_stock' => true,
              'use_config_manage_stock' => false
            )
          ));
          $responses[] = Mage::getModel('cataloginventory/stock_item_api')->update($sku, array(
              'qty'          => 0,
              'is_in_stock'  => false,
              'manage_stock' => true,
              'use_config_manage_stock' => false
            )
          );
          $multiCallStack[] = $call;
        }

        //Geschenkverpackung hinzufügen
        if(isset($currentProduct['packing']) && isset($currentProduct['packing']['packingname']) && isset($currentProduct['packing']['packingprice']) && isset($currentProduct['packing']['packingid']))
        {
          $customDropdownOption = array(
            "title" => $currentProduct['packing']['packingname'],
            "type" => "checkbox",
            "is_require" => 0,
            "sort_order" => 10,
            "additional_fields" => array(
              array(
                "title" => "1",
                "price" => ($currentProduct['packing']['packingprice'] / 100),
                "price_type" => "fixed",
                "sku" => $currentProduct['packing']['packingid'],
                "sort_order" => 0
              )
            )
          );

          if ( $action === 'create' )
          {
            $call = array( 'product_custom_option.add', array( $sku, $customDropdownOption ) );
            $responses[] = Mage::getModel('catalog/product_option_api')->add($sku, $customDropdownOption);
            $multiCallStack[] = $call;
          }
          else if ( $action === 'update' )
          {
            $customOptionList =  Mage::getModel('catalog/product_option_api')->items($sku);

            if(sizeof($customOptionList) == 1 && isset($customOptionList[0]['title']) && $customOptionList[0]['title'] === $currentProduct['packing']['packingname'])
            {
              $customOptionValueList =  Mage::getModel('catalog/product_option_value_api')->items($customOptionList[0]['option_id']);

              if(sizeof($customOptionValueList) == 1 && isset($customOptionValueList[0]['value_id']) )
              {
                $call = array( 'product_custom_option_value.update', array( $customOptionValueList[0]['value_id'], array(
                  "title" => "&nbsp;",
                  "price" => ($currentProduct['packing']['packingprice'] / 100),
                  "price_type" => "fixed",
                  "sku" => $currentProduct['packing']['packingid'],
                  "sort_order" => 0
                ) ) );
                $responses[] = Mage::getModel('catalog/product_option_value_api')->update($customOptionValueList[0]['value_id'], array(
                  "title" => "&nbsp;",
                  "price" => ($currentProduct['packing']['packingprice'] / 100),
                  "price_type" => "fixed",
                  "sku" => $currentProduct['packing']['packingid'],
                  "sort_order" => 0
                ));
                $multiCallStack[] = $call;
              }
            }
          }
        }
      }
      $callResponse = array_merge($callResponse, $responses);
      $multiCalls = array_merge($multiCalls, $multiCallStack);
      $multiCallStack = [];
    }

    for ( $i = 0, $max = sizeof( $callResponse ); $i < $max; $i++ )
    {
      $success = $callResponse[$i];
      if ( $success ) 
      {
        $data = $multiCalls[$i];

        if ( isset( $data ) && isset( $data[0] ) && $data[0] === 'catalog_product.create' && isset( $data[1] ) && isset( $data[1][2] ) ) 
        {
          $returnList[] = $data[1][2];
        }
        if ( isset( $data ) && isset( $data[0] ) && $data[0] === 'catalog_product.update' && isset( $data[1] ) && isset( $data[1][0] ) ) 
        {
          $returnList[] = $data[1][0];
        }
      }
    }
    $returnList = array_unique( $returnList );

    $returnValue = array(
      'success' => $returnList,
      'error'   => $errorList
      );


    return $returnValue;
  }

  //Einfache Produkte mit ihren konfigurierbaren verlinken
  public function linkVariants( $productList ) 
  {
    $linkReport = ['missing' => [], 'duplicate' => [], 'unknownErrors' => false, 'missingVariantsCount' => 0, 'skus'=>[]];
    $validProductList = [];
    $skuListVariants = [];
    $multiCallStack = [];
    $skuListMaster = [];
    $returnList = [];
    $missedVariants = [];
    $isLinked = false;
    
    //Überprüfen ob zu verlinkende Varianten vorhanden
    $productListSimple = $this->getProductList( 'variants' ); // auslesen Liste aller Varianten aus Magento 
    $productListMaster = $this->getProductList( 'master' );

    
    for ($i=0, $iLength = sizeof($productListSimple); $i < $iLength; $i++) 
    { // Liste aller SKUs von Varianten aus Magento (simple)
      $skuListVariants[] = $productListSimple[$i]['sku'];
    }

    for ($i=0, $iLength = sizeof($productListMaster); $i < $iLength; $i++) 
    { // Liste aller SKUs von Produktfamilien aus Magento (configurable)
      $skuListMaster[] = $productListMaster[$i]['sku'];
    }

    // alle Produktfamilien aus Staging verlinken
    foreach ( $productList as $currentProduct )
    {
      if (!empty($currentProduct['variants']))
      { // hat die Produktfamilie Varianten?
        $variants = $currentProduct['variants'];  // Liste von SKU's
        $validVariants = [];
        $pfSkus = $currentProduct['skus'];

        // existiert eine der SKUs in Magento, welche?
        $result = array_intersect($pfSkus, $skuListMaster);
        if(sizeof($result) != 1)
        { // zuviel oder keine SKU(s) gefunden
          if(empty($result))
          {
            $linkReport['missing'][] = $pfSkus;
          }
          else
          {
            $linkReport['duplicate'][] = $result;
          }

          continue;
        }

        // array_intersect übernimmt die Offsets der Treffer - dieser muss erst ermittelt werden
        $tmp = array_keys($result);
        $sku = $result[$tmp[0]];

        $linkReport['skus'][$sku] = [   'raw' => $currentProduct['raw'],
          'success' => false,
          'missingVariants' => [],
          'errorMessage' => ''
        ];

        $validVariants = array_values(array_intersect($variants, $skuListVariants));

        if(sizeof($validVariants) != sizeof($variants))
        { // mind. eine Variante wurde nicht gefunden
          $linkReport['skus'][$sku]['missingVariants'] = array_values(array_diff($variants, $validVariants));
          $linkReport['missingVariantsCount'] += sizeof($linkReport['skus'][$sku]['missingVariants']);
        }

        if(!empty($validVariants))
        { // Wenn es gültige Varianten gibt,
          // Set von Eigenschaften dieser Variante auslesen und an Produktfamilien-Datensatz schreiben
          $firstLinkedProduct = $validVariants[0];

          $returnList[] = Mage::getModel('catalog/product_api')->info($firstLinkedProduct, null, array(
            'name', 'description', 'short_description', 'categories', 'price',
            'number', 'color_code', 'iid', 'material1', 'material2', 'sellingpoints', 'color',
            'primary_size', 'secondary_size', 'third_size','url_key','style', 'keywords', 'websites')
          );
          $currentProduct['validVariants'] = $validVariants;
          unset($currentProduct['variants'],$currentProduct['skus']);
          $currentProduct['sku'] = $sku;
          $validProductList[] = $currentProduct;
        }
      }
      // Was passiert mit PF's wenn keine Varianten existieren?
    }

    $callResponse = [];
    for ( $i=0, $iLength = sizeof( $validProductList ); $i < $iLength; $i++ ) // für alle gültigen Produkte
    {
      $superAttributes = []; // Pflichtfelder für Erstellen einer Variante
      $currentProduct = $validProductList[$i];  // Liste Produkte hat selbe Reihenfolge
      $variantInfo = $returnList[$i];           // wie Liste geladender Variantendaten
      
      if ( isset( $variantInfo['secondary_size'] ) ) 
      { // BH (secondary_size = Körbchengröße)
        $superAttributes = ['color', 'secondary_size', 'third_size'];
      }
      else 
      { // anderer Artikel
        $superAttributes = ['color', 'primary_size'];
      }
      $superAttributes = $this->convertAttributeNamesToIds( $superAttributes );

      $updateData = array(
        'websites' => $variantInfo['websites'],
        'associated_skus'         => $currentProduct['validVariants'],
        'configurable_attributes' => $superAttributes,
        'name'                    => $variantInfo['name'],
        'description'             => $variantInfo['description'],
        'short_description'       => $variantInfo['short_description'],
    //    'categories'              => $variantInfo['categories'],
        'price'                   => $variantInfo['price'],
        'number'                  => $variantInfo['number'],
        'color_code'              => $variantInfo['color_code'],
        'iid'                     => $variantInfo['iid'],
        'material1'               => $variantInfo['material1'],
        'material2'               => $variantInfo['material2'],
        'sellingpoints'           => $variantInfo['sellingpoints'],
        //'url_key'                 => '',//$variantInfo['url_key'], -> funktioniert noch nicht, TODO für SEO
        'style'                   => $variantInfo['style'],
        'keywords'                => $variantInfo['keywords'],
      );

      $multiCallStack[] = array( 'catalog_product.update', // Funktion
        array( // Parameter
          $currentProduct['sku'],
          $updateData,
        )
      );
      $callResponse[] = Mage::getModel('catalog/product_api')->update($currentProduct['sku'], $updateData);
    }

    // Auswertung von multiCall-Ergebnis
    //
    // Aufbau Response:
    //  erfolgreich: TRUE
    //  fehlerhaft:
    //    isFault: bool = TRUE
    //    faultCode: string = "101"
    //    faultMessage: string = "Product not exists."
    for ( $i = 0, $max = sizeof( $callResponse ); $i < $max; $i++ ) 
    {
      $result = $callResponse[$i];
      $data = $multiCallStack[$i];

      if(isset($data) && isset($data[0]) && isset($data[1]) && isset($data[1][0])) 
      {
        $sku = $data[1][0];
      }
      else
      {
        $linkReport['unknownErrors'] = true;  
      }
      
      if( $result === true ) 
      { // wenn erfolgreich 
        $linkReport['skus'][$sku]['success'] = true;
      }
      else
        {
        $linkReport['skus'][$sku]['errorMessage'] = $result['faultMessage'];        
        }
      }
    return $linkReport;
    }


  private function convertAttributeNamesToIds( $attributeList )
  {
    $attributeSet = isset( $this->currentAttributeSet ) ? $this->currentAttributeSet : $this->currentAttributeSet = $this->getAttributeSet();
    $attributes = $this->getAttributes( $attributeSet['set_id'] );
    $attributeIds = [];
    for ( $i=0, $iLength = sizeof( $attributeList ); $i < $iLength; $i++ ) 
    {
      for ( $k = 0, $kLength = sizeof( $attributes ); $k < $kLength; $k++ ) 
      {
        if ( $attributes[$k]['code'] === $attributeList[$i] ) 
        {
          $attributeIds[] = $attributes[$k]['attribute_id'];
        }
      }
    }
    return $attributeIds;
  }


  public function debugTest( $value ) 
  {
    $filter = array( 'product_family' => $value );
    $return = Mage::getModel('catalog/product_api')->items($filter);
    var_dump( $return );
  }

  //Bestellung anlegen aus Alvine nach Magento
  public function createOrder( $orderData ) 
  {
    //Daten überprüfen
    $checkValues = ['customer_email', 'customer_firstname', 'customer_lastname'];
    foreach ( $checkValues as $value ) 
    {
      if ( !in_array( $value, array_keys( $orderData ) ) ) 
      {
        return array( 'Error' => 'Unable create Order missing ' . $value );
      }
    }

    //Warenkorb erzeugen
    $shoppingCartId = Mage::getModel('checkout/cart_api')->create('de');

    //Kunde erzeugen
    if ( isset( $orderData['customer_id'] ) && $orderData['customer_id'] != 0 )
    {
      $customer = array(
        'firstname' => $orderData['customer_firstname'],
        'lastname'  => $orderData['customer_lastname'],
        'email'     => $orderData['customer_email'],
        'website_id' => '0',
        'store_id'   => '0',
        'mode'       => 'customer',
        'entity_id'  => $orderData['customer_id'],
      );
    }
    else
    {
      $customer = array(
        'firstname' => $orderData['customer_firstname'],
        'lastname'  => $orderData['customer_lastname'],
        'email'     => $orderData['customer_email'],
        'website_id' => '0',
        'store_id'   => '0',
        'mode'       => 'guest',
      );
    }

    $resultCustomerSet = Mage::getModel('checkout/cart_customer_api')->set($shoppingCartId, $customer);

    if ( !$resultCustomerSet ) 
    {
      return array( 'Error' => 'could not create customer' );
    };

    //Adressen zuweisen
    $addresses = array(
      array(
        'mode'                => 'shipping',
        'firstname'           => isset( $orderData['shipping_address']['firstname'] ) ? $orderData['shipping_address']['firstname'] : '-',
        'lastname'            => isset( $orderData['shipping_address']['lastname'] ) ? $orderData['shipping_address']['lastname'] : '-',
        //'company'             => isset($orderData['shipping_address']['company']) ? $orderData['shipping_address']['company'] : '-',
        'street'              => isset( $orderData['shipping_address']['street'] ) ? $orderData['shipping_address']['street'] : '-',
        'city'                => isset( $orderData['shipping_address']['city'] ) ? $orderData['shipping_address']['city'] : '-',
        'postcode'            => isset( $orderData['shipping_address']['postcode'] ) ? $orderData['shipping_address']['postcode'] : '-',
        'country_id'          => isset( $orderData['shipping_address']['country_id'] ) ? strtoupper( $orderData['shipping_address']['country_id'] ) : 'DE',
        'telephone'           => isset( $orderData['shipping_address']['telephone'] ) ? $orderData['shipping_address']['telephone'] : '-',
        'is_default_shipping' => 0,
        'is_default_billing'  => 0
      ),
      array(
        'mode'                => 'billing',
        'firstname'           => isset( $orderData['billing_address']['firstname'] ) ? $orderData['billing_address']['firstname'] : '-',
        'lastname'            => isset( $orderData['billing_address']['lastname'] ) ? $orderData['billing_address']['lastname'] : '-',
        //'company'             => isset($orderData['billing_address']['company']) ? $orderData['billing_address']['company'] : '-',
        'street'              => isset( $orderData['billing_address']['street'] ) ? $orderData['billing_address']['street'] : '-',
        'city'                => isset( $orderData['billing_address']['city'] ) ? $orderData['billing_address']['city'] : '-',
        'postcode'            => isset( $orderData['billing_address']['postcode'] ) ? $orderData['billing_address']['postcode'] : '-',
        'country_id'          => isset( $orderData['billing_address']['country_id'] ) ? strtoupper( $orderData['billing_address']['country_id'] ) : 'DE',
        'telephone'           => isset( $orderData['billing_address']['telephone'] ) ? $orderData['billing_address']['telephone'] : '-',
        'is_default_shipping' => 0,
        'is_default_billing'  => 0
      )
    );
    $resultCustomerAddresses = Mage::getModel('checkout/cart_customer_api')->setAddresses($shoppingCartId, $addresses);
    if ( !$resultCustomerAddresses )
    {
      return array( 'Error' => 'could not create customer addresses' );
    };

    //Produkte zuweisen
    $items = isset( $orderData['items'] ) && isset( $orderData['items']['sku'] ) ? array( $orderData['items'] ) : $orderData['items'];
    if ( isset( $this->productListSimple ) )
    {
      $productListSimple = $this->productListSimple;
    }
    else 
    {
      $this->productListSimple = $this->getProductList( 'products' );
      $productListSimple = $this->productListSimple;
    }

    $cartItems = [];
    if ( isset( $items ) && count( $items ) !== 0 )
    {
      foreach ( $items as $currentItem )
      {
        foreach ( $productListSimple as $product ) 
        {
          if ( $currentItem['sku'] == $product['sku'] ) 
          {
            $cartItems[] = array(
              'product_id' => $product['product_id'],
              'qty' => $currentItem['qty_ordered']
            );
          }
        }
      }
    }

    $resultCartProductAdd = Mage::getModel('checkout/cart_product_api')->add($shoppingCartId, $cartItems);
    if ( !$resultCartProductAdd || !is_bool( $resultCartProductAdd ) )
    {
      return array( 'Error' => 'Unable to add products to cart ' );
    }

    //Versandmethode zuweisen Methoden erst nach Produkten und Kundenangaben abrufbar
    $shippingMethods = Mage::getModel('checkout/cart_shipping_api')->getShippingMethodsList($shoppingCartId);
    foreach ( $shippingMethods as $shippingMethod )
    {
      if ( $shippingMethod['code'] == $orderData['shipping_method'] ) 
      {
        $selectedShippingMethod = $shippingMethod['code'];
      }
    }

    if ( !isset( $selectedShippingMethod ) ) 
    {
      return array( 'Error' => 'Unable to select shipping method ' . $orderData['shipping_method'] );
    }

    $resultShippingMethod = Mage::getModel('checkout/cart_shipping_api')->setShippingMethod($shoppingCartId, $selectedShippingMethod);
    if ( !$resultShippingMethod || !is_bool( $resultShippingMethod ) )
    {
      return array( 'Error' => 'Unable to assign shipping method ' . $orderData['shipping_method'] );
    };

    //Zahlungsmethode zuweisen
    $paymentMethods = Mage::getModel('checkout/cart_payment_api')->getPaymentMethodsList($shoppingCartId);

    //dreckiger dreckiger Payment-Hack
    $orderData['payment_name'] = 'bankpayment';

    foreach ( $paymentMethods as $paymentMethod ) 
    {
      if ( $paymentMethod['code'] == $orderData['payment_name'] )
      {
        $selectedPaymentMethod = array(
          'method' => $paymentMethod['code']
        );
      }
    }

    if ( !$selectedPaymentMethod )
    {
      return array( 'Error' => 'Unable to select payment method ' . $orderData['payment_name'] );
    }

    $resultPaymentMethod = Mage::getModel('checkout/cart_payment_api')->setPaymentMethod($shoppingCartId, $selectedPaymentMethod);
    if ( !$resultPaymentMethod || !is_bool( $resultPaymentMethod ) )
    {
      return array( 'Error' => 'Unable to assign payment method ' . $orderData['payment_name'] );
    };

    //Bestellung abschicken
    $resultOrderCreation = Mage::getModel('checkout/cart_api')->createOrder($shoppingCartId, null, array(1)); //array(1) ist das akzeptieren der AGB
    return array( 'Success' => $resultOrderCreation );
  }

  //private Hilfsfuntionen
  //Die Magento SOAP bietet die Möglichkeit, Aufrufe der SOAP zu bündeln. Damit spart man deutlich Overhead und Performance.
  //Allerdings tendieren diese kombinierten Calls ab einer bestimmten Größe auch dazu, langsamer zu werden.
  //Daher wird mit dieser Funktion ein MultiCall nahand einer Konstante in mehrere aufgeteilt um damit das optimale Performance-Ergebnis zu erzielen
  // TODO: Remove after all users are changed or removed
  private function doMultiCall( $multiCallStack ) 
  {
    $productResponse = [];
    while ( count( $multiCallStack ) != 0 ) 
    {
      $stackLength = count( $multiCallStack ) < $this->multiCallStackSize ? count( $multiCallStack ) :$this->multiCallStackSize;
      $productResponse =  array_merge( $productResponse, $this->proxy->multiCall( $this->session, array_splice( $multiCallStack, 0, $stackLength ) ) );
    }
    return $productResponse;
  }

  private function getAttributeOptionIDByAdminLabel( $attributeName, $attributeValue )
  {
    $attributeInfo = $this->getAttributeInfo( $attributeName );
    $attributeOptionId = null;
    foreach ($attributeInfo as $option) 
    {
      if($option['admin']['label'] === $attributeValue)
      {
        $attributeOptionId = $option['admin']['value']; 
      }
    }
    return $attributeOptionId;
  }

  // aus allen Farb-Labels wird ein hash gebildet und als admin-wert gesetzt
  private function checkAttributeAdminLabel( $currentOption ) 
  {
    if ( !isset( $currentOption['admin'] ) ) 
    {
      $newAdminValue = "";
      foreach ( $currentOption as $currentOptionValue ) 
      {
        $newAdminValue .= $currentOptionValue;
      }
      $currentOption['admin'] = hash( 'crc32', $newAdminValue );
    }
    return $currentOption;
  }

  private function checkAttributeAdminLabelMulti( $attributeOption )
  {
    foreach ( $attributeOption as &$currentOption ) 
    {
      $currentOption = $this->checkAttributeAdminLabel( $currentOption );
    }
    return $attributeOption;
  }

  private function createAttributeOption( $attributeName, $newAttributeOption ) 
  {
    //neue Attribute Option anlegen
    $storesInfo = $this->getStores();
    $tmpData = [];
    $returnValue = false;
    foreach ( $newAttributeOption as $key => $value ) 
    {
      foreach ( $storesInfo as $currentStore ) 
      {
        if ( $this->getLanguageFromStoreCode($currentStore['code']) == $key && isset( $value ) )
        {
          array_push( $tmpData, array(
              "store_id" => $currentStore['store_id'],
              "value" => $value
            ) );
        }
      }
    }

    $data = array( "label" => $tmpData );
    $returnValue = Mage::getModel('catalog/product_attribute_api')->addOption($attributeName, $data);
    return $returnValue;
  }

  private function checkAttributeOptionMulti( $attributeName, $attributeOption ) 
  {
    foreach ( $attributeOption as &$currentNewOption ) 
    { 
      $currentNewOption = $this->checkAttributeOption( $attributeName, $currentNewOption );
    }
  
    return $attributeOption;
  }


  // Prüfung ob es für dieses Attribut-Option eine ID gibt
  // ist noch keine vorhanden, wird eine angelegt
  //
  // ist bereits eine vorhanden, werden neuer und alter Wert verglichen
  // bei Abweichungen wird alt durch neu ersetzt
  private function checkAttributeOption( $attributeName, $attributeOption ) 
  {
    $attributeInfo = $this->getAttributeInfo( $attributeName );
    $attributeOptionValue = null;
    $storesInfo = $this->getStores();

    $adminLabel = $attributeOption['admin'];
    $isNew = true;
    //crc für neue daten erzeugen
    $crc = "";
    foreach ( $attributeOption as $key => $current ) 
    {
      if ( $key != 'admin' ) 
      {
        $crc .= $current === $adminLabel ? "" : $current;
      }
    }
    $attributeOption['crc'] = md5( $crc );
    if(isset($attributeInfo))
    {
      foreach ( $attributeInfo as $optionKey => $currentAttributeInfo ) 
      {
        $currentAttributeInfoAdminLabel = $currentAttributeInfo['admin']['label'];

        if ( $adminLabel == $currentAttributeInfoAdminLabel && is_array( $attributeOption ) ) 
        {
          $isNew = false;
          if ( $currentAttributeInfo['crc'] === $attributeOption['crc'] )
          {
            $attributeOption = $optionKey;
          }
          else 
          {
            //alte Option löschen
            $returnValue = Mage::getModel('catalog/product_attribute_api')->removeOption($attributeName, $optionKey);
            //Option mit neuen Werten neu anlegen
            $returnValue = $this->createAttributeOption( $attributeName, $attributeOption );
            if ( $returnValue ) 
            {
              $attributeInfo = $this->getAttributeInfo( $attributeName, true );
              foreach ( $attributeInfo as $key => $value ) 
              {
                if ( $value['crc'] === $attributeOption['crc'] ) 
                {
                  $attributeOption = $key;
                }
              }
            }
          }
        }
      }
    }

    if ( $isNew ) 
    {
      $returnValue = $this->createAttributeOption( $attributeName, $attributeOption );
      if ( $returnValue ) 
      {
        $attributeInfo = $this->getAttributeInfo( $attributeName, true );
        foreach ( $attributeInfo as $key => $value ) 
        {
          if ( $value['crc'] === $attributeOption['crc'] ) 
          {
            $attributeOption = $key;
          }
        }
      }
    }
    return $attributeOption;
  }

  private function getAttributes( $setId ) 
  {
    $attributes = $this->attributes;
    if ( !isset( $attributes ) ) 
    {
      $attributes = Mage::getModel('catalog/product_attribute_api')->items($setId);
      $this->attributes = $attributes;
    }
    return $attributes;
  }

  private function getAttributeSet( $setName = 'Default' ) 
  {
    //gesuchtes Set zurück geben oder Default falls kein setName übergeben
    //falls Default nicht vorhanden wird Set auf das der Zeiger gesetzt ist ausgegeben
    $attributeSets = Mage::getModel('catalog/product_attribute_set_api')->items();
    $returnSet = current( $attributeSets );
    foreach ( $attributeSets as $key => $value ) 
    {
      if ( $value['name'] == $setName ) 
      {
        $returnSet = $attributeSets[$key];
      }
    }
    return $returnSet;
  }

  private function createAttributeCRC( $attributeInfo )
  {
    foreach ( $attributeInfo as &$current ) 
    {
      $crc = "";
      $adminLabel = $current['admin']['label'];
      foreach ( $current as $storeCode => $option )
      {
        if($option['label'] != $adminLabel && $storeCode == $this->getLanguageFromStoreCode($storeCode))
        {
          $crc .= $option["label"];
        }
      }
      $current['crc'] = md5( $crc );
    }
    return $attributeInfo;
  }

  private function getAttributeId( $attributeName ) 
  {
    $attributeSet = isset( $this->currentAttributeSet ) ? $this->currentAttributeSet : $this->currentAttributeSet = $this->getAttributeSet();
    $attributes = $this->getAttributes( $attributeSet['set_id'] );
    $attributeId = null;

    for ( $i = 0, $max = count( $attributes ); $i < $max; $i++ ) 
    {
      $currentAttribute = $attributes[$i];
      if ( $currentAttribute['code'] == $attributeName ) 
      {
        $attributeId = $currentAttribute['attribute_id'];
      }
    }
    return $attributeId;
  }

  private function getAttributeInfo( $attributeName, $renew = false ) 
  {
    $multiCallStack = [];
    $storesInfo = $this->getStores();

    if ( $renew === false && isset( $this->attributeInfo[$attributeName] ) ) 
    {
      return $this->attributeInfo[$attributeName];
    }

    $attributeId = $this->getAttributeId( $attributeName );

    if ( $attributeId ) 
    {
      $attributeInfo = [];
      foreach ( $storesInfo as $currentStore )
      {
        $attributeInfo[] = Mage::getModel('catalog/product_attribute_api')->options($attributeId, $currentStore['code']);
      }

      $count = 0;
      $sortedAttributeInfo = [];
      foreach ( $attributeInfo as $currentAttributeInfo ) 
      {
        foreach ( $currentAttributeInfo as $currentAttributeInfoOption ) 
        {
          $optionId = $currentAttributeInfoOption['value'];
          if ( isset( $optionId ) && $optionId !== "" ) 
          {
            if ( !isset( $sortedAttributeInfo[$optionId] ) )
            {
              $sortedAttributeInfo[$optionId] = array();
            }

            $sortedAttributeInfo[$optionId][$storesInfo[$count]['code']] = $currentAttributeInfoOption;
          }
        }
        $count++;
      }

      //crc zum vergleichen erzeugen
      $returnData = $this->createAttributeCRC( $sortedAttributeInfo );
      $this->attributeInfo[$attributeName] = $returnData;
      return $returnData;
    }
  }

  //Kategorie Funktionen
  public function getCategoryTree()
  {
    return Mage::getModel('catalog/category_api')->tree();
  }

  //Benutzer-Funktionen
  public function getCustomerList()
  {
    return Mage::getModel('customer/customer_api')->items(null);
  }

  //allgemeine Store Funktionen
  private function getStores() 
  {
    $storesInfo = $this->storesInfo;
    if ( !isset( $storesInfo ) ) 
    {
      $storesInfo = Mage::getModel('core/store_api')->items();
      $storesInfo[] = array(
        'store_id' => '0',
        'code' => 'admin',
        'name' => 'admin'
      );
    }
    $this->storesInfo = $storesInfo;
    return $storesInfo;
  }

  private function getWebsiteIdsForProduct(array $product) {
    if(array_key_exists("brand", $product) && is_array($product["brand"]) && array_key_exists("de", $product["brand"]) && $product["brand"]["de"] == "Mey Story") {
      return array(2);
    } else {
      if (array_key_exists("b2bnos_flag", $product) && $product["b2bnos_flag"] == "true") {
        return array(1, Mage::helper("mey_b2b")->getWebsiteId());
      }
      return array(1);
    }
  }

	private function getLanguageFromStoreCode ($storeCode) {
		return array_pop(explode('_', $storeCode));
	}
}
