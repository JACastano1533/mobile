<?php

class upsRate {

    var $AccessLicenseNumber;
    var $UserId;
    var $Password;
    var $shipperNumber;
    var $credentials;
    var $dimensionsUnits = "IN";
    var $weightUnits = "LBS";

    function setCredentials($access, $user, $pass, $shipper) {
	$this->AccessLicenseNumber = $access;
	$this->UserID = $user;
	$this->Password = $pass;
	$this->shipperNumber = $shipper;
	$this->credentials = 1;
    }

    function setDimensionsUnits($unit) {
	$this->dimensionsUnits = $unit;
    }

    function setWeightUnits($unit) {
	$this->weightUnits = $unit;
    }

    // Define the function getRate() - no parameters
    function getRate($PostalCode, $dest_zip, $dest_country, $service, $length, $width, $height, $weight, $timeInTransits) {
	if ($this->credentials != 1) {
	    print 'Please set your credentials with the setCredentials function';
	    die();
	}

	$data = "<?xml version=\"1.0\"?>  
		<AccessRequest xml:lang=\"en-US\">  
		    <AccessLicenseNumber>$this->AccessLicenseNumber</AccessLicenseNumber>  
		    <UserId>$this->UserID</UserId>  
		    <Password>$this->Password</Password>  
		</AccessRequest>  
		<?xml version=\"1.0\"?>  
		<RatingServiceSelectionRequest xml:lang=\"en-US\">  
		    <Request>  
			<TransactionReference>  
			    <CustomerContext>Rating and Service</CustomerContext>  
			    <XpciVersion>1.0001</XpciVersion>  
			</TransactionReference>  
			<RequestAction>Rate</RequestAction>  
			<RequestOption>Rate</RequestOption>  
		    </Request>  
		<PickupType>  
		    <Code>01</Code>  
		</PickupType>  
		<Shipment>  
		    <Shipper>  
			<Address>  
			    <PostalCode>92154</PostalCode>  
			    <CountryCode>US</CountryCode>  
				<StateProvinceCode>CA</StateProvinceCode>
			</Address>  
		    <ShipperNumber>837548</ShipperNumber>  
		    </Shipper>  
		    <ShipTo>
			<Address>
			  <PostalCode>$dest_zip</PostalCode>
			  <CountryCode>$dest_country</CountryCode>
			  <ResidentialAddressIndicator/>  
			</Address>
		    </ShipTo> 
			<Service>  
			<Code>$service</Code>  
			</Service>  
		    <ShipFrom>  
			<Address>  
			    <PostalCode>92154</PostalCode>  
			    <CountryCode>US</CountryCode>  
				<StateProvinceCode>CA</StateProvinceCode>  
			</Address>  
		    </ShipFrom>  
		    <Package>  
			<PackagingType>  
			    <Code>02</Code>  
			</PackagingType>  
			<Dimensions>  
			    <UnitOfMeasurement>  
				<Code>$this->dimensionsUnits</Code>  
			    </UnitOfMeasurement>  
			    <Length>$length</Length>  
			    <Width>$width</Width>  
			    <Height>$height</Height>  
			</Dimensions>  
			<PackageWeight>  
			    <UnitOfMeasurement>  
				<Code>$this->weightUnits</Code>  
			    </UnitOfMeasurement>  
			    <Weight>$weight</Weight>  
			</PackageWeight>  
		    </Package>  
			<RateInformation>
				<NegotiatedRatesIndicator>
				</NegotiatedRatesIndicator>
			</RateInformation>	
		</Shipment>  
		</RatingServiceSelectionRequest>"; 
							
//	$ch = curl_init("https://www.ups.com/ups.app/xml/Rate");
	$ch = curl_init("https://onlinetools.ups.com/ups.app/xml/Rate");
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$result = curl_exec($ch);
	echo '<!-- ' . $result . ' -->'; // THIS LINE IS FOR DEBUG PURPOSES ONLY-IT WILL SHOW IN HTML COMMENTS  
	$data = strstr($result, '<?');
	$xml_parser = xml_parser_create();
	xml_parse_into_struct($xml_parser, $data, $vals, $index);
	xml_parser_free($xml_parser);
	$params = array();
	$level = array();
	foreach ($vals as $xml_elem) {
	    if ($xml_elem['type'] == 'open') {
		if (array_key_exists('attributes', $xml_elem)) {
		    list($level[$xml_elem['level']], $extra) = array_values($xml_elem['attributes']);
		} else {
		    $level[$xml_elem['level']] = $xml_elem['tag'];
		}
	    }
	    if ($xml_elem['type'] == 'complete') {
		$start_level = 1;
		$php_stmt = '$params';
		while ($start_level < $xml_elem['level']) {
		    $php_stmt .= '[$level[' . $start_level . ']]';
		    $start_level++;
		}
		if (isset($xml_elem['value'])) {
		    $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
		    eval($php_stmt);
		}
	    }
	}
	curl_close($ch);


	//get status
//   echo "Response Status: " . $resp->Response->ResponseStatus->Description . "\n";
    //   echo "<pre>";
       

//		if (isset($params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['MONETARYVALUE'])) {
		if (isset($params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['NEGOTIATEDRATES']['NETSUMMARYCHARGES']['GRANDTOTAL']['MONETARYVALUE'])) {
	    $time = time();
//	    $return_data_0 = $params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['MONETARYVALUE'];	      
	    $return_data_0 = $params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['NEGOTIATEDRATES']['NETSUMMARYCHARGES']['GRANDTOTAL']['MONETARYVALUE'];
//echo $return_data_0	; 
	    $return_data_1 = '<span class="service_rate">';
	    $GUARANTEEDDAYSTODELIVERY = "";
	    foreach ($timeInTransits->TransitResponse->ServiceSummary AS $timeInTransit) {
		if ($service === $this->UPSTNTtoUPSRATES($timeInTransit->Service->Code)) {
		    if (isset($timeInTransit->EstimatedArrival->Arrival->Date)) {
			$GUARANTEEDDAYSTODELIVERY = 'Arrives ' . date('l, M d', strtotime($timeInTransit->EstimatedArrival->Arrival->Date));
		    }
		}
	    }
 	    if ($GUARANTEEDDAYSTODELIVERY == "") {
		if (isset($params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['GUARANTEEDDAYSTODELIVERY'])) {
		    $GUARANTEEDDAYSTODELIVERY = $params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['GUARANTEEDDAYSTODELIVERY'];
		    $GUARANTEEDDAYSTODELIVERY = 'Arrives ' . date('l, M d', mktime(0, 0, 0, date("n", $time), date("j", $time) + $GUARANTEEDDAYSTODELIVERY, date("Y", $time)));
		}
	    }
	    $return_data_2 = '</span> <span class="service_arrival">' . $GUARANTEEDDAYSTODELIVERY . '</span>';
	    $return_data = array("price" => $return_data_0, "rate_p_1" => $return_data_1, "rate_p_2" => $return_data_2);
	    return $return_data;
	}
    }

    function UPSTNTtoUPSRATES($serviceCode) {
	switch ($serviceCode) {
	    case "1DA":
		$result = "01";
		break;
	    case "2DA":
		$result = "02";
		break;
	    case "GND":
		$result = "03";
		break;
	    case "01":
		$result = "07";
		break;
	    case "05":
		$result = "08";
		break;
	    case "08":
		$result = "11";
		break;
	    case "03":
		$result = "11";
		break;
	    case "3DS":
		$result = "12";
		break;
	    case "1DP":
		$result = "13";
		break;
	    case "1DM":
		$result = "14";
		break;
	    case "21":
		$result = "54";
		break;
	    case "2DM":
		$result = "59";
		break;
	    case "10":
		$result = "65";
		break;
	    default:
		$result = "";
		break;
	}
	return $result;
    }

}

?>
