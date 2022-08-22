<?php
// include "functions.php";
include "labelGeneration/functionsToCreateLabels.php";

session_start();
$con_hostinger = $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

//$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");
// $con_hostinger = mysqli_connect("localhost","root","","u525933064_dashboard");
$insertflag = "false";
$postalservice = "";
if (!empty($_POST)) {
    $output = '';
    $message = '';
    if (isset($_POST["order_id"])){
        $sku = mysqli_real_escape_string($connect, $_POST["sku"]);
        $channel = mysqli_real_escape_string($connect, $_POST["channel"]);

        $query = "
        UPDATE temporders
        SET sku='$sku',
        channel='$channel'
        WHERE id='" . $_POST["order_id"] . "'";
        $message = 'Data Updated';
    } 
    // elseif (isset($_POST["list_check"])  and isset($_POST["subflag"])) {
    //     $subflag = $_POST["subflag"];
    //     $orderids = explode(',', $_POST["list_check"]);
    //     foreach ($orderids as $value) {
    //         $query = "UPDATE temporders SET subflags='$subflag' WHERE id='" . $value . "'";
    //         mysqli_query($connect, $query);
    //     }
    //     $message = 'Sub Flags Updated';
    //     $insertflag = "true";
    // } 
    // puvii commented ths
    // elseif (isset($_POST["list_check"])  and isset($_POST["chosepostalservice"])) {
    //     $postalservice = $_POST["chosepostalservice"];
    //     $orderids = explode(',', $_POST["list_check"]);

    //     $weight = getWeightByshippingService($postalservice);

    //     foreach ($orderids as $value) {
    //         $query = "UPDATE temporders SET postal_service='$postalservice', weight_In_Grams='$weight' WHERE id='" . $value . "'";
    //         mysqli_query($connect, $query);
    //     }
    //     $message = 'Postal Service Updated';
    //     $insertflag = "true";
    // } 
    // elseif (isset($_POST["list_check"])) {
    //     $orderflags = implode(', ', $_POST["flags"]);
    //     $orderids = explode(',', $_POST["list_check"]);
    //     foreach ($orderids as $value) {
    //         $querySelect = "SELECT * FROM `temporders` WHERE id='" . $value . "'";
    //         $resultSelect = mysqli_query($connect, $querySelect);
    //         while ($row = mysqli_fetch_assoc($resultSelect)) {
    //             $sku = $row["sku"];
    //             $mergeStatus = $row["merge"];
    //             $orderNumID = $row["orderID"];
    //         }

    //         $subflags = getSubFlags($sku, $orderflags, $connect);

    //         if($subflags == '' and $mergeStatus=="Merged"){
    //             // $querySelectMergedOrders = "SELECT * FROM `temporders` WHERE `merge` LIKE '%" . $orderNumID . "%'";
    //             // $resultSelectMergedOrders = mysqli_query($connect, $querySelectMergedOrders);
    //             // $notEmptySubFlags = "NULL";
    //             // while ($row = mysqli_fetch_assoc($resultSelectMergedOrders)) {
    //             //     if($row["subflags"] != ""){
    //             //         $notEmptySubFlags = $row["subflags"];
    //             //         break 1;
    //             //     }
    //             // }

    //             // if($notEmptySubFlags != "NULL"){
    //             //     $subflags = $notEmptySubFlags;
    //             // }

    //             $querySelectMergedOrders = "SELECT * FROM `temporders` WHERE `merge` LIKE '%" . $orderNumID . "%'";
    //             $resultSelectMergedOrders = mysqli_query($connect, $querySelectMergedOrders);
    //             $combineSKUs = "";
    //             while ($rowMergedOrders = mysqli_fetch_assoc($resultSelectMergedOrders)) {
    //                 $combineSKUs .= $rowMergedOrders["sku"];
    //             }

    //             $subflags = getSubFlags($combineSKUs, $orderflags, $connect);
    //         }

    //         // added zmerged
    //         if($mergeStatus!=""){
    //             $subflags = 'zmerged';
    //         }

    //         $query = "
    //            UPDATE temporders
    //            SET flags='$orderflags', subflags='$subflags'
    //            WHERE id='" . $value . "'";
    //         mysqli_query($connect, $query);
    //     }
    //     $message = 'Flags Updated';
    //     $insertflag = "true";
    // } 
    elseif (isset($_POST["idlist"])) {
        $ids = explode(',', $_POST["idlist"]);
        foreach ($ids as $value) {
            $result = mysqli_query($connect, "SELECT * FROM temporders WHERE id='" . $value . "'");
            $row = mysqli_fetch_array($result);
            $status = "Pending";
            $phpdate = strtotime($row['date']);
            $date = date('Y-m-d', $phpdate);
            $flags = $row['flags'];
            if ($row['merge'] == "Merged") {
                $flags = $row['flags'] . ", Merged";
            }

            $firstname = $row['firstname'];
            $firstname = str_replace("\'", "'", $firstname);
            $firstname = str_replace("'", "\'", $firstname);

            $productName = $row['name'];
            $productName = str_replace("\'", "'", $productName);
            $productName = str_replace("'", "\'", $productName);

            $shippingaddressline1 = $row['shippingaddressline1'];
            $shippingaddressline1 = str_replace("\'", "'", $shippingaddressline1);
            $shippingaddressline1 = str_replace("'", "\'", $shippingaddressline1);

            $shippingaddressline2 = $row['shippingaddressline2'];
            $shippingaddressline2 = str_replace("\'", "'", $shippingaddressline2);
            $shippingaddressline2 = str_replace("'", "\'", $shippingaddressline2);

            $shippingaddressline3 = $row['shippingaddressline3'];
            $shippingaddressline3 = str_replace("\'", "'", $shippingaddressline3);
            $shippingaddressline3 = str_replace("'", "\'", $shippingaddressline3);

            $shippingaddressregion = $row['shippingaddressregion'];
            $shippingaddressregion = str_replace("\'", "'", $shippingaddressregion);
            $shippingaddressregion = str_replace("'", "\'", $shippingaddressregion);

            $shippingaddresscity = $row['shippingaddresscity'];
            $shippingaddresscity = str_replace("\'", "'", $shippingaddresscity);
            $shippingaddresscity = str_replace("'", "\'", $shippingaddresscity);

            $shippingaddresspostcode = $row['shippingaddresspostcode'];
            $shippingaddresspostcode = str_replace("\'", "'", $shippingaddresspostcode);
            $shippingaddresspostcode = str_replace("'", "\'", $shippingaddresspostcode);

            $notes = $row['notes'];
            $notes = str_replace("\'", "'", $notes);
            $notes = str_replace("'", "\'", $notes);

            $find_query = 'SELECT count(id) as counting FROM `orders` WHERE status = "'.$status.'" AND orderID = "'.$row['orderID'].'" AND shippingaddresspostcode = "'.$shippingaddresspostcode.'" AND sku = "'.$row['sku'].'" AND firstname = "'.$firstname.'" AND quantity = "'.$row['quantity'].'"';
            
            $find_result = mysqli_query($connect, $find_query);

            $find_row = mysqli_fetch_array($find_result);
            $ordersCount = $find_row['counting'];

            if($ordersCount == 0){
                // $sql = "INSERT into orders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, booking, csvdate, unit, PostalService, notes) values ('" . $row['orderID'] . "','" . $status . "','" . $date . "','" . $row['channel'] . "','" . $firstname . "','" . $row['email'] . "','" . $row['currency'] . "','" . $row['ordertotal'] . "','" . $productName . "','" . $row['sku'] . "','" . $row['quantity'] . "','" . $flags . "','" . $row['shippingservice'] . "','" . $shippingaddressline1 . "','" . $shippingaddressline2 . "','" . $shippingaddressline3 . "','" . $shippingaddressregion . "','" . $shippingaddresscity . "','" . $shippingaddresspostcode . "','" . $row['shippingaddresscountry'] . "','" . $row['shippingaddresscountrycode'] . "','" . $row['booking'] . "','" . $row['csvdate'] . "','" . $row['unit'] . "','" . $row['postal_service'] . "','" . $notes . "')";

                $sql = "INSERT into orders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, `FBA_merchantSKU`, `FBA_ASIN`, quantity, flags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, booking, csvdate, unit, PostalService, notes) values ('" . $row['orderID'] . "','" . $status . "','" . $date . "','" . $row['channel'] . "','" . $firstname . "','" . $row['email'] . "','" . $row['currency'] . "','" . $row['ordertotal'] . "','" . $productName . "','" . $row['sku'] . "','" . trim($row['FBA_merchantSKU']) . "','" . trim($row['FBA_ASIN']) . "','" . $row['quantity'] . "','" . $flags . "','" . $row['shippingservice'] . "','" . $shippingaddressline1 . "','" . $shippingaddressline2 . "','" . $shippingaddressline3 . "','" . $shippingaddressregion . "','" . $shippingaddresscity . "','" . $shippingaddresspostcode . "','" . $row['shippingaddresscountry'] . "','" . $row['shippingaddresscountrycode'] . "','" . $row['booking'] . "','" . $row['csvdate'] . "','" . $row['unit'] . "','" . $row['postal_service'] . "','" . $notes . "')";

                //$sql = "INSERT into orders (orderID, status, date)
                // values ('". $row['orderID'] ."','". $status ."','". $row['date'] ."')";
                // $deletesql = "DELETE FROM temporders WHERE id = '".$value."'";
                mysqli_query($connect, $sql);
                //mysqli_query($connect, $deletesql);
            }
        }
        $message = 'Orders Moved to Open';
        $insertflag = "true";
?>

        <script type="text/javascript">
            window.open("http://dashboard.digitweblk.com/orderscsvtest.php")
        </script>
<?php
    } elseif ($_POST["iddelete"] != '') {
        $ids = explode(',', $_POST["iddelete"]);
        foreach ($ids as $value) {
            $deletesql = "DELETE FROM temporders WHERE id = '" . $value . "'";
            mysqli_query($connect, $deletesql);
        }
        $message = 'Orders Deleted';
        $insertflag = "true";
    } elseif(isset($_POST["iddeleteall"])){
        $deletesql = "DELETE FROM temporders";
        mysqli_query($connect, $deletesql);

        $message = 'All Orders Deleted';
        $insertflag = "true";
    }
    // else {
    //     $query = "
    //        INSERT INTO temporders(sku, channel)
    //        VALUES('$sku', '$channel');
    //        ";
    //     $message = 'Data Inserted' . $_POST["order_id"];
    // }

    if (($insertflag == "true") or (mysqli_query($connect, $query))) {
        $query = "SELECT * FROM temporders WHERE 1";

        $category = "";
        $subcategory = "";
        $morefilter = "";
        $sorting = "";

        if (isset($_POST["category"])) {
            $_SESSION['category'] = $_POST["category"];
            if ($_POST["category"] == "Select") {
                unset($_SESSION['category']);
            }
        }

        if (isset($_POST["subcategory"])) {
            $_SESSION['subcategory'] = $_POST["subcategory"];
            if ($_POST["subcategory"] == "Select") {
                unset($_SESSION['subcategory']);
            }
        }

        if (isset($_POST["morefilter"])) {
            $_SESSION['morefilter'] = $_POST["morefilter"];
            if ($_POST["morefilter"] == "Select") {
                unset($_SESSION['morefilter']);
            }
        }

        if (isset($_POST["postalservice"])) {
            $_SESSION['postalservice'] = $_POST["postalservice"];
            if ($_POST["postalservice"] == "Select") {
                 unset($_SESSION['postalservice']);
            }
        }

        $_SESSION['sorting'] = "ASC";

        if (isset($_POST["sorting"])) {
            $_SESSION['sorting'] = $_POST["sorting"];
        }

        if (isset($_SESSION['category'])) {
            $category = $_SESSION['category'];
            $query .= " AND flags='$category' ";
        }


        if (isset($_SESSION['subcategory'])) {
            $subcategory = $_SESSION['subcategory'];
            if ($subcategory == "Empty") {
                $query .= " AND subflags=''";
            } else {
                $query .= " AND subflags!=''";
            }
        }

        if (isset($_SESSION['morefilter'])) {
            $morefilter = $_SESSION['morefilter'];
       
            if ($morefilter == "others") {
                 $query .= " AND shippingservice != 'International' AND shippingservice != 'Prime' AND shippingservice != 'firstclass'";
            } else{
                 $query .= " AND shippingservice = '".$morefilter."'";
            }
        }

        if (isset($_SESSION['postalservice'])) {
            $postalservice = $_SESSION['postalservice'];
       
            if ($postalservice == "parcel den") {
                 $query .= " AND (postal_service LIKE '%parcel den%' OR postal_service LIKE '%Parcelden%')";
            }
        }

        if (isset($_SESSION['sorting'])) {
            $sorting = $_SESSION['sorting'];
        }

        if ($category == "Lampshade Shade Only" or $category == "Lampshade" or $category == "Transformer" or $category == "Bulbs") {
            $query .= " ORDER BY subflags " . $sorting . ", total ASC, date ASC";
        } else {
            $query .= " ORDER BY total, date ASC";
        }

        $result = mysqli_query($connect, $query);
        
        if($category == ""){
            $categoryText = "All";
        }else{
            $categoryText = $category;
        }
        
        $orderEmptyCount = getEmptyOrdersCount($category, "empty", $morefilter, $postalservice, $connect);
        $orderNotEmptyCount = getEmptyOrdersCount($category, "notempty", $morefilter, $postalservice, $connect);

        echo "<p style='text-align:center;'><strong>Empty Subflags Orders - " . $orderEmptyCount . ", Non-Empty Subflags Orders - " . $orderNotEmptyCount . "</strong> For ".$categoryText." category</p>";

        if ($category == "Lampshade Shade Only" or $category == "Lampshade" or $category == "Transformer" or $category == "Bulbs") {
            echo "<p style='text-align:center;'><strong>Orders sorted subflags by " . $_SESSION['sorting'] . "</strong></p>";
        }

        if (isset($_SESSION['postalservice'])) {
            echo "<p style='text-align:center;'>Postal Service like <strong>" . $_SESSION['postalservice'] . "</strong> Orders Only</p>";
        }
        if (isset($_SESSION['morefilter'])) {
            $output .= "<p style='text-align:center;'><strong>Orders more filter is enabled.</strong></p>";
        }
        if (isset($_SESSION['category'])) {
            $output .= "<p style='text-align:center;'>Flags like <strong>" . $_SESSION['category'] . "</strong> Orders Only</p>";
        }
        if (isset($_SESSION['subcategory'])) {
            $output .= "<p style='text-align:center;'>Sub Flags like <strong>" . $_SESSION['subcategory'] . "</strong> Orders Only</p>";
        }
        if (isset($_SESSION['category']) or isset($_SESSION['subcategory'])) {
            $output .= "<p style='text-align:center; color:red;'><i>( Please select Select All in filter to see all orders )</i></p>";
        }

        $output .= '<label class="text-success">' . $message . '</label>';
        
        $output .= '
                <form name="frmOrders" id="frmOrders" method="post" target="_blank" action="">
                <table class="table table-bordered" id="pendingts" style="table-layout: fixed;">
                    <tr>
                        <th width="6%"><button type="button" id="pendingselectAll" class="main">
                        <span class="sub"></span> Select All </button></th>
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
           ';
        while ($row = mysqli_fetch_array($result)) {
            if ($row["merge"] != "" && $row["merge"] != "Merged") {
                continue;
            }
            $ordersku = $row["sku"];
            
            $mainimageresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "'");
            $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
            $mainimageorder = "";
            $count = mysqli_num_rows($mainimageresult);
            if($count>0)
            {
                $mainimageorder = $mainimagerow['comboproducts']['image'];
            }
            if (empty($mainimageorder) && (strpos($ordersku, '+') === false)) {
                $mainimageresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
                $count = mysqli_num_rows($mainimageresult);
                if($count>0)
                {
                    $mainimageorder = $mainimagerow['comboproducts']['Mainimage'];
                }
            }

            $clientname = "Name : " . $row["firstname"];
            $address = "";
            if (!empty($clientname)) {
                $address = $address . $clientname . "<br>";
            }
            if (!empty($row["shippingaddresscompany"])) {
                $address = $address . $row["shippingaddresscompany"] . "<br>";
            }
            if (!empty($row["shippingaddressline1"])) {
                $address = $address . $row["shippingaddressline1"] . "<br>";
            }
            if (!empty($row["shippingaddressline2"])) {
                $address = $address . $row["shippingaddressline2"] . "<br>";
            }
            if (!empty($row["shippingaddressline3"])) {
                $address = $address . $row["shippingaddressline3"] . "<br>";
            }
            if (!empty($row["shippingaddressregion"])) {
                $address = $address . $row["shippingaddressregion"] . "<br>";
            }
            if (!empty($row["shippingaddresscity"])) {
                $address = $address . $row["shippingaddresscity"] . "<br>";
            }
            if (!empty($row["shippingaddresspostcode"])) {
                $address = $address . $row["shippingaddresspostcode"] . "<br>";
            }
            if (!empty($row["shippingaddresscountry"])) {
                $address = $address . $row["shippingaddresscountry"] . "<br>";
            }
            if ($row["merge"] == "Merged") {
                $mergeid = $row["date"] . "-" . $row["orderID"];
                $mergequery = "SELECT * FROM temporders WHERE merge='" . $mergeid . "'";
                $mergeresult = mysqli_query($connect, $mergequery);
                $row_cnt = mysqli_num_rows($mergeresult);
                $rowspanno = ($row_cnt + 1);
            } else {
                $rowspanno = 1;
            }
            $output .= '
                     <tr>
                         <td style="text-align:center"><input type="checkbox" name="orders[]" value=' . $row["id"] . '></td>
                         <td><img style="width:100px; height:auto;" src=' . $mainimageorder . '></td>
                         <td>' . $row["orderID"];
                         if ($row["merge"] == "Merged") {
                            $output .= '<br>Merge';
                        }if (getOpenOrderCount($row["shippingaddresspostcode"], $row["sku"], $row["date"], $connect) > 0) {
                            $output .= '<br><span class="badge badge-danger">Already In</span>';
                        }if (($row["tracking_No"] != "" || $row["label_B64"] != "") AND ($row["shippedStatus"] != "true")) {
                            $output .= '<br><span class="badge badge-warning">Not Shipped</span>';
                        }if (($row["tracking_No"] != "" || $row["label_B64"] != "") AND ($row["trackingStatus"] != "true")) {
                            $output .= '<br><span class="badge badge-secondary">Tracking Failed</span>';
                        }if ($row["label_B64"] == "") {
                            $output .= '<br><span class="badge badge-info">Label Error</span>';
                        }
            $output .= '
                         </td>
                            <td><b>Sub Flags -</b> ' . $row["subflags"] . '<br><b>Postal -<span style="color:red;">'. $row["postal_service"] . '</span></b><br><b>Qty -</b> '. $row["quantity"]  . '<br><b>Total -</b> '. $row["total"] . '<br><b>ShippingCost -</b> '. $row["shipping_cost"] . '<br><b>Weight -</b> '. $row["weight_In_Grams"] .'</td>
                            <td><p style="word-wrap: break-word; white-space: pre-wrap;" id=' . $row["id"] . '>'.$row["sku"] . '</p>' . $row["date"] . '<br>' . $row["channel"] . '</td>
                                    <td rowspan=' . $rowspanno . '>' . $address . '</td>
                                    <td>' . $row["flags"] . '</td>
                            <td><input type="button" name="edit" value="Edit" id="' . $row["id"] . '" class="btn btn-info btn-xs edit_data" /></td>
                            <td><input type="button" name="view" value="view" id="' . $row["id"] . '" class="btn btn-info btn-xs view_data" /></td>
                            <td><input type="button" name="delete" value="delete" id="' . $row["id"] . '" class="btn btn-info btn-xs delete_data" /></td>
                     </tr>
                ';
            if ($row["merge"] == "Merged") {
                while ($mergerow = mysqli_fetch_array($mergeresult)) {
                    $ordersku = $mergerow["sku"];

                    $mainimageresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
                    $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
                    $count = mysqli_num_rows($mainimageresult);
                    if($count>0)
                    {
                        $mainimageorder = $mainimagerow['comboproducts']['image'];
                    }
                    if (empty($mainimageorder) && (strpos($ordersku, '+') === false)) {
                        $mainimageresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                        $mainimagerow["comboproducts"] = mysqli_fetch_array($mainimageresult);
                        $count = mysqli_num_rows($mainimageresult);
                        if($count>0)
                        {
                            $mainimageorder = $mainimagerow['comboproducts']['Mainimage'];
                        }
                    }

                    $output .= '
                               <tr>
                                   <td style="text-align:center"><input type="checkbox" name="orders[]" value=' . $mergerow["id"] . '></td>
                                   <td><img style="width:100px; height:auto;" src=' . $mainimageorder . '></td>
                                    <td>' . $mergerow["orderID"];
                                    if ($mergerow["merge"] == "Merged") {
                                        $output .= '<br>Merge';
                                    }if (getOpenOrderCount($mergerow["shippingaddresspostcode"], $mergerow["sku"], $mergerow["date"], $connect) > 0) {
                                        $output .= '<br><span class="badge badge-danger">Already In</span>';
                                    }if (($mergerow["tracking_No"] != "" || $mergerow["label_B64"] != "") AND ($mergerow["shippedStatus"] != "true")) {
                                        $output .= '<br><span class="badge badge-warning">Not Shipped</span>';
                                    }if (($mergerow["tracking_No"] != "" || $mergerow["label_B64"] != "") AND ($mergerow["trackingStatus"] != "true")) {
                                        $output .= '<br><span class="badge badge-secondary">Tracking Failed</span>';
                                    }if ($mergerow["label_B64"] == "") {
                                        $output .= '<br><span class="badge badge-info">Label Error</span>';
                                    }
                                    $output .= '</td>
                                    <td><b>Sub Flags -</b> ' . $mergerow["subflags"] . '<br><span style="color:red;">Postal -</b> '. $mergerow["postal_service"] . '</span></b><br><b>Qty -</b> '. $mergerow["quantity"] . '<br><b>Total -</b> '. $mergerow["total"] . '<br><b>ShippingCost -</b> '. $mergerow["shipping_cost"] . '<br><b>Weight -</b> '. $mergerow["weight_In_Grams"] . '</td>
                                    <td>' . $mergerow["sku"] . '<br>' . $mergerow["date"] . '<br>' . $mergerow["channel"] . '</td>
                                    <td>' . $mergerow["flags"] . '</td>
                                    <td><input type="button" name="edit" value="Edit" id="' . $mergerow["id"] . '" class="btn btn-info btn-xs edit_data" /></td>
                                    <td><input type="button" name="view" value="view" id="' . $mergerow["id"] . '" class="btn btn-info btn-xs view_data" /></td>
                                    <td><input type="button" name="delete" value="delete" id="' . $mergerow["id"] . '" class="btn btn-info btn-xs delete_data" /></td>
                               </tr>';
                }
            }
        }
        $output .= '</table></form>';
    }
    echo $output;
}
?>