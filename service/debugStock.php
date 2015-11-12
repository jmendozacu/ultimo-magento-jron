<?php
//echo "Debug\n";
$ch = curl_init();
$data_string = '{"data": {"action" : "products","type" : "stock", "data": [{"sku": "1234512345", "ean": "4009603999885"}]}}';

//$data_string = '{"data": {"action" : "products","type" : "single", "data": { "sku" : "1990_8564_07"}}}';
curl_setopt($ch,CURLOPT_URL, "http://meyUser:93fediqd9@www.mey.com/mey_magento/public/service/syncStock.php");
//curl_setopt($ch,CURLOPT_URL, "http://magento.bbaum-pc.de/q1/service/index.php?alpha=q1Connector&beta=2z3fgOEc38UugPKqqXYntEq");
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
echo(curl_exec($ch));
curl_close($ch);
