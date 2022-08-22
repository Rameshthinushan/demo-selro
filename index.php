<?php
// include "functions.php";
include "labelGeneration/refreshStore.php";

session_start();
// If the user is not logged in redirect to the login page...

// if (!isset($_SESSION['loggedin_'])) {
//      header('Location: https://digitweb.vintageinterior.co.uk/index.html');
//      exit();
// }

$con_hostinger = $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

//$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
// $con_hostinger = mysqli_connect("localhost","root","","u525933064_dashboard");

$query = "SELECT * FROM temporders WHERE (merge = '' OR merge = 'Merged')";
$postalservice = "";
$category      = "";
$subcategory   = "";
$morefilter    = "";
$sorting       = "";
$multiple      = [];
$multiple2     = [];

if (isset($_POST["category"])) 
{
     $_SESSION['category'] = $_POST["category"];
     if ($_POST["category"] == "Select") 
     {
          unset($_SESSION['category']);
     }
}
// else{
//      $_SESSION["category"] == "Bulbs";
// }

if (isset($_POST["subcategory"])) 
{
     $_SESSION['subcategory'] = $_POST["subcategory"];
     if ($_POST["subcategory"] == "Select") 
     {
          unset($_SESSION['subcategory']);
     }
}

if (isset($_POST["morefilter"])) 
{
     $_SESSION['morefilter'] = $_POST["morefilter"];
     if ($_POST["morefilter"] == "Select") 
     {
          unset($_SESSION['morefilter']);
     }
}

if (isset($_POST["postalservice"])) 
{
     // $postalservice = implode(", ",$_POST['postalservice']);
     $_SESSION['postalservice'] = implode(", ",$_POST['postalservice']);
     // if (implode(", ",$_POST['postalservice']) == "Select") {
     //      unset($_SESSION['postalservice']);
     // }
     if (strpos(implode(", ",$_POST['postalservice']), 'Select') !== false) 
     {
          unset($_SESSION['postalservice']);
     }
}

$_SESSION['sorting'] = "ASC";
if (isset($_POST["sorting"])) 
{
     $_SESSION['sorting'] = $_POST["sorting"];
}

if (isset($_SESSION['category'])) 
{
     $category = $_SESSION['category'];
     if($category == "others")
     {
          $query .= " AND (flags !='Lampshade' AND flags !='Lampshade Shade Only')";
     }
     else
     {
          $query .= " AND flags='$category' ";
     }
}


if (isset($_SESSION['subcategory'])) 
{
     $subcategory = $_SESSION['subcategory'];
     if ($subcategory == "Empty") 
     {
          $query .= " AND subflags=''";
     }
     else 
     {
          $query .= " AND subflags!=''";
     }
}

if (isset($_SESSION['morefilter'])) 
{
     $morefilter = $_SESSION['morefilter'];

     if ($morefilter == "others") 
     {
          $query .= " AND shippingservice != 'International' AND shippingservice != 'Prime' AND shippingservice != 'firstclass' AND shippingservice != 'collection order' AND shippingservice != 'fba'";
     } 
     else
     {
          $query .= " AND shippingservice = '".$morefilter."'";
     }
}

if (isset($_SESSION['postalservice'])) 
{
     $postalservice = $_SESSION['postalservice'];
     $postalServiceArray = explode(", ",$postalservice);
     if(count($postalServiceArray) > 1)
     {
          $query .= " AND (";
          foreach ($postalServiceArray as $key => $postalServiceArr) 
          {
               $query .= "(postal_service LIKE '%".$postalServiceArr."%') OR ";
               if($key+1 == count($postalServiceArray))
               {
                    $query .= " 0)";
               }
          }
     }
     else
     {
          $query .= " AND (postal_service LIKE '%".$postalservice."%')";
     }
}

if (isset($_SESSION['sorting'])) 
{
     $sorting = $_SESSION['sorting'];
}

if($sorting == "replacementSort_dateDESC"){
     $query .= " ORDER BY date DESC";
}else if($sorting == "newBoxSize_ASC"){
     $query .= " ORDER BY box_sizes ASC, total ASC, date ASC";
}else if($sorting == "newBoxSize_DESC"){
     $query .= " ORDER BY box_sizes DESC, total ASC, date ASC";
}else if ($category == "Lampshade Shade Only" || $category == "Lampshade" || $category == "Transformer" || $category == "Bulbs") {
     $query .= " ORDER BY subflags " . $sorting . ", total ASC, date ASC";
}else if($category == "others"){
     $query .= " ORDER BY flags DESC,subflags " . $sorting . ", total ASC, date ASC";
}else{
     $query .= " ORDER BY total, date ASC";
}

$result = mysqli_query($connect, $query);
// $count = mysqli_num_rows($result);
// echo $count;
?>
<!DOCTYPE html>
<html>

<head>
     <title>LEDSone Orders</title>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
     <link   href="../style.css" rel="stylesheet" type="text/css">
     <link   rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />

     <style>
          .badge-danger {
               color: #fff;
               background-color: #dc3545;
          }

          .badge-warning {
               color: #212529;
               background-color: #ffc107;
          }

          .badge-info {
               color: #fff;
               background-color: #17a2b8;
          }

          .badge-secondary{
               color: #fff;
               background-color: #6c757d;
          }
     </style>
</head>

