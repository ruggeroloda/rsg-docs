<?php
function curl_file_get_contents($request) {
	$ch = curl_init();
	$timeout = 5; // set to zero for no timeout
	curl_setopt ($ch, CURLOPT_URL, $request);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

function getAmazonDataAsXML($config, $params) {

	uksort($params, 'strnatcmp');
	$qstr = '';
	foreach ($params as $key => $val) {
	    $qstr .= "&{$key}=".rawurlencode($val);
	}
	$qstr = substr($qstr, 1);

	preg_match("/\/\/(.*)\/onca\/xml/", $config['endpoint'], $matches);
	$host = $matches[1];
	
	$str = "GET\n"
	     . "$host\n"
	     . "/onca/xml\n"
	     . $qstr;

	$params['Signature'] = base64_encode(hash_hmac('sha256', $str, $config['secret_key'], true));
	$request = $config['endpoint'] . '?' . http_build_query($params);	
	//echo("REQUEST = <textarea>$request</textarea>");
	var_dump($request);
	$response = curl_file_get_contents($request);
	//echo("RESPONSE = <textarea>$response</textarea>");
	return simplexml_load_string($response);
}

function ItemSearch_BACKUP($config, $SearchIndex, $Keywords, $ItemPage = 1) {
	$Keywords = trim($Keywords);
	/*
	$params = array(
			'AssociateTag' 			=> $config['associates_tag'],
			'AWSAccessKeyId' 		=> $config['access_key'],
			'ItemPage'					=> $ItemPage,
			'Keywords'					=> urlencode($Keywords),
			'Operation' 				=> 'ItemSearch',
			'ResponseGroup'			=> 'ItemAttributes,Offers,Images,EditorialReview,Variations',
			'SearchIndex'				=> $SearchIndex,
			'Service' 					=> 'AWSECommerceService',
	    'SignatureMethod' 	=> 'HmacSHA256',
	    'SignatureVersion' 	=> 2,
	    'Timestamp' 				=> gmdate('Y-m-d\TH:i:s\Z'),
			'Version'						=> '2008-08-19'	
	);
	*/
	$params = array(
			'AssociateTag' 			=> $config['associates_tag'],
			'AWSAccessKeyId' 		=> $config['access_key'],
			'ItemPage'					=> $ItemPage,
			'Operation' 				=> 'ItemSearch',
			'Availability'			=> 'Available',
			'ResponseGroup'			=> 'ItemAttributes',
			'SearchIndex'				=> 'SportingGoods',
			'Service' 					=> 'AWSECommerceService',
			'Condition' => 'New',
			'Sort' => 'salesrank',
			
			
			
			'Keywords'					=> $Keywords,
			
			
			
	    'SignatureMethod' 	=> 'HmacSHA256',
	    'SignatureVersion' 	=> 2,
	    'Timestamp' 				=> gmdate('Y-m-d\TH:i:s\Z'),
			'Version'						=> '2008-08-19'	
	);
	
	//var_dump($params);
	
	$xml = getAmazonDataAsXML($config, $params);
	
	//var_dump($xml);
	$Keywords_full = $Keywords;
	$Keywords = explode(' ', $Keywords);
	foreach($Keywords as $Keyword) {
		if($Keyword[0] == '-')
			continue;
		if($Keyword[0] == '+')
			$Keyword = ltrim($Keyword, '+');
		//$match .= '(?=.*\\'.strtolower($Keyword).'\\b)';
		//var_dump($Keyword);
		if(is_numeric(strtolower($Keyword)))
			$match .= '(?=.*(^|\s)'.strtolower(str_replace('+', '\+', $Keyword)).'(^|\s|$))';
		elseif(strtolower($Keyword) == "men")
			$match .= '(?=.*(^|\s)'.strtolower(str_replace('+', '\+', $Keyword)).')';
		elseif(strtolower($Keyword) != "shoe")
			$match .= '(?=.*'.strtolower(str_replace('+', '\+', $Keyword)).')';
			//$match .= strtolower(str_replace('+', '\+', $Keyword)).'|';
	}
	//$match = rtrim($match, '|');
	//sleep(1);
	foreach($xml->Items->Item as $item) {
		//var_dump($match.'--match--'.$item->ItemAttributes->Title);
		//var_dump(preg_match('/'.$match.'/', strtolower($item->ItemAttributes->Title)));
		
		//if(preg_match_all('/\b('.$match.')\b/', strtolower($item->ItemAttributes->Title)) === 1) {
		if(preg_match('/'.$match.'/', strtolower($item->ItemAttributes->Title)) === 1) {
			//var_dump($item->ItemAttributes);
			//var_dump($item->ASIN);
			/*** CHECK FOR SHOES REMOVED ***/
			//if(strtolower($item->ItemAttributes->ProductTypeName) == 'shoes' || strtolower($item->ItemAttributes->ProductGroup) == 'shoes' ) {
				return ItemLookup($config, $item->ASIN);
			//}
		}
	}
	// No result return closest match
	if(isset($xml->Items->Item[0])) {
		$highest_similarity = null;
		$prev_percent = 0;
		foreach($xml->Items->Item as $item) {
			$percent = null;
			$returnValue = similar_text(strtolower($Keywords_full), strtolower($item->ItemAttributes->Title), $percent);

			if($percent > $prev_percent) {
				$highest_similarity = $item->ASIN;
				$prev_percent = $percent;
			}
		}
		return ItemLookup($config, $highest_similarity);
	}
	
	//return getAmazonDataAsXML($config, $params);
}

function ItemSearch($config, $SearchIndex, $Keywords, $ItemPage = 1) {
	$payload="{"
		." \"Keywords\": \"".addslashes($Keywords)."\","
		." \"SearchIndex\": \"SportsAndOutdoors\","
		." \"Resources\": ["
        ."  \"ItemInfo.Title\","
        ."  \"ParentASIN\""
        ." ],"
        ." \"SortBy\": \"Relevance\","
        ." \"PartnerTag\": \"".$config['associates_tag']."\","
		." \"Condition\": \"New\","
        ." \"Availability\": \"Available\","
        ." \"PartnerType\": \"Associates\""
        ."}";
		
	$host = $config['host'];
	$path = "/paapi5/searchitems";
	
	//Put your Access Key in place of <ACCESS_KEY> and Secret Key in place of <SECRET_KEY> in double quotes
	$awsv4 = new AwsV4 ($config['access_key'], $config['secret_key']);
	$awsv4->setRegionName($config['region']);
	$awsv4->setServiceName("ProductAdvertisingAPI");
	$awsv4->setPath ($path);
	$awsv4->setPayload ($payload);
	$awsv4->setRequestMethod ("POST");
	$awsv4->addHeader ('content-encoding', 'amz-1.0');
	$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
	$awsv4->addHeader ('host', $host);
	$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems');
	$headers = $awsv4->getHeaders ();
	$headerString = "";
	foreach ( $headers as $key => $value ) {
		$headerString .= $key . ': ' . $value . "\r\n";
	}
	
	$params = array (
			'http' => array (
				'header' => $headerString,
				'method' => 'POST',
				'content' => $payload
			)
		);
	$stream = stream_context_create ( $params );
	

	$fp = @fopen ( 'https://'.$host.$path, 'rb', false, $stream );

	if (! $fp) {
		//throw new Exception ( "Exception Occured" );
		return null;
	}
	$response = @stream_get_contents ( $fp );
	if ($response === false) {
		return null;
		//throw new Exception ( "Exception Occured" );
	}
	$response = json_decode($response);
	
	
	
	$items_Ids = Array();
	
	if(isset($response->SearchResult->Items)) {
		foreach($response->SearchResult->Items as $item) {
			
			if( (strpos(strtolower($Keywords), 'nike') !== false) && isset($item->ParentASIN) && $item->ParentASIN != $item->ASIN) {
				$items_Ids[] = (string) $item->ParentASIN;
			}
			else {
			
				$items_Ids[] = (string) $item->ASIN;
			
			}
			
		}
		
		$items = ItemLookup($config, $items_Ids);
		
		return($items);
		
		//return($response->SearchResult);
	}
























	/*

	var_dump($config);
	$Keywords = trim($Keywords);
	
	$params = array(
			'AssociateTag' 			=> $config['associates_tag'],
			'AWSAccessKeyId' 		=> $config['access_key'],
			'ItemPage'					=> $ItemPage,
			'Operation' 				=> 'ItemSearch',
			'Availability'			=> 'Available',
			'ResponseGroup'			=> 'ItemIds',
			'SearchIndex'				=> 'SportingGoods',
			'Service' 					=> 'AWSECommerceService',
			'Condition' => 'New',
			'Sort' => 'salesrank',
			
			
			
			'Keywords'					=> $Keywords,
			
			
			
	    'SignatureMethod' 	=> 'HmacSHA256',
	    'SignatureVersion' 	=> 2,
	    'Timestamp' 				=> gmdate('Y-m-d\TH:i:s\Z'),
			'Version'						=> '2008-08-19'	
	);
	
	
	$xml = getAmazonDataAsXML($config, $params);
	
	$items_Ids = Array();
	
	foreach($xml->Items->Item as $item) {
		$items_Ids[] = (string) $item->ASIN;
	}
	
	$xml_items = ItemLookup($config, implode("," , $items_Ids));
	
	unset($params['Sort']);
	
	$xml = getAmazonDataAsXML($config, $params);
	
	$items_Ids2 = Array();
	
	foreach($xml->Items->Item as $item) {
		if ( in_array( (string) $item->ASIN , $items_Ids ) == false ) 
			$items_Ids2[] = (string) $item->ASIN;
	}
	
	$xml_items2 = ItemLookup($config, implode("," , $items_Ids2));
	
	// merge XML items from one fie to other
	
	$merged_xml = '';
	
	foreach($xml_items->Items->Item as $product) {
		$merged_xml .= $product->asXML();
	}
	
	foreach($xml_items2->Items->Item as $product) {
		$merged_xml .= $product->asXML();
	}
	
	
	$merged_xml = '<xml><Items>'.$merged_xml.'</Items></xml>';
	
	
	return simplexml_load_string($merged_xml);
	
	//return ItemLookup($config, implode("," , $items_Ids));
	*/
	
	
}

function ItemSearchCSV($config, $SearchIndex, $Keywords, $ItemPage = 1) {
	$payload="{"
		." \"Keywords\": \"".addslashes($Keywords)."\","
		." \"SearchIndex\": \"SportsAndOutdoors\","
		." \"ItemPage\": ".$ItemPage.","
		." \"Resources\": ["
        ."  \"ItemInfo.Title\","
        ."  \"ParentASIN\""
        ." ],"
        ." \"SortBy\": \"Relevance\","
        ." \"PartnerTag\": \"".$config['associates_tag']."\","
		." \"Condition\": \"New\","
        ." \"Availability\": \"Available\","
        ." \"PartnerType\": \"Associates\""
        ."}";
		
	$host = $config['host'];
	$path = "/paapi5/searchitems";
	
	//Put your Access Key in place of <ACCESS_KEY> and Secret Key in place of <SECRET_KEY> in double quotes
	$awsv4 = new AwsV4 ($config['access_key'], $config['secret_key']);
	$awsv4->setRegionName($config['region']);
	$awsv4->setServiceName("ProductAdvertisingAPI");
	$awsv4->setPath ($path);
	$awsv4->setPayload ($payload);
	$awsv4->setRequestMethod ("POST");
	$awsv4->addHeader ('content-encoding', 'amz-1.0');
	$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
	$awsv4->addHeader ('host', $host);
	$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems');
	$headers = $awsv4->getHeaders ();
	$headerString = "";
	foreach ( $headers as $key => $value ) {
		$headerString .= $key . ': ' . $value . "\r\n";
	}
	
	$params = array (
			'http' => array (
				'header' => $headerString,
				'method' => 'POST',
				'content' => $payload
			)
		);
	$stream = stream_context_create ( $params );
	

	$fp = @fopen ( 'https://'.$host.$path, 'rb', false, $stream );

	if (! $fp) {
		//throw new Exception ( "Exception Occured" );
		return null;
	}
	$response = @stream_get_contents ( $fp );
	if ($response === false) {
		return null;
		//throw new Exception ( "Exception Occured" );
	}
	$response = json_decode($response);
	
	//print_r($response);
	//echo 'TotalResultCount=='.$response->SearchResult->TotalResultCount;
	
	$items_Ids = Array();
	
	if(isset($response->SearchResult->Items)) {
		foreach($response->SearchResult->Items as $item) {
			
			if( isset($item->ParentASIN) && $item->ParentASIN != $item->ASIN) {
				$items_Ids[] = (string) $item->ParentASIN;
			}
			else {
			
				$items_Ids[] = (string) $item->ASIN;
			
			}
			
		}
		
		$items = ItemLookupCSV($config, $items_Ids);
		
		$items->TotalPages = ceil ($response->SearchResult->TotalResultCount/10);
		
		return($items);
		
		//return($response->SearchResult);
	}












}

function ItemLookup_BACKUP($config, $itemId) {
	
	$params = array(
			'AssociateTag' 			=> $config['associates_tag'],
			'AWSAccessKeyId' 		=> $config['access_key'],
			'ItemId'						=> "$itemId",
			'Operation' 				=> 'ItemLookup',
			'ResponseGroup'			=> 'ItemAttributes,Offers,Images,Variations',
			'Service' 					=> 'AWSECommerceService',
	    'SignatureMethod' 	=> 'HmacSHA256',
	    'SignatureVersion' 	=> 2,
	    'Timestamp' 				=> gmdate('Y-m-d\TH:i:s\Z'),
			'Version'						=> '2008-08-19'	
	);

	return getAmazonDataAsXML($config, $params);
}

function ItemLookup($config, $itemId) {
	
	print_r(json_encode($itemId)); 
	
	if(!is_array($itemId))
		$itemId = Array($itemId);
	
	$payload="{"
        ." \"ItemIds\": ".json_encode($itemId).","
       	." \"Resources\": ["
		."  \"ItemInfo.Title\","
		."  \"Images.Primary.Small\","
        ."  \"Images.Primary.Large\","
        ."  \"Offers.Summaries.LowestPrice\""
        ." ],"
		." \"Condition\": \"New\","
        ." \"Availability\": \"Available\","
       ." \"PartnerTag\": \"".$config['associates_tag']."\","
        ." \"PartnerType\": \"Associates\""
        ."}";
	
	$host = $config['host'];
	$path = "/paapi5/getitems";
	
	
	$awsv4 = new AwsV4 ($config['access_key'], $config['secret_key']);
	$awsv4->setRegionName($config['region']);
	$awsv4->setServiceName("ProductAdvertisingAPI");
	$awsv4->setPath ($path);
	$awsv4->setPayload ($payload);
	$awsv4->setRequestMethod ("POST");
	$awsv4->addHeader ('content-encoding', 'amz-1.0');
	$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
	$awsv4->addHeader ('host', $host);
	$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems');
	$headers = $awsv4->getHeaders ();
	$headerString = "";
	foreach ( $headers as $key => $value ) {
		$headerString .= $key . ': ' . $value . "\r\n";
	}
	
	$params = array (
			'http' => array (
				'header' => $headerString,
				'method' => 'POST',
				'content' => $payload
			)
		);
	$stream = stream_context_create ( $params );

	$fp = @fopen ( 'https://'.$host.$path, 'rb', false, $stream );

	if (! $fp) {
		return null;
		//throw new Exception ( "Exception Occured" );
	}
	$response = @stream_get_contents ( $fp );
	if ($response === false) {
		return null;
		//throw new Exception ( "Exception Occured" );
	}
	$response = json_decode($response);
	
	//print_r($response);
	
	if( isset($response->ItemsResult->Items) && count( (array)$response->ItemsResult->Items ) > 0 ) {
		foreach ($response->ItemsResult->Items as &$item) {
			
			$payload="{"
				." \"ASIN\": \"".$item->ASIN."\","
				." \"Resources\": ["
				."  \"VariationSummary.Price.LowestPrice\""
				." ],"
				." \"PartnerTag\": \"".$config['associates_tag']."\","
				." \"PartnerType\": \"Associates\""
				."}";
		
			$host = $config['host'];
			$path = "/paapi5/getvariations";
			
			$awsv4 = new AwsV4 ($config['access_key'], $config['secret_key']);
			$awsv4->setRegionName($config['region']);
			$awsv4->setServiceName("ProductAdvertisingAPI");
			$awsv4->setPath ($path);
			$awsv4->setPayload ($payload);
			$awsv4->setRequestMethod ("POST");
			$awsv4->addHeader ('content-encoding', 'amz-1.0');
			$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
			$awsv4->addHeader ('host', $host);
			$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetVariations');
			$headers = $awsv4->getHeaders ();
			$headerString = "";
			foreach ( $headers as $key => $value ) {
				$headerString .= $key . ': ' . $value . "\r\n";
			}
			
			$params = array (
					'http' => array (
						'header' => $headerString,
						'method' => 'POST',
						'content' => $payload
					)
				);
			$stream = stream_context_create ( $params );
			

			$fp = @fopen ( 'https://'.$host.$path, 'rb', false, $stream );

			if (! $fp) {
				//throw new Exception ( "Exception Occured" );
				continue;
			}
			$response_variations = @stream_get_contents ( $fp );
			if ($response === false) {
				//throw new Exception ( "Exception Occured" );
				continue;
			}
			$response_variations = json_decode($response_variations);
			
			$item->VariationSummary = $response_variations->VariationsResult->VariationSummary;
		}
	}
	
	
	
	if(isset($response->ItemsResult->Items))
		return($response->ItemsResult);
	else
		return null;
	
	

	
	
	/*
	$params = array(
			'AssociateTag' 			=> $config['associates_tag'],
			'AWSAccessKeyId' 		=> $config['access_key'],
			'ItemId'						=> "$itemId",
			'Operation' 				=> 'ItemLookup',
			'ResponseGroup'			=> 'ItemAttributes,OfferSummary,Images,VariationSummary',
			'Service' 					=> 'AWSECommerceService',
	    'SignatureMethod' 	=> 'HmacSHA256',
	    'SignatureVersion' 	=> 2,
	    'Timestamp' 				=> gmdate('Y-m-d\TH:i:s\Z'),
			'Version'						=> '2008-08-19'	
	);

	return getAmazonDataAsXML($config, $params);
	*/
}

function ItemLookupCSV($config, $itemId) {
	
	print_r(json_encode($itemId)); 
	
	if(!is_array($itemId))
		$itemId = Array($itemId);
	
	$payload="{"
        ." \"ItemIds\": ".json_encode($itemId).","
       	." \"Resources\": ["
		."  \"ItemInfo.Title\","
		."  \"Images.Primary.Small\","
        ."  \"Images.Primary.Large\","
        ."  \"Offers.Summaries.LowestPrice\""
        ." ],"
		." \"Condition\": \"New\","
        ." \"Availability\": \"Available\","
       ." \"PartnerTag\": \"".$config['associates_tag']."\","
        ." \"PartnerType\": \"Associates\""
        ."}";
	
	$host = $config['host'];
	$path = "/paapi5/getitems";
	
	
	$awsv4 = new AwsV4 ($config['access_key'], $config['secret_key']);
	$awsv4->setRegionName($config['region']);
	$awsv4->setServiceName("ProductAdvertisingAPI");
	$awsv4->setPath ($path);
	$awsv4->setPayload ($payload);
	$awsv4->setRequestMethod ("POST");
	$awsv4->addHeader ('content-encoding', 'amz-1.0');
	$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
	$awsv4->addHeader ('host', $host);
	$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems');
	$headers = $awsv4->getHeaders ();
	$headerString = "";
	foreach ( $headers as $key => $value ) {
		$headerString .= $key . ': ' . $value . "\r\n";
	}
	
	$params = array (
			'http' => array (
				'header' => $headerString,
				'method' => 'POST',
				'content' => $payload
			)
		);
	$stream = stream_context_create ( $params );

	$fp = @fopen ( 'https://'.$host.$path, 'rb', false, $stream );

	if (! $fp) {
		return null;
		//throw new Exception ( "Exception Occured" );
	}
	$response = @stream_get_contents ( $fp );
	if ($response === false) {
		return null;
		//throw new Exception ( "Exception Occured" );
	}
	$response = json_decode($response);
	
	//print_r($response);
	
	if( isset($response->ItemsResult->Items) && count( (array)$response->ItemsResult->Items ) > 0 ) {
		foreach ($response->ItemsResult->Items as &$item) {
			
			$payload="{"
				." \"ASIN\": \"".$item->ASIN."\","
				." \"Resources\": ["
				."  \"Offers.Listings.Price\","
				."  \"VariationSummary.Price.LowestPrice\""
				." ],"
				." \"PartnerTag\": \"".$config['associates_tag']."\","
				." \"PartnerType\": \"Associates\""
				."}";
		
			$host = $config['host'];
			$path = "/paapi5/getvariations";
			
			$awsv4 = new AwsV4 ($config['access_key'], $config['secret_key']);
			$awsv4->setRegionName($config['region']);
			$awsv4->setServiceName("ProductAdvertisingAPI");
			$awsv4->setPath ($path);
			$awsv4->setPayload ($payload);
			$awsv4->setRequestMethod ("POST");
			$awsv4->addHeader ('content-encoding', 'amz-1.0');
			$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
			$awsv4->addHeader ('host', $host);
			$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetVariations');
			$headers = $awsv4->getHeaders ();
			$headerString = "";
			foreach ( $headers as $key => $value ) {
				$headerString .= $key . ': ' . $value . "\r\n";
			}
			
			$params = array (
					'http' => array (
						'header' => $headerString,
						'method' => 'POST',
						'content' => $payload
					)
				);
			$stream = stream_context_create ( $params );
			

			$fp = @fopen ( 'https://'.$host.$path, 'rb', false, $stream );

			if (! $fp) {
				//throw new Exception ( "Exception Occured" );
				continue;
			}
			$response_variations = @stream_get_contents ( $fp );
			if ($response === false) {
				//throw new Exception ( "Exception Occured" );
				continue;
			}
			$response_variations = json_decode($response_variations);
			
			//print_r($response_variations);
			
			$item->VariationsResult = $response_variations->VariationsResult;
		}
	}
	
	
	
	if(isset($response->ItemsResult->Items))
		return($response->ItemsResult);
	else
		return null;
	
	

}

class AwsV4 {

    private $accessKeyID = null;
    private $secretAccessKey = null;
    private $path = null;
    private $regionName = null;
    private $serviceName = null;
    private $httpMethodName = null;
    private $queryParametes = array ();
    private $awsHeaders = array ();
    private $payload = "";

    private $HMACAlgorithm = "AWS4-HMAC-SHA256";
    private $aws4Request = "aws4_request";
    private $strSignedHeader = null;
    private $xAmzDate = null;
    private $currentDate = null;

    public function __construct($accessKeyID, $secretAccessKey) {
        $this->accessKeyID = $accessKeyID;
        $this->secretAccessKey = $secretAccessKey;
        $this->xAmzDate = $this->getTimeStamp ();
        $this->currentDate = $this->getDate ();
    }

    function setPath($path) {
        $this->path = $path;
    }

    function setServiceName($serviceName) {
        $this->serviceName = $serviceName;
    }

    function setRegionName($regionName) {
        $this->regionName = $regionName;
    }

    function setPayload($payload) {
        $this->payload = $payload;
    }

    function setRequestMethod($method) {
        $this->httpMethodName = $method;
    }

    function addHeader($headerName, $headerValue) {
        $this->awsHeaders [$headerName] = $headerValue;
    }

    private function prepareCanonicalRequest() {
        $canonicalURL = "";
        $canonicalURL .= $this->httpMethodName . "\n";
        $canonicalURL .= $this->path . "\n" . "\n";
        $signedHeaders = '';
        foreach ( $this->awsHeaders as $key => $value ) {
            $signedHeaders .= $key . ";";
            $canonicalURL .= $key . ":" . $value . "\n";
        }
        $canonicalURL .= "\n";
        $this->strSignedHeader = substr ( $signedHeaders, 0, - 1 );
        $canonicalURL .= $this->strSignedHeader . "\n";
        $canonicalURL .= $this->generateHex ( $this->payload );
        return $canonicalURL;
    }

    private function prepareStringToSign($canonicalURL) {
        $stringToSign = '';
        $stringToSign .= $this->HMACAlgorithm . "\n";
        $stringToSign .= $this->xAmzDate . "\n";
        $stringToSign .= $this->currentDate . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "\n";
        $stringToSign .= $this->generateHex ( $canonicalURL );
        return $stringToSign;
    }

    private function calculateSignature($stringToSign) {
        $signatureKey = $this->getSignatureKey ( $this->secretAccessKey, $this->currentDate, $this->regionName, $this->serviceName );
        $signature = hash_hmac ( "sha256", $stringToSign, $signatureKey, true );
        $strHexSignature = strtolower ( bin2hex ( $signature ) );
        return $strHexSignature;
    }

    public function getHeaders() {
        $this->awsHeaders ['x-amz-date'] = $this->xAmzDate;
        ksort ( $this->awsHeaders );
        $canonicalURL = $this->prepareCanonicalRequest ();
        $stringToSign = $this->prepareStringToSign ( $canonicalURL );
        $signature = $this->calculateSignature ( $stringToSign );
        if ($signature) {
            $this->awsHeaders ['Authorization'] = $this->buildAuthorizationString ( $signature );
            return $this->awsHeaders;
        }
    }

    private function buildAuthorizationString($strSignature) {
        return $this->HMACAlgorithm . " " . "Credential=" . $this->accessKeyID . "/" . $this->getDate () . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "," . "SignedHeaders=" . $this->strSignedHeader . "," . "Signature=" . $strSignature;
    }

    private function generateHex($data) {
        return strtolower ( bin2hex ( hash ( "sha256", $data, true ) ) );
    }

    private function getSignatureKey($key, $date, $regionName, $serviceName) {
        $kSecret = "AWS4" . $key;
        $kDate = hash_hmac ( "sha256", $date, $kSecret, true );
        $kRegion = hash_hmac ( "sha256", $regionName, $kDate, true );
        $kService = hash_hmac ( "sha256", $serviceName, $kRegion, true );
        $kSigning = hash_hmac ( "sha256", $this->aws4Request, $kService, true );

        return $kSigning;
    }

    private function getTimeStamp() {
        return gmdate ( "Ymd\THis\Z" );
    }

    private function getDate() {
        return gmdate ( "Ymd" );
    }
}



?>
