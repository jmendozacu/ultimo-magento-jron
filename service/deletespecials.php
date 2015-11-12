<?php
$ch = curl_init();
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);

/* Online Block BEGIN */ 
  $SOAPPath = "http://meyUser:93fediqd9@www.mey.com/service/index.php";
/* Online BLock END */

/* Lokal Block BEGIN */ /*
  $SOAPPath = "http://meyUser:93fediqd9@localhost/MeyTest/service/index.php";
/* Lokal BLock END */

$soapCallString = '{"data": {"action" : "advanced", "type" : "deletespecials"}}';
$soapResponse = doCurlCall($SOAPPath,$soapCallString );

print_r($soapResponse);

echo "Specials deleted";

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