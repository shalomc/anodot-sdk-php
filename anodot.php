<?php
class anodot
{

	public $response;

	/*
	 * execute a call to the anodot API
	 * @command string Which API command to run
	 * @method string POST/GET 
	 * @payload string The payload to send
	 * @return mixed The anodot response
	 */
	protected function execute($command, $token, $method= 'POST', $payload='' )  
	{
		$anodot_base_url = "https://api.anodot.com/api/v1/";
		
		
		/* prepare ground for more complex actions
		     Sep 2015 List of actions: 
			    * metrics
		*/
		
		$action = $command;
		
		$url =   $anodot_base_url.$action.'?token='.$token;

		// Reset the response cache
		$this->response = null;

		$headers = array(
				'Content-Type: application/json',
				'Accept: application/json'
				);
		

		$ch = curl_init();  
		
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);                                                                  
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$output=curl_exec($ch);
		$GLOBALS['http_status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$this->response = $output;
		return $output;
	}

	
/////////////////////////////////////////////////////////

	/*
	 * parse an associative array into a Graphite compatible name
	 * @dimensions array an Associative array of dimensions
	 * @return string Array converted into graphite-compatible name
	 */
	public function build_graphite_name( $dimensions ) 
	{
		$dot = ".";
		$graphiteName = '';
		foreach ( $dimensions as $key => $value )
		{
			if ( $graphiteName == '' ) {
				$graphiteName = $graphiteName . $key . "=" . $value ; 
			} else {
				$graphiteName = $graphiteName . $dot . $key . "=" . $value ; 
			}
		}
		return $graphiteName;
	}



	/*
	 * Build the actual Anodot payload
	 * @name string The name of the metric
	 * @timestamp timestap 
	 * @value number Value of metric
	 * @target_type string Target type can be counter/gauge or null
	 */
	public function build_payload($name, $timestamp= null , $value, $target_type=null)   // target_type is expected to be:  counter or gauge or null
	{
		$timestamp =  ($timestamp==null) ? time() : $timestamp; 
		$anodot_array_elem= array(
			"name"=>$name,
			"timestamp"=>$timestamp,
			"value"=>$value
		);
		if ($target_type != NULL ) {
			$anodot_array_elem["tags"] = array ("target_type"=> $target_type) ; 
		}		
		$anodot_array = array(); 
		$anodot_array[] = $anodot_array_elem; 
		return json_encode($anodot_array);
	}

		/*
	 * send Metrics to anodot
	 * @payload string JSON string of metric to send
	 * @token string Security token
	 * @return string result of POST
	 */
	public function sendMetrics($payload, $token)
	{
		$result = $this->execute( 'metrics', $token, 'POST', $payload );  
		return $result;
	}


	
}