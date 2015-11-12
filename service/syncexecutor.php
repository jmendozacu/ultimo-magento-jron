<?php
$ch = curl_init();
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);

$time_start = microtime(true);

// Check Which Function iss called
$mode = $_GET["mode"];

/* Online Block BEGIN */ /*
  $filepath = "../../../../shared/public/media/imageupdates.txt";
  $SOAPPath = "http://meyUser:93fediqd9@www.mey.com/service/index.php";
/* Online BLock END */

/* Lokal Block BEGIN */ 
  $filepath = "imageupdates.txt";
  $SOAPPath = "http://meyUser:93fediqd9@localhost/MeyTest/service/index.php";
/* Lokal BLock END */

// Step 1 Set Execution Size
switch ($mode) {
  case 'image':
    $steps = 4;
    break;
  case 'url-key':
    $steps = 3;
    break;
  case 'url-rewrite':
    $steps = 250;
    break;
  
  default:
    die("Keine Vorgehensweise gefunden!");
    break;
}



$runqueue = array();
if(file_exists($filepath)){
  $checkupdates = json_decode(file_get_contents($filepath),true);
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
switch ($mode) {
  case 'image':
    $ImportDataString = '{"data": {"action" : "advanced", "type" : "addImages", "data": ' . $workdata . '}}';
    $AdvancedResponse = doCurlCall($SOAPPath,$ImportDataString );
    break;
  case 'url-key':
    $ImportDataString = '{"data": {"action" : "advanced", "type" : "createurlkeys", "data": ' . $workdata . '}}';
    $AdvancedResponse = doCurlCall($SOAPPath,$ImportDataString );
    break;
  case 'url-rewrite':
    $ImportDataString = '{"data": {"action" : "advanced", "type" : "createurlrewrite", "data": ' . $workdata . '}}';
    $AdvancedResponse = doCurlCall($SOAPPath,$ImportDataString );
    break;
  
  default:
    die("Keine Vorgehensweise gefunden!");
    break;
}


// Sandbox
//print_r($AdvancedResponse);
//die();


file_put_contents($filepath, json_encode($checkupdates,true)); 

//print_r($AdvancedResponse);
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Das Verarbeiten des Skriptes brauchte $time Sekunden und es sind noch ".sizeof($checkupdates)." Einträge unbearbeitet.\n";


if(sizeof($runqueue)>0){
?>
<!DOCTYPE html>
<html>
<head>
<title>Nicht fertig</title>
<meta http-equiv="refresh" content="1; url=syncexecutor.php?mode=<?php echo $mode; ?>">
</head>

<body>

</body>

</html>

<?php
}


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