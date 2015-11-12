<?php
require __DIR__ . "/mageSOAPAdvanced.php";

$mage = new mageSOAPAdvanced();
$data = include "/var/www/staging/current/server/mey/getCrossSellingData.php";
$mage->linkCrossselling($data);

