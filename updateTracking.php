<?php
    // ini_set('max_execution_time', 0);
    $DATABASE_HOST   = 'localhost';
    $DATABASE_USER   = 'root';
    $DATABASE_PASS   = '';
    $DATABASE_NAME = 'u525933064_dashboard';

    $connect = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
   
    // $con_hostinger = mysqli_connect("153.92.6.22", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
    $con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
    // $con_hostinger = mysqli_connect("localhost","root","","u525933064_dashboard");
    // changed puvii after cloud hosting updated

    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }else{
        if (isset($_POST["updateTrackingBtn"])) {
            $fromUpdate = $_POST["fromUpdate"];
            if($fromUpdate == "fromTable"){
                $query = "SELECT * FROM temporders";
                $result = mysqli_query($connect, $query);

                $errorInfo = "";
                $updateTrackinginOrders = 0;
                $tempOrders = 0;

                while ($row = mysqli_fetch_array($result)) {
                    $tempOrders = $tempOrders + 1;

                    $postal_service = $row["postal_service"];
                    $shipment_id = $row["shipment_id"];
                    $tracking_No = trim($row["tracking_No"]);
                    $royalmail_order_id = $row["royalmail_order_id"];
                    $p2go_id = $row["p2go_id"];
                    $p2go_hash = $row["p2go_hash"];
                    $label_B64 = $row["label_B64"];

                    if($row["trackingStatus"] != "true"){
                        $query = "UPDATE orders SET shipment_id='$shipment_id', TrackingNumber='$tracking_No', PostalService='$postal_service', royalmail_order_id='$royalmail_order_id', p2go_id='$p2go_id', p2go_hash='$p2go_hash' WHERE orderID='" . $row["orderID"] . "' AND shippingaddresspostcode='" . $row["shippingaddresspostcode"] . "'";
                        $update = mysqli_query($con_hostinger, $query);
                    }else if($row["trackingStatus"] == "true"){
                        $update = true;

                        $query_select = "SELECT * FROM orders WHERE orderID='" . $row["orderID"] . "' AND shippingaddresspostcode='" . $row["shippingaddresspostcode"] . "'";
                        $query_select = mysqli_query($con_hostinger, $query_select);

                        $result_select = mysqli_fetch_array($query_select);
                        if($tracking_No != $result_select['TrackingNumber'] || $shipment_id != $result_select['shipment_id'] || $postal_service != $result_select['PostalService'] || $royalmail_order_id != $result_select['royalmail_order_id'] || $p2go_id != $result_select['p2go_id']){
                            $query = "UPDATE orders SET shipment_id='$shipment_id', TrackingNumber='$tracking_No', PostalService='$postal_service', royalmail_order_id='$royalmail_order_id', p2go_id='$p2go_id', p2go_hash='$p2go_hash' WHERE orderID='" . $row["orderID"] . "' AND shippingaddresspostcode='" . $row["shippingaddresspostcode"] . "'";
                            $update = mysqli_query($con_hostinger, $query);
                        }
                    }

                    if($update){
                        $updateTrackinginOrders = $updateTrackinginOrders + 1;

                        // trackingStatus
                        $org_totalquery = "UPDATE temporders SET trackingStatus='true' WHERE id='".$row["id"]."'";
                        $updating = mysqli_query($connect, $org_totalquery);
                    }else{
                        $errorInfo .= $row["orderID"]." ".$tracking_No." ".$postal_service."<br>";
                    }
                }

                if($updateTrackinginOrders == $tempOrders){
                // if($orderaddedrows == $ordercsvrows){
                    echo "Update tracking completed in system. automatically close after 3 second.";
                    echo "<script>setTimeout(function(){
                        window.top.close();
                    }, 3000);</script>";
                    // echo "<script>setTimeout(function(){
                    //     window.location.replace('http://digitweb.vintageinterior.co.uk/demo%20final/');
                    // }, 1000);</script>";
                }else if($updateTrackinginOrders < $tempOrders AND $updateTrackinginOrders > 0){
                    echo "Some orders missing and others tracking completed in system.";
                    echo "<br>Missing order numbers.<br><br>";
                    echo $errorInfo;

                }else if($updateTrackinginOrders == 0){
                    echo "Update tracking failed in system.";
                }
            }else if($fromUpdate == "fromFile"){
                $fileName = $_FILES["updateTrackingfile"]["tmp_name"];
        
                if ($_FILES["updateTrackingfile"]["size"] > 0) {
                    $ordercsvrows = 0;
                    $orderaddedrows = 0;

                    $tempOrders = 0;
                    $updateTrackinginOrders = 0;
            
                    $file = fopen($fileName, "r");
                    $g=0;
                    $flag = true;
                    while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                        $ordercsvrows = $ordercsvrows + 1;

                        $orginalId = $column[0];
                        $orderID = $column[1];
                        $orderNum = $column[2];
                        $Channel = $column[3];
                        $Tracking_Number = $column[4];
                        $Postal_Service = $column[5];
                        
                        $query_first = "UPDATE temporders SET tracking_No='".$Tracking_Number."' WHERE id='".$orginalId."'";

                        $updated = mysqli_query($connect, $query_first);

                        if($updated){
                            $orderaddedrows = $orderaddedrows + 1;

                            $query = "SELECT * FROM temporders WHERE id = '$orginalId'";
                            $result = mysqli_query($connect, $query);
                            $row = mysqli_fetch_array($result);

                            $postal_service = $row["postal_service"];
                            $shipment_id = $row["shipment_id"];
                            $tracking_No = $row["tracking_No"];
                            $royalmail_order_id = $row["royalmail_order_id"];
                            $p2go_id = $row["p2go_id"];
                            $p2go_hash = $row["p2go_hash"];
                            $label_B64 = $row["label_B64"];

                            if($row["trackingStatus"] != "true"){
                                $query = "UPDATE orders SET shipment_id='$shipment_id', TrackingNumber='$tracking_No', PostalService='$postal_service', royalmail_order_id='$royalmail_order_id', p2go_id='$p2go_id', p2go_hash='$p2go_hash', label_B64='$label_B64' WHERE orderID='" . $row["orderID"] . "' AND shippingaddresspostcode='" . $row["shippingaddresspostcode"] . "'";
                                $update = mysqli_query($connect, $query);
                            }else if($row["trackingStatus"] == "true"){
                                $update = true;
                            }

                            if($update){
                                $updateTrackinginOrders = $updateTrackinginOrders + 1;

                                // trackingStatus
                                $org_totalquery = "UPDATE temporders SET trackingStatus='true' WHERE id='".$row["id"]."'";
                                $updating = mysqli_query($connect, $org_totalquery);
                            }else{
                                $errorInfo .= $row["orderID"]." ".$tracking_No." ".$postal_service."<br>";
                            }
                        }
                    }

                    // $query = "SELECT * FROM temporders";
                    // $result = mysqli_query($connect, $query);

                    // while ($row = mysqli_fetch_array($result)) {
                    //     $tempOrders = $tempOrders + 1;

                    //     $postal_service = $row["postal_service"];
                    //     $shipment_id = $row["shipment_id"];
                    //     $tracking_No = $row["tracking_No"];
                    //     $royalmail_order_id = $row["royalmail_order_id"];

                    //     $query = "UPDATE orders SET date='" . $row["date"] . "', shipment_id='$shipment_id', TrackingNumber='$tracking_No', PostalService='$postal_service', royalmail_order_id='$royalmail_order_id' WHERE orderID='" . $row["orderID"] . "' AND channel='" . $row["channel"] . "' AND firstname='" . $row["firstname"] . "' AND name='" . $row["name"] . "' AND sku='" . $row["sku"] . "'";
                    //     $update = mysqli_query($connect, $query);

                    //     if($update){
                    //         $updateTrackinginOrders = $updateTrackinginOrders + 1;
                    //     }
                    // }

                    if($updateTrackinginOrders == $tempOrders){
                        // if($orderaddedrows == $ordercsvrows){
                        echo "Update tracking completed in system. automatically close after 3 second.";
                        echo "<script>setTimeout(function(){
                            window.top.close();
                        }, 3000);</script>";
                        // echo "<script>setTimeout(function(){
                        //     window.location.replace('http://digitweb.vintageinterior.co.uk/demo%20final/');
                        // }, 1000);</script>";
                    }else if($updateTrackinginOrders < $tempOrders AND $updateTrackinginOrders > 0){
                        echo "Some orders missing and others tracking completed in system.";
                        echo "<br>Missing order numbers.<br><br>";
                        echo $errorInfo;
    
                    }else if($updateTrackinginOrders == 0){
                        echo "Update tracking failed in system.";
                    }
                }
            }
        }
    }

    // $connect = mysqli_connect("localhost","root","","u525933064_dashboard");
    // $query = "SELECT * FROM temporders";
    // $result = mysqli_query($connect, $query);

    // while ($row = mysqli_fetch_array($result)) {
    //     // $row["orderID"];
    //     // $row["firstname"];
    //     // $row["name"];
    //     // $row["sku"];

    //     $postal_service = $row["postal_service"];
    //     $shipment_id = $row["shipment_id"];
    //     $tracking_No = $row["tracking_No"];
    //     $royalmail_order_id = $row["royalmail_order_id"];
        
    //     // $sql = "INSERT into orders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, booking, csvdate, unit, PostalService) values ('" . $row['orderID'] . "','" . $status . "','" . $date . "','" . $row['channel'] . "','" . $firstname . "','" . $row['email'] . "','" . $row['currency'] . "','" . $row['ordertotal'] . "','" . $row['name'] . "','" . $row['sku'] . "','" . $row['quantity'] . "','" . $flags . "','" . $row['shippingservice'] . "','" . $row['shippingaddressline1'] . "','" . $row['shippingaddressline2'] . "','" . $row['shippingaddressline3'] . "','" . $row['shippingaddressregion'] . "','" . $row['shippingaddresscity'] . "','" . $row['shippingaddresspostcode'] . "','" . $row['shippingaddresscountry'] . "','" . $row['shippingaddresscountrycode'] . "','" . $row['booking'] . "','" . $row['csvdate'] . "','" . $row['unit'] . "','" . $row['postal_service'] . "')";

    //     $query = "UPDATE orders SET date='" . $row["date"] . "', shipment_id='$shipment_id', TrackingNumber='$tracking_No', PostalService='$postal_service', royalmail_order_id='$royalmail_order_id' WHERE orderID='" . $row["orderID"] . "' AND channel='" . $row["channel"] . "' AND firstname='" . $row["firstname"] . "' AND name='" . $row["name"] . "' AND sku='" . $row["sku"] . "'";
    //     $update = mysqli_query($connect, $query);
    // }

    // if($update){
    //     echo "Update tracking completed in system.";
    // }else{
    //     echo "Update tracking failed in system.";
    // }