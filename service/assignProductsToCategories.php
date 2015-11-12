<?php
require __DIR__ . "/mageSOAPAdvanced.php";

$mage   = new mageSOAPAdvanced();
$data   = include "/var/www/staging/current/server/mey/getCategoryAssignmentData.php";
$start  = microtime( true );
$result = $mage->linkProductsToCategories( $data );
echo "categories: ", count( $result ), "\n";
echo "duration: ", round( microtime( true ) - $start, 2 ), " sec\n";