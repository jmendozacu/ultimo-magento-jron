<?php
error_reporting( -1 );
ini_set( 'display_errors', 'On' );
set_time_limit( 6000 );
ini_set( "default_socket_timeout", 6000 );
ini_set( "max_execution_time", 6000 );
$articlesPerCall = 12;

include 'SOAPConnector.php';

$linkData   = include "/var/www/staging/current/server/mey/getSimpleMasterAssignments.php";
$wrapper    = new SOAPConnector( 'http://www.mey.com/index.php/api/soap/?wsdl' );
$linkData   = array_reverse( $linkData );
$linkData   = array_chunk( $linkData, $articlesPerCall );
$chunkCount = count( $linkData );

foreach ( $linkData as $chunk )
{
	$start    = microtime( true );
	$data     = [ "data" => [ "data" => $chunk, "type" => "link", "action" => "products" ] ];
	$response = $wrapper->doRequest( "products", $data );
	$chunkCount--;
	printProgress( $chunkCount, $start, $articlesPerCall );
	printResult( $chunk, $response );
}





////////  func /////////////////////////////////////////////////////////////////////////////////////////////////////

function printProgress( $chunkCount, $start, $articlesPerCall )
{
	$duration = round( microtime( true ) - $start, 2 );
	echo sprintf( "remaining: %s\nduration ( %s articles ): %s\n",
	              $chunkCount, $articlesPerCall, $duration );
}

function printResult( array $chunk, $response )
{
	$result = json_decode( $response, true );
	for ( $i = 0, $max = count( $chunk ); $i < $max; $i++ )
	{
		$report    = $result[ $i ];
		$target    = $chunk[ $i ]["raw"];

		if($result)
		{
			$status    = $report["unknownErrors"] == false ? "success" : "error";
			$missing   = $report["missingVariantsCount"];
			$duplicate = empty( $report["duplicate"] ) ? "no" : implode( ",", $report["duplicate"] );
		}
		else
		{
			$status    = "call failed";
			$missing   = "unknown";
			$duplicate = "unknown";
		}

		echo sprintf( "\n[%s]\nstatus: %s\nmissing: %d\nduplicate: %s\n\n", $target, $status, $missing, $duplicate );
	}
}