<?php
include 'functionsToCreateLabels.php'; 
ini_set('max_execution_time', 0);
date_default_timezone_set('Europe/London');

// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
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

if ($_POST["warehouseTransferId"] != "") {
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

        $getWarehouseTransferID = $_POST["warehouseTransferId"];

        $warehouseQuery = "SELECT * FROM `stocktransferlist` WHERE `stocktransferid` = '".$getWarehouseTransferID."'";
        $warehouseResult = mysqli_query($con_hostinger, $warehouseQuery);
        
        $rowcount = mysqli_num_rows($warehouseResult);

        if(trim($error) == "" && $rowcount>0){
            while ($warehouseRow = mysqli_fetch_array($warehouseResult)){
                $customerNotes = $shippingaddressline1 = $shippingaddressline2 = $shippingaddressline3 = $shippingaddressregion = $shippingaddresscity = $shippingaddresspostcode = $shippingaddresscountry = $shippingaddresscountrycode = $shipping_cost = $postal_service = $shippingaddresscompany = $shippingservice = $shipPhoneNumber = $email = $currency = $ordertotal = $itemsImageUrl = '';

                if (date('H') < 10) {
                    $booking = "1st Booking";
                }else{
                    $booking = "2nd Booking";
                }

                $status = 'pending';
                $date = $csvdate = date("Y-m-d");
                $unit = 'unit2';

                $channel = $warehouseRow['channel_name'].' (WAREHOUSE TRANSFER)';
                // get from stock transfer list
                $orderID = $warehouseRow['shipment_id']."-".$warehouseRow['plan_id'];
                $name = $warehouseRow['asin'];
                $sku = $warehouseRow['sku'];
                $mapped_sku =  $warehouseRow['listing_sku'];
                $firstname = $warehouseRow['ship_to_address'];
                $quantity = $warehouseRow['quantity'];

                $ordertotal = 0;
                $total = $ordertotal;
                $weight_In_Grams = 0;

                //flags start
                $flags = getFlags($sku);
                //Flags end
                
                $subflags = getSubFlags($sku,$flags,$con);

                $table = "temporders";
                $field_values = array("image_from_ship", "orderID", "status", "date", "channel", "firstname", "telephone", "email","currency", "ordertotal", "name", "sku", "orgSku", "quantity", "flags", "subflags", "shippingservice", "shippingaddresscompany", "shippingaddressline1","shippingaddressline2","shippingaddressline3","shippingaddressregion","shippingaddresscity","shippingaddresspostcode","shippingaddresscountry","shippingaddresscountrycode","shipping_cost","postal_service","booking","csvdate","unit","total", "weight_In_Grams", "notes");
                $data_values = array($itemsImageUrl, $orderID, $status, $date, $channel, $firstname, $shipPhoneNumber, $email, $currency, $ordertotal, $name, $sku, $mapped_sku, $quantity, $flags, $subflags, $shippingservice, $shippingaddresscompany, $shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressregion, $shippingaddresscity, $shippingaddresspostcode, $shippingaddresscountry, $shippingaddresscountrycode, $shipping_cost, $postal_service, $booking, $csvdate, $unit, $total, $weight_In_Grams, $customerNotes);

                $added = addData($table,$field_values,$data_values,$con);

                if(!$added){
                    $added = addData($table,$field_values,$data_values,$con);
                }
            }
            
            $sql = 'UPDATE `activity_log` SET status = "completed",actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);
            
            echo "order added successfully. automatically close this tab after 2 seconds.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 2000);</script>";
        }else if($orderDetailsCount == 0){
            $sql = 'UPDATE `activity_log` SET status = "completed", actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);

            echo "order count is zero. automatically close this tab after 5 seconds.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 5000);</script>";
        }else if(trim($error) != ""){
          $sql = 'UPDATE `activity_log` SET status = "completed", actionEnd_date_time="'.date('Y-m-d H:i:s').'" WHERE id="'.$lastAction_id.'"';
            $result = $con->query($sql);
          
            print_r($error);
        }
    }
}