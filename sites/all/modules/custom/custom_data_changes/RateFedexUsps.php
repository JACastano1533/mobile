<?php

/** this class has functions that will give shipping rate 
 *  with provided source and destination address
 *  mainly from Fedex and USPS
 * */
class RateFedexUsps {

    /**
     * 
     * @param integer $zip_from - source zip
     * @param integer $zip_to - destination zip
     * @param float $weight_pounds - total pounds of order
     * @param float $weight_ounces - total ounces of order
     * @return array
     */
    public function getUspsRate($zip_from,$zip_to,$weight_pounds,$weight_ounces) {
        
        $ship_cnt = 0;
        $response_array = array();
        
        $service_type = array("First+Class","Priority","Express");
        
        for($j=0;$j<count($service_type);$j++)
        {
        
            $str = 'API=RateV4&XML=<RateV4Request USERID="362DONNA3562" PASSWORD="">'
                    . '<Package ID="0">'
                        . '<Service>'.$service_type[$j].'</Service>'
                        . '<FirstClassMailType>Parcel</FirstClassMailType>'
                        . '<ZipOrigination>'.$zip_from.'</ZipOrigination>'
                        . '<ZipDestination>'.$zip_to.'</ZipDestination>'
                        . '<Pounds>'.$weight_pounds.'</Pounds>'
                        . '<Ounces>'.$weight_ounces.'</Ounces>'
                        . '<Container></Container>'
                        . '<Size>REGULAR</Size>'
                        . '<Machinable>True</Machinable>'
                    . '</Package></RateV4Request>';

            $url = "http://Production.ShippingAPIs.com/ShippingAPI.dll";

            $cu = curl_init();
            curl_setopt($cu, CURLOPT_URL, $url);
            curl_setopt($cu, CURLOPT_VERBOSE, 1);
            //curl_setopt($cu, CURLOPT_HEADER, 1);
            curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cu, CURLOPT_POSTFIELDS, $str);

            $body = curl_exec($cu);

            $xml = simplexml_load_string($body);

            //echo "<pre>";
            //print_r($xml);
            for ($i = 0; $i < count($xml->Package->Postage); $i++) 
            {
                $response_array[$ship_cnt]["rate"] = json_decode($xml->Package->Postage[$i]->Rate);
                $response_array[$ship_cnt]["service"] = $xml->Package->Postage[$i]->MailService;

                $ship_cnt++;
            }
        }
        
