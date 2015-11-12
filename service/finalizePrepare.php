<?php
$ch = curl_init();
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);

$filepathImages = "/var/www/mey/shared/public/media/imageupdates.txt";
$filepathRewrites = "/var/www/mey/shared/public/media/rewriteupdates.txt";
$SOAPPath = "http://meyUser:93fediqd9@www.mey.com/service/index.php";

$soapCallString = '{"data": {"action" : "advanced", "type" : "numbers"}}';
$soapResponseImages = doCurlCall($SOAPPath,$soapCallString );

//Put Loop Parameter in Numbersarray
$imagefinalresponse = array();
$beginner = 0;                  //Begin of GetImage
$steps = 1000;                     //Steps for Loop
$soapresponsearray = json_decode($soapResponseImages,true);
$counter = sizeof($soapresponsearray);  // Max Size of Loops
//$counter = 1000;
do
{

  $responder = $soapresponsearray;
  $responder[] = $beginner;
  $responder[] = $beginner+$steps;
  $responder = json_encode($responder,true);

  $imageHandlerResponse = doCurlCall( "https://s01.shop.fortuneglobe.com/mey/alvine/gfx/item/onlinestore/imageHandler.php?c", $responder );

  //print_r($imageHandlerResponse);
  $tmpimagehandlerresponse = json_decode($imageHandlerResponse,true);
  if(is_array($tmpimagehandlerresponse)){
    $imagefinalresponse = array_merge($imagefinalresponse,$tmpimagehandlerresponse);
  }
  $counter -= $steps;
  $beginner += $steps;
} while ($counter > 0);

$soapCallString = '{"data": {"action" : "advanced", "type" : "simpleprods"}}';
$soapResponseRewrite = doCurlCall( $SOAPPath, $soapCallString );

// Create Final Entrys for SOAPAdvanced
$ResponseStringImages = json_encode($imagefinalresponse,true);

//Create Files
file_put_contents($filepathImages, $ResponseStringImages);
file_put_contents($filepathRewrites, $soapResponseRewrite);

echo "true";


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

