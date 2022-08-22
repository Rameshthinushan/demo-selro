<?php
include "labelGeneration/refreshStore.php";
// ini_set('max_execution_time', 0);

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

// discount delete - start
$discountquery = "SELECT * FROM `temporders` WHERE (sku = '' OR name LIKE '%Discount%' OR name LIKE '%Coupon%') AND ordertotal < 0"; 
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

echo "order added successfully. automatically close this tab after 2 seconds.";
echo "<script>setTimeout(function(){
    window.top.close();
}, 2000);</script>";