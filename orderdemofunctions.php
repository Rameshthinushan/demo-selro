<?php


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

        $response_array = json_decode($response, true);

        return array("responsecode" => $response_code,"responsearray" => $response_array);
    }


    function fetchToken($APPLICATION_ID, $APPLICATION_SECRET, $TOKEN){
        $ERROR_AUTH = array();

        $METHOD_POST = "POST";
        $AUTH_URL = "https://api.linnworks.net//api/Auth/AuthorizeByApplication";
        $HTTP_HEADER_AUTH = array('Content-Type: application/json', 'Cache-Control: no-cache');
        $POST_FIELDS_AUTH = '{ "applicationId": "' . $APPLICATION_ID . '", "applicationSecret": "' . $APPLICATION_SECRET . '", "token": "' . $TOKEN . '"}';
        $AUTH_BOOLEAN = false;

        $getAuthentication = fetchDataFromURL($AUTH_URL, $METHOD_POST, $POST_FIELDS_AUTH, $HTTP_HEADER_AUTH);
        $response_code_Auth = $getAuthentication['responsecode'];
        if($response_code_Auth=="200"){
            $response_getAuthentication = $getAuthentication['responsearray'];
            if(array_key_exists('Token',$response_getAuthentication)){
                $AUTH_BOOLEAN = true;
                $ACCESS_TOKEN = $response_getAuthentication['Token'];
            }else{
                $ERROR_AUTH = $getAuthentication['responsearray'];
            }        
        }else{
            $ERROR_AUTH = $getAuthentication['responsearray'];
        }
        
        return array("auth" => $AUTH_BOOLEAN,"Token" => $ACCESS_TOKEN,"errorsAuth" => $ERROR_AUTH);
    }


    function imageInfo($orderID){
        $APPLICATION_ID = "ae03cc69-e527-4aca-863d-75e3b044e6ac";
        $APPLICATION_SECRET = "b289ef44-cb13-43b7-864d-f04e33a6896b";
        $TOKEN = "3787981b05b4afb38c6d76bc83b94347";

        $fetchToken = fetchToken($APPLICATION_ID, $APPLICATION_SECRET, $TOKEN);
        $booleanAuth = $fetchToken['auth'];

        if($booleanAuth){
            $TOKEN = $fetchToken['Token'];
            
            $METHOD_POST = "POST";
            $API_URL = 'https://eu-ext.linnworks.net/api/Orders/GetOrderDetailsByNumOrderId?OrderId='.$orderID;           
            $HTTP_HEADER_LOAD = array('Authorization:'.$TOKEN);
            $POST_FIELDS_LOAD = '';

            $getOrderInfo = fetchDataFromURL($API_URL, $METHOD_POST, $POST_FIELDS_LOAD, $HTTP_HEADER_LOAD);

            if($getOrderInfo['responsecode']=="200"){
                $getOrderInfoResponse = $getOrderInfo['responsearray'];
                $sizeResponse = count($getOrderInfoResponse);

                $itemid=$getOrderInfoResponse['Items'][0]['ItemId'];

                $API_URL = 'https://eu-ext.linnworks.net/api/Inventory/GetInventoryItemImages?inventoryItemId='.$itemid;

                $getItemInfo = fetchDataFromURL($API_URL, $METHOD_POST, $POST_FIELDS_LOAD, $HTTP_HEADER_LOAD);

                $getItemInfoResponse = $getItemInfo['responsearray'];

                $image=$getItemInfoResponse[0]['Source'];

                return $image;

            }
        }

    }



    //$orderID = "302143";

    //$imagesource = imageInfo($orderID);

    //echo '<img src="'.$imagesource.'" width=50px;>';



?>