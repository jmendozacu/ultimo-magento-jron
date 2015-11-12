<?php
require __DIR__ . "/mageSOAPAdvanced.php";

$path = "/var/www/mey/shared/public/media/";
$mage = new mageSOAPAdvanced();
$data = $mage->getUrlList();

foreach ( $data as $storeId => $storeUrls )
{
	file_put_contents( $path . "urllist_$storeId.txt", implode( "\r\n", $storeUrls ) );
}
