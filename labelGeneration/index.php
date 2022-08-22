<?php
    include 'functionsToCreateLabels.php'; 
    // ini_set('max_execution_time', 0);
    // added 09-05-2022 2.30 pm
    require('tcpdf/tcpdf_import.php');
    date_default_timezone_set('Europe/London');

    // We need to use sessions, so you should always start sessions using the below code.
    session_start();
    // If the user is not logged in redirect to the login page....
    
    // if (!isset($_SESSION['loggedin_'])) {
    //     header('Location: https://digitweb.vintageinterior.co.uk/index.html');
    //     exit();
    // }

    //require_once('authenticate.php');
    $DATABASE_HOST   = 'localhost';
    $DATABASE_USER   = 'root';
    $DATABASE_PASS   = '';
    $DATABASE_NAME = 'u525933064_dashboard';
    
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
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

    $ordercount = 0;
    $rowCount = count($_POST["orders"]);

    $errorOrderIdPostCodes = array();
    $missingOrderIdPostCodes = array();
    $base64Arr = array();

    $activityLogQuery = "SELECT * FROM `activity_log` WHERE action='label print' AND status='processing' AND action_by='".$_SESSION['name_']."'";
    $activityLogResult = mysqli_query($con, $activityLogQuery);
    
    $rowcountActivityLog = mysqli_num_rows($activityLogResult);

    if($rowcountActivityLog>0){
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
            $sql_1 = 'UPDATE `activity_log` SET status = "completed by system '.$elapsedFullMinutes.'" WHERE id="'.$log_org_id.'"';
            $result_1 = $con->query($sql_1);

            if($result_1){
                $activityLogQuery = "SELECT * FROM `activity_log` WHERE action='label print' AND status='processing' AND action_by='".$_SESSION['name_']."'";
                $activityLogResult = mysqli_query($con, $activityLogQuery);
                
                $rowcountActivityLog = mysqli_num_rows($activityLogResult);
            }
        }
    }
    
    if($rowcountActivityLog == 0){
        if($rowCount > 0){
            $sql = 'INSERT INTO `activity_log`(`action`, `status`, `action_by`, `actionStart_date_time`) VALUES ("label print", "processing", "'.$_SESSION['name_'].'", "'.date('Y-m-d h:i:s').'")';
            $result = $con->query($sql);

            if ($result === TRUE) {
                $lastAction_id = $con->insert_id;
            }

            for($i=0; $i<$rowCount; $i++)
            {
                $SystemOrgOrderID = $_POST["orders"][$i];
                $orderresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $SystemOrgOrderID . "'");
                $orderrow[$i] = mysqli_fetch_array($orderresult);

                if($orderrow[$i]['merge'] == 'Merged' || $orderrow[$i]['merge'] == ''){
                    $ordersku = $orderrow[$i]['sku'];
                    $orderPostalService = $orderrow[$i]['postal_service'];

                    $names = divideName($orderrow[$i]['firstname']);
                    // createDeliveryGroupOrder(
                    $FirstName = $names['firstName'];
                    $LastName = $names['lastName'];
                    $Telephone = $orderrow[$i]['telephone'];
                    $Email = $orderrow[$i]['email'];
                    $AddressCompany = $orderrow[$i]['shippingaddresscompany'];
                    $AddressLine1 = $orderrow[$i]['shippingaddressline1'];
                    $AddressLine2 = $orderrow[$i]['shippingaddressline2'];
                    $AddressLine3 = $orderrow[$i]['shippingaddressline3'];
                    $Town = $orderrow[$i]['shippingaddresscity'];
                    $PostCode = $orderrow[$i]['shippingaddresspostcode'];
                    $Region = $orderrow[$i]['shippingaddressregion'];
                    $Country = $orderrow[$i]['shippingaddresscountry'];
                    $CountryCode = $orderrow[$i]['shippingaddresscountrycode'];

                    $orderDate = $orderrow[$i]['date'];
                    $subtotal = $orderrow[$i]['ordertotal'];
                    $orderChannel = $orderrow[$i]['channel'];

                    $ItemQuantity = $orderrow[$i]['quantity'];
                    $ItemUnitValue = round(($orderrow[$i]['ordertotal'] / $ItemQuantity),2);
                    $ItemDescription = $orderrow[$i]['flags'];

                    $shippingCostCharged = $orderrow[$i]['shipping_cost'];
                    $currencyCode = $orderrow[$i]['currency'];
                    $weightInGramsDB = $orderrow[$i]['weight_In_Grams'];
                    $ItemHeight = $orderrow[$i]['item_height'];
                    $ItemLength = $orderrow[$i]['item_length'];
                    $ItemWidth = $orderrow[$i]['item_width'];

                    $orderId = $orderrow[$i]['orderID'];
                    $SystemOrderID = $orderId;
                    $SystemOrgOrderID = $orderrow[$i]['id'];

                    $tracking_No = $orderrow[$i]['tracking_No'];
                    $label_B64 = $orderrow[$i]['label_B64'];
                    $royalmail_order_id = $orderrow[$i]['royalmail_order_id'];

                    if(trim($tracking_No) == "" && trim($label_B64) == ""){
                        if($orderPostalService == "ParcelDenOnline Standard Package" || $orderPostalService == "ParcelDenOnline Standard Parcel"){    
                            // $createLabel = createParcelDenLabel($FirstName, $LastName, $Telephone, $Email, $BuildingNumber, $Street, $AddressLine1, $AddressLine2, $AddressLine3, $Town, $PostCode, $Region, $CountryCode, $orderPostalService);
                            $createLabel = createParcelDenLabel($FirstName, $LastName, $Telephone, $Email, $AddressLine1, $AddressLine2, $AddressLine3, $Town, $PostCode, $Region, $CountryCode, $orderPostalService);
            
                            if($createLabel["labelCreated"]){
                                $trackingNo = $createLabel["trackingNo"];
                                $labelB64 = $createLabel["labelB64"];
            
                                $field = array("tracking_No", "label_B64");
                                $data = array($trackingNo, $labelB64);
            
                                $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET tracking_No='$trackingNo', label_B64='$labelB64' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders

                                    $mergequery = "SELECT * FROM temporders WHERE merge='" . $mergeid . "' ORDER BY ordertotal ASC, date ASC";
                                    $mergeresult = mysqli_query($con, $mergequery);

                                    $mergeOrderID = $orderrow[$i]["orderID"];

                                    while($mergerow = mysqli_fetch_array($mergeresult))
                                    {
                                        // get different channel merge orders post codes
                                        if($mergeOrderID != $mergerow["orderID"]){
                                            array_push($missingOrderIdPostCodes,$PostCode);
                                        }
                                        // get different channel merge orders post codes
                                        
                                        // $field = array("tracking_No", "label_B64");
                                        // $data = array($trackingNo, $labelB64);
                    
                                        // $updatedMerge = updateData("temporders", $field, $data, "id", $mergerow["orderID"], $con);
                                    }
                                }

                                array_push($base64Arr ,$labelB64);
                            }else{
                                array_push($errorOrderIdPostCodes,$PostCode);
                            }
                        }else if($orderPostalService == "245g LL" || $orderPostalService == "900g parcel" || $orderPostalService == "95g LL" || $orderPostalService == "BPL Royal Mail 1st Class Large Letter" || $orderPostalService == "CRL Royal Mail 24 Large Letter" || $orderPostalService == "CRL Royal Mail 24 Parcel" || $orderPostalService == "TPN Royal Mail Tracked 24 Non Signature" || $orderPostalService == "Rm manual"){
                            $createOrder = createRoyalmailOrder($SystemOrgOrderID, $FirstName, $LastName, $Telephone, $Email, $AddressCompany, $AddressLine1, $AddressLine2, $AddressLine3, $Region, $Town, $PostCode, $Country, $CountryCode, $orderPostalService, $orderDate, $subtotal, $shippingCostCharged, $currencyCode, $weightInGramsDB);

                            if($createOrder["orderCreated"]){
                                $royalmailOrderId = $createOrder["royalMailOrderIdent"];
                                $trackingNo = "";
                                $labelB64 = "";

                                if($createOrder["trackingHas"]){
                                    $trackingNo = $createOrder["trackingNum"];
                                }

                                if($createOrder["labelHas"]){
                                    $labelB64 = $createOrder["labelB64"];
                                }
            
                                $field = array("royalmail_order_id", "tracking_No", "label_B64");
                                $data = array($royalmailOrderId, $trackingNo, $labelB64);
            
                                $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET royalmail_order_id='$royalmailOrderId', tracking_No='$trackingNo', label_B64='$labelB64' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders

                                    $mergequery = "SELECT * FROM temporders WHERE merge='" . $mergeid . "' ORDER BY ordertotal ASC, date ASC";
                                    $mergeresult = mysqli_query($con, $mergequery);

                                    $mergeOrderID = $orderrow[$i]["orderID"];

                                    while($mergerow = mysqli_fetch_array($mergeresult))
                                    {
                                        // get different channel merge orders post codes
                                        if($mergeOrderID != $mergerow["orderID"]){
                                            array_push($missingOrderIdPostCodes,$PostCode);
                                        }
                                        // get different channel merge orders post codes
                                        
                                        // $field = array("tracking_No", "label_B64");
                                        // $data = array($trackingNo, $labelB64);
                    
                                        // $updatedMerge = updateData("temporders", $field, $data, "id", $mergerow["orderID"], $con);
                                    }
                                }

                                array_push($base64Arr ,$labelB64);
                            }else{
                                array_push($errorOrderIdPostCodes,$PostCode);
                            }
                        }else if($orderPostalService == "Hermes ParcelShop Postable (Shop To Door) by MyHermes"){
                            $p2gOrderId = "";
                            $trackingNo = "";
                            $labelB64 = "";
                            $hash = "";
                            $tracking_No = "";

                            $createParcel2GoOrder = createParcel2GoOrder($FirstName, $LastName, $Email, $AddressLine1, $AddressLine2, $AddressLine3, $Region, $Town, $PostCode, $Country, $CountryCode, $subtotal, $ordersku);

                            // print_r($createParcel2GoOrder);

                            if($createParcel2GoOrder['orderCreated']){
                                $p2gOrderId = $createParcel2GoOrder["parcel2GoOrderId"];
                                $hash = $createParcel2GoOrder['hashValue'];
                                $OrderLineId = $createParcel2GoOrder['OrderLineId'];

                                $tracking_No = "P2G".$OrderLineId;

                                $field = array("p2go_id", "p2go_hash", "tracking_No");
                                $data = array($p2gOrderId, $hash, $tracking_No);
            
                                $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET p2go_id='$p2gOrderId', p2go_hash='$hash', tracking_No='$tracking_No' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders
                                }

                                $getLabelParcel2Go = getLabelParcel2Go($p2gOrderId, $hash);

                                if($getLabelParcel2Go['labelCreated']){
                                    $labelB64 = $getLabelParcel2Go['labelB64'];

                                    $field = array("label_B64");
                                    $data = array($labelB64);
                
                                    $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);

                                    if($orderrow[$i]['merge'] == 'Merged'){
                                        $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                        // update tracking number and label data for merge orders
                                        $mergetotalquery = "UPDATE temporders SET label_B64='$labelB64' WHERE merge='".$mergeid."'";
                                        mysqli_query($con, $mergetotalquery);
                                        // update tracking number and label data for merge orders
                                    }

                                    array_push($base64Arr ,$labelB64);
                                }
                            }else{
                                array_push($errorOrderIdPostCodes,$PostCode);
                            }
                        }else if($orderPostalService == "Etrak - Delivery Group"){
                            
                            $createDeliveryGroupOrder = createDeliveryGroupOrder($orderrow[$i]['firstname'], $AddressLine1, $AddressLine2, $AddressLine3, $Town, $Region, $CountryCode, $PostCode, $Telephone, $weightInGramsDB, $ItemHeight, $ItemLength, $ItemWidth, $Email, $ItemQuantity, $ItemUnitValue, $ItemDescription, $shippingCostCharged, $SystemOrgOrderID, $orderChannel);

                            $tracking_No = "";
                            $labelB64 = "";

                            if($createDeliveryGroupOrder['orderCreated']){
                                $tracking_No = $createDeliveryGroupOrder['trackingNum'];
                                $labelCreated = $createDeliveryGroupOrder["labelCreated"];

                                $field = array("tracking_No");
                                $data = array($tracking_No);
            
                                $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET tracking_No='$tracking_No' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders
                                }
                                
                                if($labelCreated){
                                    $labelB64 = $createDeliveryGroupOrder['labelB64'];

                                    array_push($base64Arr ,$labelB64);

                                    if($orderrow[$i]["invoice_B64"] == ""){
                                        $createInvoice = createCommercialInvoice($SystemOrgOrderID, $con, $SystemOrgOrderID);

                                        if($createInvoice['responseCode'] == "200"){
                                            $invoice_B64 = $createInvoice['base64Invoice'];

                                            $field_invoice = array("invoice_B64");
                                            $data_invoice = array($invoice_B64);
                        
                                            $updated_invoice = updateData("temporders", $field_invoice, $data_invoice, "id", $SystemOrgOrderID, $con);

                                            if($orderrow[$i]['merge'] == 'Merged'){
                                                $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];
                
                                                // update tracking number and label data for merge orders
                                                $mergetotalquery = "UPDATE temporders SET invoice_B64='$invoice_B64' WHERE merge='".$mergeid."'";
                                                mysqli_query($con, $mergetotalquery);
                                                // update tracking number and label data for merge orders
                                            }

                                            array_push($base64Arr ,$invoice_B64);
                                        }
                                    }else if($orderrow[$i]["invoice_B64"] != "" AND $orderPostalService == "Etrak - Delivery Group"){
                                        array_push($base64Arr ,$orderrow[$i]["invoice_B64"]);
                                    }
                                }

                                $field = array("label_B64");
                                $data = array($labelB64);
            
                                $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET label_B64='$labelB64' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders
                                }
                            }else{
                                array_push($errorOrderIdPostCodes,$PostCode." ".$createDeliveryGroupOrder['error']);
                            }
                        }
                    }else if(trim($label_B64) != ""){
                        array_push($base64Arr ,$label_B64);

                        if($orderrow[$i]["invoice_B64"] == "" AND $orderPostalService == "Etrak - Delivery Group"){
                            $createInvoice = createCommercialInvoice($SystemOrgOrderID, $con, $SystemOrgOrderID);

                            if($createInvoice['responseCode'] == "200"){
                                $invoice_B64 = $createInvoice['base64Invoice'];

                                $field_invoice = array("invoice_B64");
                                $data_invoice = array($invoice_B64);
            
                                $updated_invoice = updateData("temporders", $field_invoice, $data_invoice, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET invoice_B64='$invoice_B64' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders
                                }

                                array_push($base64Arr ,$invoice_B64);
                            }
                        }else if($orderrow[$i]["invoice_B64"] != "" AND $orderPostalService == "Etrak - Delivery Group"){
                            array_push($base64Arr ,$orderrow[$i]["invoice_B64"]);
                        }
                    }else if(trim($label_B64) == "" AND trim($tracking_No) != "" AND $orderPostalService == "Etrak - Delivery Group"){
                        $createDeliveryGroupLabel = createDeliveryGroupLabel($tracking_No);
                        $labelCreated = $createDeliveryGroupLabel['labelCreated'];
                        $labelB64 = $createDeliveryGroupLabel['labelB64'];
                        $labelError = $createDeliveryGroupLabel['error'];
                        
                        if($labelCreated){
                            $field = array("label_B64");
                            $data = array($labelB64);
        
                            $updated = updateData("temporders", $field, $data, "id", $SystemOrgOrderID, $con);
                            
                            array_push($base64Arr ,$labelB64);
                        }
                        
                        if($orderrow[$i]["invoice_B64"] == ""){
                            $createInvoice = createCommercialInvoice($SystemOrgOrderID, $con, $SystemOrgOrderID);

                            if($createInvoice['responseCode'] == "200"){
                                $invoice_B64 = $createInvoice['base64Invoice'];

                                $field_invoice = array("invoice_B64");
                                $data_invoice = array($invoice_B64);
            
                                $updated_invoice = updateData("temporders", $field_invoice, $data_invoice, "id", $SystemOrgOrderID, $con);

                                if($orderrow[$i]['merge'] == 'Merged'){
                                    $mergeid = $orderrow[$i]["date"]."-".$orderrow[$i]["orderID"];

                                    // update tracking number and label data for merge orders
                                    $mergetotalquery = "UPDATE temporders SET invoice_B64='$invoice_B64' WHERE merge='".$mergeid."'";
                                    mysqli_query($con, $mergetotalquery);
                                    // update tracking number and label data for merge orders
                                }

                                array_push($base64Arr ,$invoice_B64);
                            }
                        }else if($orderrow[$i]["invoice_B64"] != ""){
                            array_push($base64Arr ,$orderrow[$i]["invoice_B64"]);
                        }
                    }
                }
            }

            // updateTracking($con);

            $sql_1 = 'UPDATE `activity_log` SET status = "completed",actionEnd_date_time="'.date('Y-m-d h:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result_1 = $con->query($sql_1);

            if(count($base64Arr)>0){
                mergePDFByBase64($base64Arr, $pdf, 'merged.pdf');
            }
        }else{
            echo "<script>alert('Atleast choose one order/s to print label.');</script>";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 1000);</script>";
        }

        if(count($errorOrderIdPostCodes)>0 || count($missingOrderIdPostCodes)>0){
            $errorOrderIdPostCodesString = implode(",",$errorOrderIdPostCodes);
            $missingOrderIdPostCodesString = implode(",",$missingOrderIdPostCodes);

            $echoStr = "";

            if(count($base64Arr)>0){
                $echoStr .= "Labels download start...";
            }else{
                $echoStr .= "Labels download Error...";
            }

            if(count($errorOrderIdPostCodes)>0){
                $echoStr .= "<br><br>Some Error Order Postcodes<br>".$errorOrderIdPostCodesString;
            }else if(count($missingOrderIdPostCodes)>0){
                $echoStr .= "<br><br>Manual Merge Order Postcodes<br>".$missingOrderIdPostCodesString;
            }

            echo "<script>alert('".$echoStr."');</script>";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 1000);</script>";
        }
    }else{
        echo "<script>alert('Already you printed labels. So, you can not print labels before once completion.');</script>";
        echo "<script>setTimeout(function(){
            window.top.close();
        }, 1000);</script>";
    }
?>