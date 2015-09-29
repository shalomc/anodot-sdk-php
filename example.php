<?php

// an example of using the sendMetrics fuction to send metrics to anodot. 

include_once( 'anodot.php' );

$revenue = 5555; 
$customer_name = 'Globaldots';

// ensure correct timezone
date_default_timezone_set('UTC'); 
// Get your security token from the anodot dashboard. 
$token = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';  


// this is the array of the dimensions that describes the metric
$sales_dimensions = array (
	"Game" => "Poker",
	"Customer" => $customer_name , 
	"what" => "Revenue"
	); 
	

$anodot = new anodot();

// Helper function #1: 
// convert the array of dimensions into a graphite compatible name. This is not necessary but highly recommended
$graphite_name=$anodot->build_graphite_name( $sales_dimensions );

// Helper function #2: 
// Create payload string to post. You can create the payload yourself, but the helper functions are here to help.
// the last parameter can be explicitly set to "gauge" or "counter", or omitted to use the default of "gauge". 
// gauge   = aggregations using Average
// counter = aggregations using Sum
$payload = $anodot->build_payload( $graphite_name, time() , $revenue , "gauge"); 

print_r($payload ); 
print( PHP_EOL );

$result = $anodot->sendMetrics($payload, $token ) ; 
print_r( ); 
print( PHP_EOL );


/* http_status can be one of: 
200 = good metric
410 = authentication error
500 = bad data
*/

echo "Status code is " ;
echo $anodot->http_status ; 

return;

?>
