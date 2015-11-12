<?php
$ch = curl_init();

$soapCallString = '{"data": {"action" : "advanced", "type" : "showStock"}}';
$soapResponse = doCurlCall("http://meyUser:93fediqd9@www.mey.com/service/index.php",$soapCallString );


var_dump($soapResponse);

function doCurlCall($url, $dataString){
  $curlHandler = curl_init();
    
  $curlOptions = array(
    CURLOPT_URL            => $url,
    CURLOPT_HTTPHEADER     => array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($dataString)
    ),
    CURLOPT_POSTFIELDS     => $dataString,
    CURLOPT_RETURNTRANSFER =>  true,
    CURLOPT_POST           => true,
    CURLOPT_FRESH_CONNECT  => true,
    CURLOPT_FORBID_REUSE   =>  true,  
  );
  curl_setopt_array($curlHandler, $curlOptions);
  
  $callResponse = curl_exec($curlHandler);
  curl_close($curlHandler);
  return $callResponse;
}



/*  $currentStockData = array(
    'sku'          => $sku,
    'ean'          => $currentProduct->getEan(),
    'manage_stock' => $currentStockItem->getManageStock(),
    'is_in_stock'  => $currentStockItem->getIsInStock(),
    'qty'          => $currentStockItem->getQty(),
  );


echo json_encode( $result, JSON_PRETTY_PRINT );
*/