<?php
set_time_limit(0);
ini_set("default_socket_timeout", 0);
ini_set("max_execution_time", 0);


$ch = curl_init();

//Online
$filepath = "../../../../shared/public/media/urlkeys.txt";
$SoapPath = "http://meyUser:93fediqd9@www.mey.com/service/index.php";

//Offline
//$filepath = "urlkeys.txt";
//$SoapPath = "http://meyUser:93fediqd9@www.mey-testserver.dev/service/index.php";

$data_string = '{"data":{"action":"advanced","type":"geturlkeylist","data":[{"path":".'.$filepath.'"}]}}';

curl_setopt($ch,CURLOPT_URL,$SoapPath );
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
//curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;