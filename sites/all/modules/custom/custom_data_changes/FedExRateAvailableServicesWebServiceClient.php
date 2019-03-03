<?php
$actual_shipping_date= shipping_date_calculator ();
$data = "<?xml version='1.0'?>  
	<FDXRateAvailableServicesRequest xmlns:api='http://www.fedex.com/fsmapi' xmlns:xsi='http://www.w3.org/2001/XMLSchemainstance' xsi:noNamespaceSchemaLocation='FDXRateAvailableServicesRequest.xsd'>
		<RequestHeader>
			<AccountNumber>1vN6TtJYilEbreCVQktuMg4pX@t9giZERJ1SP8pkBT@353527843@103951883</AccountNumber>
			<MeterNumber>103951883</MeterNumber>
		</RequestHeader>
		<ShipDate>" . date_format($actual_shipping_date,"c") . "</ShipDate>
		<DropoffType>REGULAR_PICKUP</DropoffType>
		<Packaging>YOUR_PACKAGING</Packaging>
		<WeightUnits>LBS</WeightUnits>
		<Weight>" . $actual_weight . "</Weight>
		<ListRate>0</ListRate>
		<OriginAddress>
		    <PostalCode>92154</PostalCode>
		    <CountryCode>US</CountryCode>
		</OriginAddress>
		<DestinationAddress>
		    <PostalCode>" . str_replace('+', '', $postcode) . "</PostalCode>
		    <CountryCode>" . $tocountry . "</CountryCode>
		</DestinationAddress>
		<Payment>
			<PayorType>SENDER</PayorType>
		</Payment>
		<Dimensions>
			<Length>12</Length>
			<Width>16</Width>
			<Height>6</Height>
			<Units>IN</Units>
		</Dimensions>
		<PackageCount>1</PackageCount>
		<SpecialServices>
			<ResidentialDelivery>TRUE</ResidentialDelivery>
		</SpecialServices>
	</FDXRateAvailableServicesRequest>";

//$ch = curl_init("http://raladexan.com/apiratexlator.aspx");
//Fedex rate server changed 5.18.2018:

$ch = curl_init("http://fdxbst.servebbs.com:50384/apiratexlator.aspx");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$result = curl_exec($ch);
 
$xml_results = new SimpleXMLElement($result);
$xml_results = json_decode(json_encode((array) $xml_results), false);
 // print "<pre>";
 // print_r($xml_results);
 // print "</pre>";
