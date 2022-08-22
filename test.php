<?php

    $con_hostinger = $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

    //$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
    // if (mysqli_connect_errno()) {
    //     die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    // }else{
    //     echo "Connected: ";
    // }

    // $query = "SELECT * FROM temporders LIMIT 1";
    // $result = mysqli_query($connect, $query);
    // while($row = mysqli_fetch_array($result)){
    //     print_r($row);
    // }

    // $query = "SELECT * FROM temporders";
    // $result = mysqli_query($connect, $query);
    // while($row = mysqli_fetch_array($result)){

    // // foreach ($ids as $value) {
    //     // $result = mysqli_query($connect, "SELECT * FROM temporders WHERE id='" . $value . "'");
    //     // $row = mysqli_fetch_array($result);
    //     $status = "Pending";
    //     $phpdate = strtotime($row['date']);
    //     $date = date('Y-m-d', $phpdate);
    //     $flags = $row['flags'];
    //     if ($row['merge'] == "Merged") {
    //         $flags = $row['flags'] . ", Merged";
    //     }

    //     $firstname = $row['firstname'];
    //     $firstname = str_replace("'", "\'", $firstname);

    //     $productName = $row['name'];
    //     $productName = str_replace("'", "\'", $productName);

    //     $shippingaddressline1 = $row['shippingaddressline1'];
    //     $shippingaddressline1 = str_replace("'", "\'", $shippingaddressline1);

    //     $shippingaddressline2 = $row['shippingaddressline2'];
    //     $shippingaddressline2 = str_replace("'", "\'", $shippingaddressline2);

    //     $shippingaddressline3 = $row['shippingaddressline3'];
    //     $shippingaddressline3 = str_replace("'", "\'", $shippingaddressline3);

    //     $shippingaddressregion = $row['shippingaddressregion'];
    //     $shippingaddressregion = str_replace("'", "\'", $shippingaddressregion);

    //     $shippingaddresscity = $row['shippingaddresscity'];
    //     $shippingaddresscity = str_replace("'", "\'", $shippingaddresscity);

    //     $shippingaddresspostcode = $row['shippingaddresspostcode'];
    //     $shippingaddresspostcode = str_replace("'", "\'", $shippingaddresspostcode);

    //     $notes = $row['notes'];
    //     $notes = str_replace("'", "\'", $notes);

    //     $sql = "UPDATE orders SET firstname='$firstname',sku='".$row['sku']."',sku='".$row['sku']."',shipment_id='".$row["shipment_id"]."', TrackingNumber='".$row["tracking_No"]."', PostalService='".$row["postal_service"]."', royalmail_order_id='".$row["royalmail_order_id"]."', p2go_id='".$row["p2go_id"]."', p2go_hash='".$row["p2go_hash"]."' WHERE orderID='" . $row["orderID"] . "' AND shippingaddresspostcode='" . $row["shippingaddresspostcode"] . "'";

    //     //$sql = "INSERT into orders (orderID, status, date)
    //     // values ('". $row['orderID'] ."','". $status ."','". $row['date'] ."')";
    //     // $deletesql = "DELETE FROM temporders WHERE id = '".$value."'";
    //     $update = mysqli_query($con_hostinger, $sql);

    //     if($update){
    //         echo "suceess<br>";
    //     }else{
    //         echo "error ".$row['id']."<br>";
    //     }
    // }

    // include "labelGeneration/refreshStore.php";

    // $url = file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

    // $xml =  new SimpleXMLElement($url);

    // file_put_contents(dirname(__FILE__) . "/loc.xml", $xml->asXML());

    // $currency = "CAD";

    // $ordertotal = 10;

    // print_r($ordertotal." ".$currency);

    // if ($currency != "GBP") {

    //     foreach ($xml->Cube->Cube->Cube as $rate) {
    //         if ($rate["currency"] == $currency) {
    //             $rate = $rate["rate"];
    //             break;
    //         }
    //     }

    //     print_r("<br><br>rate conversion - ".$rate."<br><br>");

    //     $currency = "GBP";

    //     if ($ordertotal > 0) {

    //       $ordertotal = $ordertotal / $rate;
    //     }
    // }

    // $ordertotal = number_format($ordertotal, 2, '.', '');

    // print_r("<br><br>");
    // print_r($ordertotal." ".$currency);

    function fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $METHOD,
            CURLOPT_POSTFIELDS => $POST_FIELDS,
            CURLOPT_HTTPHEADER => $HTTP_HEADER
        ));

        $response = curl_exec($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        
        $response_array = "";

        if($ContentType == "XML"){
            $responseXml = simplexml_load_string($response) or die("Error: Cannot create encode data to xml object");
            $jsondata = json_encode($responseXml) or die("Error: Cannot encode record to json");
            $response_array = json_decode($jsondata, true);

            if($responseXml == "Error: Cannot create encode data to xml object"){
                $response_code = 400;
            }

            if($jsondata == "Error: Cannot encode record to json"){
                $response_code = 400;
            }
        }else if($ContentType == "PDF"){
            $response_array = base64_encode($response);
        }else if($ContentType == "JSON"){
            $response_array = json_decode($response, true);
        }

        return array("responsecode" => $response_code,"responsearray" => $response_array);
    }

    function CreateSelroOrder($orderNumber, $fullName, $street1, $street2, $street3, $city, $state, $postalCode, $country, $countryCode, $phone, $email, $sku, $name, $quantity, $unitPrice, $chosenPostal){        
        $URL = 'http://app6.selro.com/api/order?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570';
        $METHOD = 'POST';
        // purchaseDate must be in timestamps - done
        $POST_FIELDS = '{
            "orderId": "'.$orderNumber.'",
            "shipName": "'.$fullName.'",
            "shipPostalCode":"'.$postalCode.'",
            "shipAddress1":"'.$street1.'",
            "shipAddress2":"'.$street2.'",
            "shipAddress3":"'.$street3.'",
            "shipCity":"'.$city.'",
            "shipState": "'.$state.'",
            "shipCountry":"'.$country.'",
            "shipCountryCode":"'.$countryCode.'",
            "shipPhoneNumber": "'.$phone.'",
            "buyerEmail": "'.$email.'",
            "channelId": 25838,
            "channelSales": [
                {
                    "qty":'.$quantity.',
                    "title":"'.$name.'",
                    "sku":"'.$sku.'",
                    "price":'.round($unitPrice,2).',
                    "totalprice":'.round(($quantity*$unitPrice),2).',
                    "taxamount":0.00,
                    "channelId": 25838
                }
            ],
            "totalPrice":'.round(($quantity*$unitPrice),2).',
            "shippingMethod":"'.$chosenPostal.'"
        }';
        
        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');
        $ContentType = "JSON";
        
        $createReplacementOrder = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
        
        $orderCreated = false;
        $error = "";
        $selroOrderID = "";
        
        if($createReplacementOrder['responsecode']=="200"){
            $createReplacementOrderResponse = $createReplacementOrder['responsearray'];
            
            if(array_key_exists('message',$createReplacementOrderResponse)){
                if($createReplacementOrderResponse['message'] == ""){
                    $orderCreated = true;
                    $selroOrderID = $orderNumber;
                }else{
                    $error = $createReplacementOrderResponse['message'];
                }
            }
        }else{
            $error = "Error in Creating Order to selro.";
        }
        
        return array("orderCreated" => $orderCreated, "fullname" => $fullName, "selroOrderID" => $selroOrderID, "error" => $error);
    }

    // $orderIDD = "1798";
    // $orderResult = mysqli_query($con_hostinger, "SELECT * FROM `ukreplacementorders` WHERE `status` LIKE 'Open' AND id = ".$orderIDD);
    // $orderRow = mysqli_fetch_array($orderResult);

    // date_default_timezone_set('Europe/London');
    // $date = date('Y-m-d');
    // $dispatchdate= date('Y-m-d', strtotime($date. ' + 2 days'));
    // $time = date('H:i:s');

    // if($orderRow['linnworkid'] == ""){
    //     // $orderNumber="R".$orderRow['orderID'];
    //     $orderNumber = "R".$orderRow["originalorderid"]."-".$orderRow["id"];
    //     $orderDate = $date."T".$time;
    //     $shipDate = $dispatchdate."T".$time;
    //     $fullName = $orderRow["firstname"]." ".$orderRow["lastname"];
    //     $company = $orderRow['shippingaddresscompany'];
    //     $street1 = $orderRow['shippingaddressline1'];
    //     $street2 = $orderRow['shippingaddressline2'];
    //     $street3 = $orderRow['shippingaddressline3'];
    //     $city = $orderRow['shippingaddresscity'];
    //     $state = $orderRow['shippingaddressregion'];
    //     $postalCode = $orderRow['shippingaddresspostcode'];
    //     $country = $orderRow['shippingaddresscountry'];
    //     $countryCode = $orderRow['shippingaddresscountrycode'];
    //     $phone = $orderRow['telephone'];
    //     $email = $orderRow['email'];
    //     $lineItemKey = "Replacement".$orderRow['orderID'];
    //     $sku = $orderRow['sku'];
    //     $name = $orderRow['name'];
    //     $quantity = $orderRow['quantity'];
    //     $unitPrice = "3.1";
    //     $chosenPostal = $orderRow['shippingservice'];

    //     // $createReplacementOrder = CreateShipstationOrder($orderNumber, $orderDate, $fullName, $company, $street1, $street2, $street3, $city, $state, $postalCode, $countryCode, $phone, $email, $lineItemKey, $sku, $name, $quantity, $unitPrice, $shipDate, $chosenPostal);
    //     $createReplacementOrder = CreateSelroOrder($orderNumber, $fullName, $street1, $street2, $street3, $city, $state, $postalCode, $country, $countryCode, $phone, $email, $sku, $name, $quantity, $unitPrice, $chosenPostal);
    //     print_r($createReplacementOrder);

    //     if($createReplacementOrder['orderCreated']){
    //         $linnworkid = $createReplacementOrder["selroOrderID"];

    //         $query = "UPDATE ukreplacementorders SET linnworkid='".$linnworkid."' WHERE id='".$orderIDD."'"; 

    //         $updated = mysqli_query($con_hostinger, $query);

    //         if($updated){
    //             echo "<br><br>Added shipstation id";
    //         }
    //     }
    // }else{
    //     echo "<br><br>Already added shipstation id";
    // }

    // print_r("Puvii");

    // print_r(autoMergeOrders($con));

    // after this create parcel 2 go order

    // function fetchToken($CLIENT_ID, $CLIENT_SECRET){
    //     $ERROR_AUTH = array();

    //     $METHOD_POST = "POST";
    //     $AUTH_URL = "https://www.parcel2go.com/auth/connect/token";
    //     $HTTP_HEADER_AUTH = array('Content-Type: application/x-www-form-urlencoded');
    //     $POST_FIELDS_AUTH = 'grant_type=client_credentials&scope=public-api payment&client_id='.$CLIENT_ID.'&client_secret='.$CLIENT_SECRET;
    //     $AUTH_BOOLEAN = false;

    //     $getAuthentication = fetchDataFromURL($AUTH_URL, $METHOD_POST, $POST_FIELDS_AUTH, $HTTP_HEADER_AUTH);
    //     $response_code_Auth = $getAuthentication['responsecode'];
    //     if($response_code_Auth=="200"){
    //         $response_getAuthentication = $getAuthentication['responsearray'];
    //         if(array_key_exists('access_token',$response_getAuthentication)){
    //             $AUTH_BOOLEAN = true;
    //             $ACCESS_TOKEN = $response_getAuthentication['access_token'];
    //         }else{
    //             $ERROR_AUTH = $getAuthentication['responsearray'];
    //         }        
    //     }else{
    //         $ERROR_AUTH = $getAuthentication['responsearray'];
    //     }
        
    //     return array("auth" => $AUTH_BOOLEAN,"Token" => $ACCESS_TOKEN,"errorsAuth" => $ERROR_AUTH);
    // }

    // function createParcel2GoOrder($FirstName, $LastName, $Email, $AddressLine1, $AddressLine2, $AddressLine3, $Town, $PostCode, $Country, $CountryCode, $subtotal){
    //     $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
    //     $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

    //     $fetchToken = fetchToken($CLIENT_ID, $CLIENT_SECRET);
    //     $booleanAuth = $fetchToken['auth'];

    //     $orderCreated = false;

    //     $parcel2GoOrderId = "";
    //     $hashValue = "";
    //     $OrderLineId = "";
    //     $error = "";

    //     if($booleanAuth){
    //         $TOKEN = $fetchToken['Token'];

    //         $CountryIsoCode = $CountryCode; // country code in 2 digit

    //         $divideAddress = divideAddress1($AddressLine1);

    //         if($divideAddress['addressDivide']){
    //             $Property = $divideAddress['firstNumbers'];
    //             $Street = $divideAddress['restString'];

    //             $Locality = $AddressLine2;

    //             if($AddressLine3 != ""){
    //                 $Locality .= " ".$AddressLine3;
    //             }
    //         }else{
    //             $Property = $AddressLine1;
    //             $Street = $AddressLine2;

    //             $Locality = $AddressLine3;
    //         }

    //         $URL = 'https://www.parcel2go.com/api/orders';
    //         $METHOD = 'POST';
    //         $POST_FIELDS = '{
    //             "Items": [
    //                 {
    //                     "Id": "00000000-0000-0000-1234-000000000000",
    //                     "Parcels": [
    //                         {
    //                             "Id": "00000000-0000-0000-1234-000000000000",
    //                             "Height": 2.5,
    //                             "Length": 35,
    //                             "Weight": 1,
    //                             "Width": 23,
    //                             "EstimatedValue": '.$subtotal.',
    //                             "DeliveryAddress": {
    //                                 "ContactName": "'.$FirstName." ".$LastName.'",
    //                                 "Email": "'.$Email.'",
    //                                 "Property": "'.$Property.'",
    //                                 "Street": "'.$Street.'",
    //                                 "Locality": "'.$Locality.'",
    //                                 "Town": "'.$Town.'",
    //                                 "County": "'.$Country.'",
    //                                 "Postcode": "'.$PostCode.'",
    //                                 "CountryIsoCode": "'.$CountryIsoCode.'"
    //                             },
    //                             "ContentsSummary": "Slippers"
    //                         }
    //                     ],
    //                     "Service": "myhermes-parcelshop-postable",
    //                     "CollectionAddress": {
    //                         "ContactName": "Dan Chen",
    //                         "Organisation": "LEDSone UK Ltd",
    //                         "Email": "admin@ledsone.co.uk",
    //                         "Phone": "07522607969",
    //                         "Property": "Unit 18,",
    //                         "Street": "Lythal Lane, Lythal Lane Industrial Estate",
    //                         "Locality": "",
    //                         "Town": "COVENTRY",
    //                         "County": "United Kingdom",
    //                         "Postcode": "CV6 6FL",
    //                         "CountryIsoCode": "GBR"
    //                     }
    //                 }
    //             ],
    //             "CustomerDetails": {
    //                 "Email": "'.$Email.'",
    //                 "Forename": "'.$FirstName.'",
    //                 "Surname": "'.$LastName.'"
    //             }
    //         }';

    //         $HTTP_HEADER = array();
    //         array_push($HTTP_HEADER,'Authorization: Bearer '.$TOKEN,'Content-Type: application/json');

    //         $createParcel2GoOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

    //         if($createParcel2GoOrder['responsecode']=="200"){
    //             $createParcel2GoOrderResponse = $createParcel2GoOrder['responsearray'];

    //             if(array_key_exists('OrderId',$createParcel2GoOrderResponse)){
    //                 $orderCreated = true;
    //                 $parcel2GoOrderId = $createParcel2GoOrderResponse['OrderId'];
    //                 $hashValue = $createParcel2GoOrderResponse['Hash'];
    //                 $OrderLineId = ['OrderlineIdMap']['OrderLineId'];
    //             }else{
    //                 $error = "Order creating failed.";
    //             }
    //         }else{
    //             $error = "Error in Creating Parcel2Go Order.";
    //         }
    //     }else{
    //         $error = "Error in Creating bearer token.";
    //     }

    //     if($orderCreated){
    //         $prePayForOrder = prePayParcel2Go($parcel2GoOrderId);

    //         if(!$prePayForOrder["prePaid"]){
    //             $orderCreated = false;
    //             $parcel2GoOrderId = "";
    //             $hashValue = "";
    //             $OrderLineId = "";
    //             $error = $prePayForOrder["error"];
    //         }
    //     }
        
    //     return array("orderCreated" => $orderCreated, "parcel2GoOrderId" => $parcel2GoOrderId, "hashValue" => $hashValue, "OrderLineId" => $OrderLineId, "error" => $error);
    // }

    // function prePayParcel2Go($orderID){
    //     $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
    //     $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

    //     $fetchToken = fetchToken($CLIENT_ID, $CLIENT_SECRET);
    //     $booleanAuth = $fetchToken['auth'];

    //     $prePaid = false;

    //     $labelRequestUrl = "";
    //     $error = "";

    //     if($booleanAuth){
    //         $TOKEN = $fetchToken['Token'];

    //         $URL = 'https://www.parcel2go.com/api/orders/'.$orderID.'/paywithprepay';
    //         $METHOD = 'POST';
    //         $POST_FIELDS = '';

    //         $HTTP_HEADER = array();
    //         array_push($HTTP_HEADER,'Authorization: Bearer '.$TOKEN,'Content-Type: application/json');

    //         $prePayParcel2Go = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

    //         if($prePayParcel2Go['responsecode']=="200"){
    //             $prePayParcel2GoResponse = $prePayParcel2Go['responsearray'];

    //             if(array_key_exists('Links',$prePayParcel2GoResponse)){
    //                 $prePayParcel2GoLinks = $prePayParcel2GoResponse['Links'];

    //                 foreach ($prePayParcel2GoLinks as $key => $prePayParcel2GoLink) {
    //                     if($prePayParcel2GoLink["Name"] == "labels-4x6"){
    //                         $labelRequestUrl = $prePayParcel2GoLink["Link"];
    //                     }
    //                 }

    //                 $prePaid = true;
    //             }else{
    //                 $error = "Prepay failed.";
    //             }
    //         }else{
    //             $error = "Error in prepay Parcel2Go Order.";
    //         }
    //     }else{
    //         $error = "Error in Creating bearer token.";
    //     }

    //     return array("prePaid" => $prePaid, "labelRequestUrl" => $labelRequestUrl, "error" => $error);
    // }

    // function getLabelParcel2Go($orderID, $hash){
    //     $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
    //     $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

    //     $fetchToken = fetchToken($CLIENT_ID, $CLIENT_SECRET);
    //     $booleanAuth = $fetchToken['auth'];

    //     $labelCreated = false;

    //     $getLabelParcel2GoBase64 = "";
    //     $error = "";

    //     if($booleanAuth){
    //         $TOKEN = $fetchToken['Token'];

    //         $URL = 'https://www.parcel2go.com/api/labels/'.$orderID.'?referencetype=OrderId&detailLevel=Labels&labelMedia=Label4X6&labelFormat=PDF&hash='.$hash;
    //         $METHOD = 'GET';
    //         $POST_FIELDS = '';

    //         $HTTP_HEADER = array();
    //         array_push($HTTP_HEADER,'Authorization: Bearer '.$TOKEN,'Content-Type: application/json');

    //         $getLabelParcel2Go = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

    //         if($getLabelParcel2Go['responsecode']=="200"){
    //             $getLabelParcel2GoResponse = $getLabelParcel2Go['responsearray'];

    //             if(array_key_exists('Base64EncodedLabels',$getLabelParcel2GoResponse)){
    //                 $getLabelParcel2GoBase64 = $getLabelParcel2GoResponse['Base64EncodedLabels'];

    //                 $labelCreated = true;
    //             }else{
    //                 $error = "Label creation failed.";
    //             }
    //         }else{
    //             $error = "Error in creating label for parcel2go Order.";
    //         }
    //     }else{
    //         $error = "Error in Creating bearer token.";
    //     }

    //     return array("labelCreated" => $labelCreated, "labelB64" => $getLabelParcel2GoBase64, "error" => $error);
    // }

    // // $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
    // // $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

    // // $fetchToken = fetchToken($CLIENT_ID, $CLIENT_SECRET);

    // // print_r($fetchToken);

    // $FirstName = "Testing";
    // $LastName = "Label";
    // $Email = "puvii.digitweb@gmail.com";
    // $AddressLine1 = "Unit 3,";
    // $AddressLine2 = "Lythal Lane, Lythal Lane";
    // $AddressLine3 = "Industrial Estate";
    // $Town = "COVENTRY";
    // $PostCode = "CV6 6FL";
    // $Country = "United Kingdom";
    // $CountryCode = "GBR";
    // $subtotal = "10";

    // $createParcel2GoOrder = createParcel2GoOrder($FirstName, $LastName, $Email, $AddressLine1, $AddressLine2, $AddressLine3, $Town, $PostCode, $Country, $CountryCode, $subtotal);

    // print_r($createParcel2GoOrder);

    function fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $METHOD,
            CURLOPT_POSTFIELDS => $POST_FIELDS,
            CURLOPT_HTTPHEADER => $HTTP_HEADER
        ));

        $response = curl_exec($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response_array = json_decode($response);

        return array("responsecode" => $response_code,"responsearray" => $response);
    }

    $URL = 'http://app6.selro.com/api/orders?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&status=Unshipped&pagesize=500&page=1';
    $METHOD = 'GET';
    $POST_FIELDS = '';
    $HTTP_HEADER = array();
    array_push($HTTP_HEADER,'Content-Type: application/json');

    $ContentType = 'JSON';
    $getOrders = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);
    
    $ordersGot = false;
    $selroOrders = array();
    $selroOrderss = array();

    print_r($getOrders);

    if($getOrders['responsecode']=="200"){
        $ordersGot = true;
        $getOrdersResponse = $getOrders['responsearray'];

        $selroOrdersPages = 6;
        $selroOrders = $getOrdersResponse['orders'];
        // array_push($selroOrders, $selroOrderss);
        
        for($iterat = 2; $iterat <= $selroOrdersPages; $iterat++){
            $URL_Loop = 'http://app6.selro.com/api/orders?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&status=Unshipped&pagesize=500&page='.$iterat;

            $getOrders_Loop = fetchDataFromURLByContentType($URL_Loop, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
            if($getOrders_Loop['responsecode']=="200"){
                $getOrdersResponse_Loop = $getOrders_Loop['responsearray'];

                $selroOrderss = $getOrdersResponse_Loop['orders'];
                
                if(empty($selroOrderss)){
                    break;
                }

                $selroOrders = array_merge($selroOrders, $selroOrderss);
            }
        }
    }else{
        $error = "Error in getting orders from selro.";
    }
    
    // print_r(array("ordersGot" => $ordersGot, "selroOrders" => $selroOrders, "error" => $error));