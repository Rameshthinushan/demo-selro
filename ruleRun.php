<?php
include "labelGeneration/refreshStore.php";

//require_once('authenticate.php');
// $DATABASE_HOST = 'db5000259912.hosting-data.io';
// $DATABASE_USER = 'dbu211597';
// $DATABASE_PASS = 'Sone#1127';
// $DATABASE_NAME = 'dbs253594';
// $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// if (mysqli_connect_errno()) {
// 	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
// }

// 	$mergeOrdersQuery = "SELECT * FROM temporders"; 
//     $mergeOrdersResult = mysqli_query($con, $mergeOrdersQuery);
// $count = mysqli_num_rows($mergeOrdersResult);
// 	$i = 0;
//     while($totalrow = mysqli_fetch_array($mergeOrdersResult))
//     {
//       $id = $totalrow['id'];
//       $sku = $totalrow['sku'];
//       $flags = $totalrow['flags'];
//       $quantity = $totalrow['quantity'];
//       $ordertotal = $totalrow['total'];
//       $shippingcost = $totalrow['shipping_cost'];
//       $postalCode = $totalrow['shippingaddresspostcode'];
//       $channel = $totalrow['channel'];
//       $shippingService = $totalrow['shippingservice'];
      
//       $postalService = getPostalService($sku, $flags, $quantity, $ordertotal, $shippingcost, $postalCode, $channel, $shippingService, $con);
      
//       $updateQuery = "UPDATE temporders SET postal_service='$postalService' WHERE id='".$id."'";
//       $updated = mysqli_query($con, $updateQuery);
      
//       if($updated){
//         $i = $i + 1;
//       }
//     }

// if($count<$i and $i>0){
//   echo "Some updated";
// }else if($count==$i){
//   echo "updated";
// }else{
//   echo "updated fail";
// }