foreach ($xml_results->Entry AS $xml_result) {
    $serviceTypeFedExName = getFedExUnderScoredName($xml_result->Service);
    if (strpos($serviceTypeFedExName, "First Overnight") !== false) {
	continue;
    }
    $service_name = getLiveSiteName($serviceTypeFedExName, $tocountry);
    $service_name_complete = '<span class="ups5 service_name">' . $service_name . '</span>';
    $actual_amount = $xml_result->EstimatedCharges->DiscountedCharges->NetCharge;
 

// test change 
// another test
   
//echo '<pre>'; print_r($service_name); echo'</pre>'; 
//echo '<pre>'; print_r($service_name_complete); echo'</pre>'; 
//echo '<pre>'; print_r($actual_amount); echo'</pre>'; 
// FedEx Ground
// FedEx Ground
// 15.2

// Make free Fedex Ground: uncomment next 3 lines
// Removed free shipping: 2/27/2019
    
//	if (strpos($service_name, 'FedEx Ground') !== false) {
//		$actual_amount = 0;
//	}

// FedEx Ground CA needs to be excluded when making free Fedex Ground shipping
 
// 7/10/2018 - remove handling fees: comment next line
// 2/27/2019 - re-activated handling fees

    $actual_amount += getFedExHandlingFees($serviceTypeFedExName, $sub_total, $tocountry);

    $amount = '<span class="service_rate">' . number_format($actual_amount, 2, ".", ",") . '</span>';
    
    
    //dpr($xml_result);
    $deliveryDate = "";
    if (isset($xml_result->DeliveryDate)) {
	$deliveryDate = '<span class="service_arrival"> Ship Date: ' .date_format($actual_shipping_date,"l,M d").'<br/>'.'Arrival Date: ' . date('l, M d', strtotime($xml_result->DeliveryDate)) . '</span>';
    } else if (isset($xml_result->TimeInTransit)) {
		$loopTimer = $xml_result->TimeInTransit;
		// $arrivalTimeTransit = date('l, M d', strtotime("+" .$xml_result->TimeInTransit.' days'));
		$arrivalTimeTransit =   date_format($actual_shipping_date,"l,M d");
		//dpr($arrivalTimeTransit);

		while ($loopTimer > 0 ){		    
		    if( IsAHolidayOrSaturdaySunday($arrivalTimeTransit) ){
		    	$arrivalTimeTransit = date('l, M d',strtotime($arrivalTimeTransit."+1 days"));
		    }else{
	    		$arrivalTimeTransit = date('l, M d',strtotime($arrivalTimeTransit."+1 days"));
	    		$loopTimer = $loopTimer - 1;
		    }
		}

		$deliveryDate = '<span class="service_arrival">Ship Date: ' .date_format($actual_shipping_date,"l,M d").'<br/>'.'Arrival Date: ' . $arrivalTimeTransit . '</span>';

    } else {
		$timetoadd = time() + 86400;
		if (date('l') == "Friday") {
		    $timetoadd = time() + (86400 * 3);
		} else if (date('l') == "Saturday") {
		    $timetoadd = time() + (86400 * 2);
		}
		$deliveryDate = '<span class="service_arrival">Ship Date: ' .date_format($actual_shipping_date,"l,M d").'<br/>'.'Arrival Date: ' . date('l, M d', $timetoadd) . '</span>';
    }

    echo '<div class="form-item shipping_rates_bar form-item-panes-quotes-quotes-quote-option form-type-radio radio" id="edit-panes-quotes-quotes-quote-option-ups-' . $serviceTypeFedExName . '">'
    . '<label class="control-label" for="edit-panes-quotes-quotes-quote-option-usps-7"><input type="radio"   style="display:none;"  name="panes[quotes][quotes][quote_option]"'
    . ' value="customfedex---' . $serviceTypeFedExName . '"  class="form-radio ajax-processed">' . $service_name_complete . $amount . $deliveryDate . '<label></div>';
}


function IsAHolidayOrSaturdaySunday( $dateShip ){
	$TodayIsHoliday = false;
	$dateDay = date('l',strtotime($dateShip));
	$dateDayMonth = date('Y-m-d',strtotime($dateShip));
	if( $dateDay == 'Sunday'|| $dateDay == 'Saturday' ){
		$TodayIsHoliday = true;
	}

	$FedexHolidays = array();

	if( date('Y') == 2015 ){
		$FedexHolidays = array("2015-01-01" , "2015-05-25" , "2015-07-04" , "2015-09-07" , "2015-11-26" , "2015-12-25" );
	}
	if( date('Y') == 2016 ){
		$FedexHolidays = array("2016-01-01" , "2016-05-30" , "20166-07-04" , "2016-09-05" , "2016-11-24" , "2016-12-25" );
	}
	if( date('Y') == 2017 ){
		$FedexHolidays = array("2017-01-01" , "2017-05-29" , "2017-07-04" , "2017-09-04" , "2017-11-23" , "2017-11-24" , "2017-12-25" );
	}
	if( date('Y') == 2018 ){
		$FedexHolidays = array("2018-01-01" , "2018-05-28" , "2018-07-04" , "2018-09-03" , "2018-11-22" , "2018-12-25" );
	}
	if( date('Y') == 2019 ){
		$FedexHolidays = array("2019-01-01" , "2019-05-27" , "2019-07-04" , "2019-09-02" , "2019-11-28" , "2019-12-25" );
	}
	foreach( $FedexHolidays as $ddate ){
		if( $dateDayMonth == $ddate ){
			$TodayIsHoliday = true;
		}
	}

	return $TodayIsHoliday;
}

?>