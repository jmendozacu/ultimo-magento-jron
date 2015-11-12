<?php
$ch = curl_init();
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);

$time_start = microtime(true);

// Check Which Function iss called
$mode = $_GET["mode"];

/* Online Block BEGIN */ 
  $filepath = "../../../../shared/public/media/imageupdates.txt";
  $SOAPPath = "http://meyUser:93fediqd9@www.mey.com/service/index.php";
/* Online BLock END */

/* Lokal Block BEGIN */ /*
  $filepath = "imageupdates.txt";
  $SOAPPath = "http://meyUser:93fediqd9@localhost/MeyTest/service/index.php";
/* Lokal BLock END */

//Step 1 - Pepare Data
switch ($mode) {
  case 'image':
    $soapCallString = '{"data": {"action" : "advanced", "type" : "numbers"}}';
    $soapResponse = doCurlCall($SOAPPath,$soapCallString );
    break;
  case 'url-key':
    $soapCallString = '{"data": {"action" : "advanced", "type" : "configprods"}}';
    $soapResponse = doCurlCall($SOAPPath,$soapCallString );
    break;
  case 'url-rewrite':
    $soapCallString = '{"data": {"action" : "advanced", "type" : "simpleprods"}}';
    $soapResponse = doCurlCall($SOAPPath,$soapCallString );
    break;
  
  default:
    die("Keine Vorgehensweise gefunden!");
    break;
}

//file_put_contents("ipp.txt", $soapResponse); die();
//var_dump($soapResponse); die();
//$soapResponse = file_get_contents("ipp.txt");

// Compare Magento Numbers with Image-NAames and geht result
if($mode == 'image'){
  //Put Loop Parameter in Numbersarray
  $imagefinalresponse = array();
  $beginner = 0;                  //Begin of GetImage
  $steps = 1000;                     //Steps for Loop
  $soapresponsearray = json_decode($soapResponse,true);
  $counter = sizeof($soapresponsearray);  // Max Size of Loops
  //$counter = 1000;
  do {

    $responder = $soapresponsearray;
    $responder[] = $beginner;
    $responder[] = $beginner+$steps;
    $responder = json_encode($responder,true);


  /*  FIRSTLOAD EXCEPTION BEGIN */

    $imageHandlerResponse = doCurlCall("http://www.mey.com/alvine/gfx/item/onlinestore/imageHandler.php?c", $responder);

  /*  FIRSTLOAD EXCEPTION END */


  /*  Comment after Firstload Begin */ /*
    
    $imageHandlerResponse = doCurlCall("http://mey.labs.idnt.net/media/import/tmp/imageHandler.php?c", $responder);

  /*  Comment after Firstload End */
    


    //print_r($imageHandlerResponse);
    $tmpimagehandlerresponse = json_decode($imageHandlerResponse,true);
    if(is_array($tmpimagehandlerresponse)){
      $imagefinalresponse = array_merge($imagefinalresponse,$tmpimagehandlerresponse);
    }
    $counter -= $steps;
    $beginner += $steps;
  } while ($counter > 0);
  $workresponse = $imagefinalresponse;
}else{
  $workresponse = json_decode($soapResponse);
}


// Create Final Response for SOAPAdvanced
$ResponseString = json_encode($workresponse,true);

//print_r($ResponseString);
file_put_contents($filepath, $ResponseString);


$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Das Bilden der abzuarbeitenden Datei brauchte $time Sekunden und besitzt ".sizeof($workresponse)." Einträge\n";


?>
<!DOCTYPE html>
<html>
<head>
<title>Los gehts</title>

</head>

<body>
  <meta http-equiv="refresh" content="1; url=syncexecutor.php?mode=<?php echo $mode; ?>">
</body>

</html>

<?php


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

