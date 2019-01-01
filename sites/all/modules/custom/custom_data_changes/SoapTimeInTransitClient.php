<?php

// Transit API
//Configuration
$wsdl = "http://" . $_SERVER["HTTP_HOST"] . "/sites/all/modules/custom_data_changes/SCHEMA-WSDLs/TNTWS.wsdl";
$operation = "ProcessTimeInTransit";
$endpointurl = 'https://wwwcie.ups.com/webservices/TimeInTransit';

try {
    $mode = array(
	'soap_version' => 'SOAP_1_1', // use soap 1.1 client
	'trace' => 1
    );

// initialize soap client
    $client = new SoapClient($wsdl, $mode);

//set endpoint url
    $client->__setLocation($endpointurl);

//create soap header
    $usernameToken['Username'] = $ups_username;
    $usernameToken['Password'] = $ups_password;
    $serviceAccessLicense['AccessLicenseNumber'] = $ups_accessnumber;
    $upss['UsernameToken'] = $usernameToken;
    $upss['ServiceAccessToken'] = $serviceAccessLicense;

    $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $upss);
    $client->__setSoapHeaders($header);

//create soap request
    $requestoption['RequestOption'] = 'TNT';
    $request['Request'] = $requestoption;

    $addressFrom['City'] = 'San Diego';
    $addressFrom['CountryCode'] = 'US';
    $addressFrom['PostalCode'] = $fromzip;
    $addressFrom['StateProvinceCode'] = 'CA';
    $shipFrom['Address'] = $addressFrom;
    $request['ShipFrom'] = $shipFrom;
//    $addressTo['City'] = $toCity;
    $addressTo['CountryCode'] = $tocountry;
    $addressTo['PostalCode'] = $tozip;
//    $addressTo['StateProvinceCode'] = $toState;
    $shipTo['Address'] = $addressTo;
    $request['ShipTo'] = $shipTo;

    $pickup['Date'] = date("Ymd");

    $request['Pickup'] = $pickup;

    $unitOfMeasurement['Code'] = "LBS";
//	    $unitOfMeasurement['Description'] = 'Pound';
    $shipmentWeight['UnitOfMeasurement'] = $unitOfMeasurement;
    $shipmentWeight['Weight'] = $weight;
    $request['ShipmentWeight'] = $shipmentWeight;

    $request['TotalPackagesInShipment'] = '1';
    $invoiceLineTotal['CurrencyCode'] = 'USD';
    $invoiceLineTotal['MonetaryValue'] = $sub_total;
    $request['InvoiceLineTotal'] = $invoiceLineTotal;
    $request['MaximumListSize'] = '1';
//get response
    $timeInTransits = $client->__soapCall($operation, array($request));
} catch (Exception $e) {
//    print_r($e);
}
?>