<body>
     <nav class="navtop" id="desktoponly" style="height:80px; position: fixed;">
          <div>
               <h1>SELRO Orders</h1>
               <a href="../profile.php"><i class="fas fa-user-circle"></i>Profile</a>
               <a href="../products.php"><i class="fas fa-database"></i>Products</a>
               <a href="../skutool.php"><i class="fas fa-database"></i>New SKU tool</a>
               <a href="../SKUUpdate.php"><i class="fas fa-database"></i>SKU Update</a>
               <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>

          </div>
     </nav>
     <br /><br />
     <div class="container" style="width:1300px;">
          <div id="wait" style="display:none;width:200px;height:200px; position:absolute;top:50%;left:50%;padding:2px;"><img src='../demo_wait.gif' width="200" height="200" /><br>Loading..</div>
          <div class="table-responsive">
               <div align="right" style="position: fixed; 
                                         top: 90px;/* Set the navbar to fixed position 
                                         right:18px;  
                                         right:300px; 
                                         Position the navbar at the top of the page */">  
                    <!--
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_filter">Filter</button>
                    <button type="button" name="addcsv" id="addcsv" data-toggle="modal" data-target="#addcsv_data_Modal" class="btn btn-warning">Orders CSV</button>
                    <button type="button" name="add" id="add" data-toggle="modal" data-target="#add_data_Modal" class="btn btn-warning">Add New Order</button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_subflagform">Add SubFlags</button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_form">Add Flags</button>
                 	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_postalserviceform">Add PostalService</button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_move">Move to Open</button>
                    <button type="button" class="btn btn-success" id="packlist">Generate Packlist</button>
                    <button type="button" class="btn btn-success" id="shippingLabel">Generate Label</button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_gencsvform">Generate CSV</button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_delete" style="background-color: #f44336;">Delete</button>
                    <!-- <button type="button" class="btn btn-success" id="refreshOrders">Refresh</button> 
                    <button type="button" name="refreshOrders" id="refreshOrders" data-toggle="modal" data-target="#refreshOrders_Modal" class="btn btn-warning">Refresh</button>
                    <!-- <button type="button" class="btn btn-success" id="updateTracking">Update Tracking</button> 
                    <button type="button" name="addcsv" id="addcsv" data-toggle="modal" data-target="#updateTracking_Modal" class="btn btn-warning">Update Tracking</button>
                    <button type="button" class="btn btn-success" id="markShippedBtn">Mark Shipped</button>
                    -->
                    <div class="btn-group">
                         <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_filter">Filter</button>
                         <button type="button" name="addcsv" id="addcsv" data-toggle="modal" data-target="#addcsv_data_Modal" class="btn btn-warning">Orders CSV</button>
                         <button type="button" name="add" id="add" data-toggle="modal" data-target="#add_data_Modal" class="btn btn-warning">Add New Order</button>
                         <button type="button" name="refreshOrders" id="refreshOrders" data-toggle="modal" data-target="#refreshOrders_Modal" class="btn btn-warning">Refresh</button>

                         <div class="btn-group">
                              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">More Actions <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu" role="menu" >
                         
                                   <button type="button" class="btn" data-toggle="modal" data-target="#modal_subflagform" style="width:157px;">Add SubFlags</button>
                                   <button type="button" class="btn" data-toggle="modal" data-target="#modal_form" style="width:157px;">Add Flags</button>
                                   <button type="button" class="btn" data-toggle="modal" data-target="#modal_postalserviceform" style="width:157px;">Add PostalService</button>
                                   <button type="button" class="btn" data-toggle="modal" data-target="#modal_move" style="width:157px;">Move to Open</button>
                                   <button type="button" class="btn" id="packlist" style="width:157px;">Generate Packlist</button>
                                   <button type="button" class="btn" id="shippingLabel" style="width:157px;">Generate Label</button>
                                   <button type="button" class="btn" data-toggle="modal" data-target="#modal_gencsvform" style="width:157px;">Generate CSV</button>
                                   <button type="button" class="btn" data-toggle="modal" data-target="#modal_delete" style="width:157px;">Delete</button>
                                   <button type="button" name="addcsv" id="addcsv" data-toggle="modal" data-target="#updateTracking_Modal" class="btn" style="width:157px;">Update Tracking</button>
                                   <button type="button" class="btn" id="markShippedBtn" style="width:157px;">Mark Shipped</button>
                                   <button type="button" class="btn" id="getCountBtn" style="width:157px;">Get Counts</button>
                                   <button type="button" class="btn" id="autoMergeBtn" style="width:157px;">Auto Merge</button>
                                   <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal_deleteAll" style="width:157px;">Delete All</button>
                                   <!-- <button type="button" class="btn btn-warning" name="getFromWarehouse" id="getFromWarehouse" data-toggle="modal" data-target="#getFromWarehouse_Modal" style="width:157px;">Get From Warehouse
                                   Transfer</button> -->
                              </ul>
                         </div>
                    </div>
               </div>
               <br />
               <div id="employee_table" style="margin-top:80px;">
                    <p id="refreshOrdersStatus">
                         
                    </p>
                    <?php
                    if($category == "")
                    {
                         $categoryText = "All";
                    }
                    else
                    {
                         $categoryText = $category;
                    }
                    
                    $orderEmptyCount = getEmptyOrdersCount($category, "empty", $morefilter, $postalservice, $connect);
                    $orderNotEmptyCount = getEmptyOrdersCount($category, "notempty", $morefilter, $postalservice, $connect);

                    echo "<p style='text-align:center;'><strong>Empty Subflags Orders - " . $orderEmptyCount . ", Non-Empty Subflags Orders - " . $orderNotEmptyCount . "</strong> For ".$categoryText." category</p>";

                    if ($category == "Lampshade Shade Only" or $category == "Lampshade" or $category == "Transformer" or $category == "Bulbs") {
                         echo "<p style='text-align:center;'>
                                   <strong>Orders sorted subflags by " . $_SESSION['sorting'] . "</strong>
                              </p>";
                    }

                    if (isset($_SESSION['postalservice'])) {
                         echo "<p style='text-align:center;'
                                   Postal Service like <strong>" . $_SESSION['postalservice'] . "</strong> Orders Only
                              </p>";
                    }
                    if (isset($_SESSION['morefilter'])) {
                         echo "<p style='text-align:center;'>
                                   <strong>Orders more filter is enabled.</strong>
                              </p>";
                    }
                    if (isset($_SESSION['category'])) {
                         echo "<p style='text-align:center;'>
                                   Flags like <strong>" . $_SESSION['category'] . "</strong> Orders Only
                              </p>";
                    }
                    if (isset($_SESSION['subcategory'])) {
                         echo "<p style='text-align:center;'>
                                   Sub Flags like <strong>" . $_SESSION['subcategory'] . "</strong> Orders Only
                              </p>";
                    }
                    if (isset($_SESSION['category']) or isset($_SESSION['subcategory']) or isset($_SESSION['morefilter'])) {
                         echo "<p style='text-align:center; color:red;'>
                                   <i>( Please select Select All in filter to see all orders )</i>
                              </p>";
                    }
                    ?>
                    <form name="frmOrders" id="frmOrders" method="post" target="_blank" action="">
                         <table class="table table-bordered" id="pendingts" style="table-layout: fixed;">
                              <tr>
                                   <th width="6%">
                                        <button type="button" id="pendingselectAll" class="main">
                                             <span class="sub"></span> Select All 
                                        </button>
                                   </th>
                                   <th width="9%">Image</th>
                                   <th width="7%">Order ID</th>
                                   <th width="17%">Info</th>
                                   <th width="12%">SKU</th>
                                   <th width="13%">Address</th>
                                   <th width="6%">Flags</th>
                                   <th width="3%">Edit</th>
                                   <th width="3%">View</th>
                                   <th width="4%">Delete</th>
                              </tr>
                              <?php
                              $g = 0;
                              $g1 = 0;
                              while ($row = mysqli_fetch_array($result))
                              {
                                   $mainimageorder = '';
                                   $ordersku = $row["sku"];
                                   $sql = "SELECT * 
                                           FROM comboproducts 
                                           WHERE sku='" . $ordersku . "' 
                                           OR originalsku='" . $ordersku . "'";
                                   $mainimageresult = mysqli_query($con_hostinger,$sql);
                                   $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
                                   $count = mysqli_num_rows($mainimageresult);
                                   if($count>0)
                                   {
                                        $mainimageorder = $mainimagerow['comboproducts']['image'];
                                   }
                                   if (empty($mainimageorder) && (strpos($ordersku, '+') === false)) {
                                        $mainimageresult = mysqli_query($con_hostinger, "SELECT * 
                                                                                         FROM products 
                                                                                         WHERE SKU='" . $ordersku . "'");
                                        $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);

                                        $count = mysqli_num_rows($mainimageresult);
                                        if($count>0)
                                        {
                                             $mainimageorder = $mainimagerow['comboproducts']['Mainimage'];
                                        }
                                   }
                                   $clientname = "Name : " . $row["firstname"];
                                   $address = "";
                                   if (!empty($clientname)) 
                                   {
                                        $address = $address . $clientname . "<br>";
                                   }
                                   if (!empty($row["shippingaddresscompany"])) 
                                   {
                                        $address = $address . $row["shippingaddresscompany"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddressline1"])) 
                                   {
                                        $address = $address . $row["shippingaddressline1"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddressline2"])) 
                                   {
                                        $address = $address . $row["shippingaddressline2"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddressline3"])) 
                                   {
                                        $address = $address . $row["shippingaddressline3"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddressregion"])) 
                                   {
                                        $address = $address . $row["shippingaddressregion"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddresscity"])) 
                                   {
                                        $address = $address . $row["shippingaddresscity"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddresspostcode"])) 
                                   {
                                        $address = $address . $row["shippingaddresspostcode"] . "<br>";
                                   }
                                   if (!empty($row["shippingaddresscountry"])) 
                                   {
                                        $address = $address . $row["shippingaddresscountry"] . "<br>";
                                   }
                                   if ($row["merge"] == "Merged") {
                                        $mergeid     = $row["date"] . "-" . $row["orderID"];
                                        $mergequery  = "SELECT * FROM temporders WHERE merge='" . $mergeid . "'";
                                        $mergeresult = mysqli_query($connect, $mergequery);
                                        $row_cnt     = mysqli_num_rows($mergeresult);
                                        $rowspanno   = ($row_cnt + 1);
                                   } 
                                   else
                                   {
                                        $rowspanno = 1;
                                   }

                              ?>
                         
                         <tr>
                              <td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $row["id"]; ?>"></td>
                              <td><?php echo "<img id='img" . $row["id"] . "' style='width:100px; height:auto;' src='" . $mainimageorder . "'>"; ?></td>
                              <td><?php echo '<span style="word-wrap: break-word; white-space: pre-wrap;">'.$row["orderID"].'</span>';
                                   if ($row["merge"] == "Merged") echo '<br>Merge';
                                   $count = getOpenOrderCount($row["shippingaddresspostcode"], $row["sku"], $row["date"], $con_hostinger);
                                   if ($count > 0) echo '<span class="badge badge-danger">Already In - '.$count.'</span>';
                                   if (($row["tracking_No"] != "" || $row["label_B64"] != "") AND ($row["shippedStatus"] != "true")) echo '<br><span class="badge badge-warning">Not Shipped</span>';
                                   if (($row["tracking_No"] != "" || $row["label_B64"] != "") AND ($row["trackingStatus"] != "true")) echo '<br><span class="badge badge-secondary">Tracking Failed</span>';
                                   if ($row["label_B64"] == "") echo '<br><span class="badge badge-info">Label Error</span>'; ?></td>
                              <td>
                                   <b>Sub Flags -</b>
                                   <span id="<?php echo $row['id']; ?>subflags"><?php echo $row["subflags"]; ?></span><br>
                                   <b>Boxsizes -</b>
                                   <span id="<?php echo $row['id']; ?>box_sizes"><?php echo $row["box_sizes"]; ?></span><br>
                                   <b>Postal - <span style="color:red;" id="<?php echo $row['id']; ?>postal"><?php echo $row["postal_service"]; ?></span></b><br>
                                   <b>Qty -</b> <?php echo $row["quantity"]; ?><br><b>Total -</b> <?php echo $row["total"]; ?><br>
                                   <b>ShippingCost -</b> <?php echo $row["shipping_cost"]; ?><br>
                                   <b>Weight -</b> <span id="<?php echo $row['id']; ?>weight"><?php echo $row["weight_In_Grams"]; ?></span><br><b>CSV Date -</b> <?php echo $row["csvdate"]; ?>
                                   <?php 
                                        if($row["shippingservice"] != "international"){
                                             $getFBAStock = getFBAStock($row["sku"], $con_hostinger);
                                             if($getFBAStock != "ZERO REC"){
                                                  echo "<br><b><span style='color:#ff5c55;'>".$getFBAStock."</span></b>"; 
                                             }
                                        }
                                   ?>
                              </td>
                              <td>
                                   <p style="word-wrap: break-word; white-space: pre-wrap;" id="<?php echo $row['id']; ?>"><?php echo $row["sku"] . '</p>'; ?> 
                                   <?php 
                                        if($row["orgSku"] != "")
                                        {
                                             echo '<span style="color:red; word-wrap: break-word; white-space: pre-wrap;">'.$row["orgSku"].'</span><br>'; 
                                        }
                                   ?>
                                   <?php echo $row["date"] . '<br>' . $row["channel"]; ?>
                              </td>
                              <td rowspan=<?php echo $rowspanno; ?>><?php echo $address; ?></td>
                              <td><span id="<?php echo $row['id']; ?>flags"><?php echo $row["flags"]; ?></span></td>
                              <td><input type="button" name="edit" value="Edit" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs edit_data" /></td>
                              <td><input type="button" name="view" value="view" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs view_data" /></td>
                              <td><input type="button" name="delete" value="delete" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs delete_data" /></td>
                         </tr>
                                   <?php
                                   if ($row["merge"] == "Merged") {
                                        while ($mergerow = mysqli_fetch_array($mergeresult)) 
                                        {
                                             if ($row["channel"] != $mergerow["channel"]) 
                                             {
                                                  if (!in_array($row["shippingaddresspostcode"], $multiple)) 
                                                  {
                                                       $multiple[$g] = $row["shippingaddresspostcode"];
                                                       $g = $g + 1;
                                                  }
                                             }

                                             if ($row["channel"] == $mergerow["channel"]) 
                                             {
                                                  if (!in_array($row["shippingaddresspostcode"], $multiple2)) 
                                                  {
                                                       $multiple2[$g1] = $row["shippingaddresspostcode"];
                                                       $g1 = $g1 + 1;
                                                  }
                                             }
                                             $ordersku = $mergerow["sku"];
                                             $mainimageorder = '';
                                             $mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
                                             $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
                                             $count = mysqli_num_rows($mainimageresult);
                                             if($count>0)
                                             {
                                                  $mainimageorder = $mainimagerow['comboproducts']['image'];
                                             }
                                             if (empty($mainimageorder) && (strpos($ordersku, '+') === false)) {
                                                  $mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                                                  $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
                                                  $count = mysqli_num_rows($mainimageresult);
                                                  if($count>0)
                                                  {
                                                       $mainimageorder = $mainimagerow['comboproducts']['Mainimage'];
                                                  }
                                             }
                                   ?>
                                             <tr>
                                                  <td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $mergerow["id"]; ?>"></td>
                                                  <td><?php echo "<img id='img" . $mergerow["id"] . "' style='width:100px; height:auto;' src='" . $mainimageorder . "'>"; ?></td>
                                                  <td><?php echo '<span style="word-wrap: break-word; white-space: pre-wrap;">'.$mergerow["orderID"].'</span>'; 
                                                  $countMerge = getOpenOrderCount($mergerow["shippingaddresspostcode"], $mergerow["sku"], $mergerow["date"], $con_hostinger);
                                                       if ($countMerge > 0) echo '<br><span class="badge badge-danger">Already In - '.$countMerge.'</span>';
                                                       if (($mergerow["tracking_No"] != "" || $mergerow["label_B64"] != "") AND ($mergerow["shippedStatus"] != "true")) echo '<br><span class="badge badge-warning">Not Shipped</span>';
                                                       if (($mergerow["tracking_No"] != "" || $mergerow["label_B64"] != "") AND ($mergerow["trackingStatus"] != "true")) echo '<br><span class="badge badge-secondary">Tracking Failed</span>';
                                                       if ($mergerow["label_B64"] == "") echo '<br><span class="badge badge-info">Label Error</span>'; ?></td>
                                                  <td><b>Sub Flags -</b> <span id="<?php echo $mergerow['id']; ?>subflags"><?php echo $mergerow["subflags"]; ?></span> <br><b>Boxsizes -</b> <span id="<?php echo $mergerow['id']; ?>box_sizes"><?php echo $mergerow["box_sizes"]; ?></span> <br><b>Postal - <span style="color:red;" id="<?php echo $mergerow['id']; ?>postal"><?php echo $mergerow["postal_service"]; ?></span></b><br><b>Qty -</b> <?php echo $mergerow["quantity"]; ?><br><b>Total -</b> <?php echo $mergerow["total"]; ?><br><b>ShippingCost -</b> <?php echo $mergerow["shipping_cost"]; ?><br><b>Weight -</b> <span id="<?php echo $mergerow['id']; ?>weight"><?php echo $mergerow["weight_In_Grams"]; ?></span><br><b>CSV Date -</b> <?php echo $mergerow["csvdate"]; ?></td>
                                                  <td>
                                                       <p style="word-wrap: break-word; 
                                                                 white-space: pre-wrap;" id=<?php echo $mergerow["id"]; ?>><?php echo $mergerow["sku"] . '</p>'; ?> 
                                                       <?php 
                                                            if($mergerow["orgSku"] != "")
                                                            {
                                                                 echo '<span style="color:red; word-wrap: break-word; white-space: pre-wrap;">'.$mergerow["orgSku"].'</span><br>'; 
                                                            }
                                                       ?>
                                                       <?php echo $mergerow["date"] . '<br>' . $mergerow["channel"]; ?>
                                                  </td>
                                                  <td><span id="<?php echo $mergerow['id']; ?>flags"><?php echo $mergerow["flags"]; ?></span></td>
                                                  <td><input type="button" name="edit" value="Edit" id="<?php echo $mergerow["id"]; ?>" class="btn btn-info btn-xs edit_data" /></td>
                                                  <td><input type="button" name="view" value="view" id="<?php echo $mergerow["id"]; ?>" class="btn btn-info btn-xs view_data" /></td>
                                                  <td><input type="button" name="delete" value="delete" id="<?php echo $mergerow["id"]; ?>" class="btn btn-info btn-xs delete_data" /></td>
                                             </tr>
                              <?php
                                        }
                                   }
                              }
                              ?>
                         </table>
                    </form>
               </div>
          </div>
          <?php
          if (!empty($multiple)) {
          ?>
               <div>
                    <table class="table table-dark">
                         <thead>
                              <tr>
                                   <th scope="col">Merge Different Channels Post Code</th>
                              </tr>
                         </thead>
                         <tbody>
                              <?php
                              foreach ($multiple as $value) {
                                   echo '<tr><td>' . $value . '</td></tr>';
                              }
                              ?>
                         </tbody>
                    </table>
               </div>
          <?php
          }
          ?>
          <?php
          if (!empty($multiple2)) {
          ?>
               <div>
                    <table class="table table-dark">
                         <thead>
                              <tr>
                                   <th scope="col">Merge Same Channels Post Code</th>
                              </tr>
                         </thead>
                         <tbody>
                              <?php
                              foreach ($multiple2 as $value) {
                                   echo '<tr><td>' . $value . '</td></tr>';
                              }
                              ?>
                         </tbody>
                    </table>
               </div>
          <?php
          }
          ?>
     </div>
</body>

</html>
<div id="dataModal" class="modal fade">
     <div class="modal-dialog">
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Order Details</h4>
               </div>
               <div class="modal-body" id="employee_detail">
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
          </div>
     </div>
</div>
<div id="addcsv_data_Modal" class="modal fade">
     <div class="modal-dialog">
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Upload CSV</h4>
               </div>
               <div class="modal-body">
                    <form method="post" action="orderupdates.php" id="csv_form" enctype='multipart/form-data'>
                         <label>Date</label>
                         <input type="date" name="csvdate" id="csvdate" class="form-control" required/>
                         <br />
                         <label>Booking</label>
                         <select name="booking" id="booking" class="form-control">
                              <option value="1st Booking">1st Booking</option>
                              <option value="2nd Booking">2nd Booking</option>
                              <option value="New Post">New Post</option>
                         </select>
                      	 <br />
                         <label>CSV Type</label>
                         <select name="csvtype" id="csvtype" class="form-control">
                              <option value="linnworks">Linnworks</option>
                              <option value="zenstores">Zenstores</option>
                              <option value="avasam">Avasam</option>
                              <option value="manomano">Mano Mano</option>
                              <option value="FBA">FBA</option>
                         </select>
                         <br />
                         <label>Upload CSV</label>
                         <input type="file" name="file" id="file" class="form-control" accept=".csv" required/>
                         <br />
                         <input type="submit" name="csvbutton" id="csvbutton" value="Upload" class="btn btn-success" />
                    </form>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
          </div>
     </div>
</div>

<!-- refresh orders modal - start -->
<div id="refreshOrders_Modal" class="modal fade">
     <div class="modal-dialog">
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Choose</h4>
               </div>
               <div class="modal-body">
                    <form method="post" action="labelGeneration/refreshSelroOrders.php" id="refreshSelroOrders_form" target="_blank">
                         <label>Choose Type</label>
                         <select name="refreshSelroOrderstype" id="refreshSelroOrderstype" class="form-control">
                              <option value="all">All</option>
                              <option value="replacement">Replacement Only</option>
                              <option value="withoutReplacement">Without Replacement</option>
                              <option value="primeOnly">Prime Only</option>
                              <option value="firstClassOnly">Firstclass Only</option>
                         </select>
                         <br />
                         <input type="submit" name="refreshSelroOrdersBtn" id="refreshSelroOrdersBtn" value="Get Orders" class="btn btn-success" />
                    </form>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
          </div>
     </div>
</div>
<!-- refresh orders modal - end -->

<!-- get from warehouse transfer modal - start -->
<!-- <div id="getFromWarehouse_Modal" class="modal fade">
     <div class="modal-dialog">
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Choose</h4>
               </div>
               <div class="modal-body">
                    <form method="post" action="labelGeneration/getWarehouseTransfer.php" target="_blank">
                         <label>Choose Warehouse Transfer ID</label>
                         <select name="warehouseTransferId" id="warehouseTransferId" class="form-control">
                              <?php
                              $warehouseIdQuery = "SELECT `id` FROM `stocktransfer` WHERE `stockto` LIKE '%FBA%' ORDER BY `id` DESC;";
                              $warehouseIdResult = mysqli_query($con_hostinger, $warehouseIdQuery);
                              while ($warehouseIdRow = mysqli_fetch_array($warehouseIdResult)){
                                   echo '<option value="'.$warehouseIdRow['id'].'">'.$warehouseIdRow['id'].'</option>';
                              }
                                   
                              ?>
                         </select>
                         <br />
                         <input type="submit" name="getWarehouseTransferDetails" id="getWarehouseTransferDetails" value="Get Details" class="btn btn-success" />
                    </form>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
          </div>
     </div>
</div> -->
<!-- get from warehouse transfer modal - end -->

<!-- update tracking modal - start -->
<div id="updateTracking_Modal" class="modal fade">
     <div class="modal-dialog">
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Upload CSV</h4>
               </div>
               <div class="modal-body">
                    <form method="post" action="updateTracking.php" id="updateTracking_form" enctype='multipart/form-data' target="blank">
                         <label>From</label>
                         <select name="fromUpdate" id="fromUpdate" class="form-control">
                              <option value="fromTable">From Table</option>
                              <option value="fromFile">From File</option>
                         </select>
                         <br />
                         <label>Upload CSV</label>
                         <input type="file" name="updateTrackingfile" id="updateTrackingfile" class="form-control" accept=".csv" />
                         <br />
                         <input type="submit" name="updateTrackingBtn" id="updateTrackingBtn" value="Update" class="btn btn-success" />
                    </form>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
          </div>
     </div>
</div>
<!-- update tracking modal - end -->

<div id="add_data_Modal" class="modal fade">
     <div class="modal-dialog">
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Order Details</h4>
               </div>
               <div class="modal-body">
                    <form method="post" id="insert_form">
                         <label>Enter SKU</label>
                         <input type="text" name="sku" id="sku" class="form-control" />
                         <!-- <input type="checkbox" name="addtomapping" id="addtomapping" class="" style="width: 3%; height: 12px;">
                         <label style="font-weight: 400;">add this sku to auto mapping</label> -->
                         <br />
                         <label>Name</label>
                         <input type="text" name="firstname" id="firstname" class="form-control" />
                      	 <br />
                         <label>Phone</label>
                         <input type="text" name="cusphone" id="cusphone" class="form-control" />
                         <br />
                         <label>Email</label>
                         <input type="text" name="cusemail" id="cusemail" class="form-control" />
                         <br />
                         <label>Shipping Address Company</label>
                         <input type="text" name="shippingaddresscompany" id="shippingaddresscompany" class="form-control" />
                         <br />
                         <label>Shipping address Line1</label>
                         <input type="text" name="shippingaddressline1" id="shippingaddressline1"  class="form-control" Required/> 
                         <br />
                         <label>Shipping address Line2</label>
                         <input type="text" name="shippingaddressline2" id="shippingaddressline2"  class="form-control" />
                         <br />
                         <label>Shipping address Line3</label>
                         <input type="text" name="shippingaddressline3" id="shippingaddressline3"  class="form-control" />
                         <br />
                         <label>Shipping address Region</label>
                         <input type="text" name="shippingaddressregion" id="shippingaddressregion" class="form-control" />
                         <br />
                         <label>Shipping address City</label>
                         <input type="text" name="shippingaddresscity" id="shippingaddresscity" class="form-control" Required/>
                         <br />
                         <label>Shipping Address Country</label>
                         <input type="text" name="shippingaddresscountry" id="shippingaddresscountry" class="form-control" />
                         <br />
                         <label>Shipping address Post Code</label>
                         <input type="text" name="shippingaddresspostcode" id="shippingaddresspostcode" class="form-control" Required/>
                         <br />
                         <label>Weight</label>
                         <input type="text" name="weight" id="weight" class="form-control" Required/>
                      	 <br />
                         <label>Item Height</label>
                         <input type="text" name="ItemHeight" id="ItemHeight" class="form-control"/>
                      	 <br />
                         <label>Item Length</label>
                         <input type="text" name="ItemLength" id="ItemLength" class="form-control"/>
                      	 <br />
                         <label>Item Width</label>
                         <input type="text" name="ItemWidth" id="ItemWidth" class="form-control"/>
                         <br />
                         <label>Notes</label>
                         <input type="text" name="notes" id="notes" class="form-control"/>
                         <br />
                         <label>FBA Merchant SKU</label>
                         <input type="text" name="fbaMerchantSku" id="fbaMerchantSku" class="form-control"/>
                         <br />
                         <label>FBA ASIN</label>
                         <input type="text" name="fbaASIN" id="fbaASIN" class="form-control"/>
                         <br />
                         <input type="hidden" name="order_id" id="order_id" />
                         <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" />
                    </form>
               </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
          </div>
     </div>
</div>

<!-- add flags form start -->
<div class="modal fade" id="modal_form" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Flags</h4>
               </div>
               <form id="flagform" method="post">
                    <div class="modal-body form">
                         <div class="form-body">
                              <div class="form-group">
                                   <label class="control-label col-md-3">Flags</label>
                                   <table class="table">
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='Lampshade'></td>
                                             <td>Lampshade</td>
                                        </tr>
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='Lampshade Shade Only'></td>
                                             <td>Lampshade Shade Only</td>
                                        </tr>
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='Bulbs'></td>
                                             <td>Bulbs</td>
                                        </tr>
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='Lamp Holders'></td>
                                             <td>Lampholders</td>
                                        </tr>
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='cables'></td>
                                             <td>Cables</td>
                                        </tr>
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='Transformer'></td>
                                             <td>Transformer</td>
                                        </tr>
                                        <tr>
                                             <td><input type="checkbox" name="flags[]" value='packing Area'></td>
                                             <td>Packing Area</td>
                                        </tr>
                                        <tr>
                                             <th colspan="2"><input type="text" name='list_check' style="border: 1px solid #505050;" readonly=true></th>
                                        </tr>
                                   </table>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="submit" id="btnSave" class="btn btn-primary">Save</button>
                         <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                    </div>
               </form>

          </div>

     </div>
</div>

<!-- add sub flags form start -->
<div class="modal fade" id="modal_subflagform" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Sub Flags</h4>
               </div>
               <form id="subflagform" method="post">
                    <div class="modal-body form">
                         <div class="form-body">
                              <div class="form-group">
                                   <label class="control-label col-md-3">Sub Flags</label>
                                   <table class="table">
                                        <tr>
                                             <input type="text" name='subflag' calss="form-control">
                                        </tr>
                                        <tr>
                                             <th colspan="2"><input type="text" name='list_check' style="border: 1px solid #505050;" readonly=true></th>
                                        </tr>
                                   </table>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="submit" id="btnSubFlagSave" class="btn btn-primary">Save</button>
                         <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                    </div>
               </form>

          </div>

     </div>
</div>

<!-- generate csv form start -->
<div class="modal fade" id="modal_gencsvform" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Generate CSV</h4>
               </div>
               <form name="gencsvform" id="gencsvform" method="post">
                    <div class="modal-body form">
                         <div class="form-body">
                              <div class="form-group">
                                   <label class="control-label col-md-3">CSV Type</label>
                                   <table class="table">
                                        <tr>
                                             <select class="form-control" name="downloadcsvtype" id="downloadcsvtype">
                                                  <option value="default">Default</option>
                                                  <option value="parcelden2">Parcel Den 2KG</option>
                                                  <option value="parcelden5">Parcel Den 5KG</option>
                                                  <option value="parcel2go">Parcel2Go</option>
                                                  <option value="allTracking">All Tracking</option>
                                                  <option value="tracking">Tracking</option>
                                                  <option value="parcelforce">Parcelforce</option>
                                                  <option value="amazonfbalabel">AMAZON FBA Label</option>
                                             </select>
                                        </tr>
                                        <tr>
                                             <th colspan="2"><input type="text" name='list_check' style="border: 1px solid #505050;" readonly=true></th>
                                        </tr>
                                   </table>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="submit" id="btnCsvGenSave" class="btn btn-primary">Download</button>
                         <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                    </div>
               </form>

          </div>

     </div>
</div>

<!-- add postal service form start -->
<div class="modal fade" id="modal_postalserviceform" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Postal Service</h4>
               </div>
               <form id="postalserviceform" method="post">
                    <div class="modal-body form">
                         <div class="form-body">
                              <div class="form-group">
                                   <label class="control-label col-md-3">Postal Service</label>
                                   <table class="table">
                                        <tr>
                                             <select class="form-control" name="chosepostalservice">
                                               	<option value="245g LL">245g LL</option>
                                                  <option value="900g parcel">900g parcel</option>
                                                  <option value="95g LL">95g LL</option>
                                                  <option value="BPL Royal Mail 1st Class Large Letter">BPL Royal Mail 1st Class Large Letter</option>
                                                  <option value="CRL Royal Mail 24 Large Letter">CRL Royal Mail 24 Large Letter</option>
                                                  <option value="CRL Royal Mail 24 Parcel">CRL Royal Mail 24 Parcel</option>
                                                  <option value="TPN Royal Mail Tracked 24 Non Signature">TPN Royal Mail Tracked 24 Non Signature</option>
                                                  <option value="express24">express24</option>
                                                  <option value="Hermes ParcelShop Postable (Shop To Door) by MyHermes">Hermes ParcelShop Postable (Shop To Door) by MyHermes</option>
                                                  <option value="Return 2kg Hermes">Return 2kg Hermes</option>
                                                  <option value="Etrak - Delivery Group">Etrak - Delivery Group</option>
                                                  <option value="UPS">UPS</option>
                                                  <option value="ParcelDenOnline Standard Package">ParcelDenOnline Standard Package</option>
                                                  <option value="ParcelDenOnline Standard Parcel">ParcelDenOnline Standard Parcel</option>
                                                  <option value="Rm manual">Rm manual</option>

                                                  <option value="AMAZON-LEDsone FBA">AMAZON-LEDsone FBA</option>
                                                  <option value="AMAZON-Dcvoltage FBA">AMAZON-Dcvoltage FBA</option>
                                                  <option value="AMAZON-Vintagelight FBA">AMAZON-Vintagelight FBA</option>
                                                  <option value="AMAZON-SRM FBA">AMAZON-SRM FBA</option>
                                                  <option value="AMAZON-LEDsone DE FBA">AMAZON-LEDsone DE FBA</option>
                                                  <option value="AMAZON-Dcvoltage DE FBA">AMAZON-Dcvoltage DE FBA</option>
                                             </select>
                                        </tr>
                                        <tr>
                                             <th colspan="2"><input type="text" name='list_check' style="border: 1px solid #505050;" readonly=true></th>
                                        </tr>
                                   </table>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="submit" id="btnPostalServiceSave" class="btn btn-primary">Save</button>
                         <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                    </div>
               </form>

          </div>

     </div>
</div>

<!-- move orders to open modal start -->
<div class="modal fade" id="modal_move" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Confirm orders move to Open</h4>
               </div>
               <form id="moveform" method="post">
                    <div class="modal-body form">
                         <div class="form-body">
                              <div class="form-group">
                                   <label class="control-label col-md-3">Please Confirm orders move to Open orders</label>
                                   <input type="hidden" name='idlist'>
                              </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                         <button type="submit" id="btnmove" class="btn btn-primary">Save</button>
                         <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                    </div>
               </form>

          </div>

     </div>
</div>
<div class="modal fade" id="modal_delete" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Confirm</h4>
               </div>
               <div class="modaldeleteform">
                    <form id="deleteform" method="post">
                         <input type="hidden" name='iddelete'>
               </div>
               <div class="modal-footer">
                    <button type="submit" id="btndelete" class="btn btn-primary">Yes</button>
                    <button type="button" class="btn btn-info" data-dismiss="modal">No</button>
               </div>
               </form>

          </div>

     </div>
</div>
<div class="modal fade" id="modal_deleteAll" role="dialog">
     <div class="modal-dialog">

          <!-- Modal content-->
          <div class="modal-content">
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Do you want to delete all ?!</h4>
               </div>
               <div class="modaldeleteallform">
                    <form id="deleteAllform" method="post">
                        <input type="hidden" name='iddeleteall' value='all'>
               </div>
               <div class="modal-footer">
                    <button type="submit" id="btndeleteAll" class="btn btn-primary">Yes</button>
                    <button type="button" class="btn btn-info" data-dismiss="modal">No</button>
               </div>
               </form>

          </div>

     </div>
</div>
<!-- Filter Modal Start-->
<div class="modal fade" id="modal_filter" tabindex="-1" role="dialog" aria-labelledby="Filter data dialog">
     <div class="modal-dialog">
          <div class="modal-content">
               <!-- Modal Header -->
               <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                         <span aria-hidden="true">&times;</span>
                         <span class="sr-only">Close</span>
                    </button>
               </div>

               <!-- Modal Body -->
               <div class="modal-body">
                    <div class="container-fluid">

                         <form id="filter_form" method="post" action="" class="form-inline" role="form">
                              <div class="col">
                                   <div class="form">
                                        <label for="column_name">Flags</label>
                                        <select class="form-control" id="category" name="category" aria-label="category" style="width:100%;">
                                             <option value="Select" <?php if ($category == "") {
                                                                           echo "selected";
                                                                      } ?>>Select All</option>
                                             <option value='packing Area' <?php if ($category == "packing Area") {
                                                                                echo "selected";
                                                                           } ?>> Packing Area </option>
                                             <option value='cables' <?php if ($category == "cables") {
                                                                           echo "selected";
                                                                      } ?>> Cables </option>
                                             <option value='Lamp Holders' <?php if ($category == "Lamp Holders") {
                                                                                echo "selected";
                                                                           } ?>> Lamp Holders </option>
                                             <option value='Bulbs' <?php if ($category == "Bulbs") {
                                                                           echo "selected";
                                                                      } ?>> Bulbs </option>
                                             <option value='Transformer' <?php if ($category == "Transformer") {
                                                                                echo "selected";
                                                                           } ?>> Transformer </option>
                                             <option value='Lampshade' <?php if ($category == "Lampshade") { echo "selected"; } ?>> Lampshade </option>
                                             <option value='Lampshade Shade Only' <?php if ($category == "Lampshade Shade Only") { echo "selected"; } ?>> Lampshade Shade Only </option>
                                             <option value='others' <?php if ($category == "others") { echo "selected"; } ?>> Others </option>
                                        </select>
                                   </div>
                              </div>
                              <div class="col">
                                   <div class="form">
                                        <label for="column_name">Sub Flags</label>
                                        <select class="form-control" id="subcategory" name="subcategory" aria-label="subcategory" style="width:100%;">
                                             <option value="Select" <?php if ($subcategory == "") {
                                                                           echo "selected";
                                                                      } ?>>Select All</option>
                                             <option value="Empty" <?php if ($subcategory == "Empty") {
                                                                           echo "selected";
                                                                      } ?>>Empty</option>
                                             <option value='Not Empty' <?php if ($subcategory == "Not Empty") {
                                                                                echo "selected";
                                                                           } ?>> Not Empty </option>
                                        </select>
                                   </div>
                              </div>
                              <div class="col">
                                   <div class="form">
                                        <label for="column_name">More Filter (It's exclude international, prime, firstclass, Collection order)</label>
                                        <select class="form-control" id="morefilter" name="morefilter" aria-label="morefilter" style="width:100%;">
                                             <option value="Select" <?php if ($morefilter == "") {
                                                                           echo "selected";
                                                                      } ?>>Select All</option>
                                             <option value="International" <?php if ($morefilter == "International") {
                                                                                echo "selected";
                                                                           } ?>>International</option>
                                             <option value="Prime" <?php if ($morefilter == "Prime") {
                                                                           echo "selected";
                                                                      } ?>>Prime</option>
                                             <option value="firstclass" <?php if ($morefilter == "firstclass") {
                                                                                echo "selected";
                                                                           } ?>>First Class</option>
                                             <option value="collection order" <?php if ($morefilter == "collection order") { echo "selected"; } ?>>Collection Order</option>
                                             <option value="fba" <?php if ($morefilter == "fba") { echo "selected"; } ?>>FBA</option>
                                             <option value="others" <?php if ($morefilter == "others") {
                                                                           echo "selected";
                                                                      } ?>>Exclude Above</option>
                                        </select>
                                   </div>
                              </div>
                              <div class="col">
                                   <div class="form">
                                        <label for="column_name">Postal Service</label>
                                        <select class="form-control" id="postalservice" name="postalservice[]" multiple style="width:100%;" size = 19>
                                             <option value="Select" <?php if ($postalservice == "") {
                                                                           echo "selected";
                                                                      } ?>>Select All</option>
                                             <option value="245g LL" <?php if (strpos($postalservice, '245g LL') !== false) {
                                                                                echo "selected";
                                                                           } ?>>245g LL</option>
                                             <option value="900g parcel" <?php if (strpos($postalservice, '900g parcel') !== false) {
                                                                                echo "selected";
                                                                           } ?>>900g parcel</option>
                                             <option value="95g LL" <?php if (strpos($postalservice, '95g LL') !== false) {
                                                                                echo "selected";
                                                                           } ?>>95g LL</option>
                                             <option value="BPL Royal Mail 1st Class Large Letter" <?php if (strpos($postalservice, 'BPL Royal Mail 1st Class Large Letter') !== false) {
                                                                                echo "selected";
                                                                           } ?>>BPL Royal Mail 1st Class Large Letter</option>
                                             <option value="CRL Royal Mail 24 Large Letter" <?php if (strpos($postalservice, 'CRL Royal Mail 24 Large Letter') !== false) {
                                                                                echo "selected";
                                                                           } ?>>CRL Royal Mail 24 Large Letter</option>
                                             <option value="CRL Royal Mail 24 Parcel" <?php if (strpos($postalservice, 'CRL Royal Mail 24 Parcel') !== false) {
                                                                                echo "selected";
                                                                           } ?>>CRL Royal Mail 24 Parcel</option>
                                             <option value="TPN Royal Mail Tracked 24 Non Signature" <?php if (strpos($postalservice, 'TPN Royal Mail Tracked 24 Non Signature') !== false) {
                                                                                echo "selected";
                                                                           } ?>>TPN Royal Mail Tracked 24 Non Signature</option>
                                             <option value="express24" <?php if (strpos($postalservice, 'express24') !== false) {
                                                                                echo "selected";
                                                                           } ?>>express24</option>
                                             <option value="Hermes ParcelShop Postable (Shop To Door) by MyHermes" <?php if (strpos($postalservice, 'Hermes ParcelShop Postable (Shop To Door) by MyHermes') !== false) {
                                                                                echo "selected";
                                                                           } ?>>Hermes ParcelShop Postable (Shop To Door) by MyHermes</option>
                                             <option value="Return 2kg Hermes" <?php if (strpos($postalservice, 'Return 2kg Hermes') !== false) {
                                                                                echo "selected";
                                                                           } ?>>Return 2kg Hermes</option>
                                             <option value="Etrak - Delivery Group" <?php if (strpos($postalservice, 'Etrak - Delivery Group') !== false) {
                                                                                echo "selected";
                                                                           } ?>>Etrak - Delivery Group</option>
                                             <option value="UPS" <?php if (strpos($postalservice, 'UPS') !== false) {
                                                                                echo "selected";
                                                                           } ?>>UPS</option>
                                             <option value="ParcelDenOnline Standard Package" <?php if (strpos($postalservice, 'ParcelDenOnline Standard Package') !== false) {
                                                                                echo "selected";
                                                                           } ?>>ParcelDenOnline Standard Package</option>
                                             <option value="ParcelDenOnline Standard Parcel" <?php if (strpos($postalservice, 'ParcelDenOnline Standard Parcel') !== false) {
                                                                                echo "selected";
                                                                           } ?>>ParcelDenOnline Standard Parcel</option>
                                          	
                                               <!-- mukuntha akka not told this services but in linnworks -->
                                             <option value="Rm manual" <?php if (strpos($postalservice, 'Rm manual') !== false) {
                                                                                echo "selected";
                                                                           } ?>>Rm manual</option>
                                          	<option value="2KG label" <?php if (strpos($postalservice, '2KG label') !== false) {
                                                                                echo "selected";
                                                                           } ?>>2KG label</option>              
                                             <option value="AMAZON-LEDsone FBA" <?php if (strpos($postalservice, 'AMAZON-LEDsone FBA') !== false) {
                                                                                echo "selected";
                                                                           } ?>>AMAZON-LEDsone FBA</option>
                                             <option value="AMAZON-Dcvoltage FBA" <?php if (strpos($postalservice, 'AMAZON-Dcvoltage FBA') !== false) {
                                                                                echo "selected";
                                                                           } ?>>AMAZON-Dcvoltage FBA</option>
                                             <option value="AMAZON-Vintagelight FBA" <?php if (strpos($postalservice, 'AMAZON-Vintagelight FBA') !== false) {
                                                                                echo "selected";
                                                                           } ?>>AMAZON-Vintagelight FBA</option>
                                             <option value="AMAZON-SRM FBA" <?php if (strpos($postalservice, 'AMAZON-SRM FBA') !== false) {
                                                                                echo "selected";
                                                                           } ?>>AMAZON-SRM FBA</option>
                                             <option value="AMAZON-LEDsone DE FBA" <?php if (strpos($postalservice, 'AMAZON-LEDsone DE FBA') !== false) {
                                                                                echo "selected";
                                                                           } ?>>AMAZON-LEDsone DE FBA</option>
                                             <option value="AMAZON-Dcvoltage DE FBA" <?php if (strpos($postalservice, 'AMAZON-Dcvoltage DE FBA') !== false) {
                                                                                echo "selected";
                                                                           } ?>>AMAZON-Dcvoltage DE FBA</option>
                                        </select>
                                   </div>
                              </div>
                              <div class="col">
                                   <div class="form">
                                        <label for="column_name">Sorting (It's working only for lampshades)</label>
                                        <select class="form-control" id="sorting" name="sorting" aria-label="sorting" style="width:100%;">
                                             <option value="ASC" <?php if ($sorting == "ASC") {
                                                                      echo "selected";
                                                                 } ?>>Asc</option>
                                             <option value="DESC" <?php if ($sorting == "DESC") {
                                                                      echo "selected";
                                                                 } ?>>Desc</option>
                                             <option value="replacementSort_dateDESC" <?php if ($sorting == "replacementSort_dateDESC") {
                                                                      echo "selected";
                                                                 } ?>>replacementSort_dateDESC</option>
                                             <option value="newBoxSize_ASC" <?php if ($sorting == "newBoxSize_ASC") {
                                                                      echo "selected";
                                                                 } ?>>newBoxSize_ASC</option>
                                             <option value="newBoxSize_DESC" <?php if ($sorting == "newBoxSize_DESC") {
                                                                      echo "selected";
                                                                 } ?>>newBoxSize_DESC</option>
                                        </select>
                                   </div>
                              </div>
                              <div class="col-md-12" style="padding-top: 2rem;">
                                   <button type="submit" name="btnFilter" id="btnFilter" class="btn btn-primary">Filter</button>
                                   <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                         </form>
                    </div>
               </div>

          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
     </div>
</div>
</div>
<!--Filter Modal End-->
<script>
     $(document).ready(function() {
          // puvii added for refresh status shows evry 5 seconds without refresh
          refreshStatusText();

          function refreshStatusText(){
               $("#wait").css("display", "none");

               $.ajax({
                    url:"getStatusText.php",
                    method:"POST",
                    data:{statusText:"statusText"},
                    dataType:"json",
                    success:function(data){
                         $('#refreshOrdersStatus').html(data.statusText);
                    }
               });

               $("#wait").css("display", "none");
          }

          setInterval(function(){
               refreshStatusText();
          }, 30000);
          // puvii added for refresh status shows evry 5 seconds without refresh

          $('#add').click(function() {
               $('#insert').val("Insert");
               $('#insert_form')[0].reset();
          });
          $(document).on('click', '.edit_data', function() {
               var order_id = $(this).attr("id");
               $.ajax({
                    url: "fetch.php",
                    method: "POST",
                    data: {
                         order_id: order_id
                    },
                    dataType: "json",
                    success: function(data) {
                         $('#sku').val(data.sku);
                         $('#sku').attr("data-sku", data.sku);
                         $('#firstname').val(data.firstname);
                         $('#date').val(data.date);
                         $('#order_id').val(data.id);

                         $('#cusemail').val(data.email);
                         $('#cusphone').val(data.telephone);
                         $('#shippingaddresscompany').val(data.shippingaddresscompany);
                         $('#shippingaddressline1').val(data.shippingaddressline1);
                         $('#shippingaddressline2').val(data.shippingaddressline2);
                         $('#shippingaddressline3').val(data.shippingaddressline3);
                         $('#shippingaddressregion').val(data.shippingaddressregion);
                         $('#shippingaddresscity').val(data.shippingaddresscity);
                         $('#shippingaddresscountry').val(data.shippingaddresscountry);
                         $('#shippingaddresspostcode').val(data.shippingaddresspostcode);
                         
                         $('#ItemHeight').val(data.item_height);
                         $('#ItemLength').val(data.item_length);
                         $('#ItemWidth').val(data.item_width);
                         $('#weight').val(data.weight_In_Grams);
                         $('#notes').val(data.notes);

                         $('#fbaMerchantSku').val(data.FBA_merchantSKU);
                         $('#fbaASIN').val(data.FBA_ASIN);

                         $('#insert').val("Update");
                         $('#add_data_Modal').modal('show');
                    }
               });
          });

          $('#getCountBtn').click(function(event) {
               event.preventDefault();
               
               $.ajax({
                    url: "getParcelCount.php",
                    success: function(data) {
                         alert(data);
                    }
               });
          });

          $(document).on('click', '.delete_data', function() {
               event.preventDefault();
               var order_id = $(this).attr("id");
               $.ajax({
                    url: "delete.php",
                    method: "POST",
                    data: {
                         order_id: order_id
                    },
                    success: function(data) {
                         $('#insert_form')[0].reset();
                         $('#add_data_Modal').modal('hide');
                         $('#employee_table').html(data);
                    }
               });
          });
          $('#insert_form').on("submit", function(event) {
               event.preventDefault();
               if ($('#sku').val() == "") {
                    alert("SKU is required");
               } else if ($('#firstname').val() == '') {
                    alert("Firstname is required");
               } else {
                    var formData = $('#insert_form').serializeArray();
                    formData.push({ name: "oldSku", value: $('#sku').attr("data-sku") });
                    // submitForm(formData);

                    $.ajax({
                         url: "inserttest.php",
                         method: "POST",
                         data: formData,
                         beforeSend: function() {
                              $('#insert').val("Inserting");
                         },
                         success: function(response) {
                              var values = $.parseJSON(response);
                              $('#insert_form')[0].reset();
                              $('#add_data_Modal').modal('hide');
                              var imgid = "#img" + values.id;
                              document.getElementById(values.id).innerHTML = values.sku;
                              $(imgid).attr("src", values.image);
                              
                              if(values.flags != ""){
                                   var flags = "#" + values.id + "flags";
                                   $(flags).html(values.flags);

                                   var subflags = "#" + values.id + "subflags";
                                   $(subflags).html(values.subflags);
                                   // location.reload();
                              }
                         }
                    });
               }
          });
          $(document).on('click', '.view_data', function() {
               var order_id = $(this).attr("id");
               if (order_id != '') {
                    $.ajax({
                         url: "select.php",
                         method: "POST",
                         data: {
                              order_id: order_id
                         },
                         success: function(data) {
                              $('#employee_detail').html(data);
                              $('#dataModal').modal('show');
                         }
                    });
               }
          });
          
          // add selected order ids to add flags modal input
          $('#modal_form').on('show.bs.modal', function(event) {
               var button = $(event.relatedTarget); // Button that triggered the modal
               var recipient = button.data('whatever'); // Extract info from data-* attributes
               // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
               // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
               var vals = $('input:checkbox[name="orders[]"]').map(function() {
                    return this.checked ? this.value : undefined;
               }).get();
               var modal = $(this);
               modal.find('.modal-title').text("Select the Flags which you want to assign"); // just for fun.
               modal.find(".modal-body input[name='list_check']").val(vals);
          });

          // add selected order ids to add sub flags modal input
          $('#modal_subflagform').on('show.bs.modal', function(event) {
               var button = $(event.relatedTarget); // Button that triggered the modal
               var recipient = button.data('whatever'); // Extract info from data-* attributes
               // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
               // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
               var vals = $('input:checkbox[name="orders[]"]').map(function() {
                    return this.checked ? this.value : undefined;
               }).get();
               var modal = $(this);
               modal.find('.modal-title').text("Select the Sub Flags which you want to assign"); // just for fun.
               modal.find(".modal-body input[name='list_check']").val(vals);
          });

          // add selected order ids to add csv generate modal input
          $('#modal_gencsvform').on('show.bs.modal', function(event) {
               var button = $(event.relatedTarget); // Button that triggered the modal
               var recipient = button.data('whatever'); // Extract info from data-* attributes
               // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
               // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
               var vals = $('input:checkbox[name="orders[]"]').map(function() {
                    return this.checked ? this.value : undefined;
               }).get();
               var modal = $(this);
               modal.find('.modal-title').text("Select the csv type which you want to download"); // just for fun.
               modal.find(".modal-body input[name='list_check']").val(vals);
          });

          // add selected order ids to add postal service modal input
          $('#modal_postalserviceform').on('show.bs.modal', function(event) {
               var button = $(event.relatedTarget); // Button that triggered the modal
               var recipient = button.data('whatever'); // Extract info from data-* attributes
               // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
               // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
               var vals = $('input:checkbox[name="orders[]"]').map(function() {
                    return this.checked ? this.value : undefined;
               }).get();
               var modal = $(this);
               modal.find('.modal-title').text("Select the Postal Service which you want to assign"); // just for fun.
               modal.find(".modal-body input[name='list_check']").val(vals);
          });

          $("#flagform").on("submit", function(event) {
               event.preventDefault();
               $.ajax({
                    url: "inserttest.php",
                    method: "POST",
                    data: $('#flagform').serialize(),
                    success: function(data) {
                         $('#flagform')[0].reset();
                         $('#modal_form').modal('hide');
                         // $('#employee_table').html(data);

                         $('#frmOrders')[0].reset();
                         var values = $.parseJSON(data);

                         values.forEach(value => {
                              var flags = "#" + value.id + "flags";
                              $(flags).html(value.flags);

                              var subflags = "#" + value.id + "subflags";
                              $(subflags).html(value.subflags);
                         });
                    }
               });
          });

          $("#subflagform").on("submit", function(event) {
               event.preventDefault();
               $.ajax({
                    url: "inserttest.php",
                    method: "POST",
                    data: $('#subflagform').serialize(),
                    success: function(data) {
                         $('#subflagform')[0].reset();
                         $('#modal_subflagform').modal('hide');
                         // $('#employee_table').html(data);

                         $('#frmOrders')[0].reset();
                         var values = $.parseJSON(data);

                         values.forEach(value => {
                              var postal = "#" + value.id + "subflags";
                              $(postal).html(value.subflag);
                         });
                    }
               });
          });

          $("#gencsvform").on("submit", function(event) {
               event.preventDefault();

               // $.ajax({
               //      url: 'downloadCSV.php',
               //      method: "POST",
               //      data: $('#gencsvform').serialize(),
               //      success: function() {
               //           $('#gencsvform')[0].reset();
               //           $('#modal_gencsvform').modal('hide');
               //      }
               // });
               if (confirm("Are you sure want to generate csv for this products")) {
                    document.gencsvform.action = "downloadCSV.php";
                    document.gencsvform.submit();
               }
               
               $('#modal_gencsvform').modal('hide');
          });

          $("#postalserviceform").on("submit", function(event) {
               event.preventDefault();
               $.ajax({
                    url: "inserttest.php",
                    method: "POST",
                    data: $('#postalserviceform').serialize(),
                    success: function(data) {
                         $('#postalserviceform')[0].reset();
                         $('#modal_postalserviceform').modal('hide');
                         // $('#employee_table').html(data);

                         $('#frmOrders')[0].reset();
                         var values = $.parseJSON(data);

                         values.forEach(value => {
                              var postal = "#" + value.id + "postal";
                              $(postal).html(value.postal);

                              var weight = "#" + value.id + "weight";
                              $(weight).html(value.weight);
                         });
                    }
               });
          });

          $('#modal_move').on('show.bs.modal', function(event) {
               var button = $(event.relatedTarget); // Button that triggered the modal
               var recipient = button.data('whatever'); // Extract info from data-* attributes
               // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
               // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
               var vals = $('input:checkbox[name="orders[]"]').map(function() {
                    return this.checked ? this.value : undefined;
               }).get();
               var modal = $(this);
               modal.find('.modal-title').text("Confirm"); // just for fun.
               modal.find(".modal-body input[name='idlist']").val(vals);
          });
          $('#modal_delete').on('show.bs.modal', function(event) {
               var button = $(event.relatedTarget); // Button that triggered the modal
               var recipient = button.data('whatever'); // Extract info from data-* attributes
               // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
               // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
               var vals = $('input:checkbox[name="orders[]"]').map(function() {
                    return this.checked ? this.value : undefined;
               }).get();
               var modal = $(this);
               modal.find('.modal-title').text("Confirm"); // just for fun.
               modal.find(".modaldeleteform input[name='iddelete']").val(vals);
          });

          $("#moveform").on("submit", function(event) {
               event.preventDefault();
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#moveform').serialize(),
                    success: function(data) {
                         $('#moveform')[0].reset();
                         $('#modal_move').modal('hide');
                         $('#employee_table').html(data);
                    }
               });
          })
          $("#deleteform").on("submit", function(event) {
               event.preventDefault();
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#deleteform').serialize(),
                    success: function(data) {
                         $('#deleteform')[0].reset();
                         $('#modal_delete').modal('hide');
                         $('#employee_table').html(data);
                    }
               });
          })
          $("#deleteAllform").on("submit", function(event) {
               event.preventDefault();
               $.ajax({
                    url: "insert.php",
                    method: "POST",
                    data: $('#deleteAllform').serialize(),
                    success: function(data) {
                         $('#deleteAllform')[0].reset();
                         $('#modal_deleteAll').modal('hide');
                         $('#employee_table').html(data);
                    }
               });
          })
          $('body').on('click', '#pendingselectAll', function() {
               if ($(this).hasClass('allChecked')) {
                    $('input[type="checkbox"]', '#pendingts').prop('checked', false);
               } else {
                    $('input[type="checkbox"]', '#pendingts').prop('checked', true);
               }
               $(this).toggleClass('allChecked');
          })

          $(document).ajaxStart(function() {
               $("#wait").css("display", "block");
          });
          $(document).ajaxComplete(function() {
               $("#wait").css("display", "none");
          });
     });

     $(document).on('click', '#packlist', function() {
          if (confirm("Are you sure want to create packlist for this products")) {
               document.frmOrders.action = "packlist.php";
               document.frmOrders.submit();
          }
     });

     $(document).on('click', '#shippingLabel', function() {
          if (confirm("Are you sure want to create label for this products")) {
               document.frmOrders.action = "labelGeneration/index.php";
               document.frmOrders.submit();
          }
     });

     $("#markShippedBtn").on("click", function(event) {
          Object.assign(document.createElement("a"), {
               target: "_blank",
               href: "markAsShipped.php"
          }).click();
     });

     $("#autoMergeBtn").on("click", function(event) {
          Object.assign(document.createElement("a"), {
               target: "_blank",
               href: "autoMerging.php"
          }).click();
     });
     
     // $("#refreshOrders").on("click", function(event) {
     //      // Object.assign(document.createElement("a"), {
     //      //      target: "_blank",
     //      //      href: "http://digitweb.vintageinterior.co.uk/demo%20final/labelGeneration/refreshOrders.php"
     //      // }).click();
     //      event.preventDefault();
     //      $.ajax({
     //           url: "labelGeneration/refreshOrders.php",
     //           method: "POST",
     //           data: {
     //                "type" : "refreshOrders"
     //           },
     //           success: function(data) {
     //                alert(data);
     //                if(data == "order added successfully"){
     //                     location.reload();
     //                }
     //           }
     //      });
     // });

     // $("#updateTracking").on("click", function(event) {
     //      event.preventDefault();
     //      $.ajax({
     //           url: "updateTracking.php",
     //           method: "POST",
     //           data: {
     //                "type" : "updateTracking"
     //           },
     //           success: function(data) {
     //                alert(data);
     //           }
     //      });
     // });
</script>