        return $response_array;
    }

    /* fedex related functions */
    /** 
     * @param $store is store details
     * @param $weight is total weight
     * @param $ship_address1 is shipping address line 1
     * @param $ship_address2 is shipping address line 2
     * @param $ship_city is ship city
     * @param $ship_state is ship state
     * @param $ship_zip is ship postal code
     * @param $ship_country is ship country. Default is US
     * @return array 
     */

    public function getFedexRate($weight,$ship_zip,$ship_country) {

        
        
        $opts = array(
            'http'=>array(
            'user_agent' => 'PHPSoapClient'
            )
        );

        $context = stream_context_create($opts);

        $soapClientOptions = array(
            'stream_context' => $context,
            'cache_wsdl' => WSDL_CACHE_NONE
        );
        $wsdlUrl = "https://m.roofbag.com/sites/all/modules/custom_data_changes/RateService_v10.wsdl";
        //$client = new SoapClient($wsdlUrl, $soapClientOptions);

        
        
        
        //ini_set("soap.wsdl_cache_enabled", "0");
        
        $client = new SoapClient($wsdlUrl, $soapClientOptions); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
                
        

        $request = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key' => $this->getProperty('key'),
                    'Password' => $this->getProperty('password')
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $this->getProperty('shipaccount'),
                'MeterNumber' => $this->getProperty('meter')
            ),
            'TransactionDetail' => array(
                'CustomerTransactionId' => '*** Rate Request v10 using PHP ***'
            ),
            'Version' => array(
                'ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0'
            ),
            'ReturnTransitAndCommit' => true,
            'RequestedShipment' => array(
                'ShipTimestamp' => date('c'),
                'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
                //'ServiceType' => '', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
                'PackagingType' => 'YOUR_PACKAGING',
                'TotalInsuredValue' => array('Amount' => 0, 'Currency' => "USD"),
                'TotalWeight' => array(
                    'Value' => number_format($weight, 2),
                    'Units' => "LB"
                ),                
                'Shipper' => array(
                    'Address' => array(
                        /*'StreetLines' => array(
                            $store->address1,
                            $store->address2
                        ),*/ // Origin details
                        //'City' => 'San Diego',
                        'StateOrProvinceCode' => 'CA',
                        'PostalCode' => 92154,
                        'CountryCode' => 'US'
                    )
                ),
                'Recipient' => array(
                    'Address' => array(
                        /*'StreetLines' => array(
                            $ship_address1,
                            $ship_address2
                            
                        ), // Destination details
                        'City' => $ship_city,
                        'StateOrProvinceCode' => $ship_state,*/
                        //'City' => 'Los Angeles',
                        'StateOrProvinceCode' => 'CA',
                        'PostalCode' => $ship_zip,
                        'CountryCode' => $ship_country,
                        'Residential' => true
                    )
                ),
                'ShippingChargesPayment' => array(
                    'PaymentType' => 'SENDER',
                    'Payor' => array(
                        'AccountNumber' => '480809688',
                        'CountryCode' => 'US'
                    )
                ),
                'RateRequestTypes' => 'ACCOUNT', //'LIST' or 'ACCOUNT'	
                'PackageCount' => 1,
                'PackageDetail' => 'PACKAGE_SUMMARY', //'INDIVIDUAL_PACKAGES',  //  Or PACKAGE_SUMMARY
                'RequestedPackageLineItems' => array(
                    'SequenceNumber' => 1,
                    'GroupPackageCount' => 1,
                    'Weight' => [
                        'Units' => 'LB',
                        'Value' => number_format($weight, 2)
                    ],
                    'Dimensions' => array(
                        'Length' => 12,
                        'Width' => 16,
                        'Height' => 6,
                        'Units' => "IN"
                    ),
                    'PackageCount' => 1
                )
            )
        );


        
        try {
            if ($this->setEndpoint('changeEndpoint')) {
                $newLocation = $client->__setLocation($this->setEndpoint('endpoint'));
            }

            $response = $client->getRates($request);
        //print "<pre>";
        //print_r($request);
        //print_r($response);
        //print "</pre>";            
            
            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR' && $response->HighestSeverity != 'WARNING') {
                //success
                $response_array = array();
                                
                $i=0;
                foreach ($response->RateReplyDetails as $rate_items) {
                    //echo '<pre>'; print_r($rate_items); echo '</pre>'; exit;
                    $serviceTypeFedExName = $response_array[$i]["service"]= $rate_items->ServiceType;
                    if (strpos($serviceTypeFedExName, "First Overnight") !== false) {
                        continue;
                    }
                    $service_name = getLiveSiteName($serviceTypeFedExName, $tocountry);
                    $service_name_complete = '<span class="ups5 service_name">' . $service_name . '</span>';
                    
                    //$response_array[$i]["delivery"]= $rate_items->DeliveryTimestamp;
                    //$response_array[$i]["day"]= $rate_items->DeliveryDayOfWeek;
                    if ($rate_items->RatedShipmentDetails && is_array($rate_items->RatedShipmentDetails)) {
                        $actual_amount = $rate_items->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
                        
                        $amount = number_format($rate_items->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount, 2, ".", ",") ;
                    } elseif ($rate_items->RatedShipmentDetails && !is_array($rate_items->RatedShipmentDetails)) {
                        $actual_amount = $rate_items->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount;
                        
                    }
                    $actual_amount += getFedExHandlingFees($serviceTypeFedExName, $sub_total, $tocountry);
                    $amount = '<span class="service_rate">' . number_format($actual_amount, 2, ".", ",") . '</span>';

                    //$response_array[$i]["rate"]=$amount;
                    $deliveryDate = "";
                    if (isset($rate_items->DeliveryTimestamp)) {
                    $deliveryDate = '<span class="service_arrival">Arrives ' . date('l, M d', strtotime($rate_items->DeliveryTimestamp)) . '</span>';
                    } else {
                    $timetoadd = time() + 86400;
                    if (date('l') == "Friday") {
                        $timetoadd = time() + (86400 * 3);
                    } else if (date('l') == "Saturday") {
                        $timetoadd = time() + (86400 * 2);
                    }
                    $deliveryDate = '<span class="service_arrival">Arrives ' . date('l, M d', $timetoadd) . '</span>';
                    }

                    echo '<div class="form-item shipping_rates_bar form-item-panes-quotes-quotes-quote-option form-type-radio radio" id="edit-panes-quotes-quotes-quote-option-ups-' . $serviceTypeFedExName . '">'
    . '<label class="control-label" for="edit-panes-quotes-quotes-quote-option-usps-7"><input type="radio"   style="display:none;"  name="panes[quotes][quotes][quote_option]"'
    . ' value="customfedex---' . $serviceTypeFedExName . '"  class="form-radio ajax-processed">' . $service_name_complete . $amount . $deliveryDate . '<label></div>';

                    $i++;
                } //exit;
                
                return $response_array;
            } else {
                return $this->printError($client, $response);
            }
            
        } catch (SoapFault $exception) {
            $this->printFault($exception, $client);
        }
    }

    

    public function printReply($client, $response) {
        /*$highestSeverity = $response->HighestSeverity;
        
        if ($highestSeverity == "SUCCESS") {
            echo 'The transaction was successful.';
        }
        if ($highestSeverity == "WARNING") {
            echo 'The transaction returned a warning.';
        }
        if ($highestSeverity == "ERROR") {
            echo 'The transaction returned an Error.';
        }
        if ($highestSeverity == "FAILURE") {
            echo 'The transaction returned a Failure.';
        }*/
        //echo "\n";
        return $response->Notifications->Message;
        
        //$this->printNotifications($response->Notifications);
        //$this->printRequestResponse($client, $response);
    }

    public function printRequestResponse($client) {
        echo '<h2>Request</h2>' . "\n";
        echo '' . htmlspecialchars($client->__getLastRequest()) . '';
        //echo $client->__getLastRequest();  
        echo "\n";

        echo '<h2>Response</h2>' . "\n";
        echo '<pre>' . htmlspecialchars($client->__getLastResponse()) . '</pre>';
        echo "\n";
    }

    /**
     *  Print SOAP Fault
     */
    public function printFault($exception, $client) {
        echo '<h2>Fault</h2>' . "<br>\n<pre>";

        echo "<pre>";
        print_r($exception->detail);
        echo "</pre>";


        echo "<b>Code:</b>{$exception->faultcode}<br>\n";
        echo "<b>String:</b>{$exception->faultstring}<br>\n";

        
        echo '<h2>Request</h2>' . "\n";
        //echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
        //echo "\n";
    }

    

    /**
     * This section provides a convenient place to setup many commonly used variables
     * needed for the php sample code to function.
     */
    public function getProperty($var) {
        if ($var == 'key')
            Return 'dDA83sqV3xFWXM6N';
        if ($var == 'password')
            Return 't4VSXDNrkWfBAcIIbXExvrMla';
        if ($var == 'shipaccount')
            Return '480809688';
        if ($var == 'billaccount')
            Return '480809688';
        if ($var == 'dutyaccount')
            Return '480809688';
        if ($var == 'freightaccount')
            Return '480809688';
        if ($var == 'trackaccount')
            Return '480809688';
        if ($var == 'dutiesaccount')
            Return 'XXX';
        if ($var == 'importeraccount')
            Return 'XXX';
        if ($var == 'brokeraccount')
            Return 'XXX';
        if ($var == 'distributionaccount')
            Return 'XXX';
        if ($var == 'locationid')
            Return 'PLBA';
        if ($var == 'printlabels')
            Return false;
        if ($var == 'printdocuments')
            Return true;
        if ($var == 'packagecount')
            Return '4';

        if ($var == 'meter')
            Return '104064306';

        if ($var == 'shiptimestamp')
            Return mktime(10, 0, 0, date("m"), date("d") + 1, date("Y"));

        if ($var == 'spodshipdate')
            Return '2014-07-21';
        if ($var == 'serviceshipdate')
            Return '2017-07-26';

        if ($var == 'readydate')
            Return '2014-07-09T08:44:07';
        //if($var == 'closedate') Return date("Y-m-d");
        if ($var == 'closedate')
            Return '2014-07-17';
        if ($var == 'pickupdate')
            Return date("Y-m-d", mktime(8, 0, 0, date("m"), date("d") + 1, date("Y")));
        if ($var == 'pickuptimestamp')
            Return mktime(8, 0, 0, date("m"), date("d") + 1, date("Y"));
        if ($var == 'pickuplocationid')
            Return 'XXX';
        if ($var == 'pickupconfirmationnumber')
            Return '1';

        if ($var == 'dispatchdate')
            Return date("Y-m-d", mktime(8, 0, 0, date("m"), date("d") + 1, date("Y")));
        if ($var == 'dispatchlocationid')
            Return 'XXX';
        if ($var == 'dispatchconfirmationnumber')
            Return '1';

        if ($var == 'tag_readytimestamp')
            Return mktime(10, 0, 0, date("m"), date("d") + 1, date("Y"));
        if ($var == 'tag_latesttimestamp')
            Return mktime(20, 0, 0, date("m"), date("d") + 1, date("Y"));

        if ($var == 'expirationdate')
            Return date("Y-m-d", mktime(8, 0, 0, date("m"), date("d") + 15, date("Y")));
        if ($var == 'begindate')
            Return '2014-07-22';
        if ($var == 'enddate')
            Return '2014-07-25';

        if ($var == 'trackingnumber')
            Return 'XXX';

        if ($var == 'hubid')
            Return '5531';

        if ($var == 'jobid')
            Return 'XXX';

        if ($var == 'searchlocationphonenumber')
            Return '5555555555';
        if ($var == 'customerreference')
            Return 'Cust_Reference';

        if ($var == 'shipper')
            Return array(
                'Contact' => array(
                    'PersonName' => 'Sender Name',
                    'CompanyName' => 'Sender Company Name',
                    'PhoneNumber' => '1234567890'
                ),
                'Address' => array(
                    'StreetLines' => array('Address Line 1'),
                    'City' => 'Collierville',
                    'StateOrProvinceCode' => 'TN',
                    'PostalCode' => '38017',
                    'CountryCode' => 'US',
                    'Residential' => 1
                )
            );
        if ($var == 'recipient')
            Return array(
                'Contact' => array(
                    'PersonName' => 'Recipient Name',
                    'CompanyName' => 'Recipient Company Name',
                    'PhoneNumber' => '1234567890'
                ),
                'Address' => array(
                    'StreetLines' => array('Address Line 1'),
                    'City' => 'Herndon',
                    'StateOrProvinceCode' => 'VA',
                    'PostalCode' => '20171',
                    'CountryCode' => 'US',
                    'Residential' => 1
                )
            );

        if ($var == 'address1')
            Return array(
                'StreetLines' => array('10 Fed Ex Pkwy'),
                'City' => 'Memphis',
                'StateOrProvinceCode' => 'TN',
                'PostalCode' => '38115',
                'CountryCode' => 'US'
            );
        if ($var == 'address2')
            Return array(
                'StreetLines' => array('13450 Farmcrest Ct'),
                'City' => 'Herndon',
                'StateOrProvinceCode' => 'VA',
                'PostalCode' => '20171',
                'CountryCode' => 'US'
            );
        if ($var == 'searchlocationsaddress')
            Return array(
                'StreetLines' => array('240 Central Park S'),
                'City' => 'Austin',
                'StateOrProvinceCode' => 'TX',
                'PostalCode' => '78701',
                'CountryCode' => 'US'
            );

        if ($var == 'shippingchargespayment')
            Return array(
                'PaymentType' => 'SENDER',
                'Payor' => array(
                    'ResponsibleParty' => array(
                        'AccountNumber' => getProperty('billaccount'),
                        'Contact' => null,
                        'Address' => array('CountryCode' => 'US')
                    )
                )
            );
        if ($var == 'freightbilling')
            Return array(
                'Contact' => array(
                    'ContactId' => 'freight1',
                    'PersonName' => 'Big Shipper',
                    'Title' => 'Manager',
                    'CompanyName' => 'Freight Shipper Co',
                    'PhoneNumber' => '1234567890'
                ),
                'Address' => array(
                    'StreetLines' => array(
                        '1202 Chalet Ln',
                        'Do Not Delete - Test Account'
                    ),
                    'City' => 'Harrison',
                    'StateOrProvinceCode' => 'AR',
                    'PostalCode' => '72601-6353',
                    'CountryCode' => 'US'
                )
            );
    }

    public function setEndpoint($var) {
        if ($var == 'changeEndpoint')
            Return false;
        if ($var == 'endpoint')
            Return 'XXX';
    }

    public function printNotifications($notes) {
        foreach ($notes as $noteKey => $note) {
            if (is_string($note)) {
                echo $noteKey . ': ' . $note . "<br />";
            } else {
                $this->printNotifications($note);
            }
        }
        echo "<br />";
    }

    public function printError($client, $response) {
        return $this->printReply($client, $response);
    }

    /* end of fedex related functions */
}


$shipping_class = new RateFedexUsps();
$weight = $actual_weight;
$ship_zip = $postcode;
$ship_country= $tocountry;

$shipping_methods_fedex = $shipping_class->getFedexRate($weight,$ship_zip,$ship_country);

//echo '<pre>'; print_r($shipping_methods_fedex); echo '<pre>'; exit();