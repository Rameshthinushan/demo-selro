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

$con_hostinger = $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

//$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
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

if ($_POST["refreshSelroOrderstype"] != "") {
    $activityLogQuery = "SELECT * FROM `activity_log` WHERE action='refresh selro orders' AND (status='started' || status='ordersgot' || status='merged-order' || status='discount-deleted')";
    $activityLogResult = mysqli_query($con, $activityLogQuery);
    
    $rowcount=mysqli_num_rows($activityLogResult);

    if($rowcount>0){
        $activityLogrow = mysqli_fetch_array($activityLogResult);
        
        $actionStart_date_time = $activityLogrow['actionStart_date_time'];
        $log_org_id = $activityLogrow['id'];

        $datetime1 = new DateTime($actionStart_date_time);
        $currentDateTime = date("Y-m-d H:i:s");
        $datetime2 = new DateTime($currentDateTime);
        $interval = $datetime1->diff($datetime2);

        $elapsedMinutes = $interval->i;
        $elapsedHours = $interval->h;
        $elapsedDays = $interval->d;

        $elapsedFullMinutes = $elapsedMinutes + ($elapsedHours * 60) + ($elapsedDays * 24 * 60);

        if($elapsedFullMinutes > 10){
            $sql = 'UPDATE `activity_log` SET status = "completed by system" WHERE id="'.$log_org_id.'"';
            $result = $con->query($sql);

            if($result){
                $activityLogQuery = "SELECT * FROM `activity_log` WHERE action='refresh selro orders' AND (status='started' || status='ordersgot' || status='merged-order' || status='discount-deleted')";
                $activityLogResult = mysqli_query($con, $activityLogQuery);
                
                $rowcount=mysqli_num_rows($activityLogResult);
            }
        }
    }

    if($rowcount>0){
        echo "Orders already refreshed. You can't again refresh.";
    }else{
        $sql = 'INSERT INTO `activity_log`(`action`, `status`, `action_by`, `actionStart_date_time`) VALUES ("refresh selro orders", "started", "'.$_SESSION['name_'].'", "'.date('Y-m-d H:i:s').'")';
        $result = $con->query($sql);

        if ($result === TRUE) {
            $lastAction_id = $con->insert_id;
        }

        $getTypeOfFetching = $_POST["refreshSelroOrderstype"];

        $error = "";
        $orderDetails = array();

        $orders = getSelroOrders();
        
        if($orders['ordersGot']){
            $orderDetailsss = $orders['selroOrders'];

            if($getTypeOfFetching == "replacement"){
                $like = "25838";

                $orderDetails= array_filter($orderDetailsss, function ($orderDetaill) use ($like) {
                    if ($orderDetaill['channelUserId'] == $like) {
                        return true;
                    }
                    return false;
                });
            }else if($getTypeOfFetching == "withoutReplacement"){
                $like = "25838";

                $orderDetails= array_filter($orderDetailsss, function ($orderDetaill) use ($like) {
                    if ($orderDetaill['channelUserId'] != $like) {
                        return true;
                    }
                    return false;
                });
            }else if($getTypeOfFetching == "firstClassOnly"){
                $orderDetails = array_filter($orderDetailsss, function ($orderDetaill) {
                    if(strpos($orderDetaill['shippingMethod'], "Next") !== false){
                        return true;
                    }else if($orderDetaill['shipCountryCode'] == "GB" && $orderDetaill['shippingPrice'] >= 3){
                        if(strpos($orderDetaill['shippingMethod'], "Custom Shipping") !== false && strtoupper($orderDetaill['channel']) == "SHOPIFY"){
                            return false;
                        }else if($orderDetaill['shippingMethod'] == "Express" && strtoupper($orderDetaill['channel']) == "SHOPIFY"){
                            return true;
                        }else{
                            return true;
                        }
                    }
                    return false;
                });
            }else if($getTypeOfFetching == "primeonly"){
                $orderDetails = array_filter($orderDetailsss, function ($orderDetaill) {
                    if ($orderDetaill['field1'] == "prime" || $orderDetaill['field4'] == "prime") {
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

        $orderDetailsCount = count($orderDetails);

        if(trim($error) == "" && $orderDetailsCount>0){
            $i = 1;
            foreach($orderDetails as $orderDetail){
                // field7 has Hold that is Hold Order so we can exclude
                if($orderDetail['field7'] != "Hold"){
                $orderID = $orderDetail['id'].":".$orderDetail['orderId'];

                $status = 'Pending';
                $date = date('Y-m-d H:i:s', substr($orderDetail['purchaseDate'],0,-3));

                $totalDiscounts = $orderDetail['totalDiscounts'];

                $sourceName = strtoupper($orderDetail['channel']); // ebay, amazon
                $channelID = $orderDetail['channelUserId']; // 25838 this is manual order, i assume as replacement
                $getChannelNameById = getChannelNameById($channelID);
                $channelName = "";

                if($getChannelNameById['channelInfoGot']){
                    $channelName = $getChannelNameById['channelName'];
                }else{
                    $getChannelNameById = getChannelNameById($channelID);
                    if($getChannelNameById['channelInfoGot']){
                        $channelName = $getChannelNameById['channelName'];
                    }
                }

                $channel = $sourceName."-".$channelName;

                if($channel == "SHOPIFY-LEDSone UK Ltd"){
                    $orderID = $orderDetail['id'].":LED".$orderDetail['orderId']; // add LED, default not contains in selro
                }else if($channel == "SHOPIFY-Electrical sone"){
                    $orderID = $orderDetail['id'].":ES".$orderDetail['orderId']; // add ES, default not contains in selro
                }else if($channel == "SHOPIFY-Vintagelite"){
                    $orderID = $orderDetail['id'].":VL".$orderDetail['orderId']; // add VL, default not contains in selro
                }else if($channel == "SHOPIFY-Ledsone DE"){
                    $orderID = $orderDetail['id'].":LSDE".$orderDetail['orderId']; // add VL, default not contains in selro
                }

                $customerNotes = "";

                $firstname = $orderDetail['shipName'];
                $firstname = str_replace("'", "\'", $firstname);
                $firstname = str_replace('"', '\"', $firstname);
                $firstname = ucwords(strtolower($firstname));

                $shippingaddresscompany = $orderDetail['recipientName'];
                $shippingaddresscompany = str_replace("'", "\'", $shippingaddresscompany);
                $shippingaddresscompany = str_replace('"', '\"', $shippingaddresscompany);
                $shippingaddresscompany = ucwords(strtolower($shippingaddresscompany));

                $email = $orderDetail['buyerEmail'];
                $shipPhoneNumber = $orderDetail['shipPhoneNumber'];
                
                $items = $orderDetail['channelSales'];

                $csvdate = date("Y-m-d");
                $unit = 'unit2';
                
                $shippingaddressline1 = $orderDetail['shipAddress1'];
                $shippingaddressline1 = str_replace("'", "\'", $shippingaddressline1);
                $shippingaddressline1 = trim(str_replace('"', '\"', $shippingaddressline1));

                $shippingaddressline2 = $orderDetail['shipAddress2'];
                $shippingaddressline2 = str_replace("'", "\'", $shippingaddressline2);
                $shippingaddressline2 = trim(str_replace('"', '\"', $shippingaddressline2));

                $shippingaddressline2Array= explode(" ", $shippingaddressline2 );

                $shippingaddressline2ArrayLast = count($shippingaddressline2Array) - 1;

                if(strpos($shippingaddressline2Array[$shippingaddressline2ArrayLast], 'ebay') !== false){
                    $shippingaddressline2_new = "";
                    foreach ($shippingaddressline2Array as $key => $shippingaddressline2Arr) {
                        if($key != $shippingaddressline2ArrayLast){
                            $shippingaddressline2_new .= " ".$shippingaddressline2Arr;
                        }
                    }
                    $shippingaddressline2 = $shippingaddressline2_new;
                    $shippingaddressline2 = str_replace("'", "\'", $shippingaddressline2);
                    $shippingaddressline2 = trim(str_replace('"', '\"', $shippingaddressline2));
                }

                $shippingaddressline3 = $orderDetail['shipAddress3'];
                $shippingaddressline3 = str_replace("'", "\'", $shippingaddressline3);
                $shippingaddressline3 = trim(str_replace('"', '\"', $shippingaddressline3));

                if($shippingaddressline1 == ""){
                    $shippingaddressline1 = $shippingaddressline2;
                    $shippingaddressline2 = $shippingaddressline3;
                    $shippingaddressline3 = '';
                }

                $shippingaddressregion = $orderDetail['shipState'];
                $shippingaddressregion = str_replace("'", "\'", $shippingaddressregion);
                $shippingaddressregion = str_replace('"', '\"', $shippingaddressregion);

                $shippingaddresscity = $orderDetail['shipCity'];
                $shippingaddresscity = str_replace("'", "\'", $shippingaddresscity);
                $shippingaddresscity = str_replace('"', '\"', $shippingaddresscity);

                $shippingaddresspostcode = $orderDetail['shipPostalCode'];
                $shippingaddresspostcode = str_replace("'", "\'", $shippingaddresspostcode);
                $shippingaddresspostcode = str_replace('"', '\"', $shippingaddresspostcode);
                $shippingaddresspostcode = str_replace('-', '', $shippingaddresspostcode);
                
                $shippingaddresscountrycode = $orderDetail['shipCountryCode'];
                $shippingaddresscountrycode = str_replace("'", "\'", $shippingaddresscountrycode);
                $shippingaddresscountrycode = str_replace('"', '\"', $shippingaddresscountrycode);

                // $shippingaddresscountry = $orderDetail['shipCountry'];
                $shippingaddresscountry = ucwords(strtolower($orderDetail['shipCountry']));

                if(trim($shippingaddresscountrycode) == ""){
                    $shippingaddresscountrycode = findCountryCode($shippingaddresscountry);
                }

                $shippingaddresscountry = findCountryByCountryCode($shippingaddresscountrycode);

                $currency = $orderDetail['currencyCode'];

                if(trim($currency) == ""){
                    $currency = findCurrencyByCountryCode($shippingaddresscountrycode);
                }
                
                if($channelID == "25838"){
                    $shippingservice = $orderDetail['shippingMethod'];
                    $replacementStatus = "REPLACEMENT";
                    $replacementShippingservice = $orderDetail['shippingMethod'];
                }else{
                    $shippingservice = $orderDetail['shippingMethod'];
                    $replacementStatus = "";
                    $replacementShippingservice = "";
                }

                $shipping_cost = $orderDetail['shippingPrice'];

                $totalCount = count($items);

                // added to exclude germany orders
                if($shippingaddresscountrycode != "DE"){
                    $j = 1;

                    $subTotalValue = $orderDetail['subTotal'];

                    foreach ($items as $key_index => $item) {
                        $unitPrice  = $item['itemPrice'];
                        $name = $item['title'];
                        $sku = $item['sku'];
                        $quantity = $item['quantityPurchased'];

                        $itemsOptions = array();
                        $itemsImageUrl = $item['imageUrl'];

                        // for different orders info
                        $items_orderItemId = $item['orderItemId'];

                        // if($item['lineItemKey'] == "discount"){
                            
                        // }

                        $customerNotes = $item['giftMessage'];

                        // $totalItemsOptionsCount = count($itemsOptions);

                        // if($totalItemsOptionsCount>0){
                        //     $name .= "[";
                        //     foreach ($itemsOptions as $key => $itemsOption) {
                        //         $optionValue = $itemsOption['value'];
                        //         $name .= $optionValue;
                        //         if($totalItemsOptionsCount != ($key+1)){
                        //             $name .= ", ";
                        //         }
                        //     }
                        //     $name .= "]";
                        // }

                        $name = str_replace("'", "\'", $name);
                        $name = str_replace('"', '\"', $name);

                        $sku = str_replace("'", "\'", $sku);
                        $sku = str_replace('"', '\"', $sku);
                        $sku = trim($sku);

                        // get mapping sku 
                        // mapping stop
                        $mapped_sku = "";
                        // $mapped_sku = getMappingSKU($sku, $channel, $con);
                        $mapped_sku = getMappingSKU($sku, $channel, $con_hostinger);

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
                        
                        if($channel == "SHOPIFY-LEDSone UK Ltd" && $shippingservice == "Unit 3 Marshbrook Cl"){
                            $shippingservice = "collection order";
                        }else if(strpos($shippingservice, "InternationalPriorityShippingUK") !== false){
                            $shippingservice = "csv";
                        }else if(strpos($shippingservice, "Next") !== false){
                            $shippingservice = "firstclass";
                        }else if($shippingaddresscountrycode == "GB" && $shipping_cost >= 3){
                            if($shippingservice == "UK_Parcelforce24" && $sourceName == "EBAY"){
                                $shippingservice = "firstclass";
                            }else if($shippingservice != "UK_Parcelforce24" && $sourceName == "EBAY"){
                                $shippingservice = "csv";
                            }else if(strpos($shippingservice, "Custom Shipping") !== false && $sourceName == "SHOPIFY"){
                                $shippingservice = "csv";
                            }else{
                                $shippingservice = "firstclass";
                            }
                        }else if($shippingaddresscountrycode == "GB"){
                            $shippingservice = "csv";
                        }else if($shippingaddresscountrycode != "GB"){
                            $shippingservice = "international";
                        }

                        if($orderDetail['field1'] == "prime" || $orderDetail['field4'] == "prime"){
                            $shippingservice = "prime";
                        }

                        if($shippingservice != "prime" && $shippingservice != "firstclass" && $shippingservice != "international" && $shippingservice != "csv" && $shippingservice != "collection order"){
                            $shippingservice = "csv";
                        }

                        if($shippingservice == "collection order"){
                            $firstname = $item['buyerName'];
                            $firstname = str_replace("'", "\'", $firstname);
                            $firstname = str_replace('"', '\"', $firstname);
                            $firstname = ucwords(strtolower($firstname));
                        }

                        if($sourceName == "AMAZON"){
                            if(($unitPrice * $quantity) > $subTotalValue){
                                $ordertotal = ($unitPrice + $shipping_cost) - $totalDiscounts;
                            }else{
                                $ordertotal = (($unitPrice * $quantity) + $shipping_cost) - $totalDiscounts;
                            }
                        }else{
                            $ordertotal = (($unitPrice * $quantity) + $shipping_cost) - $totalDiscounts;
                        }

                        // $ordertotal = (($unitPrice * $quantity) + $shipping_cost) - $totalDiscounts;

                        // if($subTotalValue == $unitPrice){
                        //     $ordertotal = ($unitPrice + $shipping_cost) - $totalDiscounts;
                        // }else{
                        //     $ordertotal = (($unitPrice * $quantity) + $shipping_cost) - $totalDiscounts;
                        // }

                        $total = $ordertotal;

                        $checkQuer = "SELECT * FROM `temporders` WHERE orderID = '".$orderID."' AND orderItemId = '".$items_orderItemId."' AND sku = '".$sku."' AND orgSku = '".$mapped_sku."' AND quantity = '".$quantity."'"; 
                        $checkQuerResu = mysqli_query($con, $checkQuer);
                        $rowcount = mysqli_num_rows($checkQuerResu);
                        if($rowcount == 0){
                            //flags start
                            $flags = getFlags($sku);
                            //Flags end

                            if ($currency != "GBP") {
                                $changedVal_ordertotal = thmx_currency_convert($currency, $ordertotal);
                                $changedVal_shipping_cost = thmx_currency_convert($currency, $shipping_cost);

                                if($changedVal_ordertotal != "#NA" && $changedVal_shipping_cost != "#NA"){
                                    $ordertotal = $changedVal_ordertotal;
                                    $shipping_cost = $changedVal_shipping_cost;
                                    $total = $ordertotal;

                                    $currency = "GBP";
                                }
                            }

                            $postal_service = getPostalService($sku, $flags, $quantity, $ordertotal, $shipping_cost, $shippingaddresspostcode, $channel, $shippingservice, $con);

                            // addded for muguntha akka request, added by puvii
                            if($postal_service == "Hermes ParcelShop Postable (Shop To Door) by MyHermes"){
                                $postal_service = "ParcelDenOnline Standard Package";
                            }
                            // addded for muguntha akka request, added by puvii

                            if($replacementStatus == "REPLACEMENT"){
                                $postal_service = $replacementShippingservice;
                                $channel = "REPLACEMENT-REPLACEMENT";
                            }

                            $weight_In_Grams = getWeightByshippingService($postal_service);

                            $subflags = getSubFlags($sku,$flags,$con);

                            $BoxSizes = getBoxSizes($sku, $quantity);

                            $table = "temporders";
                            $field_values = array("image_from_ship", "orderID", "status", "date", "channel", "firstname", "telephone", "email","currency", "ordertotal", "name", "sku", "orgSku", "quantity", "flags", "subflags", "box_sizes", "shippingservice", "shippingaddresscompany", "shippingaddressline1","shippingaddressline2","shippingaddressline3","shippingaddressregion","shippingaddresscity","shippingaddresspostcode","shippingaddresscountry","shippingaddresscountrycode","shipping_cost","postal_service","booking","csvdate","unit","total", "weight_In_Grams", "notes", "orderItemId");
                            $data_values = array($itemsImageUrl, $orderID, $status, $date, $channel, $firstname, $shipPhoneNumber, $email, $currency, $ordertotal, $name, $sku, $mapped_sku, $quantity, $flags, $subflags, $BoxSizes, $shippingservice, $shippingaddresscompany, $shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressregion, $shippingaddresscity, $shippingaddresspostcode, $shippingaddresscountry, $shippingaddresscountrycode, $shipping_cost, $postal_service, $booking, $csvdate, $unit, $total, $weight_In_Grams, $customerNotes, $items_orderItemId);

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
            }

            $sql = 'UPDATE `activity_log` SET status = "ordersgot",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            // discount delete - start
            $discountquery = "SELECT * FROM `temporders` WHERE sku = '' AND ordertotal < 0"; 
            $discountresult = mysqli_query($con, $discountquery);
            while($discountrow = mysqli_fetch_array($discountresult))
            {
                $discount_idd = $discountrow["id"];
                $orderr_id = $discountrow["orderID"];
                $org_order_idQuery = "SELECT * FROM temporders WHERE orderID='" . $orderr_id . "' ORDER BY id ASC";
                $org_order_idresult = mysqli_query($con, $org_order_idQuery);
                $org_order_idrow = mysqli_fetch_array($org_order_idresult);
                $org_order_idtotal = $org_order_idrow["ordertotal"];
                $org_order_id_id = $org_order_idrow["id"];

                $discounttotal = $discountrow["ordertotal"];

                $orginalTotal = $org_order_idtotal + $discounttotal;
                
                $org_totalquery = "UPDATE temporders SET ordertotal='$orginalTotal', total='$orginalTotal' WHERE id='".$org_order_id_id."'";
                $updating = mysqli_query($con, $org_totalquery);

                if($updating){
                    $delete_id_query = "DELETE FROM temporders WHERE id='".$discount_idd."'";
                    mysqli_query($con, $delete_id_query);
                }
            }
            // discount delete - end
            $sql = 'UPDATE `activity_log` SET status = "discount-deleted",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            //merge start
            $ids_array = array();
            $idquery = "SELECT id FROM temporders";  
            $idresult = mysqli_query($con, $idquery);
            while($idrow = mysqli_fetch_array($idresult))
            {
                $ids_array[] = $idrow['id'];
            } 
            $rowCount = count($ids_array);
            $merge=array();
            for($i=0;$i<$rowCount;$i++)
            {
                $orderresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $ids_array[$i] . "'");
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
            $mergeresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $ids_array[$j] . "'");
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
                    UPDATE temporders   
                    SET merge='Merged',
                    flags= 'Lampshade'
                    WHERE id='".$mergefrom."'";
                    mysqli_query($con, $mergefromquery); 

                    $mergequery = "  
                    UPDATE temporders   
                    SET merge='$mergefromid'
                    WHERE id='".$mergeid."'";
                    mysqli_query($con, $mergequery); 
                    }
                    else
                    {              
                    $mergefromquery = "  
                    UPDATE temporders   
                    SET merge='Merged'
                    WHERE id='".$mergefrom."'";
                    mysqli_query($con, $mergefromquery); 

                    $mergequery = "  
                    UPDATE temporders   
                    SET merge='$mergefromid'
                    WHERE id='".$mergeid."'";
                    mysqli_query($con, $mergequery);
                    }
                    $mergeafterresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $ids_array[$i] . "'");
                    $mergeafterrow= mysqli_fetch_array($mergeafterresult);
                    $mergeflag=$mergeafterrow["flags"];
                    $mergeflagquery = "  
                    UPDATE temporders   
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
            
            $sql = 'UPDATE `activity_log` SET status = "merged-order",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            //mergetotal update - start
            $totalmergequery = "SELECT * FROM temporders WHERE merge='Merged' ORDER BY ordertotal ASC, date ASC"; 
            $totalresult = mysqli_query($con, $totalmergequery);
            while($totalrow = mysqli_fetch_array($totalresult))
            {
                $mergeid=$totalrow["date"]."-".$totalrow["orderID"];
                $mergequery = "SELECT * FROM temporders WHERE merge='" . $mergeid . "' ORDER BY ordertotal ASC, date ASC";
                $mergeresult = mysqli_query($con, $mergequery);
                $mergetotal=$totalrow["ordertotal"];
                while($mergerow = mysqli_fetch_array($mergeresult))
                {
                    $mergetotal = $mergetotal + $mergerow["ordertotal"];
                }
                
                $mergetotalquery = "  
                        UPDATE temporders   
                        SET total='$mergetotal'
                        WHERE merge='".$mergeid."'";
                        mysqli_query($con, $mergetotalquery);
                $totalid=$totalrow["id"];
                $totalquery = "  
                        UPDATE temporders   
                        SET total='$mergetotal'
                        WHERE id='".$totalid."'";
                        mysqli_query($con, $totalquery);

                // postal service change after merge - start
                // $postalServiceM = getPostalService($totalrow["sku"], $totalrow["flags"], $totalrow["quantity"], $mergetotal, $totalrow["shipping_cost"], $totalrow["shippingaddresspostcode"], $totalrow["channel"], $totalrow["shippingservice"], $con);

                // $mergetotalquery2 = "UPDATE temporders SET postal_service = '$postalServiceM' WHERE merge='".$mergeid."'";
                // mysqli_query($con, $mergetotalquery2);
                
                // $totalquery2 = "UPDATE temporders SET postal_service = '$postalServiceM' WHERE id='".$totalid."'";
                // mysqli_query($con, $totalquery2);
                // postal service change after merge - end
            }
            //mergetotal update - end
            
            $sql = 'UPDATE `activity_log` SET status = "completed",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);
            
            echo "order added successfully. automatically close this tab after 2 seconds.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 2000);</script>";
        }else if($orderDetailsCount == 0){
            $sql = 'UPDATE `activity_log` SET status = "completed",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            echo "order count is zero. automatically close this tab after 5 seconds.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 5000);</script>";
        }else if(trim($error) != ""){
          $sql = 'UPDATE `activity_log` SET status = "completed",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);
          
            print_r($error);
        }
    }
}