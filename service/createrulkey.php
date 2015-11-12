<?php
$ch = curl_init();
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);


$time_start = microtime(true);

print_r(mb_strtolower('hallo wetWd  wd '));
$soapCallString = '{"data": {"action" : "advanced", "type" : "urlkeys"}}';
$soapResponse = doCurlCall("http://meyUser:93fediqd9@www.mey.com/MeyTest/service/index.php",$soapCallString );
//$soapResponse = doCurlCall("http://meyUser:93fediqd9@localhost/service/index.php",$soapCallString );

print_r($soapResponse);
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Das Bilden der notwendigen URL Keys Updates brauchte $time Sekunden\n";


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

