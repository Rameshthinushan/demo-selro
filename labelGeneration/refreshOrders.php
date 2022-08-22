<?php
include 'functionsToCreateLabels.php'; 
ini_set('max_execution_time', 0);
date_default_timezone_set('Europe/London');

// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin_'])) {
    header('Location: https://digitweb.vintageinterior.co.uk/index.html');
    exit();
}

//require_once('authenticate.php');
$DATABASE_HOST   = 'localhost';
$DATABASE_USER   = 'root';
$DATABASE_PASS   = '';
$DATABASE_NAME = 'u525933064_dashboard';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
// $con_hostinger = mysqli_connect("localhost", "root", "", "u525933064_dashboard");

if (mysqli_connect_errno()) {
    die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// We don't have the password or email info stored in sessions so instead we can get the results from the database.
$stmt = $con->prepare('SELECT password, email FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($password, $email);
$stmt->fetch();
$stmt->close();

$url = file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

$xml =  new SimpleXMLElement($url);

file_put_contents(dirname(__FILE__) . "/loc.xml", $xml->asXML());

if ($_POST["refreshOrderstype"] != "") {
    $activityLogQuery = "SELECT * FROM `activity_log` WHERE action='refresh orders' AND (status='started' || status='ordersgot' || status='merged-order' || status='discount-deleted')";
    $activityLogResult = mysqli_query($con, $activityLogQuery);
    
    $rowcount=mysqli_num_rows($activityLogResult);

    if($rowcount>0){
        echo "Orders already refreshed. You can't again refresh.";
    }else{
        $sql = 'INSERT INTO `activity_log`(`action`, `status`, `action_by`, `actionStart_date_time`) VALUES ("refresh orders", "started", "'.$_SESSION['name_'].'", "'.date('Y-m-d h:i:s').'")';
        $result = $con->query($sql);

        if ($result === TRUE) {
            $lastAction_id = $con->insert_id;
        }

        $getTypeOfFetching = $_POST["refreshOrderstype"];

        $error = "";
        $orderDetails = array();

        if($getTypeOfFetching=="all" || $getTypeOfFetching=="replacement" || $getTypeOfFetching=="withoutReplacement"){
            // $sql = 'DELETE FROM `temporders1`';
            // $result = $con->query($sql);
            
            $orders = getOrders();
            // ordersGot" => $ordersGot, "shipStationOrders" => $shipStationOrders, "error
            if($orders['ordersGot']){
                $orderDetailsss = $orders['shipStationOrders'];

                $primeOrders = getPrimeOrders();
                if($primeOrders['primeOrdersGot']){
                    $shipStationPrimeOrders = $primeOrders['shipStationPrimeOrders'];

                    $orderDetailsss = array_merge($orderDetailsss, $shipStationPrimeOrders);
                }

                // $result = array_filter($array, function ($item) use ($like) {
                //     if (stripos($item['name'], $like) !== false) {
                //         return true;
                //     }
                //     return false;
                // });

                if($getTypeOfFetching == "replacement"){
                    $like = "REPLACEMENT";

                    $orderDetails= array_filter($orderDetailsss, function ($orderDetaill) use ($like) {
                        if ($orderDetaill['advancedOptions']['customField2'] == $like) {
                            return true;
                        }
                        return false;
                    });
                }else if($getTypeOfFetching == "withoutReplacement"){
                    $like = "";

                    $orderDetails= array_filter($orderDetailsss, function ($orderDetaill) use ($like) {
                        if ($orderDetaill['advancedOptions']['customField2'] == $like) {
                            return true;
                        }
                        return false;
                    });
                }else if($getTypeOfFetching == "all"){
                    $orderDetails= $orderDetailsss;
                }
            }else{
                $error = $orders['error'];
            }
        }else if($getTypeOfFetching=="primeOnly"){
            $primeOrders = getPrimeOrders();
            if($primeOrders['primeOrdersGot']){
                $orderDetails = $primeOrders['shipStationPrimeOrders'];
            }else{
                $error = $primeOrders['error'];
            }
        }else if($getTypeOfFetching=="firstClassOnly"){
            $firstClassOrders = getFirstClassOrders();
            if($firstClassOrders['firstClassOrdersGot']){
                $orderDetails = $firstClassOrders['shipStationFirstClassOrders'];
            }else{
                $error = $firstClassOrders['error'];
            }
        }

        $orderDetailsCount = count($orderDetails);

        if(trim($error) == "" && $orderDetailsCount>0){
            $i = 1;
            foreach($orderDetails as $orderDetail){
                $orderID = $orderDetail['orderId'].":".$orderDetail['orderNumber'];

                $status = 'Pending';
                $date = date_create($orderDetail['orderDate']);
                $date = date_format($date,"Y-m-d H:i:s");

                $storeId = $orderDetail['advancedOptions']['storeId'];
                $storeInfo = getStoreInfo($storeId);
                $channel = "";

                if($storeInfo['storeInfoGot']){
                    if(strpos(strtoupper($storeInfo['shipStationOrderMarketPlaceName']), 'AMAZON') !== false){
                        $source_name = "AMAZON";
                    }else{
                        $source_name = strtoupper($storeInfo['shipStationOrderMarketPlaceName']);
                    }

                    $channel = $source_name."-".$storeInfo['shipStationOrderStoreName'];
                }else{
                    $storeInfo = getStoreInfo($storeId);
                    if($storeInfo['storeInfoGot']){
                        if(strpos(strtoupper($storeInfo['shipStationOrderMarketPlaceName']), 'AMAZON') !== false){
                            $source_name = "AMAZON";
                        }else{
                            $source_name = strtoupper($storeInfo['shipStationOrderMarketPlaceName']);
                        }

                        $channel = $source_name." - ".$storeInfo['shipStationOrderStoreName'];
                    }
                }

                $customerNotes = $orderDetail['customerNotes'];
                $customerNotes = str_replace("'", "\'", $customerNotes);
                $customerNotes = str_replace('"', '\"', $customerNotes);

                $firstname = $orderDetail['shipTo']['name'];
                $firstname = str_replace("'", "\'", $firstname);
                $firstname = str_replace('"', '\"', $firstname);

                $email = $orderDetail['customerEmail'];
                
                $items = $orderDetail['items'];

                $csvdate = date("Y-m-d");
                $unit = 'unit2';
                
                $shippingaddressline1 = $orderDetail['shipTo']['street1'];
                $shippingaddressline1 = str_replace("'", "\'", $shippingaddressline1);
                $shippingaddressline1 = str_replace('"', '\"', $shippingaddressline1);

                $shippingaddressline2 = $orderDetail['shipTo']['street2'];
                $shippingaddressline2Array= explode(" ", $shippingaddressline2 );

                $shippingaddressline2ArrayLast = count($shippingaddressline2Array) - 1;

                if(strpos($shippingaddressline2Array[$shippingaddressline2ArrayLast], 'ebay') !== false){
                    $shippingaddressline2_new = "";
                    foreach ($shippingaddressline2Array as $key => $shippingaddressline2Arr) {
                        if($key != $shippingaddressline2ArrayLast){
                            $shippingaddressline2_new .= " ".$shippingaddressline2Arr;
                        }
                    }
                    $shippingaddressline2 = trim($shippingaddressline2_new);
                    $shippingaddressline2 = str_replace("'", "\'", $shippingaddressline2);
                    $shippingaddressline2 = str_replace('"', '\"', $shippingaddressline2);
                }

                $shippingaddressline3 = $orderDetail['shipTo']['street3'];
                $shippingaddressline3 = str_replace("'", "\'", $shippingaddressline3);
                $shippingaddressline3 = str_replace('"', '\"', $shippingaddressline3);

                $shippingaddressregion = $orderDetail['shipTo']['state'];
                $shippingaddressregion = str_replace("'", "\'", $shippingaddressregion);
                $shippingaddressregion = str_replace('"', '\"', $shippingaddressregion);

                $shippingaddresscity = $orderDetail['shipTo']['city'];
                $shippingaddresscity = str_replace("'", "\'", $shippingaddresscity);
                $shippingaddresscity = str_replace('"', '\"', $shippingaddresscity);

                $shippingaddresspostcode = $orderDetail['shipTo']['postalCode'];
                $shippingaddresspostcode = str_replace("'", "\'", $shippingaddresspostcode);
                $shippingaddresspostcode = str_replace('"', '\"', $shippingaddresspostcode);
                $shippingaddresspostcode = str_replace('-', '', $shippingaddresspostcode);
                
                $shippingaddresscountrycode = $orderDetail['shipTo']['country'];
                $shippingaddresscountrycode = str_replace("'", "\'", $shippingaddresscountrycode);
                $shippingaddresscountrycode = str_replace('"', '\"', $shippingaddresscountrycode);

                $shippingaddresscountry = findCountryByCountryCode($shippingaddresscountrycode);
                $currency = findCurrencyByCountryCode($shippingaddresscountrycode);
                
                $shippingservice = $orderDetail['advancedOptions']['customField1'];
                $replacementStatus = $orderDetail['advancedOptions']['customField2'];
                $replacementShippingservice = $orderDetail['advancedOptions']['customField3'];

                $requestedShippingService = $orderDetail['requestedShippingService'];

                $shipping_cost = $orderDetail['shippingAmount'];

                $telephone = $orderDetail['shipTo']['phone'];

                $totalCount = count($items);

                // added to exclude germany orders
                if($shippingaddresscountry != "Germany"){
                    $j = 1;
                    foreach ($items as $key_index => $item) {
                        $unitPrice  = $item['unitPrice'];
                        $name = $item['name'];
                        $sku = $item['sku'];
                        $quantity = $item['quantity'];

                        $itemsOptions = $item['options'];
                        $itemsImageUrl = $item['imageUrl'];

                        // if($item['lineItemKey'] == "discount"){
                            
                        // }

                        $totalItemsOptionsCount = count($itemsOptions);

                        if($totalItemsOptionsCount>0){
                            $name .= "[";
                            foreach ($itemsOptions as $key => $itemsOption) {
                                $optionValue = $itemsOption['value'];
                                $name .= $optionValue;
                                if($totalItemsOptionsCount != ($key+1)){
                                    $name .= ", ";
                                }
                            }
                            $name .= "]";
                        }

                        $name = str_replace("'", "\'", $name);
                        $name = str_replace('"', '\"', $name);

                        $sku = str_replace("'", "\'", $sku);
                        $sku = str_replace('"', '\"', $sku);
                        $sku = trim($sku);

                        // get mapping sku 
                        // mapping stop
                        $mapped_sku = "";
                        $mapped_sku = getMappingSKU($sku, $channel, $con);

                        if($sku != $mapped_sku){
                            $temp = $sku;
                            $sku = $mapped_sku;
                            $mapped_sku = $temp;
                        }else if($sku == $mapped_sku){
                            $sku = $mapped_sku;
                            $mapped_sku = "";
                        }

                        if($key_index != 0){
                            $shipping_cost = 0;
                        }

                        $postal_service = "";

                        if (date('H') < 10) {
                            $booking = "1st Booking";
                        }else{
                            $booking = "2nd Booking";
                        }
                        

                        // if($shippingaddresscountry == "United Kingdom" && $shipping_cost > 3){
                        //     $shippingservice = "firstclass";
                        // }else if($shippingaddresscountry != "United Kingdom" && $shippingaddresscountry != "Great Britain"){
                        //     $shippingservice = "international";
                        // }else if($orderDetail['orderStatus'] == "pending_fulfillment"){
                        //     $shippingservice = "prime";
                        // }else{
                        //     $shippingservice = "csv";
                        // }

                        // lastly added for ebay firstclass orders
                        if($shippingservice == "firstclass" && $requestedShippingService == "UK_Parcelforce24" && $source_name == "EBAY"){
                            $shippingservice = "firstclass";
                        }else if($shippingservice == "firstclass" && $requestedShippingService != "UK_Parcelforce24" && $source_name == "EBAY"){
                            $shippingservice = "csv";
                        }

                        if($orderDetail['orderStatus'] == "pending_fulfillment"){
                            $shippingservice = "prime";
                        }

                        $ordertotal = ($unitPrice * $quantity) + $shipping_cost;
                        $total = $ordertotal;

                        $checkQuer = "SELECT * FROM `temporders1` WHERE orderID = '".$orderID."' AND sku = '".$sku."'  AND quantity = '".$quantity."'"; 
                        $checkQuerResu = mysqli_query($con, $checkQuer);
                        $rowcount = mysqli_num_rows($checkQuerResu);
                        if($rowcount == 0){
                            //flags start
                            $flags = getFlags($sku);
                            //Flags end

                            $postal_service = getPostalService($sku, $flags, $quantity, $ordertotal, $shipping_cost, $shippingaddresspostcode, $channel, $shippingservice, $con);

                            if($replacementStatus == "REPLACEMENT"){
                                $postal_service = $replacementShippingservice;
                                $channel = "REPLACEMENT-REPLACEMENT";
                            }

                            $weight_In_Grams = getWeightByshippingService($postal_service);

                            $subflags = getSubFlags($sku,$flags,$con);

                            // if ($currency != "GBP") {

                            //     foreach ($xml->Cube->Cube->Cube as $rate) {
                            //         if ($rate["currency"] == $currency) {
                            //             $rate = $rate["rate"];
                            //             break;
                            //         }
                            //     }
                        
                            //     $currency = "GBP";
                        
                            //     if ($ordertotal > 0) {
                            //         $ordertotal = $ordertotal / $rate;
                            //     }

                            //     if($shipping_cost > 0){
                            //         $shipping_cost = $shipping_cost / $rate;
                            //     }
                                
                            //     if($total > 0){
                            //         $total = $total / $rate;
                            //     }
                            // }
                        
                            // $ordertotal = number_format($ordertotal, 2, '.', '');
                            // $shipping_cost = number_format($shipping_cost, 2, '.', '');
                            // $total = number_format($total, 2, '.', '');

                            $table = "temporders1";
                            $field_values = array("image_from_ship", "orderID", "status", "date", "channel", "firstname", "telephone", "email","currency", "ordertotal", "name", "sku", "orgSku", "quantity", "flags", "subflags", "shippingservice","shippingaddressline1","shippingaddressline2","shippingaddressline3","shippingaddressregion","shippingaddresscity","shippingaddresspostcode","shippingaddresscountry","shippingaddresscountrycode","shipping_cost","postal_service","booking","csvdate","unit","total", "weight_In_Grams", "notes");
                            $data_values = array($itemsImageUrl, $orderID, $status, $date, $channel, $firstname, $telephone, $email, $currency, $ordertotal, $name, $sku, $mapped_sku, $quantity, $flags, $subflags, $shippingservice, $shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressregion, $shippingaddresscity, $shippingaddresspostcode, $shippingaddresscountry, $shippingaddresscountrycode, $shipping_cost, $postal_service, $booking, $csvdate, $unit, $total, $weight_In_Grams, $customerNotes);

                            $added = addData($table,$field_values,$data_values,$con);
                        }else{
                            $added = 1;
                        }

                        // $added = addData($table,$field_values,$data_values,$con);
                        
                        if($added){
                            $i = $i + 1;
                            $j = $j + 1;
                        }
                    }
                }
            }

            $sql = 'UPDATE `activity_log` SET status = "ordersgot",actionEnd_date_time="'.date('Y-m-d h:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            // discount delete - start
            $discountquery = "SELECT * FROM `temporders1` WHERE sku = '' AND ordertotal < 0"; 
            $discountresult = mysqli_query($con, $discountquery);
            while($discountrow = mysqli_fetch_array($discountresult))
            {
                $discount_idd = $discountrow["id"];
                $orderr_id = $discountrow["orderID"];
                $org_order_idQuery = "SELECT * FROM temporders1 WHERE orderID='" . $orderr_id . "' ORDER BY id ASC";
                $org_order_idresult = mysqli_query($con, $org_order_idQuery);
                $org_order_idrow = mysqli_fetch_array($org_order_idresult);
                $org_order_idtotal = $org_order_idrow["ordertotal"];
                $org_order_id_id = $org_order_idrow["id"];

                $discounttotal = $discountrow["ordertotal"];

                $orginalTotal = $org_order_idtotal + $discounttotal;
                
                $org_totalquery = "UPDATE temporders1 SET ordertotal='$orginalTotal', total='$orginalTotal' WHERE id='".$org_order_id_id."'";
                $updating = mysqli_query($con, $org_totalquery);

                if($updating){
                    $delete_id_query = "DELETE FROM temporders1 WHERE id='".$discount_idd."'";
                    mysqli_query($con, $delete_id_query);
                }
            }
            // discount delete - end
            $sql = 'UPDATE `activity_log` SET status = "discount-deleted",actionEnd_date_time="'.date('Y-m-d h:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            //merge start
            $ids_array = array();
            $idquery = "SELECT id FROM temporders1";  
            $idresult = mysqli_query($con, $idquery);
            while($idrow = mysqli_fetch_array($idresult))
            {
                $ids_array[] = $idrow['id'];
            } 
            $rowCount = count($ids_array);
            $merge=array();
            for($i=0;$i<$rowCount;$i++)
            {
                $orderresult = mysqli_query($con, "SELECT * FROM temporders1 WHERE id='" . $ids_array[$i] . "'");
                $orderrow= mysqli_fetch_array($orderresult);
                if (in_array($orderrow['id'], $merge))
                {
                    continue;
                }
                        $clientname="Name : ".changToLower($orderrow["firstname"]);
                        $address="";
                        if(!empty($clientname))
                        {
                            $address=$address.$clientname."<br>";
                        }
                        if(!empty($orderrow["shippingaddressline1"]))
                        {
                            $address=$address.changToLower($orderrow["shippingaddressline1"])."<br>";
                        }
                        if(!empty($orderrow["shippingaddressregion"]))
                        {
                            $address=$address.changToLower($orderrow["shippingaddressregion"])."<br>";
                        }
                        if(!empty($orderrow["shippingaddresscity"]))
                        {
                            $address=$address.changToLower($orderrow["shippingaddresscity"])."<br>";
                        }
                        if(!empty($orderrow["shippingaddresspostcode"]))
                        {
                            $address=$address.changToLower($orderrow["shippingaddresspostcode"])."<br>";
                        }
                        if(!empty($orderrow["shippingaddresscountry"]))
                        {
                            $address=$address.changToLower($orderrow["shippingaddresscountry"])."<br>";
                        }
            for($j=$i+1;$j<$rowCount;$j++)
            {
            $mergeresult = mysqli_query($con, "SELECT * FROM temporders1 WHERE id='" . $ids_array[$j] . "'");
            $mergerow= mysqli_fetch_array($mergeresult);
                $clientname="Name : ".changToLower($mergerow["firstname"]);
                $addressnew="";
                    if(!empty($clientname))
                    {
                    $addressnew=$addressnew.$clientname."<br>";
                    }
                    if(!empty($mergerow["shippingaddressline1"]))
                    {
                    $addressnew=$addressnew.changToLower($mergerow["shippingaddressline1"])."<br>";
                    }
                    if(!empty($mergerow["shippingaddressregion"]))
                    {
                    $addressnew=$addressnew.changToLower($mergerow["shippingaddressregion"])."<br>";
                    }
                    if(!empty($mergerow["shippingaddresscity"]))
                    {
                    $addressnew=$addressnew.changToLower($mergerow["shippingaddresscity"])."<br>";
                    }
                    if(!empty($mergerow["shippingaddresspostcode"]))
                    {
                    $addressnew=$addressnew.changToLower($mergerow["shippingaddresspostcode"])."<br>";
                    }
                    if(!empty($mergerow["shippingaddresscountry"]))
                    {
                    $addressnew=$addressnew.changToLower($mergerow["shippingaddresscountry"])."<br>";
                    }
                if($addressnew==$address)
                {
                $mergeid=$mergerow['id']; 
                $mergefrom=$orderrow["id"];
                $mergefromid=$orderrow["date"]."-".$orderrow["orderID"];
                    if($orderrow["flags"]=="Lampshade"||$mergerow["flags"]=="Lampshade")
                    {
                    $mergefromquery = "  
                    UPDATE temporders1   
                    SET merge='Merged',
                    flags= 'Lampshade'
                    WHERE id='".$mergefrom."'";
                    mysqli_query($con, $mergefromquery); 

                    $mergequery = "  
                    UPDATE temporders1   
                    SET merge='$mergefromid'
                    WHERE id='".$mergeid."'";
                    mysqli_query($con, $mergequery); 
                    }
                    else
                    {              
                    $mergefromquery = "  
                    UPDATE temporders1   
                    SET merge='Merged'
                    WHERE id='".$mergefrom."'";
                    mysqli_query($con, $mergefromquery); 

                    $mergequery = "  
                    UPDATE temporders1   
                    SET merge='$mergefromid'
                    WHERE id='".$mergeid."'";
                    mysqli_query($con, $mergequery);
                    }
                    $mergeafterresult = mysqli_query($con, "SELECT * FROM temporders1 WHERE id='" . $ids_array[$i] . "'");
                    $mergeafterrow= mysqli_fetch_array($mergeafterresult);
                    $mergeflag=$mergeafterrow["flags"];
                    $mergeflagquery = "  
                    UPDATE temporders1   
                    SET flags='$mergeflag'
                    WHERE merge='".$mergefromid."'";
                    mysqli_query($con, $mergeflagquery);
                if(empty($merge))
                        {
                        $merge = array($mergeid);
                        }
                    else
                        {
                        $v=count($merge);
                        $merge[$v]=$mergeid;
                        }
                }

            }
            }
            //merge end
            
            $sql = 'UPDATE `activity_log` SET status = "merged-order",actionEnd_date_time="'.date('Y-m-d h:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            //mergetotal update - start
            $totalmergequery = "SELECT * FROM temporders1 WHERE merge='Merged' ORDER BY ordertotal ASC, date ASC"; 
            $totalresult = mysqli_query($con, $totalmergequery);
            while($totalrow = mysqli_fetch_array($totalresult))
            {
                $mergeid=$totalrow["date"]."-".$totalrow["orderID"];
                $mergequery = "SELECT * FROM temporders1 WHERE merge='" . $mergeid . "' ORDER BY ordertotal ASC, date ASC";
                $mergeresult = mysqli_query($con, $mergequery);
                $mergetotal=$totalrow["ordertotal"];
                while($mergerow = mysqli_fetch_array($mergeresult))
                {
                    $mergetotal = $mergetotal + $mergerow["ordertotal"];
                }
                
                $mergetotalquery = "  
                        UPDATE temporders1   
                        SET total='$mergetotal'
                        WHERE merge='".$mergeid."'";
                        mysqli_query($con, $mergetotalquery);
                $totalid=$totalrow["id"];
                $totalquery = "  
                        UPDATE temporders1   
                        SET total='$mergetotal'
                        WHERE id='".$totalid."'";
                        mysqli_query($con, $totalquery);

                // postal service change after merge - start
                // $postalServiceM = getPostalService($totalrow["sku"], $totalrow["flags"], $totalrow["quantity"], $mergetotal, $totalrow["shipping_cost"], $totalrow["shippingaddresspostcode"], $totalrow["channel"], $totalrow["shippingservice"], $con);

                // $mergetotalquery2 = "UPDATE temporders1 SET postal_service = '$postalServiceM' WHERE merge='".$mergeid."'";
                // mysqli_query($con, $mergetotalquery2);
                
                // $totalquery2 = "UPDATE temporders1 SET postal_service = '$postalServiceM' WHERE id='".$totalid."'";
                // mysqli_query($con, $totalquery2);
                // postal service change after merge - end
            }
            //mergetotal update - end
            
            $sql = 'UPDATE `activity_log` SET status = "completed",actionEnd_date_time="'.date('Y-m-d h:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);
            
            echo "order added successfully. automatically close this tab after 2 seconds.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 2000);</script>";
        }else if($orderDetailsCount == 0){
            echo "order count is zero. automatically close this tab after 5 seconds.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 5000);</script>";
        }else if(trim($error) != ""){
            print_r($error);
        }
    }
}