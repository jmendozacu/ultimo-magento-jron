<?php
set_time_limit(6000);
ini_set("default_socket_timeout", 6000);
ini_set("max_execution_time", 6000);

$validUser = 'SOAPTESTER';
$validPwd  = '86c8c7153234f8c1c0c34c502a7ec1fb';
$response  = 'false';

$currentUser = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : $_SERVER['HTTP_USER'];
$currentPwd  = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : $_SERVER['HTTP_PW'];
echo $currentUser.' '.$currentPwd;
if(empty($currentPwd) && empty($currentUser) && array_key_exists('Authorization', $_SERVER))
{
  $data = explode(':', base64_decode(substr($_SERVER['Authorization'], 6)));
  if(!empty($data))
  {
    $currentUser = $data[0];
    $currentPwd = $data[1];
  }  
}

if(isset($currentUser) && isset($currentPwd) && $currentUser === $validUser && $currentPwd === $validPwd)
{
  include 'SOAPConnector.php';

  $bodyString = file_get_contents('php://input');
  $data = json_decode($bodyString, true);
  $action = isset($data['data']) && isset($data['data']['action']) ? $data['data']['action'] : null;

  if(isset($action))
  {
    $currentConnect = new SOAPConnector('http://127.0.0.1:8080/ultimo/index.php/api/soap/?wsdl');
    //$currentConnect = new SOAPConnector('http://mey.labs.idnt.net/index.php/api/soap/?wsdl');
   
    $response = $currentConnect->doRequest($action, $data);    
  }
}

echo $response;