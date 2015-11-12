<?php
$ch = curl_init();
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);


$time_start = microtime(true);

/* Test to Update an URL Key */
/* BEGIN */ /*
$productids = array(298722);
$soapCallString = '{"data": {"action" : "advanced", "type" : "createurlkeys", "data": ' . json_encode($productids) . '}}';
$soapResponse = doCurlCall("http://meyUser:93fediqd9@localhost/MeyTest/service/index.php",$soapCallString );
/* END */
/* Test to Update an URL Key  */


/* Test to Create an URL Rewrite */
/* BEGIN */ 
$productids = array(10530);
$soapCallString = '{"data": {"action" : "advanced", "type" : "createurlrewrite", "data": ' . json_encode($productids) . '}}';

$soapResponse = doCurlCall("http://SOAPTESTER:86c8c7153234f8c1c0c34c502a7ec1fb@127.0.0.1:8080/ultimo/service/index.php",$soapCallString );
/* END */
/* Test to Create a URL Rewrite  */



print_r($soapResponse);

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Das Bilden der notwendigen URL Keys brauchte $time Sekunden\n";


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

