<?php
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);


$filepathRewrites = "/var/www/mey/shared/public/media/rewriteupdates.txt";
$SOAPPath = "http://meyUser:93fediqd9@www.mey.com/service/index.php";

// Step 1 Set Execution Size
$steps = 250;

$runqueue = array();
if(file_exists($filepathRewrites)){
  $checkupdates = json_decode(file_get_contents($filepathRewrites),true);
}else{ 
  $checkupdates = array();
}
if(sizeof($checkupdates) > 0){
  $runqueue = array_slice($checkupdates, 0, $steps);
  for ($i=0; $i < $steps; $i++) {
    if(sizeof($checkupdates)>0){
      unset($checkupdates[key($checkupdates)]);
    }else{
      break;
    }
  }
}
if(sizeof($runqueue)<=0){
  die("Fertig");
}

$workdata = json_encode($runqueue,true);

// Step 2 Work with Data
$ImportDataString = '{"data": {"action" : "advanced", "type" : "createurlrewrite", "data": ' . $workdata . '}}';

if( doCurlCall($SOAPPath,$ImportDataString ) )
{
	file_put_contents($filepathRewrites, json_encode($checkupdates,true));
}

if(sizeof($runqueue)>0)
{
  echo "false";
}
else
{
  echo "true";
}


function doCurlCall($url, $dataString)
{
  $curlHandler = curl_init();
  $curlOptions = array(
    CURLOPT_URL            => $url,
    CURLOPT_HTTPHEADER     => array
    (
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
  $httpStatusCode = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
  curl_close($curlHandler);
  return ($callResponse && $httpStatusCode == 200);
}