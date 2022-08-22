<?php 
    include "labelGeneration/functionsToCreateLabels.php";

    $con_hostinger = $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

    //$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");

    if (isset($_POST["order_id"]) && isset($_POST["sku"])) {
        $skulogquery = "";
        $flags = "";
        $subflags = "";

        $sku = $_POST["sku"];
        $firstname = mysqli_real_escape_string($connect, $_POST["firstname"]);
        $cusemail = mysqli_real_escape_string($connect, $_POST["cusemail"]);
      	$cusphone = mysqli_real_escape_string($connect, $_POST["cusphone"]);
        $shippingaddresscompany = mysqli_real_escape_string($connect, $_POST["shippingaddresscompany"]);
        $shippingaddressline1 = mysqli_real_escape_string($connect, $_POST["shippingaddressline1"]);
        $shippingaddressline2 = mysqli_real_escape_string($connect, $_POST["shippingaddressline2"]);
        $shippingaddressline3 = mysqli_real_escape_string($connect, $_POST["shippingaddressline3"]);
        $shippingaddressregion = mysqli_real_escape_string($connect, $_POST["shippingaddressregion"]);
        $shippingaddresscity = mysqli_real_escape_string($connect, $_POST["shippingaddresscity"]);
        $shippingaddresscountry = mysqli_real_escape_string($connect, $_POST["shippingaddresscountry"]);
        $shippingaddresspostcode = mysqli_real_escape_string($connect, $_POST["shippingaddresspostcode"]);
        $ItemHeight = mysqli_real_escape_string($connect, $_POST["ItemHeight"]);
        $ItemLength = mysqli_real_escape_string($connect, $_POST["ItemLength"]);
        $ItemWidth = mysqli_real_escape_string($connect, $_POST["ItemWidth"]);
        $weight = mysqli_real_escape_string($connect, $_POST["weight"]);
        $notes = mysqli_real_escape_string($connect, $_POST["notes"]);

        $fbaMerchantSku = trim(mysqli_real_escape_string($connect, $_POST["fbaMerchantSku"]));
        $fbaASIN = trim(mysqli_real_escape_string($connect, $_POST["fbaASIN"]));
        
        $mainimageorder = "";
        $mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='".$sku."' OR originalsku='".$sku."'");
        $mainimagerow= mysqli_fetch_array($mainimageresult);
        if(mysqli_num_rows($mainimageresult)>0){
            $mainimageorder=$mainimagerow['image'];
        }
        
        if (empty(trim($mainimageorder)) && (strpos($sku, '+') === false)) {
            $mainimageresultt = mysqli_query($con_hostinger, "SELECT * FROM products WHERE SKU='" . $sku . "'");

            $mainimageroww= mysqli_fetch_array($mainimageresultt);
            if(mysqli_num_rows($mainimageresultt)>0)
            {
                $mainimageorder=$mainimageroww['Mainimage'];
            }
        }

        // puvii lastly added 24-06-2022
        $shippingaddresscountrycode = findCountryCode($shippingaddresscountry);
        // puvii lastly added 24-06-2022

        $querySelect = "SELECT * FROM `temporders` WHERE id='".$_POST["order_id"]."'";
        $resultSelect = mysqli_query($connect, $querySelect);
        while ($row = mysqli_fetch_assoc($resultSelect))
        {
            // it is for mapping sku
            // if(isset($_POST["addtomapping"])){
            //     $channelDetail = explode("-", $row["channel"]);
            //     $source = $channelDetail[0];
            //     $skuQueryMapping = 'INSERT INTO `mappingSKU`(`sku`, `map_sku`, `source`) VALUES ("'.$_POST["sku"].'", "'.$_POST["oldSku"].'", "'.$source.'")';
            //     mysqli_query($connect, $skuQueryMapping);
            // }

            if($row["sku"] != $_POST["sku"])
            {
                $changelog=$_POST["order_id"].' - Changed sku from '.$row["sku"].' to '.$_POST["sku"];
                $originalsku=$row["sku"];
                $correctsku=$_POST["sku"];
                $skulogquery = "INSERT INTO skuchangelog(sku, correctsku, logs) VALUES('$originalsku', '$correctsku', '$changelog')";

                $flags = getFlags($_POST["sku"]);

                $subflags = getSubFlags($_POST["sku"], $flags, $connect);
            }

            // puvii lastly added 24-06-2022
            // here i can check international or normal
            if($row["shippingaddresscountrycode"] != $shippingaddresscountrycode){
                if($shippingaddresscountrycode == "GB"){
                    $shippingservice = 'csv';
                    $postal_service = getPostalService($sku, $flags, $row["quantity"], $row["ordertotal"], $row["shipping_cost"], $shippingaddresspostcode, $row["channel"], $shippingservice, $connect);
                    $weight_In_Grams = $weight;
                }else{
                    $shippingservice = 'international';
                    $postal_service = 'international';
                    $weight_In_Grams = $weight;
                }
            }else{
                $shippingservice = $row["shippingservice"];
                $postal_service = $row["postal_service"];
                $weight_In_Grams = $weight;
            }
            // puvii lastly added 24-06-2022
        }

        // $query = "UPDATE temporders SET sku ='$sku', firstname = '$firstname', shippingaddresscompany = '$shippingaddresscompany', shippingaddressline1 = '$shippingaddressline1', shippingaddressline2 = '$shippingaddressline2', shippingaddressline3 = '$shippingaddressline3', shippingaddressregion = '$shippingaddressregion', shippingaddresscity ='$shippingaddresscity', shippingaddresspostcode ='$shippingaddresspostcode', weight_In_Grams ='$weight', email ='$cusemail', telephone = '$cusphone', item_height = '$ItemHeight', item_length = '$ItemLength', item_width = '$ItemWidth', shippingaddresscountry = '$shippingaddresscountry', notes ='$notes'";

        $query = "UPDATE temporders SET sku ='$sku', firstname = '$firstname', shippingaddresscompany = '$shippingaddresscompany', shippingaddressline1 = '$shippingaddressline1', shippingaddressline2 = '$shippingaddressline2', shippingaddressline3 = '$shippingaddressline3', shippingaddressregion = '$shippingaddressregion', shippingaddresscity ='$shippingaddresscity', shippingaddresspostcode ='$shippingaddresspostcode', weight_In_Grams ='$weight', email ='$cusemail', telephone = '$cusphone', item_height = '$ItemHeight', item_length = '$ItemLength', item_width = '$ItemWidth', shippingaddresscountry = '$shippingaddresscountry', shippingaddresscountrycode = '$shippingaddresscountrycode', shippingservice = '$shippingservice', postal_service = '$postal_service', weight_In_Grams = '$weight_In_Grams', notes ='$notes', FBA_merchantSKU = '$fbaMerchantSku', FBA_ASIN = '$fbaASIN'";

        if($flags != ''){
            $query .= ", flags ='$flags', subflags = '$subflags'";
        }
        
        $query .= " WHERE id ='".$_POST["order_id"]."'";

        if(mysqli_query($connect, $query))
        {  
            $querySelect2 = "SELECT * FROM `temporders` WHERE id='" . $_POST["order_id"] . "'";
            $resultSelect2 = mysqli_query($connect, $querySelect2);
            $orderRow = mysqli_fetch_array($resultSelect2);
            $mergeIds = $orderRow['date']."-".$orderRow["orderID"];

            // $query2 = "UPDATE temporders SET firstname = '$firstname', shippingaddresscompany = '$shippingaddresscompany', shippingaddressline1 = '$shippingaddressline1', shippingaddressline2 = '$shippingaddressline2', shippingaddressline3 = '$shippingaddressline3', shippingaddressregion = '$shippingaddressregion', shippingaddresscity = '$shippingaddresscity', shippingaddresspostcode = '$shippingaddresspostcode', weight_In_Grams = '$weight', email = '$cusemail', telephone = '$cusphone', item_height = '$ItemHeight', item_length = '$ItemLength', item_width = '$ItemWidth', shippingaddresscountry ='$shippingaddresscountry', notes='$notes'";

            $query2 = "UPDATE temporders SET firstname = '$firstname', shippingaddresscompany = '$shippingaddresscompany', shippingaddressline1 = '$shippingaddressline1', shippingaddressline2 = '$shippingaddressline2', shippingaddressline3 = '$shippingaddressline3', shippingaddressregion = '$shippingaddressregion', shippingaddresscity = '$shippingaddresscity', shippingaddresspostcode = '$shippingaddresspostcode', weight_In_Grams = '$weight', email = '$cusemail', telephone = '$cusphone', item_height = '$ItemHeight', item_length = '$ItemLength', item_width = '$ItemWidth', shippingaddresscountry ='$shippingaddresscountry', shippingaddresscountrycode = '$shippingaddresscountrycode', shippingservice = '$shippingservice', postal_service = '$postal_service', weight_In_Grams = '$weight_In_Grams' , notes='$notes'";

            if($flags != ''){
                $query2 .= ", flags ='$flags', subflags = '$subflags'";
            }
                              
            $query2 .= " WHERE merge='".$mergeIds."'";

            mysqli_query($connect, $query2);

            if($skulogquery!=''){
                mysqli_query($connect, $skulogquery);
            }

            echo json_encode(array (
                "id"       => $_POST["order_id"], 
                "sku"      => $sku, 
                "image"    => $mainimageorder, 
                "mergeIDs" => $mergeIds,
                "flags" => $flags,
                "subflags" => $subflags
            ));
        }  
    }
    // puvii latly added for postal service fags change
    elseif(isset($_POST["chosepostalservice"]) && isset($_POST["list_check"])){
        $postalservice = $_POST["chosepostalservice"];
        $orderids = explode(',', $_POST["list_check"]);
        $weight = getWeightByshippingService($postalservice);

        $echoArray = array();

        foreach ($orderids as $value) {
            $query = "UPDATE temporders SET postal_service='$postalservice', weight_In_Grams='$weight' WHERE id='" . $value . "'";
            $updateQuery = mysqli_query($connect, $query);

            if($updateQuery){
                $rowArray = array("id" => $value, "postal" => $postalservice, "weight" => $weight);
                array_push($echoArray, $rowArray);
            }
        }

        echo json_encode($echoArray);
    }
    elseif (isset($_POST["list_check"])  and isset($_POST["subflag"])) {
        $subflag = $_POST["subflag"];
        $orderids = explode(',', $_POST["list_check"]);

        $echoArray = array();

        foreach ($orderids as $value) {
            $query = "UPDATE temporders SET subflags='$subflag' WHERE id='" . $value . "'";
            $updateQuery = mysqli_query($connect, $query);

            if($updateQuery){
                $rowArray = array("id" => $value, "subflag" => $subflag);
                array_push($echoArray, $rowArray);
            }
        }

        echo json_encode($echoArray);
    }
    elseif (isset($_POST["list_check"])  and isset($_POST["flags"])) {
        $orderflags = implode(', ', $_POST["flags"]);
        $orderids = explode(',', $_POST["list_check"]);

        $echoArray = array();

        foreach ($orderids as $value) {
            $mergeStatus = '';
            $subflags = '';

            $querySelect = "SELECT * FROM `temporders` WHERE id='" . $value . "'";
            $resultSelect = mysqli_query($connect, $querySelect);
            while ($row = mysqli_fetch_assoc($resultSelect)) {
                $sku = $row["sku"];
                $mergeStatus = $row["merge"];
                $orderNumID = $row["orderID"];
            }

            $subflags = getSubFlags($sku, $orderflags, $connect);

            if($mergeStatus!=""){
                $subflags = 'zmerged';
            }

            $query = "UPDATE temporders SET flags='$orderflags', subflags='$subflags' WHERE id='" . $value . "'";

            if($mergeStatus != ''){
                $query .= " OR `merge` LIKE '%" . $orderNumID . "%'";
            }
            
            $updateQuery = mysqli_query($connect, $query);

            if($updateQuery){
                $rowArray = array("id" => $value, "flags" => $orderflags, "subflags" => $subflags);
                array_push($echoArray, $rowArray);
            }
        }

        echo json_encode($echoArray);
    }
?>