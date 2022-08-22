<?php
    include 'PDFMerger.php';

	use PDFMerger\PDFMerger; 
	$pdf = new PDFMerger;

    function thmx_currency_convert($from, $amount){
        $amount = number_format($amount, 2);
        
        if($amount == 0){
            $changedValue = "0.00";
        }else{
            $to = 'GBP';
            $url = 'https://api.exchangerate-api.com/v4/latest/'.$from.'';
            $json = file_get_contents($url);
            $exp = json_decode($json, true);
            
            if(array_key_exists("rates",$exp) && array_key_exists($to,$exp['rates'])){
                $convert = $exp['rates'][$to];
                $gbpAmount = $convert * $amount;
                $changedValue = round($gbpAmount,2);
            }else{
                $changedValue = "#NA";
            }
        }

        return $changedValue;
    }

    function getDemoOrders($con,$searchByFlag="",$searchBysubFlag="",$searchBymorefilter="",$searchBypostalfilter="",$sortingBy="",$searchByAll="",$partId=null){
        $data = array();

        if($partId==null){
            $sql = 'SELECT t_o.id, t_o.orderID, t_o.sku, t_o.date, t_o.channel, t_o.firstname, t_o.lastname, cp.image, pr.Mainimage, t_o.quantity, t_o.shippingservice, t_o.shippingaddresscompany, t_o.shippingaddressline1, t_o.shippingaddressline2, t_o.shippingaddressline3, t_o.shippingaddressregion, t_o.shippingaddresscity, t_o.shippingaddresspostcode, t_o.shippingaddresscountry, t_o.merge, t_o.total, t_o.ordertotal, t_o.flags, t_o.subflags, t_o.name, t_o.booking, t_o.csvdate, t_o.postal_service, t_o.shipment_id, t_o.tracking_No, t_o.label_B64, t_o.weight_In_Grams, t_o.royalmail_order_id, t_o.p2go_id, t_o.p2go_hash, t_o.zenstoresOrderTotal, t_o.shippedStatus, t_o.trackingStatus, t_o.shipping_cost FROM temporders t_o LEFT JOIN comboproducts cp on t_o.sku = cp.sku or t_o.sku = cp.originalsku LEFT JOIN products pr on pr.SKU = t_o.sku WHERE 1 ';
        }else{
            $sql = 'SELECT t_o.id, t_o.orderID, t_o.sku, t_o.date, t_o.channel, t_o.firstname, t_o.lastname, cp.image, pr.Mainimage, t_o.quantity, t_o.shippingservice, t_o.shippingaddresscompany, t_o.shippingaddressline1, t_o.shippingaddressline2, t_o.shippingaddressline3, t_o.shippingaddressregion, t_o.shippingaddresscity, t_o.shippingaddresspostcode, t_o.shippingaddresscountry, t_o.merge, t_o.total, t_o.ordertotal, t_o.flags, t_o.subflags, t_o.name, t_o.booking, t_o.csvdate, t_o.postal_service, t_o.shipment_id, t_o.tracking_No, t_o.label_B64, t_o.weight_In_Grams, t_o.royalmail_order_id, t_o.p2go_id, t_o.p2go_hash, t_o.zenstoresOrderTotal, t_o.shippedStatus, t_o.trackingStatus, t_o.shipping_cost FROM temporders t_o LEFT JOIN comboproducts cp on t_o.sku = cp.sku or t_o.sku = cp.originalsku LEFT JOIN products pr on pr.SKU = t_o.sku WHERE t_o.id = '.$partId;
        }

        ## Search 
        $searchQuery = "";
        
        if($searchByFlag != ''){
            if($searchByFlag == "others"){
                    $searchQuery .= " AND (t_o.flags !='Lampshade' AND t_o.flags !='Lampshade Shade Only')";
            }else{
                    $searchQuery .= " AND t_o.flags = '$searchByFlag' ";
            }
        }

        if($searchBysubFlag != ''){
            if ($searchBysubFlag == "Empty") {
                $searchQuery .= " AND (t_o.subflags = '' )";
            } else {
                $searchQuery .= " AND (t_o.subflags != '' )";
            }
        }

        if($searchBymorefilter != ''){
            if ($searchBymorefilter == "others") {
                $searchQuery .= " and (t_o.shippingservice != 'International' AND t_o.shippingservice != 'Prime' AND t_o.shippingservice != 'firstclass')";
            } else{
                $searchQuery .= " and (t_o.shippingservice = '".$searchBymorefilter."')";
            }
        }

        if($searchByAll != ''){
            $searchQuery .= " and (t_o.orderID like '%".$searchByAll."%' OR t_o.shippingaddresspostcode like '%".$searchByAll."%' OR t_o.firstname like '%".$searchByAll."%')";
        }

        $searchBypostalfilter = strval($searchBypostalfilter);
        if($searchBypostalfilter != ""){
            $postalServiceArray = explode(",",$searchBypostalfilter);
            
            if(count($postalServiceArray) > 0){
                $searchQuery .= " AND (";
                foreach ($postalServiceArray as $key => $postalServiceArr) {
                    $searchQuery .= "t_o.postal_service LIKE '%".$postalServiceArr."%' ";
                    if($key+1 != count($postalServiceArray)){
                        $searchQuery .= " OR ";
                    }
                }
                $searchQuery .= ")";
            }
        }

        $sql .= $searchQuery.' GROUP BY t_o.id ORDER BY ';

        // if($sortingBy != ''){
        //     $sql .= ' t_o.subflags '.$sortingBy.',';
        // }

        // $sql .= ' t_o.total ASC, t_o.date ASC';
        
        if ($searchByFlag == "Lampshade Shade Only" or $searchByFlag == "Lampshade" or $searchByFlag == "Transformer" or $searchByFlag == "Bulbs") {
            $sql .= " t_o.subflags " . $sortingBy . ", t_o.total ASC, t_o.date ASC";
        } else if($searchByFlag == "others"){
            $sql .= " t_o.flags DESC, t_o.subflags " . $sortingBy . ", t_o.total ASC, t_o.date ASC";
        } else {
            $sql .= " t_o.total, t_o.date ASC";
        }

        $result = $con->query($sql);
        $resultCount = $result->num_rows;
        if ($resultCount > 0){
            while($row = $result->fetch_assoc()){
                $mainimageOrderSKU = null;

                $orderSystId = $row['id'];
                $orderID = $row['orderID'];
                $orderSKU = $row['sku'];
                $orderDate = $row['date'];
                $orderChannel = $row['channel'];
                $orderFlags = $row['flags'];
                $orderSubFlags = $row['subflags'];
                $orderMergeStatus = $row['merge'];
                $orderMainImageCombo = $row['image'];
                $orderMainImageSingle = $row['Mainimage'];
                $orderProdQty = $row['quantity'];
                $orderProdTotal = $row['ordertotal'];
                $trackingNumb = $row['tracking_No'];
                $labelB64 = $row['label_B64'];
                $shippedStatus = $row['shippedStatus'];
                $trackingStatus = $row['trackingStatus'];
                $postalService = $row['postal_service'];
                $shippingCost = $row['shipping_cost'];
                $weightInGrams = $row['weight_In_Grams'];

                $booking = $row['booking'];
                $csvdate = $row['csvdate'];
                
                $orderCustomerPostCode = $row['shippingaddresspostcode'];

                $mainimageOrderSKU = $orderMainImageCombo;

                if($mainimageOrderSKU==NULL && strpos($orderSKU, '+') === false){
                    $mainimageOrderSKU = $orderMainImageSingle;
                }
                
                $orderCustomerFirName = $row['firstname'];
                $orderCustomerLasName = $row['lastname'];
                
                $orderProductName = $row['name'];
                
                $address="";
                if(!empty($orderCustomerFirName))
                {
                    $orderCustomerFirNamee = "Name: ".$orderCustomerFirName;
                    $address=$address.$orderCustomerFirNamee."<br>";
                }
                if(!empty($row["shippingaddresscompany"]))
                {
                    $address=$address.$row["shippingaddresscompany"]."<br>";
                }
                if(!empty($row["shippingaddressline1"]))
                {
                    $address=$address.$row["shippingaddressline1"]."<br>";
                }
                if(!empty($row["shippingaddressline2"]))
                {
                    $address=$address.$row["shippingaddressline2"]."<br>";
                }
                if(!empty($row["shippingaddressline3"]))
                {
                    $address=$address.$row["shippingaddressline3"]."<br>";
                }
                if(!empty($row["shippingaddressregion"]))
                {
                    $address=$address.$row["shippingaddressregion"]."<br>";
                }
                if(!empty($row["shippingaddresscity"]))
                {
                    $address=$address.$row["shippingaddresscity"]."<br>";
                }
                if(!empty($row["shippingaddresspostcode"]))
                {
                    $address=$address.$row["shippingaddresspostcode"]."<br>";
                }
                if(!empty($row["shippingaddresscountry"]))
                {
                    $address=$address.$row["shippingaddresscountry"]."<br>";
                }

                $singleRow = array();
                $singleRow['systemOrderId'] = $orderSystId;
                $singleRow['orderID'] = $orderID;
                $singleRow['orderSKU'] = $orderSKU;
                $singleRow['orderDate'] = $orderDate;
                $singleRow['orderChannel'] = $orderChannel;
                $singleRow['orderFlags'] = $orderFlags;
                $singleRow['orderSubFlags'] = $orderSubFlags;
                $singleRow['orderMergeStatus'] = $orderMergeStatus;

                $singleRow['customerAddress'] = $address;
                $singleRow['orderCustomerPostCode'] = $orderCustomerPostCode;
                $singleRow['orderCustomerCompany'] = $row["shippingaddresscompany"];
                $singleRow['orderCustomerAddress1'] = $row["shippingaddressline1"];
                $singleRow['orderCustomerAddress2'] = $row["shippingaddressline2"];
                $singleRow['orderCustomerAddress3'] = $row["shippingaddressline3"];
                $singleRow['orderCustomerRegion'] = $row["shippingaddressregion"];
                $singleRow['orderCustomerCity'] = $row["shippingaddresscity"];
                $singleRow['orderCustomerCountry'] = $row["shippingaddresscountry"];
                $singleRow['orderCustomerFirName'] = $orderCustomerFirName;
                $singleRow['orderCustomerLasName'] = $orderCustomerLasName;

                $singleRow['orderProductName'] = $orderProductName;
                $singleRow['orderProductMainImage'] = $mainimageOrderSKU;
                $singleRow['orderProdQty'] = $orderProdQty;
                $singleRow['orderProdTotal'] = $orderProdTotal;
                $singleRow['trackingNumb'] = $trackingNumb;
                $singleRow['labelB64'] = $labelB64;
                $singleRow['shippedStatus'] = $shippedStatus;
                $singleRow['trackingStatus'] = $trackingStatus;
                $singleRow['postalService'] = $postalService;
                $singleRow['shippingCost'] = $shippingCost;
                $singleRow['weightInGrams'] = $weightInGrams;

                $singleRow['booking'] = $booking;
                $singleRow['csvdate'] = $csvdate;

                array_push($data,$singleRow);

                if ($row["merge"] == "Merged") {
                    $mergeid = $row["date"] . "-" . $row["orderID"];

                    $mergequery = "SELECT t_o.id, t_o.orderID, t_o.sku, t_o.date, t_o.channel, t_o.firstname, t_o.lastname, cp.image, pr.Mainimage, t_o.quantity, t_o.shippingservice, t_o.shippingaddresscompany, t_o.shippingaddressline1, t_o.shippingaddressline2, t_o.shippingaddressline3, t_o.shippingaddressregion, t_o.shippingaddresscity, t_o.shippingaddresspostcode, t_o.shippingaddresscountry, t_o.merge, t_o.total, t_o.ordertotal, t_o.flags, t_o.subflags, t_o.name, t_o.booking, t_o.csvdate, t_o.postal_service, t_o.shipment_id, t_o.tracking_No, t_o.label_B64, t_o.weight_In_Grams, t_o.royalmail_order_id, t_o.p2go_id, t_o.p2go_hash, t_o.zenstoresOrderTotal, t_o.shippedStatus, t_o.trackingStatus, t_o.shipping_cost FROM temporders t_o LEFT JOIN comboproducts cp on t_o.sku = cp.sku or t_o.sku = cp.originalsku LEFT JOIN products pr on pr.SKU = t_o.sku WHERE t_o.merge='" . $mergeid . "'";

                    $resultMerge = $con->query($mergequery);

                    while($rowMerge = $resultMerge->fetch_assoc()){
                        $orderSystId = $rowMerge['id'];
                        $orderID = $rowMerge['orderID'];
                        $orderSKU = $rowMerge['sku'];
                        $orderDate = $rowMerge['date'];
                        $orderChannel = $rowMerge['channel'];
                        $orderFlags = $rowMerge['flags'];
                        $orderSubFlags = $rowMerge['subflags'];
                        $orderMergeStatus = $rowMerge['merge'];
                        $orderMainImageCombo = $rowMerge['image'];
                        $orderMainImageSingle = $rowMerge['Mainimage'];
                        $orderProdQty = $rowMerge['quantity'];
                        $orderProdTotal = $rowMerge['ordertotal'];
                        $trackingNumb = $rowMerge['tracking_No'];
                        $labelB64 = $rowMerge['label_B64'];
                        $shippedStatus = $rowMerge['shippedStatus'];
                        $trackingStatus = $rowMerge['trackingStatus'];
                        $postalService = $rowMerge['postal_service'];
                        $shippingCost = $rowMerge['shipping_cost'];
                        $weightInGrams = $rowMerge['weight_In_Grams'];

                        $booking = $rowMerge['booking'];
                        $csvdate = $rowMerge['csvdate'];
                        
                        $orderCustomerPostCode = $rowMerge['shippingaddresspostcode'];

                        $mainimageOrderSKU = $orderMainImageCombo;

                        if($mainimageOrderSKU==NULL && strpos($orderSKU, '+') === false){
                            $mainimageOrderSKU = $orderMainImageSingle;
                        }
                        
                        $orderCustomerFirName = $rowMerge['firstname'];
                        $orderCustomerLasName = $rowMerge['lastname'];
                        
                        $orderProductName = $rowMerge['name'];
                        
                        $address="";
                        if(!empty($orderCustomerFirName))
                        {
                            $orderCustomerFirNamee = "Name: ".$orderCustomerFirName;
                            $address=$address.$orderCustomerFirNamee."<br>";
                        }
                        if(!empty($rowMerge["shippingaddresscompany"]))
                        {
                            $address=$address.$rowMerge["shippingaddresscompany"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddressline1"]))
                        {
                            $address=$address.$rowMerge["shippingaddressline1"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddressline2"]))
                        {
                            $address=$address.$rowMerge["shippingaddressline2"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddressline3"]))
                        {
                            $address=$address.$rowMerge["shippingaddressline3"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddressregion"]))
                        {
                            $address=$address.$rowMerge["shippingaddressregion"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddresscity"]))
                        {
                            $address=$address.$rowMerge["shippingaddresscity"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddresspostcode"]))
                        {
                            $address=$address.$rowMerge["shippingaddresspostcode"]."<br>";
                        }
                        if(!empty($rowMerge["shippingaddresscountry"]))
                        {
                            $address=$address.$rowMerge["shippingaddresscountry"]."<br>";
                        }

                        $singleRow = array();
                        $singleRow['systemOrderId'] = $orderSystId;
                        $singleRow['orderID'] = $orderID;
                        $singleRow['orderSKU'] = $orderSKU;
                        $singleRow['orderDate'] = $orderDate;
                        $singleRow['orderChannel'] = $orderChannel;
                        $singleRow['orderFlags'] = $orderFlags;
                        $singleRow['orderSubFlags'] = $orderSubFlags;
                        $singleRow['orderMergeStatus'] = $orderMergeStatus;

                        $singleRow['customerAddress'] = $address;
                        $singleRow['orderCustomerPostCode'] = $orderCustomerPostCode;
                        $singleRow['orderCustomerCompany'] = $rowMerge["shippingaddresscompany"];
                        $singleRow['orderCustomerAddress1'] = $rowMerge["shippingaddressline1"];
                        $singleRow['orderCustomerAddress2'] = $rowMerge["shippingaddressline2"];
                        $singleRow['orderCustomerAddress3'] = $rowMerge["shippingaddressline3"];
                        $singleRow['orderCustomerRegion'] = $rowMerge["shippingaddressregion"];
                        $singleRow['orderCustomerCity'] = $rowMerge["shippingaddresscity"];
                        $singleRow['orderCustomerCountry'] = $rowMerge["shippingaddresscountry"];
                        $singleRow['orderCustomerFirName'] = $orderCustomerFirName;
                        $singleRow['orderCustomerLasName'] = $orderCustomerLasName;

                        $singleRow['orderProductName'] = $orderProductName;
                        $singleRow['orderProductMainImage'] = $mainimageOrderSKU;
                        $singleRow['orderProdQty'] = $orderProdQty;
                        $singleRow['orderProdTotal'] = $orderProdTotal;
                        $singleRow['trackingNumb'] = $trackingNumb;
                        $singleRow['labelB64'] = $labelB64;
                        $singleRow['shippedStatus'] = $shippedStatus;
                        $singleRow['trackingStatus'] = $trackingStatus;
                        $singleRow['postalService'] = $postalService;
                        $singleRow['shippingCost'] = $shippingCost;
                        $singleRow['weightInGrams'] = $weightInGrams;

                        $singleRow['booking'] = $booking;
                        $singleRow['csvdate'] = $csvdate;

                        array_push($data,$singleRow);
                    }
                } 
            }
        }

        return $data;
    }

    // count rows by table name
    function countTable($con, $table){
        $query = "SELECT * FROM ".$table;
        return mysqli_num_rows(mysqli_query($con, $query));
    }

    // get postal codes of merge orders by some parameters
    function getMergePostCodes($flags, $subflags, $morefilter, $postalservice, $con){
        $query = 'SELECT * from temporders where 1 ';

        if ($subflags != "") {
            if ($subflags == "Empty") {
                $query .= ' AND subflags= ""';
            } else if ($subflags == "Not Empty") {
                $query .= ' AND subflags!= ""';
            }
        }

        if ($flags != "") {
            $query .= ' AND flags="'.$flags.'"';
        }

        if($morefilter != ""){
            if ($morefilter == "others") {
                $query .= " AND shippingservice != 'International' AND shippingservice != 'Prime' AND shippingservice != 'first class'";
            } else {
                $query .= " AND shippingservice = '".$morefilter."'";
            } 
        }

        $postalservice = strval($postalservice);
        if($postalservice != ""){
            $postalServiceArray = explode(",",$postalservice);
            
            if(count($postalServiceArray) > 0){
                $query .= " AND (";
                foreach ($postalServiceArray as $key => $postalServiceArr) {
                    $query .= "(postal_service LIKE '%".$postalServiceArr."%') OR ";
                    if($key+1 == count($postalServiceArray)){
                            $query .= " 0)";
                    }
                }
            }
        }

        $query .= ' AND (merge != "")';

        $dataArray = array();
        $samechDataArray = array();
        $diffchDataArray = array();

        $mergeOrderResult=$con->query($query);

        $count = $mergeOrderResult->num_rows;

        if ($count > 0) 
        {
            while($row = $mergeOrderResult->fetch_assoc())
            {

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

                $singleRow = array();
                $singleRow['channel'] = $row['channel'];
                $singleRow['postCode'] = $row['shippingaddresspostcode'];
                $singleRow['address'] = $address;

                foreach ($dataArray as $key => $data) {
                    if ($data['channel'] === $singleRow['channel'] && $data['address'] === $singleRow['address']) {
                        $key1 = array_search($singleRow['postCode'], array_column($samechDataArray, 'postCode'));
                        $str_key1 = (string)$key1;
                        if($str_key1 == ""){
                            array_push($samechDataArray,$singleRow);
                        }
                    }else if ($data['address'] === $singleRow['address']) {
                        $key2 = array_search($singleRow['postCode'], array_column($diffchDataArray, 'postCode'));
                        $str_key2 = (string)$key2;
                        if($str_key2 == ""){
                            array_push($diffchDataArray,$singleRow);
                        }
                    }
                }

                array_push($dataArray,$singleRow);
            }
        }

        return array("count" => $count, "dataArr" => $dataArray, "samechDataArray" => $samechDataArray, "diffchDataArray" => $diffchDataArray);
    }

    /*
		mergePDFByBase64 is a function to merge base 64 pdf string.

		parameter description
		$base64Arr - it is an base 64 array
		$pdf - it is PDFMerger type child
	*/
	function mergePDFByBase64($base64Arr, $pdf, $downloadedPdfName){
		$fileNames = array();

		foreach($base64Arr as $key => $base64){
			$file = 'pdf'.$key.'.pdf';
			array_push($fileNames,$file);

			$pdf_decoded = base64_decode ($base64);
			$pdfff = fopen($file,'w') or die("can't write file ".$file); // w - write mode , a- append mode
			fwrite ($pdfff, $pdf_decoded);
			//close output file
			fclose ($pdfff);

			$pdf->addPDF($file, 'all');
		}

		$pdf->merge('download', $downloadedPdfName);

		foreach($fileNames as $fileName){
			if (file_exists($fileName)) {
				unlink($fileName);	
			}
		}
	}

    /*
        Fetch data using CURL
        This Function return two values
            first one - response code(200,400)
            second one - response data as array
    */
    function fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $METHOD,
            CURLOPT_POSTFIELDS => $POST_FIELDS,
            CURLOPT_HTTPHEADER => $HTTP_HEADER
        ));

        $response = curl_exec($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response_array = json_decode($response, true);

        return array("responsecode" => $response_code,"responsearray" => $response_array);
    }

    /*
        Download file as pdf without store file in server.
    */
    function downloadBase64($filename, $filepath, $base64_encoded_file_data) {
        $filename = $filename."_" . date('Y-m-d') . ".pdf";

        // Prevents run-out-of-memory issue
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Decodes encoded data
        $decoded_file_data = base64_decode($base64_encoded_file_data);

        // Writes data to the specified file
        file_put_contents($filepath, $decoded_file_data);

        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . filesize($filepath));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        
        // Deletes the temp file
        if (file_exists($filepath)) {
            unlink($filepath);	
        }
    }

    /*
        Basic Auth is when input username and password, it returns base 64 encode string of combination of username and password
    */
    function basicAuth($username, $password){
        $authString = $username.":".$password;
        $authString = base64_encode($authString);

        return $authString;
    }

    function authenticateParcelDen(){
        // here username and password is parcel den login credentials
        $username = "ledsone";
        $password = "1212LED";

        $authString = basicAuth($username, $password);
        return $authString;
    }

    function removeSome($str){
        $strParams = [ "'" => "\'" ];

        $str = str_replace(array_keys($strParams), array_values($strParams), $str);
        $str = trim($str);

        return $str;
    }

    function changToLower($str){
        $str = strtolower($str);
        $str = trim($str);

        return $str;
    }

    /*
        1.“Name”, “BuildingNo”, “Street”, “Town”, “PostCode”, “Region”, CountryCode, 
        “Signature”, “DeliveryOptionsId”, ”PackageCode” are mandatory fields.

        2. “RegionId”, “MinGrams”, “MaxGrams”, “LabelValue” leave them as it is with the 
        default value 0 (zero)

        3. “CountryCode” - Country codes are in ISO Alpha-2 format 

        4. “Signature” - true or false

        5. “DeliveryOptionId” - For Next Day deliveries the value is 2, for Standard Packet and 
        Standard Parcel deliveries the value is 1

        6. “PackageCode” - For Standard Parcels and Next Day Parcels the value is 0, for 
        Standard Packets the value is 10
    */
    function createParcelDenLabel($FirstName, $LastName, $Telephone, $Email, $AddressLine1, $AddressLine2, $AddressLine3, $Town, $PostCode, $Region, $CountryCode, $postalService){

        $authString = authenticateParcelDen();

        if(trim($LastName) == ""){
            $LastName = $FirstName;
        }

        if($postalService == "ParcelDenOnline Standard Package"){
            // 2KG (Standard Packet)
            $DeliveryOptionsId = 1;
            $PackageCode = 10;
        }else if($postalService == "ParcelDenOnline Standard Parcel"){
            // 5KG (Standard Parcel)
            $DeliveryOptionsId = 1;
            $PackageCode = 10;
        }else if($postalService == "Next Day"){
            $DeliveryOptionsId = 2;
            $PackageCode = 0;
        }

        $divideAddress = divideAddress1($AddressLine1);

        if($divideAddress['addressDivide']){
            $BuildingNumber = $divideAddress['firstNumbers'];
            $Street = $divideAddress['restString'];

            $AddressLine1 = $AddressLine2;
            $AddressLine2 = $AddressLine3;
            $AddressLine3 = "";
        }else{
            $BuildingNumber = $AddressLine1;
            $Street = $AddressLine2;

            $AddressLine1 = $AddressLine3;
            $AddressLine3 = "";
        }

        // $BuildingNumber = $AddressLine1;
        // $Street = $AddressLine2;

        // $AddressLine1 = $AddressLine3;
        // $AddressLine3 = "";

        $MinGrams = 0;
        $MaxGrams = 0;
        $LabelValue = 0;
        $RegionId = 0;

        $URL = 'http://www.parceldenonline.com/api/LabelOperations/CreateLabel';
        $METHOD = 'POST';
        $POST_FIELDS = '{
            "LabelForm": {
                "Recipient": {
                    "Title": "",
                    "FirstName": "'.$FirstName.'",
                    "LastName": "'.$LastName.'",
                    "Telephone": "'.$Telephone.'",
                    "Email": "'.$Email.'",
                    "Reference": "",
                    "DeliveryAddress": {
                        "BuildingNumber": "'.$BuildingNumber.'",
                        "Street": "'.$Street.'",
                        "AddressLine1": "'.$AddressLine1.'",
                        "AddressLine2": "'.$AddressLine2.'",
                        "AddressLine3": "'.$AddressLine3.'",
                        "Town": "'.$Town.'",
                        "PostCode": "'.$PostCode.'",
                        "RegionId": '.$RegionId.',
                        "Region": "'.$Region.'",
                        "CountryCode": "'.$CountryCode.'"
                    }
                },
                "ParcelUsersXref": {
                    "Parcel": {
                        "Signature": false,
                        "DeliveryOptionsId": '.$DeliveryOptionsId.',
                        "ItemWeight": {
                            "PackageCode": '.$PackageCode.',
                            "MinGrams": '.$MinGrams.',
                            "MaxGrams": '.$MaxGrams.',
                            "LabelValue": '.$LabelValue.'
                        }
                    }
                }
            }
        }';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $createParcelDenLabel = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $labelCreated = false;
        $createParcelDenLabelTrackingNo = "";
        $createParcelDenLabelLabelB64 = "";
        $error = "";

        if($createParcelDenLabel['responsecode']=="200"){
            $createParcelDenLabelResponse = $createParcelDenLabel['responsearray'];

            if(array_key_exists('TrackingNo',$createParcelDenLabelResponse)){
                $labelCreated = true;
                $createParcelDenLabelTrackingNo = $createParcelDenLabelResponse['TrackingNo'];
                $createParcelDenLabelLabelB64 = $createParcelDenLabelResponse['Label'];
            }else{
                $error = $createParcelDenLabelResponse['Status']." - ".$createParcelDenLabelResponse['Label'];
            }
        }else{
            $error = "Error in Creating Parcel Den Label.";
        }
        
        return array("labelCreated" => $labelCreated, "trackingNo" => $createParcelDenLabelTrackingNo, "labelB64" => $createParcelDenLabelLabelB64, "error" => $error);
    }

    function getOpenOrderCount($postcode, $sku, $orderDate, $connect){
        $orderDate = date_create($orderDate);
        $orderDate = date_format($orderDate, "Y-m-d");

        // $orderID
        // 'SELECT count(id) as counting FROM `orders` WHERE orderID = "'.$orderID.'"';
        // $query = 'SELECT count(id) as counting FROM `orders` WHERE shippingaddresspostcode = "'.$postcode.'" AND sku = "'.$sku.'"';
        $query = 'SELECT count(id) as counting FROM `orders` WHERE shippingaddresspostcode = "'.$postcode.'" AND sku = "'.$sku.'" AND date = "'.$orderDate.'"';
        
        $result = mysqli_query($connect, $query);

        $row = mysqli_fetch_array($result);
        $ordersCount = $row['counting'];

        return $ordersCount;
    }

    function getMappingSKU($sku, $channelInfo, $connect){
        $channelDetail = explode("-", $channelInfo, 2);
        $source = $channelDetail[0];
        $sub_source = $channelDetail[1];

        $querySelect = "SELECT * FROM `mappingSKUSelro` WHERE map_sku='" . $sku . "' AND source = '" . $source . "' AND sub_source = '". $sub_source ."'";
        $resultSelect = mysqli_query($connect, $querySelect);
        $row = mysqli_fetch_assoc($resultSelect);

        if($row["sku"] != ""){
            $sku = $row["sku"];
        }

        return $sku;
    }

    // FBA stock info get to send from amazon warehouse
    function getFBAStock($sku, $connect){
        $message = "";
        $currDate = date('Y-m-d', strtotime('today'));
        $lastMonthDate = date('Y-m-d', strtotime('-1 month', strtotime($currDate)));
        $last2WeekDate = date('Y-m-d', strtotime('-2 week', strtotime($currDate)));

        $querySelect = "SELECT * FROM `fbastock` WHERE `system_sku` LIKE '" . $sku . "' AND `qty` >= 8 AND `channel_name` = 'AMAZON-LEDsone FBA' AND `system_sku` != '12BO18'";
        $resultSelect = mysqli_query($connect, $querySelect);

        $rowcount = mysqli_num_rows($resultSelect);

        if($rowcount > 0){
            while ($row = mysqli_fetch_array($resultSelect)) {
                $currentQty = $row['qty'];
                $currentListingSKU = $row['listing_sku'];
                $currentASIN = $row['asin'];
                $currentChannel = $row['channel_name'];

                $lastTwowWeeksQnt = $lastMonthQnt = 0;

                $getlast2week = "SELECT SUM(`quantity`) as totalQnt FROM `fbasales` WHERE date BETWEEN '$last2WeekDate' AND '$currDate' AND (`asin` LIKE '".$currentASIN."') GROUP by asin";
                $getLast2weekResult = mysqli_query($connect, $getlast2week);
                $rowLast2week = mysqli_fetch_array($getLast2weekResult);
                if(mysqli_num_rows($getLast2weekResult)>0){
                    $lastTwowWeeksQnt = $rowLast2week['totalQnt'];
                }

                $getlastMonth = "SELECT SUM(`quantity`) as totalQnt FROM `fbasales` WHERE date BETWEEN '$lastMonthDate' AND '$currDate' AND (`asin` LIKE '".$currentASIN."') GROUP by asin";
                $getLastMonthResult = mysqli_query($connect, $getlastMonth);
                $rowLastMonth = mysqli_fetch_array($getLastMonthResult);
                if(mysqli_num_rows($getLastMonthResult)>0){
                    $lastMonthQnt = $rowLastMonth['totalQnt'];
                }

                if($lastTwowWeeksQnt == 0 && $lastMonthQnt == 0 && $currentQty >=8){
                    $message .= $currentChannel." ASIN ".$currentASIN.", SKU ".$currentListingSKU."<br>";
                }else if($lastTwowWeeksQnt <= 1 && $lastMonthQnt <= 2 && $currentQty >=10){
                    $message .= $currentChannel." ASIN ".$currentASIN.", SKU ".$currentListingSKU."<br>";
                }
            }
        }else{
            $message = "ZERO REC";
        }

        $message = trim($message);
        $message = trim($message, "<br>");

        return $message;
    }

    // createOrders(
    function CreateShipstationOrder($orderNumber, $orderDate, $fullName, $company, $street1, $street2, $street3, $city, $state, $postalCode, $countryCode, $phone, $email, $lineItemKey, $sku, $name, $quantity, $unitPrice, $shipDate, $chosenPostal){
        $authString = authenticateShipStation();
        
        $URL = 'https://ssapi.shipstation.com/orders/createorder';
        $METHOD = 'POST';
        $POST_FIELDS = '{
            "orderNumber": "'.$orderNumber.'",
            "orderKey": "'.$orderNumber.'",
            "orderDate": "'.$orderDate.'",
            "orderStatus": "awaiting_shipment",
            "customerEmail": "'.$email.'",
            "billTo": {
                "name": "'.$fullName.'",
                "company": "'.$company.'",
                "street1": "'.$street1.'",
                "street2": "'.$street2.'",
                "street3": "'.$street3.'",
                "city": "'.$city.'",
                "state": "'.$state.'",
                "postalCode": "'.$postalCode.'",
                "country": "'.$countryCode.'",
                "phone": "'.$phone.'",
                "residential": false,
                "addressVerified": "Address validated successfully"
            },
            "shipTo": {
                "name": "'.$fullName.'",
                "company": "'.$company.'",
                "street1": "'.$street1.'",
                "street2": "'.$street2.'",
                "street3": "'.$street3.'",
                "city": "'.$city.'",
                "state": "'.$state.'",
                "postalCode": "'.$postalCode.'",
                "country": "'.$countryCode.'",
                "phone": "'.$phone.'",
                "residential": false,
                "addressVerified": "Address validated successfully"
            },
            "items": [
                {
                    "lineItemKey": "'.$lineItemKey.'",
                    "sku": "'.$sku.'",
                    "name": "'.$name.'",
                    "weight": null,
                    "quantity": '.$quantity.',
                    "unitPrice": '.$unitPrice.',
                    "taxAmount": 0.00,
                    "shippingAmount": 0.00
                }
            ],
            "orderTotal": '.round(($quantity*$unitPrice),2).',
            "shipDate": "'.$shipDate.'",
            "amountPaid": '.round(($quantity*$unitPrice),2).',
            "taxAmount": 0.00,
            "shippingAmount": 0.00,
            "weight": {
                "value": 30000.00,
                "units": "grams",
                "WeightUnits": 2
            },
            "advancedOptions": {
                "customField1": "csv",
                "customField2": "REPLACEMENT",
                "customField3": "'.$chosenPostal.'"
            }
        }';
        
        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');
        
        $createReplacementOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);
        
        $orderCreated = false;
        $error = "";
        
        if($createReplacementOrder['responsecode']=="200"){
            $createReplacementOrderResponse = $createReplacementOrder['responsearray'];
            
            if(array_key_exists('orderId',$createReplacementOrderResponse)){
                $orderCreated = true;
                $createReplacementOrderResponseOrderID = $createReplacementOrderResponse['orderId'];
            }
        }else{
            $error = "Error in Creating Order to ship station.";
        }
        
        return array("orderCreated" => $orderCreated, "fullname" => $fullName, "shipStationOrderID" => $createReplacementOrderResponseOrderID, "error" => $error);
    }

    function createRoyalmailOrder($SystemOrderID, $FirstName, $LastName, $Telephone, $Email, $AddressCompany, $AddressLine1, $AddressLine2, $AddressLine3, $Region, $Town, $PostCode, $Country, $CountryCode, $postalService, $orderDate, $subtotal, $shippingCostCharged, $currencyCode, $weightInGramsDB){
        if($Region != ""){
            $AddressLine3 .= " ".$Region;
        }

        if($postalService == "245g LL"){
            $weightInGrams = "245";
            $packageFormatIdentifier = "largeLetter"; // "undefined" "letter" "largeLetter" "smallParcel" "mediumParcel" "parcel"
            $serviceCode = "CRL48";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false
            }';
        }else if($postalService == "900g parcel"){
            $weightInGrams = "1000";
            $packageFormatIdentifier = "parcel";
            $serviceCode = "CRL48";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false
            }';
        }else if($postalService == "95g LL"){
            $weightInGrams = "95";
            $packageFormatIdentifier = "largeLetter";
            $serviceCode = "BPL2";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'"
            }';
        }else if($postalService == "BPL Royal Mail 1st Class Large Letter"){
            $weightInGrams = "95";
            $packageFormatIdentifier = "largeLetter";
            $serviceCode = "BPL1";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false
            }';
        }else if($postalService == "CRL Royal Mail 24 Large Letter"){
            $weightInGrams = "245";
            $packageFormatIdentifier = "largeLetter";
            $serviceCode = "CRL24";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false
            }';
        }else if($postalService == "CRL Royal Mail 24 Parcel"){
            $weightInGrams = "900";
            $packageFormatIdentifier = "parcel";
            $serviceCode = "CRL24";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false
            }';
        }else if($postalService == "TPN Royal Mail Tracked 24 Non Signature"){
            $weightInGrams = "1000";
            $packageFormatIdentifier = "parcel";
            $serviceCode = "TPN24";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false,
                "receiveEmailNotification": true,
                "receiveSmsNotification": true
            }';
        }else if($postalService == "Rm manual"){
            $weightInGrams = "245";
            $packageFormatIdentifier = "largeLetter";
            $serviceCode = "CRL48";

            $postageDetails = '{
                "serviceCode": "'.$serviceCode.'",
                "requestSignatureUponDelivery": false
            }';
        }

        if($weightInGramsDB != "" && $weightInGramsDB != 0){
            $weightInGrams = $weightInGramsDB;
        }

        $plannedDespatchDate = date('Y-m-d');

        $URL = 'https://api.parcel.royalmail.com/api/v1/orders';
        $METHOD = 'POST';
        $POST_FIELDS = '{
            "items": [
                {
                    "orderReference": "'.$SystemOrderID.'",
                    "recipient": {
                        "address": {
                            "fullName": "'.$FirstName.' '.$LastName.'",
                            "companyName": "'.$AddressCompany.'",
                            "addressLine1": "'.$AddressLine1.'",
                            "addressLine2": "'.$AddressLine2.'",
                            "addressLine3": "'.$AddressLine3.'",
                            "city": "'.$Town.'",
                            "county": "'.$Country.'",
                            "postcode": "'.$PostCode.'",
                            "countryCode": "'.$CountryCode.'"
                        },
                        "phoneNumber": "'.$Telephone.'",
                        "emailAddress": "'.$Email.'",
                        "addressBookReference": ""
                    },
                    "packages": [
                        {
                            "weightInGrams": '.$weightInGrams.',
                            "packageFormatIdentifier": "'.$packageFormatIdentifier.'"
                        }
                    ],
                    "orderDate": "'.$orderDate.'",
                    "plannedDespatchDate": "'.$plannedDespatchDate.'",
                    "specialInstructions": "",
                    "subtotal": '.$subtotal.',
                    "shippingCostCharged": '.$shippingCostCharged.',
                    "otherCosts": 0,
                    "total": '.((float)$subtotal + (float)$shippingCostCharged).',
                    "currencyCode": "'.$currencyCode.'",
                    "label": {
                        "includeLabelInResponse": true,
                        "includeCN": false,
                        "includeReturnsLabel": false
                    },
                    "postageDetails": '.$postageDetails.'
                }
            ]
        }';

        $HTTP_HEADER = array();
        if($postalService == "Rm manual"){
            array_push($HTTP_HEADER,'Authorization: Bearer 54dec6d2-a5c2-449f-ba49-3db518fa963d','Content-Type: application/json');
        }else{
            array_push($HTTP_HEADER,'Authorization: Bearer 6715af2a-7803-483e-8769-ec141140d176','Content-Type: application/json');
        }

        $createRoyalmailOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $orderCreated = false;
        $trackingHas = false;
        $labelHas = false;

        $createRoyalmailOrderCreatedResOrderIden = "";
        $createRoyalmailOrderCreatedResTrackingNo = "";
        $createRoyalmailOrderCreatedResLabel = "";
        $error = "";

        if($createRoyalmailOrder['responsecode']=="200"){
            $createRoyalmailOrderResponse = $createRoyalmailOrder['responsearray'];

            $createRoyalmailOrderSuccessCount = $createRoyalmailOrderResponse['successCount'];

            if($createRoyalmailOrderSuccessCount > 0){
                $createRoyalmailOrderCreatedResponse = $createRoyalmailOrderResponse['createdOrders'];

                foreach ($createRoyalmailOrderCreatedResponse as $key => $createRoyalmailOrderCreatedRes) {
                    if(array_key_exists('orderIdentifier',$createRoyalmailOrderCreatedRes)){
                        $orderCreated = true;
                        $createRoyalmailOrderCreatedResOrderIden = $createRoyalmailOrderCreatedRes['orderIdentifier'];
                    }

                    if(array_key_exists('trackingNumber',$createRoyalmailOrderCreatedRes)){
                        $trackingHas = true;
                        $createRoyalmailOrderCreatedResTrackingNo = $createRoyalmailOrderCreatedRes['trackingNumber'];
                    }

                    if(array_key_exists('label',$createRoyalmailOrderCreatedRes)){
                        $labelHas = true;
                        $createRoyalmailOrderCreatedResLabel = $createRoyalmailOrderCreatedRes['label'];
                    }
                }
            }else{
                $error = "Order creating failed.";
            }
        }else{
            $error = "Error in Creating Parcel Den Label.";
        }
        
        return array("orderCreated" => $orderCreated, "trackingHas" => $trackingHas, "labelHas" => $labelHas, "royalMailOrderIdent" => $createRoyalmailOrderCreatedResOrderIden, "trackingNum" => $createRoyalmailOrderCreatedResTrackingNo, "labelB64" => $createRoyalmailOrderCreatedResLabel, "error" => $error);
    }

    function getTrackingNumberRoyalMail($royalMailOrderIdent){
        $trackingGot = false;
        $error = "";
        $getTrackingNumberRoyalMailResTrackingNumb = "";

        $URL = 'https://api.parcel.royalmail.com/api/v1/orders/'.$royalMailOrderIdent;
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Bearer 6715af2a-7803-483e-8769-ec141140d176','Content-Type: application/json');

        $getTrackingNumberRoyalMail = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        if($getTrackingNumberRoyalMail['responsecode']=="200"){
            $getTrackingNumberRoyalMailResponse = $getTrackingNumberRoyalMail['responsearray'];

            foreach ($getTrackingNumberRoyalMailResponse as $key => $getTrackingNumberRoyalMailRes) {

                if(array_key_exists('trackingNumber',$getTrackingNumberRoyalMailRes)){
                    $trackingGot = true;
                    $getTrackingNumberRoyalMailResTrackingNumb = $getTrackingNumberRoyalMailRes['trackingNumber'];
                }
            }
        }else{
            $error = "Error in Getting tracking number.";
        }

        return array("trackingGot" => $trackingGot, "trackingNum" => $getTrackingNumberRoyalMailResTrackingNumb, "error" => $error);
    }

    function getLabelRoyalMailByIdentifier($royalMailOrderIdent){
        $labelGot = false;
        $getLabelRoyalMailByIdentifierResponse = "";
        $error = "";

        $URL = 'https://api.parcel.royalmail.com/api/v1/orders/'.$royalMailOrderIdent.'/label?documentType=postageLabel&includeReturnsLabel=true';
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Bearer 6715af2a-7803-483e-8769-ec141140d176','Content-Type: application/pdf');

        $getLabelRoyalMailByIdentifier = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        if($getLabelRoyalMailByIdentifier['responsecode']=="200"){
            $labelGot = true;

            $getLabelRoyalMailByIdentifierResponse = base64_encode($getLabelRoyalMailByIdentifier['responsearray']);
        }else{
            $error = "Error in Getting label.";
        }

        return array("labelGot" => $labelGot, "label" => $getLabelRoyalMailByIdentifierResponse, "error" => $error);
    }

    /*
        Fetch data by content type using CURL response got as xml then encode as json
        This Function return two values
            first one - response code(200,400)
            second one - response data as array
    */
    function fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $METHOD,
            CURLOPT_POSTFIELDS => $POST_FIELDS,
            CURLOPT_HTTPHEADER => $HTTP_HEADER
        ));

        $response = curl_exec($curl);
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        
        $response_array = "";

        if($ContentType == "XML"){
            $responseXml = simplexml_load_string($response) or die("Error: Cannot create encode data to xml object");
            $jsondata = json_encode($responseXml) or die("Error: Cannot encode record to json");
            $response_array = json_decode($jsondata, true);

            if($responseXml == "Error: Cannot create encode data to xml object"){
                $response_code = 400;
            }

            if($jsondata == "Error: Cannot encode record to json"){
                $response_code = 400;
            }
        }else if($ContentType == "PDF"){
            $response_array = base64_encode($response);
        }else if($ContentType == "JSON"){
            $response_array = json_decode($response, true);
        }

        return array("responsecode" => $response_code,"responsearray" => $response_array);
    }


    /*
        sample response of delivery group order creation

        $RecipientName = 'Test Label Puvii';
        $AddressLine1 = '13 Boulevard Rodocanachi';
        $AddressLine2 = 'Bat C';
        $AddressLine3 = '';
        $AddressLine4 = 'Marseille'; // city
        $AddressLine5 = ''; // state
        $CountryCode = 'FR';
        $PostCode = '13008';
        $Phone = '076325632726';
        $Weight = 1000;
        $ItemHeight = 35;
        $ItemLength = 35;
        $ItemWidth = 35;
        $Email = 'puvii.digitweb@gmail.com';
        $ItemQuantity = 1;
        $ItemUnitValue = 8.26;
        $ItemDescription = "Lampshades";
        $ShippingCost = 5.5;
        $ClientItemReference = "1239";
    */
    function createDeliveryGroupOrder($RecipientName, $AddressLine1, $AddressLine2, $AddressLine3, $AddressLine4, $AddressLine5, $CountryCode, $PostCode, $Phone, $Weight, $ItemHeight, $ItemLength, $ItemWidth, $Email, $ItemQuantity, $ItemUnitValue, $ItemDescription, $ShippingCost, $ClientItemReference, $Channel){
        $ItemValue = $ItemQuantity * $ItemUnitValue;
        // puvii lastly added for packig Area 19-05-2022
        if(strpos($ItemDescription, 'packing Area') !== false){
        //if($ItemDescription == 'packing Area'){
            $ItemDescription = 'Ceiling Light Accessories';
        }

        $IOSSNumber = '';

        $channel1 = explode('-',$Channel);
        if(strtoupper($channel1[0]) == "EBAY"){
            $IOSSNumber = 'IM2760000742';
        }else if(strtoupper($channel1[0])=="AMAZON"){
            $IOSSNumber = 'IM4420001201';
        }else if(strtoupper($channel1[0])=="CDISCOUNT"){
            $IOSSNumber = 'IM2500000295';
        }

        $URL = 'https://services.dockethub.com/Mosaic.DocketHub.ItemAdvice/ItemAdviceServiceV3.svc/restService/SubmitItemAdvice';
        $METHOD = 'POST';
        $POST_FIELDS = '{
            "ItemDetails": [
                {
                    "ValueCurrency": "GBP",
                    "SaturdayDelivery": false,
                    "RecipientName": "'.$RecipientName.'",
                    "RecipientAddress": {
                        "AddressLine1": "'.$AddressLine1.'",
                        "AddressLine2": "'.$AddressLine2.'",
                        "AddressLine3": "'.$AddressLine3.'",
                        "AddressLine4": "'.$AddressLine4.'",
                        "AddressLine5": "'.$AddressLine5.'",
                        "CountryCode": "'.$CountryCode.'",
                        "PostCode": "'.$PostCode.'"
                    },
                    "Phone": "'.$Phone.'",
                    "Weight": '.$Weight.',
                    "ItemHeight": '.$ItemHeight.',
                    "ItemLength": '.$ItemLength.',
                    "ItemWidth": '.$ItemWidth.',
                    "InsuranceValue": 0,
                    "Email": "'.$Email.'",
                    "DocumentsOnly": false,
                    "DDP": true,
                    "Contents": [
                        {
                            "ItemQuantity": '.$ItemQuantity.',
                            "ItemUnitValue": '.$ItemUnitValue.',
                            "ItemValue": '.$ItemValue.',
                            "ItemDescription": "'.$ItemDescription.'",
                            "ItemCommodityCode": "9405990090"
                        }
                    ],
                    "CommercialInvoice": {
                        "CommercialInvoiceRequired": true,
                        "IOSSNumber": "'.$IOSSNumber.'",
                        "CountryOfOrigin": "GB",
                        "ReasonForExport": "Sales",
                        "ShippingCost": '.$ShippingCost.',
                        "ExportLicenceNumber": "GB287587828000"
                    },
                    "ClientName": "Ledsone UK Ltd",
                    "ClientItemReference": "'.$ClientItemReference.'",
                    "CarrierServiceCode": "TDGOT",
                    "CarrierName": "Secured Mail-Nat-Mixed Weight"
                }
            ],
            "Authentication": {
                "Password": "vcdgea",
                "Username": "user@LedsoneUKLtd"
            }
        }';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');

        $ContentType = "XML";

        // $createDeliveryGroupOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
        $createDeliveryGroupOrder = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);

        $orderCreated = false;
        $error = "";
        $CarrierBarcode = "";
        $CarrierItemReference = "";
        $ErrorCode = "";
        $labelCreated = "";
        $labelB64 = "";
        $labelError = "";

        if($createDeliveryGroupOrder['responsecode'] == "200"){
            $createDeliveryGroupOrderResponse = $createDeliveryGroupOrder['responsearray'];

            $ItemDetailResponse = $createDeliveryGroupOrderResponse['ItemDetailResponse'];

            $CarrierBarcode = $ItemDetailResponse['CarrierBarcode'];
            $CarrierItemReference = $ItemDetailResponse['CarrierItemReference'];
            $ErrorCode = $ItemDetailResponse['ErrorCode'];

            if($ErrorCode == '0'){
                $orderCreated = true;

                if($CarrierBarcode == ""){
                    $CarrierBarcode = $CarrierItemReference;
                }

                $createDeliveryGroupLabel = createDeliveryGroupLabel($CarrierBarcode);
                $labelCreated = $createDeliveryGroupLabel['labelCreated'];
                $labelB64 = $createDeliveryGroupLabel['labelB64'];
                $labelError = $createDeliveryGroupLabel['error'];
            }else{
                $error = "Error: ".$ItemDetailResponse['ErrorMessage']." ".$ErrorCode;
                // $error = "Order creating failed.";
            }
            
        }else{
            $error = "Error in Creating Delivery Group Order.";
        }
        
        return array("orderCreated" => $orderCreated, "trackingNum" => $CarrierBarcode, "error" => $error, "labelCreated" => $labelCreated, "labelB64" => $labelB64, "labelError" => $labelError);
    }

    function createDeliveryGroupLabel($trackingNumber){
        $URL = 'https://services.dockethub.com/Mosaic.DocketHub.ItemAdvice/ItemAdviceServiceV3.svc/restService/ItemLabel/'.$trackingNumber;
        $METHOD = 'GET';
        $POST_FIELDS = '{
            "Authentication": {
                "Password": "vcdgea",
                "Username": "user@LedsoneUKLtd"
            }
        }';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');

        $ContentType = "PDF";

        // $createDeliveryGroupOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
        $createDeliveryGroupOrder = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);

        $labelCreated = false;
        $error = "";
        $labelB64 = "";

        if($createDeliveryGroupOrder['responsecode'] == "200"){
            $labelB64 = $createDeliveryGroupOrder['responsearray'];

            if(substr($labelB64,0,5) == "JVBER"){
                $labelCreated = true;
            }
        }else{
            $error = "Error in Creating Delivery Group Label.";
        }
        
        return array("labelCreated" => $labelCreated, "labelB64" => $labelB64, "error" => $error);
    }

    function getWeightByshippingService($postalservice){
        if($postalservice == "245g LL"){
            $weightInGrams = "245";
        }else if($postalservice == "900g parcel"){
            $weightInGrams = "900";
        }else if($postalservice == "95g LL"){
            $weightInGrams = "95";
        }else if($postalservice == "BPL Royal Mail 1st Class Large Letter"){
            $weightInGrams = "95";
        }else if($postalservice == "CRL Royal Mail 24 Large Letter"){
            $weightInGrams = "245";
        }else if($postalservice == "CRL Royal Mail 24 Parcel"){
            $weightInGrams = "900";
        }else if($postalservice == "TPN Royal Mail Tracked 24 Non Signature"){
            $weightInGrams = "1000";
        }else if($postalservice == "express24"){
            $weightInGrams = "3000";
        }else if($postalservice == "Rm manual"){
            $weightInGrams = "245";
        }

        return $weightInGrams;
    }

    function updateTracking($connect){
        $tempOrders = 0;
        $updateTrackinginOrders = 0;

        $trackingUpdated = false;
        $info = "";
        $error = "";

        $query = "SELECT * FROM temporders";
        $result = mysqli_query($connect, $query);

        while ($row = mysqli_fetch_array($result)) {
            $tempOrders = $tempOrders + 1;

            $postal_service = $row["postal_service"];
            $shipment_id = $row["shipment_id"];
            $tracking_No = $row["tracking_No"];
            $royalmail_order_id = $row["royalmail_order_id"];

            $query = "UPDATE orders SET date='" . $row["date"] . "', shipment_id='$shipment_id', TrackingNumber='$tracking_No', PostalService='$postal_service', royalmail_order_id='$royalmail_order_id' WHERE orderID='" . $row["orderID"] . "' AND channel='" . $row["channel"] . "' AND firstname='" . $row["firstname"] . "' AND name='" . $row["name"] . "' AND sku='" . $row["sku"] . "'";
            $update = mysqli_query($connect, $query);

            if($update){
                $updateTrackinginOrders = $updateTrackinginOrders + 1;
            }
        }

        if($updateTrackinginOrders == $tempOrders){
            $trackingUpdated = true;
            $info = "Update tracking completed in system. automatically redirect after 1 second.";
        }else if($updateTrackinginOrders < $tempOrders AND $updateTrackinginOrders > 0){
            $trackingUpdated = true;
            $info = "Some orders missing and others tracking completed in system.";
        }else if($updateTrackinginOrders == 0){
            $error = "Update tracking failed in system.";
        }

        return array("trackingUpdated" => $trackingUpdated, "info" => $info, "error" => $error);
    }

    function authenticateShipStation(){
        // here username and password is ship station login credentials
        $username = "e6ef1ac0071049f2918a8526bae0259e";
        $password = "4680dd341ca9463b82fb925be1266183";

        $authString = basicAuth($username, $password);
        return $authString;
    }

    function genLabelForOrder($orderId, $orderPostalService){      
        $splitOrderID = explode(":",$orderId);  
        $orderId = $splitOrderID[0];

        if($orderPostalService == "express24"){
            $carrierCode = "parcelforce";
            $serviceCode = "Exp24";
            $packageCode = "package";
        }
        
        $confirmation = "none";
        $shipDate = date("Y-m-d");
        $testLabel = "false";
        
        $weight_units = "grams";
        $weight_WeightUnits = "2";
        
        $authString = authenticateShipStation();
        
        $URL = 'https://ssapi.shipstation.com/orders/createlabelfororder';
        $METHOD = 'POST';
        $POST_FIELDS = '{
            "orderId": '.$orderId.',
            "carrierCode": "'.$carrierCode.'",
            "serviceCode": "'.$serviceCode.'",
            "packageCode": "'.$packageCode.'",
            "confirmation": "'.$confirmation.'",
            "shipDate": "'.$shipDate.'",
            "testLabel": '.$testLabel.'
        }';

        // $POST_FIELDS = '{
        //     "orderId": '.$orderId.',
        //     "carrierCode": "'.$carrierCode.'",
        //     "serviceCode": "'.$serviceCode.'",
        //     "packageCode": "'.$packageCode.'",
        //     "confirmation": "'.$confirmation.'",
        //     "shipDate": "'.$shipDate.'",
        //     "testLabel": '.$testLabel.',
        //     "weight": {
        //         "value": '.$weight_value.',
        //         "units": "'.$weight_units.'",
        //         "WeightUnits": '.$weight_WeightUnits.'
        //     }
        // }';
        
        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');
        
        $createLabelForOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);
        
        $labelCreated = false;
        $createLabelForOrderTrackingNo = "";
        $createLabelForOrderLabelB64 = "";
        $createLabelForOrderShipmentID = "";
        $error = "";
        
        if($createLabelForOrder['responsecode']=="200"){
            $createLabelForOrderResponse = $createLabelForOrder['responsearray'];
        
            if(array_key_exists('trackingNumber',$createLabelForOrderResponse)){
                $labelCreated = true;
                $createLabelForOrderTrackingNo = $createLabelForOrderResponse['trackingNumber'];
                $createLabelForOrderLabelB64 = $createLabelForOrderResponse['labelData'];
                $createLabelForOrderShipmentID = $createLabelForOrderResponse['shipmentId'];
            }
        }else{
            $error = "Error in Creating Label from ship station.";
        }
        
        return array("labelCreated" => $labelCreated, "trackingNo" => $createLabelForOrderTrackingNo, "labelB64" => $createLabelForOrderLabelB64, "shipmentId" => $createLabelForOrderShipmentID, "error" => $createLabelForOrder);
    }

    /*
        get orders from ship station.
    */
    function getOrders(){
        $authString = authenticateShipStation();

        $URL = 'https://ssapi.shipstation.com/orders?sortBy=OrderDate&sortDir=ASC&page=1&pageSize=500&orderStatus=awaiting_shipment';
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $getOrders = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $ordersGot = false;
        $shipStationOrders = array();
        $shipStationOrderss = array();

        if($getOrders['responsecode']=="200"){
            $ordersGot = true;
            $getOrdersResponse = $getOrders['responsearray'];

            $shipStationOrderPages = $getOrdersResponse['pages'];
            $shipStationOrders = $getOrdersResponse['orders'];
            // array_push($shipStationOrders, $shipStationOrderssss);
            
            for($iterat = 2; $iterat <= $shipStationOrderPages; $iterat++){
                $URL_Loop = 'https://ssapi.shipstation.com/orders?sortBy=OrderDate&sortDir=ASC&page='.$iterat.'&pageSize=500&orderStatus=awaiting_shipment';

                $getOrders_Loop = fetchDataFromURL($URL_Loop, $METHOD, $POST_FIELDS, $HTTP_HEADER);
                if($getOrders_Loop['responsecode']=="200"){
                    $getOrdersResponse_Loop = $getOrders_Loop['responsearray'];

                    $shipStationOrderss = $getOrdersResponse_Loop['orders'];

                    $shipStationOrders = array_merge($shipStationOrders, $shipStationOrderss);
                }
            }
        }else{
            $error = "Error in getting orders from ship station.";
        }
        
        return array("ordersGot" => $ordersGot, "shipStationOrders" => $shipStationOrders, "error" => $error);
    }

    function getPrimeOrders(){
        $authString = authenticateShipStation();

        $URL_PRIME = 'https://ssapi.shipstation.com/orders/listbytag?orderStatus=pending_fulfillment&tagId=14432&page=1&pageSize=500';
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $getPrimeOrders = fetchDataFromURL($URL_PRIME, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $primeOrdersGot = false;
        $shipStationPrimeOrders = array();
        $shipStationPrimeOrderss = array();

        if($getPrimeOrders['responsecode']=="200"){
            $primeOrdersGot = true;
            $getPrimeOrdersResponse = $getPrimeOrders['responsearray'];

            $shipStationPrimeOrderPages = $getPrimeOrdersResponse['pages'];
            $shipStationPrimeOrders = $getPrimeOrdersResponse['orders'];
            // array_push($shipStationOrders, $shipStationOrderssss);
            
            for($iterat = 2; $iterat <= $shipStationPrimeOrderPages; $iterat++){
                $URL_PRIME_Loop = 'https://ssapi.shipstation.com/orders/listbytag?orderStatus=pending_fulfillment&tagId=14432&page='.$iterat.'&pageSize=500';

                $getPrimeOrders_Loop = fetchDataFromURL($URL_PRIME_Loop, $METHOD, $POST_FIELDS, $HTTP_HEADER);
                if($getPrimeOrders_Loop['responsecode']=="200"){
                    $getPrimeOrdersResponse_Loop = $getPrimeOrders_Loop['responsearray'];

                    $shipStationPrimeOrderss = $getPrimeOrdersResponse_Loop['orders'];

                    $shipStationPrimeOrders = array_merge($shipStationPrimeOrders, $shipStationPrimeOrderss);
                }
            }
        }else{
            $error = "Error in getting prime orders from ship station.";
        }
        
        return array("primeOrdersGot" => $primeOrdersGot, "shipStationPrimeOrders" => $shipStationPrimeOrders, "error" => $error);
    }

    /*
        get first class orders from ship station.
    */
    function getFirstClassOrders(){
        $authString = authenticateShipStation();

        $URL = 'https://ssapi.shipstation.com/orders?sortBy=OrderDate&sortDir=ASC&page=1&pageSize=500&orderStatus=awaiting_shipment';
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $getOrders = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $ordersGot = false;
        $shipStationOrders = array();
        $shipStationOrderss = array();

        if($getOrders['responsecode']=="200"){
            $ordersGot = true;
            $getOrdersResponse = $getOrders['responsearray'];

            $shipStationOrderPages = $getOrdersResponse['pages'];
            $shipStationOrders = $getOrdersResponse['orders'];
            // array_push($shipStationOrders, $shipStationOrderssss);
            
            for($iterat = 2; $iterat <= $shipStationOrderPages; $iterat++){
                $URL_Loop = 'https://ssapi.shipstation.com/orders?sortBy=OrderDate&sortDir=ASC&page='.$iterat.'&pageSize=500&orderStatus=awaiting_shipment';

                $getOrders_Loop = fetchDataFromURL($URL_Loop, $METHOD, $POST_FIELDS, $HTTP_HEADER);
                if($getOrders_Loop['responsecode']=="200"){
                    $getOrdersResponse_Loop = $getOrders_Loop['responsearray'];

                    $shipStationOrderss = $getOrdersResponse_Loop['orders'];

                    $shipStationOrders = array_merge($shipStationOrders, $shipStationOrderss);
                }
            }

            $like = "firstclass";

            $shipStationFirstClassOrders = array_filter($shipStationOrders, function ($orderDetaill) use ($like) {
                if ($orderDetaill['advancedOptions']['customField1'] == $like) {
                    return true;
                }
                return false;
            });
        }else{
            $error = "Error in getting first class orders from ship station.";
        }
        
        return array("firstClassOrdersGot" => $ordersGot, "shipStationFirstClassOrders" => $shipStationFirstClassOrders, "error" => $error);
    }

    /*
        get store details from ship station.
    */
    function getStoreInfo($storeId){
        $authString = authenticateShipStation();

        $URL = 'https://ssapi.shipstation.com/stores/'.$storeId;
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $getStoreInfo = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $storeInfoGot = false;

        if($getStoreInfo['responsecode']=="200"){
            $storeInfoGot = true;
            $getStoreInfoResponse = $getStoreInfo['responsearray'];

            $shipStationOrderStoreName = $getStoreInfoResponse['storeName'];
            $shipStationOrderMarketPlaceName = $getStoreInfoResponse['marketplaceName'];
        }else{
            $error = "Error in getting store details from ship station.";
        }
        
        return array("storeInfoGot" => $storeInfoGot, "shipStationOrderStoreName" => $shipStationOrderStoreName, "shipStationOrderMarketPlaceName" => $shipStationOrderMarketPlaceName, "error" => $error);
    }

    /*
        refresh store from ship station.
    */
    function refreshStore(){
        $authString = authenticateShipStation();
        $error = "";
        $URL = 'https://ssapi.shipstation.com/stores/refreshstore';
        $METHOD = 'POST';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $refreshStore = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $refreshStoreHappen = false;

        if($refreshStore['responsecode']=="200"){
            $refreshStoreResponse = $refreshStore['responsearray'];

            $refreshStoreHappen = $refreshStoreResponse['success'];

            if(!$refreshStoreHappen){
                $error = $refreshStoreResponse['message'];
            }
        }else{
            $error = "Error in getting store details from ship station.";
        }
        
        return array("refreshStoreHappen" => $refreshStoreHappen, "error" => $error);
    }

    function getShipstationOrderByID($orderId){
        $authString = authenticateShipStation();

        $URL = 'https://ssapi.shipstation.com/orders/'.$orderId;
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');

        $getOrderInfo = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

        $orderInfoGot = false;
        $shipStationOrderOrderStatus = "";
        $error = "";

        if($getOrderInfo['responsecode']=="200"){
            $orderInfoGot = true;
            $getOrderInfoResponse = $getOrderInfo['responsearray'];
            
            if(array_key_exists('orderStatus',$getOrderInfoResponse)){
                $orderInfoGot = true;
                $shipStationOrderOrderStatus = $getOrderInfoResponse['orderStatus'];
            }
            
        }else{
            $error = "Error in getting order details from ship station.";
        }
        
        return array("orderInfoGot" => $orderInfoGot, "shipStationOrderOrderStatus" => $shipStationOrderOrderStatus, "error" => $error);
    }

    function MarkAsShipped($orderId, $postalService, $shipDate, $trackingNumber, $channel){
        $getShipstationOrderByID = getShipstationOrderByID($orderId);

        $markAsShipped = false;
        $error = "";

        if($getShipstationOrderByID['orderInfoGot'] && $getShipstationOrderByID['shipStationOrderOrderStatus'] != "shipped"){
            $authString = authenticateShipStation();

            if($postalService == "245g LL" || $postalService == "900g parcel" || $postalService == "95g LL" || $postalService == "BPL Royal Mail 1st Class Large Letter" || $postalService == "CRL Royal Mail 24 Large Letter" || $postalService == "CRL Royal Mail 24 Parcel" || $postalService == "TPN Royal Mail Tracked 24 Non Signature" || $postalService == "Rm manual"){
                $carrierCode = "royal_mail";
            }else if($postalService == "ParcelDenOnline Standard Package" || $postalService == "ParcelDenOnline Standard Parcel" || $postalService == "Hermes ParcelShop Postable (Shop To Door) by MyHermes"){
                $carrierCode = "hermes";
            }else if($postalService == "express24"){
                $carrierCode = "parcelforce";
            }
            
            $URL = 'https://ssapi.shipstation.com/orders/markasshipped';
            $METHOD = 'POST';
            
            if (strpos($channel, 'SHOPIFY') !== false) {
                $POST_FIELDS = '{
                    "orderId": '.$orderId.',
                    "carrierCode": "'.$carrierCode.'",
                    "shipDate": "'.$shipDate.'",
                    "trackingNumber": "'.$trackingNumber.'",
                    "notifyCustomer": false,
                    "notifySalesChannel": false
                }';
            }else{
                $POST_FIELDS = '{
                    "orderId": '.$orderId.',
                    "carrierCode": "'.$carrierCode.'",
                    "shipDate": "'.$shipDate.'",
                    "trackingNumber": "'.$trackingNumber.'",
                    "notifyCustomer": true,
                    "notifySalesChannel": true
                }';
            }
            
            $HTTP_HEADER = array();
            array_push($HTTP_HEADER,'Authorization: Basic '.$authString,'Content-Type: application/json');
            
            $markAsShippedOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);
            
            if($markAsShippedOrder['responsecode']=="200"){
                $markAsShippedOrderResponse = $markAsShippedOrder['responsearray'];
                
                if(array_key_exists('orderId',$markAsShippedOrderResponse)){
                    $markAsShipped = true;
                    $markAsShippedOrderResponseOrderID = $markAsShippedOrderResponse['orderId'];
                }
            }else{
                $error = "Error in mark as shipped to ship station.";
            }
        }else if($getShipstationOrderByID['orderInfoGot'] && $getShipstationOrderByID['shipStationOrderOrderStatus'] == "shipped"){
            $markAsShipped = true;
            $markAsShippedOrderResponseOrderID = $orderId;
        }else{
            $error = $getShipstationOrderByID['error'];
        }
        
        return array("markAsShipped" => $markAsShipped, "markAsShippedOrderResponseOrderID" => $markAsShippedOrderResponseOrderID, "error" => $error);
    }

    function divideName($name){
        $names = explode(" ",$name);

        $first_name = "";
        $last_name = "";

        foreach ($names as $key => $name) {
            if($key == 0){
                $first_name .= $name;
            }else{
                if($key == 0){
                    $last_name .= $name;
                }else{
                    $last_name .= " ".$name;
                }
            }
            # code...
        }

        return array("firstName" => $first_name, "lastName" => $last_name);
    }

    //to update Data
    function updateData($table,$fields,$datas,$field,$data,$con){
        foreach( $fields as $index => $singlefield ) {
            $field_data[] = '`'.$singlefield.'`="'.$datas[$index].'"';
        }

        $field_data_values= implode(', ',$field_data);

        $sql='UPDATE `'.$table.'` SET '.$field_data_values.' WHERE '.$field.'='.$data.'';
        $result=$con->query($sql);
        
        return $result;
    }

    //to add Data
    function addData($table,$fields,$datas,$con){
        foreach ($fields as $singlefield) {
            $new_field[] = '`'.$singlefield.'`';
        }

        foreach ($datas as $singledata) {
            $new_data[] = '"'.$singledata.'"';
        }

        $field_values= implode(',',$new_field);
        $data_values=implode(',',$new_data);

        $sql='INSERT INTO `'.$table.'`('.$field_values.') VALUES('.$data_values.')';
        $result=$con->query($sql);
        
        return $result;
    }

    //to delete Data
    function deleteData($table,$field,$data,$con){
        $sql='DELETE FROM `'.$table.'` WHERE '.$field.'='.$data.'';
        $result=$con->query($sql);
            
        return $result;
    }

    // to find country code by country name
    function findCountryCode($country){
        $country = trim($country);
        // $countryCodes = array('United Kingdom'=>'UK', 'Germany'=>'DE');
        $countryCodes = array("Afghanistan" => "AF","Aland Islands" => "AX","Albania" => "AL","Algeria" => "DZ","American Samoa" => "AS","Andorra" => "AD","Angola" => "AO","Anguilla" => "AI","Antarctica" => "AQ","Antigua and Barbuda" => "AG","Argentina" => "AR","Armenia" => "AM","Aruba" => "AW","Australia" => "AU","Austria" => "AT","Azerbaijan" => "AZ","Bahamas" => "BS","Bahrain" => "BH","Bangladesh" => "BD","Barbados" => "BB","Belarus" => "BY","Belgium" => "BE","Belize" => "BZ","Benin" => "BJ","Bermuda" => "BM","Bhutan" => "BT","Bolivia" => "BO","Bonaire, Sint Eustatius and Saba" => "BQ","Bosnia and Herzegovina" => "BA","Botswana" => "BW","Bouvet Island" => "BV","Brazil" => "BR","British Indian Ocean Territory" => "IO","Brunei Darussalam" => "BN","Bulgaria" => "BG","Burkina Faso" => "BF","Burundi" => "BI","Cambodia" => "KH","Cameroon" => "CM","Canada" => "CA","Cape Verde" => "CV","Cayman Islands" => "KY","Central African Republic" => "CF","Chad" => "TD","Chile" => "CL","China" => "CN","Christmas Island" => "CX","Cocos (Keeling) Islands" => "CC","Colombia" => "CO","Comoros" => "KM","Congo" => "CG","Congo, Democratic Republic of the Congo" => "CD","Cook Islands" => "CK","Costa Rica" => "CR","Cote D'Ivoire" => "CI","Croatia" => "HR","Cuba" => "CU","Curacao" => "CW","Cyprus" => "CY","Czech Republic" => "CZ","Denmark" => "DK","Djibouti" => "DJ","Dominica" => "DM","Dominican Republic" => "DO","Ecuador" => "EC","Egypt" => "EG","El Salvador" => "SV","Equatorial Guinea" => "GQ","Eritrea" => "ER","Estonia" => "EE","Ethiopia" => "ET","Falkland Islands (Malvinas)" => "FK","Faroe Islands" => "FO","Fiji" => "FJ","Finland" => "FI","France" => "FR","French Guiana" => "GF","French Polynesia" => "PF","French Southern Territories" => "TF","Gabon" => "GA","Gambia" => "GM","Georgia" => "GE","Germany" => "DE","Ghana" => "GH","Gibraltar" => "GI","Greece" => "GR","Greenland" => "GL","Grenada" => "GD","Guadeloupe" => "GP","Guam" => "GU","Guatemala" => "GT","Guernsey" => "GG","Guinea" => "GN","Guinea-Bissau" => "GW","Guyana" => "GY","Haiti" => "HT","Heard Island and Mcdonald Islands" => "HM","Holy See (Vatican City State)" => "VA","Honduras" => "HN","Hong Kong" => "HK","Hungary" => "HU","Iceland" => "IS","India" => "IN","Indonesia" => "ID","Iran, Islamic Republic of" => "IR","Iraq" => "IQ","Ireland" => "IE","Isle of Man" => "IM","Israel" => "IL","Italy" => "IT","Jamaica" => "JM","Japan" => "JP","Jersey" => "JE","Jordan" => "JO","Kazakhstan" => "KZ","Kenya" => "KE","Kiribati" => "KI","Korea, Democratic People's Republic of" => "KP","Korea, Republic of" => "KR","Kosovo" => "XK","Kuwait" => "KW","Kyrgyzstan" => "KG","Lao People's Democratic Republic" => "LA","Latvia" => "LV","Lebanon" => "LB","Lesotho" => "LS","Liberia" => "LR","Libyan Arab Jamahiriya" => "LY","Liechtenstein" => "LI","Lithuania" => "LT","Luxembourg" => "LU","Macao" => "MO","Macedonia, the Former Yugoslav Republic of" => "MK","Madagascar" => "MG","Malawi" => "MW","Malaysia" => "MY","Maldives" => "MV","Mali" => "ML","Malta" => "MT","Marshall Islands" => "MH","Martinique" => "MQ","Mauritania" => "MR","Mauritius" => "MU","Mayotte" => "YT","Mexico" => "MX","Micronesia, Federated States of" => "FM","Moldova, Republic of" => "MD","Monaco" => "MC","Mongolia" => "MN","Montenegro" => "ME","Montserrat" => "MS","Morocco" => "MA","Mozambique" => "MZ","Myanmar" => "MM","Namibia" => "NA","Nauru" => "NR","Nepal" => "NP","Netherlands" => "NL","Netherlands Antilles" => "AN","New Caledonia" => "NC","New Zealand" => "NZ","Nicaragua" => "NI","Niger" => "NE","Nigeria" => "NG","Niue" => "NU","Norfolk Island" => "NF","Northern Mariana Islands" => "MP","Norway" => "NO","Oman" => "OM","Pakistan" => "PK","Palau" => "PW","Palestinian Territory, Occupied" => "PS","Panama" => "PA","Papua New Guinea" => "PG","Paraguay" => "PY","Peru" => "PE","Philippines" => "PH","Pitcairn" => "PN","Poland" => "PL","Portugal" => "PT","Puerto Rico" => "PR","Qatar" => "QA","Reunion" => "RE","Romania" => "RO","Russian Federation" => "RU","Rwanda" => "RW","Saint Barthelemy" => "BL","Saint Helena" => "SH","Saint Kitts and Nevis" => "KN","Saint Lucia" => "LC","Saint Martin" => "MF","Saint Pierre and Miquelon" => "PM","Saint Vincent and the Grenadines" => "VC","Samoa" => "WS","San Marino" => "SM","Sao Tome and Principe" => "ST","Saudi Arabia" => "SA","Senegal" => "SN","Serbia" => "RS","Serbia and Montenegro" => "CS","Seychelles" => "SC","Sierra Leone" => "SL","Singapore" => "SG","Sint Maarten" => "SX","Slovakia" => "SK","Slovenia" => "SI","Solomon Islands" => "SB","Somalia" => "SO","South Africa" => "ZA","South Georgia and the South Sandwich Islands" => "GS","South Sudan" => "SS","Spain" => "ES","Sri Lanka" => "LK","Sudan" => "SD","Suriname" => "SR","Svalbard and Jan Mayen" => "SJ","Swaziland" => "SZ","Sweden" => "SE","Switzerland" => "CH","Syrian Arab Republic" => "SY","Taiwan, Province of China" => "TW","Tajikistan" => "TJ","Tanzania, United Republic of" => "TZ","Thailand" => "TH","Timor-Leste" => "TL","Togo" => "TG","Tokelau" => "TK","Tonga" => "TO","Trinidad and Tobago" => "TT","Tunisia" => "TN","Turkey" => "TR","Turkmenistan" => "TM","Turks and Caicos Islands" => "TC","Tuvalu" => "TV","Uganda" => "UG","Ukraine" => "UA","United Arab Emirates" => "AE","United Kingdom" => "GB","United States" => "US","United States Minor Outlying Islands" => "UM","Uruguay" => "UY","Uzbekistan" => "UZ","Vanuatu" => "VU","Venezuela" => "VE","Viet Nam" => "VN","Virgin Islands, British" => "VG","Virgin Islands, U.s." => "VI","Wallis and Futuna" => "WF","Western Sahara" => "EH","Yemen" => "YE","Zambia" => "ZM","Zimbabwe" => "ZW", "Great Britain" => "GB", "Deutschland" => "DE");
        
        $realCountryCode = $countryCodes[$country];

        return $realCountryCode;
    }

    // to find country by country code
    function findCountryByCountryCode($countryCode){
        $countryCode = trim($countryCode);
        // $countryCodes = array('United Kingdom'=>'UK', 'Germany'=>'DE');
        $country = array("AF" => "Afghanistan","AX" => "Aland Islands","AL" => "Albania","DZ" => "Algeria","AS" => "American Samoa","AD" => "Andorra","AO" => "Angola","AI" => "Anguilla","AQ" => "Antarctica","AG" => "Antigua and Barbuda","AR" => "Argentina","AM" => "Armenia","AW" => "Aruba","AU" => "Australia","AT" => "Austria","AZ" => "Azerbaijan","BS" => "Bahamas","BH" => "Bahrain","BD" => "Bangladesh","BB" => "Barbados","BY" => "Belarus","BE" => "Belgium","BZ" => "Belize","BJ" => "Benin","BM" => "Bermuda","BT" => "Bhutan","BO" => "Bolivia","BQ" => "Bonaire, Sint Eustatius and Saba","BA" => "Bosnia and Herzegovina","BW" => "Botswana","BV" => "Bouvet Island","BR" => "Brazil","IO" => "British Indian Ocean Territory","BN" => "Brunei Darussalam","BG" => "Bulgaria","BF" => "Burkina Faso","BI" => "Burundi","KH" => "Cambodia","CM" => "Cameroon","CA" => "Canada","CV" => "Cape Verde","KY" => "Cayman Islands","CF" => "Central African Republic","TD" => "Chad","CL" => "Chile","CN" => "China","CX" => "Christmas Island","CC" => "Cocos (Keeling) Islands","CO" => "Colombia","KM" => "Comoros","CG" => "Congo","CD" => "Congo, Democratic Republic of the Congo","CK" => "Cook Islands","CR" => "Costa Rica","CI" => "Cote D'Ivoire","HR" => "Croatia","CU" => "Cuba","CW" => "Curacao","CY" => "Cyprus","CZ" => "Czech Republic","DK" => "Denmark","DJ" => "Djibouti","DM" => "Dominica","DO" => "Dominican Republic","EC" => "Ecuador","EG" => "Egypt","SV" => "El Salvador","GQ" => "Equatorial Guinea","ER" => "Eritrea","EE" => "Estonia","ET" => "Ethiopia","FK" => "Falkland Islands (Malvinas)","FO" => "Faroe Islands","FJ" => "Fiji","FI" => "Finland","FR" => "France","GF" => "French Guiana","PF" => "French Polynesia","TF" => "French Southern Territories","GA" => "Gabon","GM" => "Gambia","GE" => "Georgia","DE" => "Germany","GH" => "Ghana","GI" => "Gibraltar","GR" => "Greece","GL" => "Greenland","GD" => "Grenada","GP" => "Guadeloupe","GU" => "Guam","GT" => "Guatemala","GG" => "Guernsey","GN" => "Guinea","GW" => "Guinea-Bissau","GY" => "Guyana","HT" => "Haiti","HM" => "Heard Island and Mcdonald Islands","VA" => "Holy See (Vatican City State)","HN" => "Honduras","HK" => "Hong Kong","HU" => "Hungary","IS" => "Iceland","IN" => "India","ID" => "Indonesia","IR" => "Iran, Islamic Republic of","IQ" => "Iraq","IE" => "Ireland","IM" => "Isle of Man","IL" => "Israel","IT" => "Italy","JM" => "Jamaica","JP" => "Japan","JE" => "Jersey","JO" => "Jordan","KZ" => "Kazakhstan","KE" => "Kenya","KI" => "Kiribati","KP" => "Korea, Democratic People's Republic of","KR" => "Korea, Republic of","XK" => "Kosovo","KW" => "Kuwait","KG" => "Kyrgyzstan","LA" => "Lao People's Democratic Republic","LV" => "Latvia","LB" => "Lebanon","LS" => "Lesotho","LR" => "Liberia","LY" => "Libyan Arab Jamahiriya","LI" => "Liechtenstein","LT" => "Lithuania","LU" => "Luxembourg","MO" => "Macao","MK" => "Macedonia, the Former Yugoslav Republic of","MG" => "Madagascar","MW" => "Malawi","MY" => "Malaysia","MV" => "Maldives","ML" => "Mali","MT" => "Malta","MH" => "Marshall Islands","MQ" => "Martinique","MR" => "Mauritania","MU" => "Mauritius","YT" => "Mayotte","MX" => "Mexico","FM" => "Micronesia, Federated States of","MD" => "Moldova, Republic of","MC" => "Monaco","MN" => "Mongolia","ME" => "Montenegro","MS" => "Montserrat","MA" => "Morocco","MZ" => "Mozambique","MM" => "Myanmar","NA" => "Namibia","NR" => "Nauru","NP" => "Nepal","NL" => "Netherlands","AN" => "Netherlands Antilles","NC" => "New Caledonia","NZ" => "New Zealand","NI" => "Nicaragua","NE" => "Niger","NG" => "Nigeria","NU" => "Niue","NF" => "Norfolk Island","MP" => "Northern Mariana Islands","NO" => "Norway","OM" => "Oman","PK" => "Pakistan","PW" => "Palau","PS" => "Palestinian Territory, Occupied","PA" => "Panama","PG" => "Papua New Guinea","PY" => "Paraguay","PE" => "Peru","PH" => "Philippines","PN" => "Pitcairn","PL" => "Poland","PT" => "Portugal","PR" => "Puerto Rico","QA" => "Qatar","RE" => "Reunion","RO" => "Romania","RU" => "Russian Federation","RW" => "Rwanda","BL" => "Saint Barthelemy","SH" => "Saint Helena","KN" => "Saint Kitts and Nevis","LC" => "Saint Lucia","MF" => "Saint Martin","PM" => "Saint Pierre and Miquelon","VC" => "Saint Vincent and the Grenadines","WS" => "Samoa","SM" => "San Marino","ST" => "Sao Tome and Principe","SA" => "Saudi Arabia","SN" => "Senegal","RS" => "Serbia","CS" => "Serbia and Montenegro","SC" => "Seychelles","SL" => "Sierra Leone","SG" => "Singapore","SX" => "Sint Maarten","SK" => "Slovakia","SI" => "Slovenia","SB" => "Solomon Islands","SO" => "Somalia","ZA" => "South Africa","GS" => "South Georgia and the South Sandwich Islands","SS" => "South Sudan","ES" => "Spain","LK" => "Sri Lanka","SD" => "Sudan","SR" => "Suriname","SJ" => "Svalbard and Jan Mayen","SZ" => "Swaziland","SE" => "Sweden","CH" => "Switzerland","SY" => "Syrian Arab Republic","TW" => "Taiwan, Province of China","TJ" => "Tajikistan","TZ" => "Tanzania, United Republic of","TH" => "Thailand","TL" => "Timor-Leste","TG" => "Togo","TK" => "Tokelau","TO" => "Tonga","TT" => "Trinidad and Tobago","TN" => "Tunisia","TR" => "Turkey","TM" => "Turkmenistan","TC" => "Turks and Caicos Islands","TV" => "Tuvalu","UG" => "Uganda","UA" => "Ukraine","AE" => "United Arab Emirates","GB" => "United Kingdom","US" => "United States","UM" => "United States Minor Outlying Islands","UY" => "Uruguay","UZ" => "Uzbekistan","VU" => "Vanuatu","VE" => "Venezuela","VN" => "Viet Nam","VG" => "Virgin Islands, British","VI" => "Virgin Islands, U.s.","WF" => "Wallis and Futuna","EH" => "Western Sahara","YE" => "Yemen","ZM" => "Zambia","ZW" => "Zimbabwe");
        
        $realCountry = $country[$countryCode];

        return $realCountry;
    }

    // to find currency by country code
    function findCurrencyByCountryCode($countryCode){
        $countryCode = trim($countryCode);
        // $countryCodes = array('United Kingdom'=>'UK', 'Germany'=>'DE');
        $country = array("AF" => "AFN", "AX" => "EUR", "AL" => "ALL", "DZ" => "DZD", "AS" => "USD", "AD" => "EUR", "AO" => "AOA", "AI" => "XCD", "AQ" => "AAD", "AG" => "XCD", "AR" => "ARS", "AM" => "AMD", "AW" => "AWG", "AU" => "AUD", "AT" => "EUR", "AZ" => "AZN", "BS" => "BSD", "BH" => "BHD", "BD" => "BDT", "BB" => "BBD", "BY" => "BYN", "BE" => "EUR", "BZ" => "BZD", "BJ" => "XOF", "BM" => "BMD", "BT" => "BTN", "BO" => "BOB", "BQ" => "USD", "BA" => "BAM", "BW" => "BWP", "BV" => "NOK", "BR" => "BRL", "IO" => "USD", "BN" => "BND", "BG" => "BGN", "BF" => "XOF", "BI" => "BIF", "KH" => "KHR", "CM" => "XAF", "CA" => "CAD", "CV" => "CVE", "KY" => "KYD", "CF" => "XAF", "TD" => "XAF", "CL" => "CLP", "CN" => "CNY", "CX" => "AUD", "CC" => "AUD", "CO" => "COP", "KM" => "KMF", "CG" => "XAF", "CD" => "CDF", "CK" => "NZD", "CR" => "CRC", "CI" => "XOF", "HR" => "HRK", "CU" => "CUP", "CW" => "ANG", "CY" => "EUR", "CZ" => "CZK", "DK" => "DKK", "DJ" => "DJF", "DM" => "XCD", "DO" => "DOP", "EC" => "USD", "EG" => "EGP", "SV" => "USD", "GQ" => "XAF", "ER" => "ERN", "EE" => "EUR", "ET" => "ETB", "FK" => "FKP", "FO" => "DKK", "FJ" => "FJD", "FI" => "EUR", "FR" => "EUR", "GF" => "EUR", "PF" => "XPF", "TF" => "EUR", "GA" => "XAF", "GM" => "GMD", "GE" => "GEL", "DE" => "EUR", "GH" => "GHS", "GI" => "GIP", "GR" => "EUR", "GL" => "DKK", "GD" => "XCD", "GP" => "EUR", "GU" => "USD", "GT" => "GTQ", "GG" => "GBP", "GN" => "GNF", "GW" => "XOF", "GY" => "GYD", "HT" => "HTG", "HM" => "AUD", "VA" => "EUR", "HN" => "HNL", "HK" => "HKD", "HU" => "HUF", "IS" => "ISK", "IN" => "INR", "ID" => "IDR", "IR" => "IRR", "IQ" => "IQD", "IE" => "EUR", "IM" => "GBP", "IL" => "ILS", "IT" => "EUR", "JM" => "JMD", "JP" => "JPY", "JE" => "GBP", "JO" => "JOD", "KZ" => "KZT", "KE" => "KES", "KI" => "AUD", "KP" => "KPW", "KR" => "KRW", "XK" => "EUR", "KW" => "KWD", "KG" => "KGS", "LA" => "LAK", "LV" => "EUR", "LB" => "LBP", "LS" => "LSL", "LR" => "LRD", "LY" => "LYD", "LI" => "CHF", "LT" => "EUR", "LU" => "EUR", "MO" => "MOP", "MK" => "MKD", "MG" => "MGA", "MW" => "MWK", "MY" => "MYR", "MV" => "MVR", "ML" => "XOF", "MT" => "EUR", "MH" => "USD", "MQ" => "EUR", "MR" => "MRO", "MU" => "MUR", "YT" => "EUR", "MX" => "MXN", "FM" => "USD", "MD" => "MDL", "MC" => "EUR", "MN" => "MNT", "ME" => "EUR", "MS" => "XCD", "MA" => "MAD", "MZ" => "MZN", "MM" => "MMK", "NA" => "NAD", "NR" => "AUD", "NP" => "NPR", "NL" => "EUR", "AN" => "ANG", "NC" => "XPF", "NZ" => "NZD", "NI" => "NIO", "NE" => "XOF", "NG" => "NGN", "NU" => "NZD", "NF" => "AUD", "MP" => "USD", "NO" => "NOK", "OM" => "OMR", "PK" => "PKR", "PW" => "USD", "PS" => "ILS", "PA" => "PAB", "PG" => "PGK", "PY" => "PYG", "PE" => "PEN", "PH" => "PHP", "PN" => "NZD", "PL" => "PLN", "PT" => "EUR", "PR" => "USD", "QA" => "QAR", "RE" => "EUR", "RO" => "RON", "RU" => "RUB", "RW" => "RWF", "BL" => "EUR", "SH" => "SHP", "KN" => "XCD", "LC" => "XCD", "MF" => "EUR", "PM" => "EUR", "VC" => "XCD", "WS" => "WST", "SM" => "EUR", "ST" => "STD", "SA" => "SAR", "SN" => "XOF", "RS" => "RSD", "CS" => "RSD", "SC" => "SCR", "SL" => "SLL", "SG" => "SGD", "SX" => "ANG", "SK" => "EUR", "SI" => "EUR", "SB" => "SBD", "SO" => "SOS", "ZA" => "ZAR", "GS" => "GBP", "SS" => "SSP", "ES" => "EUR", "LK" => "LKR", "SD" => "SDG", "SR" => "SRD", "SJ" => "NOK", "SZ" => "SZL", "SE" => "SEK", "CH" => "CHF", "SY" => "SYP", "TW" => "TWD", "TJ" => "TJS", "TZ" => "TZS", "TH" => "THB", "TL" => "USD", "TG" => "XOF", "TK" => "NZD", "TO" => "TOP", "TT" => "TTD", "TN" => "TND", "TR" => "TRY", "TM" => "TMT", "TC" => "USD", "TV" => "AUD", "UG" => "UGX", "UA" => "UAH", "AE" => "AED", "GB" => "GBP", "US" => "USD", "UM" => "USD", "UY" => "UYU", "UZ" => "UZS", "VU" => "VUV", "VE" => "VEF", "VN" => "VND", "VG" => "USD", "VI" => "USD", "WF" => "XPF", "EH" => "MAD", "YE" => "YER", "ZM" => "ZMW", "ZW" => "ZWL");
        
        $realCountry = $country[$countryCode];

        return $realCountry;
    }

    // to find currency by country code
    function findCountryCode3ByCountryCode($countryCode){
        $countryCode = trim($countryCode);
        // $countryCodes = array('United Kingdom'=>'UK', 'Germany'=>'DE');
        // $country = array("GB" => "GBR");
        $country = array("AF" => "AFG", "AL" => "ALB", "DZ" => "DZA", "AS" => "ASM", "AD" => "AND", "AO" => "AGO", "AI" => "AIA", "AQ" => "ATA", "AG" => "ATG", "AR" => "ARG", "AM" => "ARM", "AW" => "ABW", "AU" => "AUS", "AT" => "AUT", "AZ" => "AZE", "BS" => "BHS", "BH" => "BHR", "BD" => "BGD", "BB" => "BRB", "BY" => "BLR", "BE" => "BEL", "BZ" => "BLZ", "BJ" => "BEN", "BM" => "BMU", "BT" => "BTN", "BO" => "BOL", "BQ" => "BES", "BA" => "BIH", "BW" => "BWA", "BV" => "BVT", "BR" => "BRA", "BN" => "BRN", "BG" => "BGR", "BF" => "BFA", "BI" => "BDI", "CV" => "CPV", "KH" => "KHM", "CM" => "CMR", "CA" => "CAN", "KY" => "CYM", "CF" => "CAF", "TD" => "TCD", "CL" => "CHL", "CN" => "CHN", "CX" => "CXR", "CC" => "CCK", "CO" => "COL", "KM" => "COM", "CD" => "COD", "CG" => "COG", "CK" => "COK", "CR" => "CRI", "HR" => "HRV", "CU" => "CUB", "CW" => "CUW", "CY" => "CYP", "CZ" => "CZE", "CI" => "CIV", "DK" => "DNK", "DJ" => "DJI", "DM" => "DMA", "DO" => "DOM", "EC" => "ECU", "EG" => "EGY", "SV" => "SLV", "GQ" => "GNQ", "ER" => "ERI", "EE" => "EST", "SZ" => "SWZ", "ET" => "ETH", "FO" => "FRO", "FJ" => "FJI", "FI" => "FIN", "FR" => "FRA", "GF" => "GUF", "PF" => "PYF", "TF" => "ATF", "GA" => "GAB", "GM" => "GMB", "GE" => "GEO", "DE" => "DEU", "GH" => "GHA", "GI" => "GIB", "GR" => "GRC", "GL" => "GRL", "GD" => "GRD", "GP" => "GLP", "GU" => "GUM", "GT" => "GTM", "GG" => "GGY", "GN" => "GIN", "GW" => "GNB", "GY" => "GUY", "HT" => "HTI", "HM" => "HMD", "VA" => "VAT", "HN" => "HND", "HK" => "HKG", "HU" => "HUN", "IS" => "ISL", "IN" => "IND", "ID" => "IDN", "IR" => "IRN", "IQ" => "IRQ", "IE" => "IRL", "IM" => "IMN", "IL" => "ISR", "IT" => "ITA", "JM" => "JAM", "JP" => "JPN", "JE" => "JEY", "JO" => "JOR", "KZ" => "KAZ", "KE" => "KEN", "KI" => "KIR", "KP" => "PRK", "KR" => "KOR", "KW" => "KWT", "KG" => "KGZ", "LA" => "LAO", "LV" => "LVA", "LB" => "LBN", "LS" => "LSO", "LR" => "LBR", "LY" => "LBY", "LI" => "LIE", "LT" => "LTU", "LU" => "LUX", "MO" => "MAC", "MG" => "MDG", "MW" => "MWI", "MY" => "MYS", "MV" => "MDV", "ML" => "MLI", "MT" => "MLT", "MH" => "MHL", "MQ" => "MTQ", "MR" => "MRT", "MU" => "MUS", "YT" => "MYT", "MX" => "MEX", "FM" => "FSM", "MD" => "MDA", "MC" => "MCO", "MN" => "MNG", "ME" => "MNE", "MS" => "MSR", "MA" => "MAR", "MZ" => "MOZ", "MM" => "MMR", "NA" => "NAM", "NR" => "NRU", "NP" => "NPL", "NL" => "NLD", "NC" => "NCL", "NZ" => "NZL", "NI" => "NIC", "NE" => "NER", "NG" => "NGA", "NU" => "NIU", "NF" => "NFK", "MK" => "MKD", "MP" => "MNP", "NO" => "NOR", "OM" => "OMN", "PK" => "PAK", "PW" => "PLW", "PS" => "PSE", "PA" => "PAN", "PG" => "PNG", "PY" => "PRY", "PE" => "PER", "PH" => "PHL", "PN" => "PCN", "PL" => "POL", "PT" => "PRT", "PR" => "PRI", "QA" => "QAT", "RO" => "ROU", "RU" => "RUS", "RW" => "RWA", "RE" => "REU", "BL" => "BLM", "SH" => "SHN", "KN" => "KNA", "LC" => "LCA", "MF" => "MAF", "PM" => "SPM", "VC" => "VCT", "WS" => "WSM", "SM" => "SMR", "ST" => "STP", "SA" => "SAU", "SN" => "SEN", "RS" => "SRB", "SC" => "SYC", "SL" => "SLE", "SG" => "SGP", "SX" => "SXM", "SK" => "SVK", "SI" => "SVN", "SB" => "SLB", "SO" => "SOM", "ZA" => "ZAF", "GS" => "SGS", "SS" => "SSD", "ES" => "ESP", "LK" => "LKA", "SD" => "SDN", "SR" => "SUR", "SJ" => "SJM", "SE" => "SWE", "CH" => "CHE", "SY" => "SYR", "TW" => "TWN", "TJ" => "TJK", "TZ" => "TZA", "TH" => "THA", "TL" => "TLS", "TG" => "TGO", "TK" => "TKL", "TO" => "TON", "TT" => "TTO", "TN" => "TUN", "TR" => "TUR", "TM" => "TKM", "TC" => "TCA", "TV" => "TUV", "UG" => "UGA", "UA" => "UKR", "AE" => "ARE", "GB" => "GBR", "UM" => "UMI", "US" => "USA", "UY" => "URY", "UZ" => "UZB", "VU" => "VUT", "VE" => "VEN", "VN" => "VNM", "VG" => "VGB", "VI" => "VIR", "WF" => "WLF", "EH" => "ESH", "YE" => "YEM", "ZM" => "ZMB", "ZW" => "ZWE", "AX" => "ALA");
        
        $countryCode3 = $country[$countryCode];

        return $countryCode3;
    }

    // divide address as number and rest string
    function divideAddress1($keyword){
        $number = "";
        $restString = "";
        $stringNotFound = true;
        $addressDivide = false;

        $keyword = trim($keyword);
        $keyword = str_replace("  "," ",$keyword);
      
        for ($i = 0; $i <= strlen($keyword)-1; $i++) {
            if(is_numeric($keyword[$i]) && $stringNotFound)  {
               $number .= $keyword[$i];
               $lastKey = $i + 1;
            }else{
                $stringNotFound = false;
            }
        }
      
        for ($i = $lastKey; $i <= strlen($keyword)-1; $i++) {
            $restString .= $keyword[$i];
        }
      
        $restString = trim($restString);
        $number = trim($number);
        
        if($number != "" && $restString != ""){
            $addressDivide = true;
        }

        if(!$addressDivide){
            $explodeAddress = explode(" ",$keyword, 2);
            $number = $explodeAddress[0];
            $restString = $explodeAddress[1];

            if($number != "" && $restString != ""){
                $addressDivide = true;
            }
        }

        $restString = trim($restString);
        $number = trim($number);

        if(!$addressDivide){
            if(strlen($keyword) > 10){
                $number = ".";
                $restString = $keyword;
            }

            if($number != "" && $restString != ""){
                $addressDivide = true;
            }
        }

        $restString = trim($restString);
        $number = trim($number);
        
        return array("addressDivide" => $addressDivide,"firstNumbers" => $number, "restString" => $restString);
    }

    function sendMail($from, $to, $Msg){
        $email_subject = "Error message from demo page";
        
        $email_body = "Hello Postage Team. \n\tPlease make sure above orders shipped or not in shipstation. \n\n\t$Msg \n\nThanks,\nAdmin.";
        
        $headers = "From: digitweb-jf.com/\n"; // This is the email address the generated message will be from. We recommend using something like noreply@yourdomain.com.
        $headers .= "Reply-To: $from";	
        
        $sendmail = mail($to,$email_subject,$email_body,$headers);

        if($sendmail){
            $result='sent';
        }
        else{
            $result='not sent';
        }
        
        return $result;
    }

    function fetchP2GToken($CLIENT_ID, $CLIENT_SECRET){
        $ERROR_AUTH = array();

        $METHOD_POST = "POST";
        $AUTH_URL = "https://www.parcel2go.com/auth/connect/token";
        $HTTP_HEADER_AUTH = array('Content-Type: application/x-www-form-urlencoded');
        $POST_FIELDS_AUTH = 'grant_type=client_credentials&scope=public-api payment&client_id='.$CLIENT_ID.'&client_secret='.$CLIENT_SECRET;
        $AUTH_BOOLEAN = false;

        $getAuthentication = fetchDataFromURL($AUTH_URL, $METHOD_POST, $POST_FIELDS_AUTH, $HTTP_HEADER_AUTH);
        $response_code_Auth = $getAuthentication['responsecode'];
        if($response_code_Auth=="200"){
            $response_getAuthentication = $getAuthentication['responsearray'];
            if(array_key_exists('access_token',$response_getAuthentication)){
                $AUTH_BOOLEAN = true;
                $ACCESS_TOKEN = $response_getAuthentication['access_token'];
            }else{
                $ERROR_AUTH = $getAuthentication['responsearray'];
            }        
        }else{
            $ERROR_AUTH = $getAuthentication['responsearray'];
        }
        
        return array("auth" => $AUTH_BOOLEAN,"Token" => $ACCESS_TOKEN,"errorsAuth" => $ERROR_AUTH);
    }

    function createParcel2GoOrder($FirstName, $LastName, $Email, $AddressLine1, $AddressLine2, $AddressLine3, $Region, $Town, $PostCode, $Country, $CountryCode, $subtotal, $ContentsSummary){
        $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
        $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

        $fetchToken = fetchP2GToken($CLIENT_ID, $CLIENT_SECRET);
        $booleanAuth = $fetchToken['auth'];

        $orderCreated = false;

        $parcel2GoOrderId = "";
        $hashValue = "";
        $OrderLineId = "";
        $error = "";

        if($booleanAuth){
            $TOKEN = $fetchToken['Token'];

            $CountryIsoCode = findCountryCode3ByCountryCode($CountryCode);

            // $Property = $AddressLine1;
            // $Street = $AddressLine2;
            // $Locality = $AddressLine3;

            // if($Region != ""){
            //     $Locality .= " ".$Region;
            // }

            $divideAddress = divideAddress1($AddressLine1);

            if($divideAddress['addressDivide']){
                $Property = $divideAddress['firstNumbers'];
                $Street = $divideAddress['restString'];

                $Locality = $AddressLine2;

                if($AddressLine3 != ""){
                    $Locality .= " ".$AddressLine3;
                }
            }else{
                $Property = $AddressLine1;
                $Street = $AddressLine2;

                $Locality = $AddressLine3;
            }

            $URL = 'https://www.parcel2go.com/api/orders';
            $METHOD = 'POST';
            $POST_FIELDS = '{
                "Items": [
                    {
                        "Id": "00000000-0000-0000-1234-000000000000",
                        "Parcels": [
                            {
                                "Id": "00000000-0000-0000-1234-000000000000",
                                "Height": 2.5,
                                "Length": 35,
                                "Weight": 1,
                                "Width": 23,
                                "EstimatedValue": '.$subtotal.',
                                "DeliveryAddress": {
                                    "ContactName": "'.$FirstName." ".$LastName.'",
                                    "Email": "'.$Email.'",
                                    "Property": "'.$Property.'",
                                    "Street": "'.$Street.'",
                                    "Locality": "'.$Locality.'",
                                    "Town": "'.$Town.'",
                                    "County": "'.$Country.'",
                                    "Postcode": "'.$PostCode.'",
                                    "CountryIsoCode": "'.$CountryIsoCode.'"
                                },
                                "ContentsSummary": "'.$ContentsSummary.'"
                            }
                        ],
                        "Service": "myhermes-parcelshop-postable",
                        "CollectionAddress": {
                            "ContactName": "Dan Chen",
                            "Organisation": "LEDSone UK Ltd",
                            "Email": "admin@ledsone.co.uk",
                            "Phone": "07522607969",
                            "Property": "Unit 18,",
                            "Street": "Lythal Lane, Lythal Lane Industrial Estate",
                            "Locality": "",
                            "Town": "COVENTRY",
                            "County": "United Kingdom",
                            "Postcode": "CV6 6FL",
                            "CountryIsoCode": "GBR"
                        }
                    }
                ],
                "CustomerDetails": {
                    "Email": "admin@ledsone.co.uk",
                    "Forename": "Dan",
                    "Surname": "Chen"
                }
            }';

            $HTTP_HEADER = array();
            array_push($HTTP_HEADER,'Authorization: Bearer '.$TOKEN,'Content-Type: application/json');

            $createParcel2GoOrder = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

            if($createParcel2GoOrder['responsecode']=="200"){
                $createParcel2GoOrderResponse = $createParcel2GoOrder['responsearray'];

                if(array_key_exists('OrderId',$createParcel2GoOrderResponse)){
                    $orderCreated = true;
                    $parcel2GoOrderId = $createParcel2GoOrderResponse['OrderId'];
                    $hashValue = $createParcel2GoOrderResponse['Hash'];
                    $OrderLineId = $createParcel2GoOrderResponse['OrderlineIdMap'][0]['OrderLineId'];
                }else{
                    $error = "Order creating failed.";
                }
            }else{
                $error = $POST_FIELDS;
            }
        }else{
            $error = "Error in Creating bearer token.";
        }

        if($orderCreated){
            $prePayForOrder = prePayParcel2Go($parcel2GoOrderId);

            if(!$prePayForOrder["prePaid"]){
                $orderCreated = false;
                $parcel2GoOrderId = "";
                $hashValue = "";
                $OrderLineId = "";
                $error = $prePayForOrder["error"];
            }
        }
        
        return array("orderCreated" => $orderCreated, "parcel2GoOrderId" => $parcel2GoOrderId, "hashValue" => $hashValue, "OrderLineId" => $OrderLineId, "error" => $error);
    }

    function prePayParcel2Go($orderID){
        $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
        $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

        $fetchToken = fetchP2GToken($CLIENT_ID, $CLIENT_SECRET);
        $booleanAuth = $fetchToken['auth'];

        $prePaid = false;

        $labelRequestUrl = "";
        $error = "";

        if($booleanAuth){
            $TOKEN = $fetchToken['Token'];

            $URL = 'https://www.parcel2go.com/api/orders/'.$orderID.'/paywithprepay';
            $METHOD = 'POST';
            $POST_FIELDS = '';

            $HTTP_HEADER = array();
            array_push($HTTP_HEADER,'Authorization: Bearer '.$TOKEN,'Content-Type: application/json');

            $prePayParcel2Go = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

            if($prePayParcel2Go['responsecode']=="200"){
                $prePayParcel2GoResponse = $prePayParcel2Go['responsearray'];

                if(array_key_exists('Links',$prePayParcel2GoResponse)){
                    $prePayParcel2GoLinks = $prePayParcel2GoResponse['Links'];

                    foreach ($prePayParcel2GoLinks as $key => $prePayParcel2GoLink) {
                        if($prePayParcel2GoLink["Name"] == "labels-4x6"){
                            $labelRequestUrl = $prePayParcel2GoLink["Link"];
                        }
                    }

                    $prePaid = true;
                }else{
                    $error = "Prepay failed.";
                }
            }else{
                $error = "Error in prepay Parcel2Go Order.";
            }
        }else{
            $error = "Error in Creating bearer token.";
        }

        return array("prePaid" => $prePaid, "labelRequestUrl" => $labelRequestUrl, "error" => $error);
    }

    function getLabelParcel2Go($orderID, $hash){
        $CLIENT_ID = "ce7aef4f2c304fac8e1bcca357a89457:digitweb";
        $CLIENT_SECRET = "4f2c304fac8e1bcca357a89457:digitwebsecret";

        $fetchToken = fetchP2GToken($CLIENT_ID, $CLIENT_SECRET);
        $booleanAuth = $fetchToken['auth'];

        $labelCreated = false;

        $getLabelParcel2GoBase64 = "";
        $error = "";

        if($booleanAuth){
            $TOKEN = $fetchToken['Token'];

            $URL = 'https://www.parcel2go.com/api/labels/'.$orderID.'?referencetype=OrderId&detailLevel=Labels&labelMedia=Label4X6&labelFormat=PDF&hash='.$hash;
            $METHOD = 'GET';
            $POST_FIELDS = '';

            $HTTP_HEADER = array();
            array_push($HTTP_HEADER,'Authorization: Bearer '.$TOKEN,'Content-Type: application/json');

            $getLabelParcel2Go = fetchDataFromURL($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER);

            if($getLabelParcel2Go['responsecode']=="200"){
                $getLabelParcel2GoResponse = $getLabelParcel2Go['responsearray'];

                if(array_key_exists('Base64EncodedLabels',$getLabelParcel2GoResponse)){
                    $getLabelParcel2GoBase64 = $getLabelParcel2GoResponse['Base64EncodedLabels'][0];

                    $labelCreated = true;
                }else{
                    $error = "Label creation failed.";
                }
            }else{
                $error = "Error in creating label for parcel2go Order.";
            }
        }else{
            $error = "Error in Creating bearer token.";
        }

        return array("labelCreated" => $labelCreated, "labelB64" => $getLabelParcel2GoBase64, "error" => $error);
    }

    // remove dash after text in single SKU
    function removeDashAfterTxt($sku){
        $divideSKU= explode('-', $sku);
        $sku=trim($divideSKU[0]);

        return $sku;
    }

    function getEmptyOrdersCount($flags, $subflags, $morefilter, $postalservice, $connect)
    {
        $query = 'SELECT count(id) as countOrders from temporders where 1 ';

        if ($subflags == "empty") {
            $query .= ' AND subflags= ""';
        } else if ($subflags == "notempty") {
            $query .= ' AND subflags!= ""';
        }

        if ($flags != "") {
            if($flags == "others"){
                $query .= " AND (flags !='Lampshade' AND flags !='Lampshade Shade Only')";
           }else{
                $query .= ' AND flags="'.$flags.'"';
           }
        }

        if($morefilter != ""){
            if ($morefilter == "others") {
                $query .= " AND shippingservice != 'International' AND shippingservice != 'Prime' AND shippingservice != 'first class' AND shippingservice != 'collection order'";
            } else {
                $query .= " AND shippingservice = '".$morefilter."'";
            } 
        }

        $postalservice = strval($postalservice);
        if($postalservice != ""){
            $postalServiceArray = explode(",",$postalservice);
            
            if(count($postalServiceArray) > 0){
                $query .= " AND (";
                foreach ($postalServiceArray as $key => $postalServiceArr) {
                    $query .= "postal_service LIKE '%".$postalServiceArr."%' ";
                    if($key+1 != count($postalServiceArray)){
                        $query .= " OR ";
                    }
                }
                $query .= ")";
            }
        }

        $query .= ' AND (merge = "Merged" OR merge = "")';

        $orderEmptyResult = mysqli_query($connect,$query);

        $orderEmptyRow = mysqli_fetch_array($orderEmptyResult);
        $orderEmptyCount = $orderEmptyRow['countOrders'];

        return $orderEmptyCount;
    }

    // function getAlternative($sku,$channel,$con){
    //     $sourceInfo = explode("-", $channel);
    //     $source = $sourceInfo[0];

    //     $query = 'SELECT `sku` FROM `mappingSKU` WHERE map_sku = "'.$sku.'" AND source="'.$source.'"';
    //     $orderEmptyResult = mysqli_query($con,$query);

    //     $orderEmptyRow = mysqli_fetch_array($orderEmptyResult);
    //     $orderEmptyCount = $orderEmptyRow['countOrders'];

    //     return $orderEmptyCount;
    // }

    function getFlags($sku){
        $flags = 'Not Set';

        $sku = trim($sku);

        if(strpos($sku, 'LS') === 0||strpos($sku, 'EN') === 0||strpos($sku, 'WC') === 0||strpos($sku, 'SW') === 0||strpos($sku, 'PL') === 0||strpos($sku, 'SV') === 0||strpos($sku, 'PH') === 0||strpos($sku, 'PS') === 0||strpos($sku, 'LA') === 0||strpos($sku, 'CR') === 0||strpos($sku, 'SF') === 0||strpos($sku, 'FF') === 0||strpos($sku, 'LHAHE27') === 0||strpos($sku, 'LHCCE27') === 0||strpos($sku, 'LHNSE27') === 0||strpos($sku, 'LHSHE27') === 0||strpos($sku, 'LHTTE27') === 0||strpos($sku, 'SCRN70') === 0 ||strpos($sku, 'SOV52GUB') === 0 ||strpos($sku, 'SOAG1GUB') === 0 ||strpos($sku, 'SOV102G') === 0 ||strpos($sku, 'SOCL2G') === 0 || strpos($sku, 'SOAG1GAD') === 0 ||strpos($sku, 'SOGS2GGK') === 0 ||strpos($sku, 'SOTB') === 0 ||strpos($sku, 'SOGS') === 0){
            $flags="Lampshade";
        }
        
        elseif (strpos($sku, '+LS') !== false||strpos($sku, '+WS') !== false||strpos($sku, '+WC') !== false||strpos($sku, '+PH') !== false||strpos($sku, 'WC') !== false&&strpos($sku, '12WC') === false&&strpos($sku, '5WC') === false || strpos($sku, 'CRSF') === 0){
            $flags="Lampshade";
        }
        
        elseif(strpos($sku, 'LD') === 0||strpos($sku, 'IC') === 0||strpos($sku, 'ST') === 0||strpos($sku, 'G9') === 0||strpos($sku, 'T1') === 0||strpos($sku, 'G8') === 0||strpos($sku, 'BC') === 0 || strpos($sku, 'LPRO') === 0 || strpos($sku, 'LLRO') === 0 || strpos($sku, 'LQDO') === 0 || strpos($sku, 'LPSQ') === 0){
            $flags="Bulbs";
        }
        
        elseif(strpos($sku, 'CL') === 0||strpos($sku, '2C') === 0||strpos($sku, '3C') === 0){
            $flags="cables";
        }
        
        elseif(strpos($sku, 'LH') === 0){
            $flags="Lamp Holders";
        }
        
        elseif(strpos($sku, '12') === 0||strpos($sku, '24') === 0||strpos($sku, 'CC') === 0||strpos($sku, '5') === 0){
            $flags="Transformer";
        }
        
        else{
            $flags="packing Area"; 
        }
        
        if (substr($sku, 0, 2) == "LS" || substr($sku, 0, 2) == "WC"){
            $flags="Lampshade Shade Only";
        }
    
        if ($sku == "PCRN3WC" || substr($sku, 0, 4) == "BC3W" || substr($sku, 0, 4) == "IMCW" || substr($sku, 0, 4) == "BC2W" || substr($sku, 0, 5) == "PCRN4" || $sku == "5050 blue 10m Strip light"){
            $flags="packing Area";
        }
    
        if ($sku == "LHMTE27CO" || $sku == "LHMTE27BM" || $sku == "LHMTE27GB" || $sku == "LHMTE27FG" || $sku == "LHMTE27CH" || $sku == "LHMTE27BM3PK" || $sku == "SOV102GUBTD" || $sku == "WSWMBM+ICT45E2760" || substr($sku, 0, 6) == "WSIWBM" || $sku == "SOV51GUBBE" || $sku == "SOAG2GAD" || $sku == "SOV52GBE" || $sku == "WSWTBM" || $sku == "WSBWBS+ICST64E27" || $sku == "WSBWBC+ICST64E27" || $sku == "WSFTWH2PK" || $sku == "WSFTBM2PK"|| $sku == "SOCL1GUBBM" || $sku == "SOAG2GUBAD" || $sku == "TPWRWO+PSDS2BPB+LHBRE27BM" || substr($sku, 0, 4) == "WSWT"){
            $flags="Lampshade";
        }
    
        if($sku == "CH18SO30" || $sku == "CH18SO15" || substr($sku, 0, 3) == "CH4" || substr($sku, 0, 8) == "CH18HW30"){
            $flags="Transformer";
        }
    
        if($sku == "LLSQ36W" || substr($sku, 0, 4) == "LPBZ" || substr($sku, 0, 3) == "LQD" || substr($sku, 0, 3) == "LQK" || substr($sku, 0, 4) == "LLSQ"){
            $flags="Bulbs";
        }
    
        return $flags;
    }

    function getBoxSizes($sku, $quantity){
        $boxSize = "";
        
        if((strpos($sku, "LSCY290AT") !== false && $quantity == 2) || (strpos($sku, "LSBS160BD") !== false && $quantity == 2)){
            $boxSize = "a7box";
        }else if((strpos($sku, "LSDO210RE") !== false && $quantity == 4)){
            $boxSize = "a5box";
        }
        else if((strpos($sku, "LSSS300GB") !== false && $quantity == 1)){
            $boxSize = "a6box";
        }
        else if((strpos($sku, "LSBS160BD") !== false && $quantity == 1)){
            $boxSize = "a5box";
        }
        else if((strpos($sku, "LSBS160BD") !== false && $quantity == 3)){
            $boxSize = "a8box";
        }

        return $boxSize;
    }
    
    function getSubFlags($sku, $flags, $con)
    {
        $subflags = '';
        $realsku = $sku;
    
        if (substr($sku, 0, 3) == "ENC") {
            $encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $sku . "'");
            $encrow = mysqli_fetch_array($encresult);
            $realsku = $encrow['originalsku'];
        }
    
        if (strpos($flags, 'Lampshade Shade Only') !== false) {
            if (substr($realsku, 0, 4) == "LSCY") {
                $subflags = '4Curvy Lampshade';
            } else if (substr($realsku, 0, 4) == "LSRP") {
                $subflags = '4Curvy with pattern';
            } else if (substr($realsku, 0, 4) == "LSSS") {
                $subflags = '4Barn Slotted Lampshade';
            } else if (substr($realsku, 0, 4) == "LSMS") {
                $subflags = '4Temple shade 4cm Hole';
            } else if (substr($realsku, 0, 4) == "WCB6") {
                $subflags = '4Barrel with Pattern 6';
            } else if (substr($realsku, 0, 4) == "WCB5") {
                $subflags = '4Barrel with Pattern 5';
            } else if (substr($realsku, 0, 7) == "LSDO300") {
                $subflags = '4Dome Lampshade';
            } else if (substr($realsku, 0, 4) == "LSDM") {
                $subflags = '5Bowl Shape';
            } else if (substr($realsku, 0, 4) == "LSLC") {
                $subflags = '5Cone shade 1';
            } else if (substr($realsku, 0, 7) == "LSOL220") {
                $subflags = '5Cone shade 2';
            } else if (substr($realsku, 0, 7) == "LSOL180") {
                $subflags = '5Cone shade 3';
            } else if (substr($realsku, 0, 4) == "LSFT") {
                $subflags = '5Cone shade 4';
            } else if (substr($realsku, 0, 4) == "LSTF") {
                $subflags = '5Cone shade 5';
            } else if (substr($realsku, 0, 4) == "LSDO210") {
                $subflags = '5Dome Lampshade';
            } else if (substr($realsku, 0, 4) == "LSHH") {
                $subflags = '5Hemisphere shape shade';
            } else if (substr($realsku, 0, 4) == "LSUL") {
                $subflags = '5umbrella Lampshade';
            } else if (substr($realsku, 0, 4) == "WCVC") {
                $subflags = '8zE1Vase cage';
            } else if (substr($realsku, 0, 4) == "WCWY") {
                $subflags = '8zE2Waterlily cage';
            } else if (substr($realsku, 0, 4) == "WCNC") {
                $subflags = '8zE3Nest cage';
            } else if (substr($realsku, 0, 4) == "WCLC") {
                $subflags = '8zE4Long cage';
            } else if (substr($realsku, 0, 4) == "WCWV") {
                $subflags = '8zE5Wavy cage';
            } else if (substr($realsku, 0, 4) == "WCWV") {
                $subflags = '8zE5Wavy cage';
            } else if (substr($realsku, 0, 4) == "WCDE") {
                $subflags = '8zE6Dome cage';
            } else if (substr($realsku, 0, 4) == "WCBC") {
                $subflags = '8zE7Bird cage';
            } else if (substr($realsku, 0, 4) == "WCLD") {
                $subflags = '8zE8Long double side';
            } else if (substr($realsku, 0, 4) == "WCLO") {
                $subflags = '8zE9Luster cage';
            } else if (substr($realsku, 0, 4) == "WCFC") {
                $subflags = '8zE10Flower cage';
            } else if (substr($realsku, 0, 4) == "WCSR") {
                $subflags = '8zE11Step round cage';
            } else if (substr($realsku, 0, 4) == "WCDC") {
                $subflags = '8zE12Diamond cage';
            } else if (substr($realsku, 0, 4) == "WCDR") {
                $subflags = '8zE13Diamond cage rope';
            } else if (substr($realsku, 0, 4) == "WCBL") {
                $subflags = '8zE14Barrel cage';
            } else if (substr($realsku, 0, 4) == "WCSC") {
                $subflags = '8zE15Square cage';
            } else if (substr($realsku, 0, 4) == "WCTC") {
                $subflags = '8zE16Triangle cage';
            } else if (substr($realsku, 0, 4) == "WCBE") {
                $subflags = '8zE17Bottle cage';
            } else if (substr($realsku, 0, 4) == "WCWB") {
                $subflags = '8zE18wine bottle cage';
            } else if (substr($realsku, 0, 4) == "WCFP") {
                $subflags = '8zE26Flowerpot Shape';
            } else if (substr($realsku, 0, 4) == "WCMS") {
                $subflags = '8zE27Mug Shape';
            } else if (substr($realsku, 0, 4) == "WCWD") {
                $subflags = '8zE28Wide diamond cage';
            } else if (substr($realsku, 0, 4) == "WCCE") {
                $subflags = '8zE29Circle Shape';
            } else if (substr($realsku, 0, 4) == "WCND") {
                $subflags = '8zE30Narrow Diamond cage';
            } else if (substr($realsku, 0, 4) == "WCSD") {
                $subflags = '8zE31Sharp Diamond cage';
            } else if (substr($realsku, 0, 4) == "WCRN") {
                $subflags = '8zE32Rope Nest cage';
            } else if (substr($realsku, 0, 4) == "WCBT") {
                $subflags = '8zE33Small round Cage';
            } else if (substr($realsku, 0, 4) == "WCNB") {
                $subflags = '8zE34Net Bottle Cage';
            } else if (substr($realsku, 0, 4) == "WCLE") {
                $subflags = '8zE35Line Rectangle cage';
            } else if (substr($realsku, 0, 4) == "WCLS") {
                $subflags = '8zE36Line Square cage';
            }else if (substr($realsku, 0, 4) == "WCLT") {
                $subflags = '8zE37Line Trangle cage';
            }else if (substr($realsku, 0, 4) == "WCLR") {
                $subflags = '8zE38Line Round cage';
            }else if (substr($realsku, 0, 4) == "WCBX") {
                $subflags = '8zE39Box cage';
            }else if (substr($realsku, 0, 4) == "WCBN") {
                $subflags = '8zE40Balloon Cage';
            }else if (substr($realsku, 0, 4) == "WCGS") {
                $subflags = '8zE41Geomatric Shape';
            }else if (substr($realsku, 0, 4) == "WCNZ") {
                $subflags = '8zE42Flower cage';
            }else if (substr($realsku, 0, 4) == "WCNS") {
                $subflags = '8zE43Net Half round';
            }else if (substr($realsku, 0, 4) == "WCBW") {
                $subflags = '8zE44Bottle pattern cage';
            }else if (substr($realsku, 0, 4) == "WCSN") {
                $subflags = '8zE45Small 1cm Diamond cage';
            }else if (substr($realsku, 0, 4) == "WCSN") {
                $subflags = '8zE45Small 1cm Diamond cage';
            }else if (substr($realsku, 0, 4) == "WCHN") {
                $subflags = '8zE46Hip net lampshade';
            }else if (substr($realsku, 0, 4) == "WCTB") {
                $subflags = '8zE473 Bird Cage';
            }else if (substr($realsku, 0, 4) == "WCNR") {
                $subflags = '8zE48Inner Round Net Lampshade';
            }else if (substr($realsku, 0, 4) == "WCNM") {
                $subflags = '8zE49Nest cage 1cm hole';
            }else if (substr($realsku, 0, 4) == "WCMV") {
                $subflags = '8zE50Vase cage 1cm hole';
            }else if (substr($realsku, 0, 4) == "WCSL") {
                $subflags = '8zE51Small 4cm Diamond cage';
            }else if (substr($realsku, 0, 4) == "WCLN") {
                $subflags = '8zE52Long Diamond Cage';
            }else if (substr($realsku, 0, 4) == "WCDU") {
                $subflags = '8zE53Drum Cage';
            }else if (substr($realsku, 0, 4) == "WCRP") {
                $subflags = '8zE54rural pot shape';
            }else if (substr($realsku, 0, 4) == "WCBK") {
                $subflags = '8zE55Basket shape';
            }else if (substr($realsku, 0, 4) == "WCNI") {
                $subflags = '8zE56Nordic Bird nest';
            }else if (substr($realsku, 0, 4) == "WCSQ") {
                $subflags = '8zE57Square Nest';
            }else if (substr($realsku, 0, 4) == "WCRT") {
                $subflags = '8zE581cm rattan barrel Cage';
            }else if (substr($realsku, 0, 4) == "WCLB") {
                $subflags = '8zE591cm Long basket cage';
            }else if (substr($realsku, 0, 4) == "WCDT") {
                $subflags = '8zE60Diamond cage Type 2';
            }else if (substr($realsku, 0, 4) == "WCJR") {
                $subflags = '8zE61Jar Shape Cage';
            }else if (substr($realsku, 0, 4) == "WCTM") {
                $subflags = '8zE62Temble Shape';
            }else if (substr($realsku, 0, 4) == "WCST") {
                $subflags = '8zE63Star Cage';
            }
        }else if (strpos($flags, 'Lampshade') !== false) {
            if (strpos($realsku, 'LSCY') !== false) {
                $subflags = 'B1 Curvy Lampshade';
            } else if (strpos($realsku, 'LSDO210') !== false) {
                $subflags = 'A7 Dome Lampshade';
            } else if (strpos($realsku, 'LSFT') !== false) {
                $subflags = 'A5 Cone shade 4';
            } else if (strpos($realsku, 'LSMS') !== false) {
                $subflags = 'B4 Temple shade 4cm Hole';
            } else if (strpos($realsku, 'LSSS') !== false) {
                $subflags = 'B3 Barn Slotted Lampshade';
            } else if (strpos($realsku, 'LSUL') !== false) {
                $subflags = 'A9 umbrella Lampshade';
            } else if (strpos($realsku, 'LSWD') !== false) {
                $subflags = "wide round";
            } else if (strpos($realsku, 'LSDM') !== false) {
                $subflags = 'A1 Bowl Shape';
            } else if (strpos($realsku, 'LSCG') !== false) {
                $subflags = 'Glass Cone';
            } else if (strpos($realsku, 'LSGG') !== false) {
                $subflags = 'Glass Globe';
            } else if (strpos($realsku, 'LSBG') !== false) {
                $subflags = 'Glass Bell';
            } else if (strpos($realsku, 'LSPG') !== false) {
                $subflags = 'Glass Cone with Pattern';
            } else if (strpos($realsku, 'LSFG') !== false) {
                $subflags = 'Glass Flat with pattern';
            } else if (strpos($realsku, 'LSGD') !== false) {
                $subflags = 'Glass Boul shade';
            } else if (strpos($realsku, 'LSCR') !== false) {
                $subflags = 'Crystal Round';
            } else if (strpos($realsku, 'LSGF') !== false) {
                $subflags = 'Glass Half Bottle';
            } else if (strpos($realsku, 'LSBP') !== false) {
                $subflags = 'Glass Full bottle';
            } else if (strpos($realsku, 'LSWE') !== false) {
                $subflags = 'Beat Style Colour';
            } else if (strpos($realsku, 'LSWG') !== false) {
                $subflags = 'weaving Lampshade';
            } else if (strpos($realsku, 'LSLG') !== false) {
                $subflags = 'Gold inner small';
            } else if (strpos($realsku, 'LSDG') !== false) {
                $subflags = 'Gold Inner dome Style';
            } else if (strpos($realsku, 'LSGP') !== false) {
                $subflags = 'Beat style gold inner lampshade';
            } else if (strpos($realsku, 'LSHG') !== false) {
                $subflags = 'Cone Gold inner Lampshade';
            } else if (strpos($realsku, 'LSLH') !== false) {
                $subflags = 'Long Hemisphere Lampshades';
            } else if (strpos($realsku, 'LSDD') !== false) {
                $subflags = 'Dome Lampshades';
            } else if (strpos($realsku, 'LSSH') !== false) {
                $subflags = 'Short Hemisphere Lampshades';
            } else if (strpos($realsku, 'LSBF') !== false) {
                $subflags = 'Butterfly Lampshade';
            } else if (strpos($realsku, 'LSNY') !== false) {
                $subflags = 'NewYork Pattern';
            } else if (strpos($realsku, 'LSTB') !== false) {
                $subflags = 'T-bell Lampshade';
            } else if (strpos($realsku, 'LSBT') !== false) {
                $subflags = 'Beat Style lampshade';
            } else if (strpos($realsku, 'LSCF') !== false) {
                $subflags = 'Coffe Shade';
            } else if (strpos($realsku, 'LSCC') !== false) {
                $subflags = 'Small Coffe Shade';
            } else if (strpos($realsku, 'LSHQ') !== false) {
                $subflags = 'Half Round Colour Shade';
            } else if (strpos($realsku, 'LSCP') !== false) {
                $subflags = 'Colour Mug shade';
            } else if (strpos($realsku, 'LSRP') !== false) {
                $subflags = 'B2 Curvy with pattern';
            } else if (strpos($realsku, 'LSHM') !== false or strpos($realsku, 'LSHI') !== false or strpos($realsku, 'LSHJ') !== false or strpos($realsku, 'LSH3') !== false or strpos($realsku, 'LSHB') !== false) {
                $subflags = 'Hemp shades';
            } else if (strpos($realsku, 'LSMJ') !== false) {
                $subflags = 'Mason Jar';
            } else if (strpos($realsku, 'LSDJ') !== false) {
                $subflags = 'Black and Gray Step Curvy';
            } else if (strpos($realsku, 'LSHS') !== false) {
                $subflags = 'Hemisphere Lampshades';
            } else if (strpos($realsku, 'LSCI') !== false) {
                $subflags = 'Curvy inner lampshade';
            } else if (strpos($realsku, 'LSDW') !== false) {
                $subflags = 'white pattern';
            } else if (strpos($realsku, 'LSWB135') !== false) {
                $subflags = 'Crystal fitted small long';
            } else if (strpos($realsku, 'LSWB270') !== false) {
                $subflags = 'Crystal fitted big';
            } else if (strpos($realsku, 'LSBL') !== false) {
                $subflags = 'Bell lampshade';
            } else if (strpos($realsku, 'LSCB') !== false) {
                $subflags = 'Crystal Barral';
            } else if (strpos($realsku, 'LSLN') !== false) {
                $subflags = 'Top Lampshade with Net';
            } else if (strpos($realsku, 'LSLT360') !== false) {
                $subflags = 'C4 Top Shade Big';
            } else if (strpos($realsku, 'LSLT') !== false) {
                $subflags = 'Top Shade Big';
            } else if (strpos($realsku, 'LSTL') !== false) {
                $subflags = 'Top Shade small';
            } else if (strpos($realsku, 'LSGM') !== false) {
                $subflags = 'Glass mug Shape';
            } else if (strpos($realsku, 'LSGL') !== false) {
                $subflags = 'Glass leef pattern';
            } else if (strpos($realsku, 'LSGO') !== false) {
                $subflags = 'Globe';
            } else if (strpos($realsku, 'LSGK') !== false) {
                $subflags = 'Glass Globe Round';
            } else if (strpos($realsku, 'LSWS') !== false) {
                $subflags = 'Small wide Shade';
            } else if (strpos($realsku, 'LSLC') !== false) {
                $subflags = 'A2 Cone shade 1';
            } else if (strpos($realsku, 'LSOL220') !== false) {
                $subflags = 'A3 Cone shade 2';
            } else if (strpos($realsku, 'LSOL180') !== false) {
                $subflags = 'A4 Cone shade 3';
            } else if (strpos($realsku, 'LSTF') !== false) {
                $subflags = 'A6 Cone shade 5';
            } else if (strpos($realsku, 'LSFD') !== false) {
                $subflags = 'Flat Dome Lampshades';
            } else if (strpos($realsku, 'LSYG') !== false) {
                $subflags = 'C1 BGY Round Lampshade';
            } else if (strpos($realsku, 'LSBY') !== false) {
                $subflags = 'Bicycle';
            } else if (strpos($realsku, 'LSLR') !== false) {
                $subflags = 'Wide half Round shade';
            } else if (strpos($realsku, 'LSWL') !== false) {
                $subflags = 'Wheel Lampshades';
            } else if (strpos($realsku, 'LSGW') !== false) {
                $subflags = 'Gear Wheel Lampshades';
            } else if (strpos($realsku, 'LSGC') !== false) {
                $subflags = 'Glass Cone Lampshade Colour Gray';
            } else if (strpos($realsku, 'LSDL') !== false) {
                $subflags = 'Dolak Lampshade';
            } else if (strpos($realsku, 'LSDS') !== false) {
                $subflags = 'Crystal square Fitted round';
            } else if (strpos($realsku, 'LSDF') !== false) {
                $subflags = 'Fish Pattern';
            } else if (strpos($realsku, 'LSDT') !== false) {
                $subflags = 'Dot Pattern';
            } else if (strpos($realsku, 'LSUB') !== false) {
                $subflags = 'UK Bridge';
            } else if (strpos($realsku, 'LSHO') !== false) {
                $subflags = 'Hole pattern';
            } else if (strpos($realsku, 'LSWT') !== false or strpos($realsku, 'PHWL') !== false) {
                $subflags = '3 Wheel Lampshades';
            } else if (strpos($realsku, 'LSTH') !== false) {
                $subflags = 'Top Half Bubble lampshade';
            } else if (strpos($realsku, 'LSHL') !== false) {
                $subflags = 'Half Bubble Lampshade';
            } else if (strpos($realsku, 'LSBS') !== false) {
                $subflags = 'Beat Style Long Shade';
            } else if (strpos($realsku, 'LSTP') !== false) {
                $subflags = 'Tree Pattern Shade';
            } else if (strpos($realsku, 'LSWD360') !== false) {
                $subflags = 'C2 wide round shape';
            } else if (strpos($realsku, 'LSWD') !== false) {
                $subflags = 'wide round shape';
            } else if (strpos($realsku, 'LSST') !== false) {
                $subflags = 'step Style Shade';
            } else if (strpos($realsku, 'LSHH') !== false) {
                $subflags = 'A8 Hemisphere shape shade';
            } else if (strpos($realsku, 'LSCU') !== false) {
                $subflags = 'Crystal Round Type 2';
            } else if (strpos($realsku, 'LSCL') !== false) {
                $subflags = 'Crystal Barral Long';
            } else if (strpos($realsku, 'WCVC') !== false) {
                $subflags = 'E1Vase cage';
            } else if (strpos($realsku, 'WCWY') !== false) {
                $subflags = 'E2Waterlily cage';
            } else if (strpos($realsku, 'WCNC') !== false) {
                $subflags = 'E3Nest cage';
            } else if (strpos($realsku, 'WCLC') !== false) {
                $subflags = 'E4Long cage';
            } else if (strpos($realsku, 'WCWV') !== false) {
                $subflags = 'E5Wavy cage';
            } else if (strpos($realsku, 'WCDE') !== false) {
                $subflags = 'E6Dome cage';
            } else if (strpos($realsku, 'WCBC') !== false) {
                $subflags = 'E7Bird cage';
            } else if (strpos($realsku, 'WCLD') !== false) {
                $subflags = 'E8Long double side';
            } else if (strpos($realsku, 'WCLO') !== false) {
                $subflags = 'E9Luster cage';
            } else if (strpos($realsku, 'WCFC') !== false) {
                $subflags = 'E10Flower cage';
            } else if (strpos($realsku, 'WCSR') !== false) {
                $subflags = 'E11Step round cage';
            } else if (strpos($realsku, 'WCDC') !== false) {
                $subflags = 'E12Diamond cage';
            } else if (strpos($realsku, 'WCDR') !== false) {
                $subflags = 'E13Diamond cage rope';
            } else if (strpos($realsku, 'WCBL') !== false) {
                $subflags = 'E14Barrel cage';
            } else if (strpos($realsku, 'WCSC') !== false) {
                $subflags = 'E15Square cage';
            } else if (strpos($realsku, 'WCTC') !== false) {
                $subflags = 'E16Triangle cage';
            } else if (strpos($realsku, 'WCBE') !== false) {
                $subflags = 'E17Bottle cage';
            } else if (strpos($realsku, 'WCWB') !== false) {
                $subflags = 'E18wine bottle cage';
            } else if (substr($realsku, 0, 4) == "WCB7") {
                $subflags = '8Barrel with Pattern 7';
            } else if (substr($realsku, 0, 4) == "WCB6") {
                $subflags = '8Barrel with Pattern 6';
            } else if (substr($realsku, 0, 4) == "WCB5") {
                $subflags = '821Barrel with Pattern 5';
            } else if (substr($realsku, 0, 4) == "WCB4") {
                $subflags = '822Barrel with Pattern 4';
            } else if (substr($realsku, 0, 4) == "WCB3") {
                $subflags = '823Barrel with Pattern 3';
            } else if (substr($realsku, 0, 4) == "WCB2") {
                $subflags = '8Barrel with Pattern 2';
            } else if (substr($realsku, 0, 4) == "WCB1") {
                $subflags = '8Barrel with Pattern 1';
            } else if (strpos($realsku, 'WCB7') !== false) {
                $subflags = 'E19Barrel with Pattern 7';
            } else if (strpos($realsku, 'WCB6') !== false) {
                $subflags = 'E20Barrel with Pattern 6';
            } else if (strpos($realsku, 'WCB5') !== false) {
                $subflags = 'E21Barrel with Pattern 5';
            } else if (strpos($realsku, 'WCB4') !== false) {
                $subflags = 'E22Barrel with Pattern 4';
            } else if (strpos($realsku, 'WCB3') !== false) {
                $subflags = 'E23Barrel with Pattern 3';
            } else if (strpos($realsku, 'WCB2') !== false) {
                $subflags = 'E24Barrel with Pattern 2';
            } else if (strpos($realsku, 'WCB1') !== false) {
                $subflags = 'E25Barrel with Pattern 1';
            } else if (strpos($realsku, 'WCFP') !== false) {
                $subflags = 'E26Flowerpot Shape';
            } else if (strpos($realsku, 'WCMS') !== false) {
                $subflags = 'E27Mug Shape';
            } else if (strpos($realsku, 'WCWD') !== false) {
                $subflags = 'E28Wide diamond cage';
            } else if (strpos($realsku, 'WCCE') !== false) {
                $subflags = 'E29Circle Shape';
            } else if (strpos($realsku, 'WCND') !== false) {
                $subflags = 'E30Narrow Diamond cage';
            } else if (strpos($realsku, 'WCSD') !== false) {
                $subflags = 'E31Sharp Diamond cage';
            } else if (strpos($realsku, 'WCRN') !== false) {
                $subflags = 'E32Rope Nest cage';
            } else if (strpos($realsku, 'WCBT') !== false) {
                $subflags = 'E33Small round Cage';
            } else if (strpos($realsku, 'WCNB') !== false) {
                $subflags = 'E34Net Bottle Cage';
            } else if (strpos($realsku, 'WCLE') !== false) {
                $subflags = 'E35Line Rectangle cage';
            } else if (strpos($realsku, 'WCLS') !== false) {
                $subflags = 'E36Line Square cage';
            } else if (strpos($realsku, 'WCLT') !== false) {
                $subflags = 'E37Line Trangle cage';
            } else if (strpos($realsku, 'WCLR') !== false) {
                $subflags = 'E38Line Round cage';
            } else if (strpos($realsku, 'WCBX') !== false) {
                $subflags = 'E39Box cage';
            } else if (strpos($realsku, 'WCBN') !== false) {
                $subflags = 'E40Balloon Cage';
            } else if (strpos($realsku, 'WCGS') !== false) {
                $subflags = 'E41Geomatric Shape';
            } else if (strpos($realsku, 'WCNZ') !== false) {
                $subflags = 'E42Flower cage';
            } else if (strpos($realsku, 'WCNS') !== false) {
                $subflags = 'E43Net Half round';
            } else if (strpos($realsku, 'WCBW') !== false) {
                $subflags = 'E44Bottle pattern cage';
            } else if (strpos($realsku, 'WCSN') !== false) {
                $subflags = 'E45Small 1cm Diamond cage';
            } else if (strpos($realsku, 'WCHN') !== false) {
                $subflags = 'E46Hip net lampshade';
            } else if (strpos($realsku, 'WCTB') !== false) {
                $subflags = 'E473 Bird Cage';
            } else if (strpos($realsku, 'WCNR') !== false) {
                $subflags = 'E48Inner Round Net Lampshade';
            } else if (strpos($realsku, 'WCNM') !== false) {
                $subflags = 'E49Nest cage 1cm hole';
            } else if (strpos($realsku, 'WCMV') !== false) {
                $subflags = 'E50Vase cage 1cm hole';
            } else if (strpos($realsku, 'WCSL') !== false) {
                $subflags = 'E51Small 4cm Diamond cage';
            } else if (strpos($realsku, 'WCLN') !== false) {
                $subflags = 'E52Long Diamond Cage';
            } else if (strpos($realsku, 'WCDU') !== false) {
                $subflags = 'E53Drum Cage';
            } else if (strpos($realsku, 'WCRP') !== false) {
                $subflags = 'E54rural pot shape';
            } else if (strpos($realsku, 'WCBK') !== false) {
                $subflags = 'E55Basket shape';
            } else if (strpos($realsku, 'WCNI') !== false) {
                $subflags = 'E56Nordic Bird nest';
            } else if (strpos($realsku, 'WCSQ') !== false) {
                $subflags = 'E57Square Nest';
            } else if (strpos($realsku, 'WCRT') !== false) {
                $subflags = 'E581cm rattan barrel Cage';
            } else if (strpos($realsku, 'WCLB') !== false) {
                $subflags = 'E591cm Long basket cage';
            } else if (strpos($realsku, 'WCDT') !== false) {
                $subflags = 'E60Diamond cage Type 2';
            } else if (strpos($realsku, 'WCJR') !== false) {
                $subflags = 'E61Jar Shape Cage';
            } else if (strpos($realsku, 'WCTM') !== false) {
                $subflags = 'E62Temble Shape';
            } else if (strpos($realsku, 'WCST') !== false) {
                $subflags = 'E63Star Cage';
            } else if (substr($realsku, 0, 2) == "SW" or substr($realsku, 0, 2) == "SO") {
                $subflags = '2Switches&Sockets';
            } else if (substr($realsku, 0, 3) == "PS2" or substr($realsku, 0, 5) == "PSDS2" or substr($realsku, 0, 5) == "PSDS4" or substr($realsku, 0, 4) == "PSOS") {
                $subflags = 'Aplugin Pendant';
            } else if (substr($realsku, 0, 4) == "PHSF") {
                $subflags = 'Apendant set';
            } else if ((substr($realsku, 0, 4) == "CRFF" or substr($realsku, 0, 4) == "CRSF") and (strpos($realsku, '+') === false or strpos($realsku, '+HK') !== false)) {
                $subflags = '1ceiling rose';
            } else if (substr($realsku, 0, 4) == "LHSH" or substr($realsku, 0, 4) == "LHAH" or substr($realsku, 0, 4) == "LHNS") {
                $subflags = '3short holder';
            } else if (strpos($realsku, 'LSDO300') !== false) {
                $subflags = 'B5 Dome Lampshade';
            } else if (strpos($realsku, 'LSDO400') !== false) {
                $subflags = 'C3 Dome Lampshade';
            }
            
            if (substr($realsku, 0, 2) == "PL") {
                $subflags = 'XPipelight';
            } else if (substr($realsku, 0, 4) == "LSCY") {
                $subflags = '4Curvy Lampshade';
            } else if (substr($realsku, 0, 4) == "LSRP") {
                $subflags = '4Curvy with pattern';
            } else if (substr($realsku, 0, 4) == "LSSS") {
                $subflags = '4Barn Slotted Lampshade';
            } else if (substr($realsku, 0, 4) == "LSMS") {
                $subflags = '4Temple shade 4cm Hole';
            } else if (substr($realsku, 0, 4) == "WCB6") {
                $subflags = '4Barrel with Pattern 6';
            } else if (substr($realsku, 0, 4) == "WCB5") {
                $subflags = '4Barrel with Pattern 5';
            } else if (substr($realsku, 0, 7) == "LSDO300") {
                $subflags = '4Dome Lampshade';
            } else if (substr($realsku, 0, 4) == "LSDM") {
                $subflags = '5Bowl Shape';
            } else if (substr($realsku, 0, 4) == "LSLC") {
                $subflags = '5Cone shade 1';
            } else if (substr($realsku, 0, 7) == "LSOL220") {
                $subflags = '5Cone shade 2';
            } else if (substr($realsku, 0, 7) == "LSOL180") {
                $subflags = '5Cone shade 3';
            } else if (substr($realsku, 0, 4) == "LSFT") {
                $subflags = '5Cone shade 4';
            } else if (substr($realsku, 0, 4) == "LSTF") {
                $subflags = '5Cone shade 5';
            } else if (substr($realsku, 0, 4) == "LSDO210") {
                $subflags = '5Dome Lampshade';
            } else if (substr($realsku, 0, 4) == "LSHH") {
                $subflags = '5Hemisphere shape shade';
            } else if (substr($realsku, 0, 4) == "LSUL") {
                $subflags = '5umbrella Lampshade';
            } else if (substr($realsku, 0, 4) == "WCVC") {
                $subflags = '8zE1Vase cage';
            } else if (substr($realsku, 0, 4) == "WCWY") {
                $subflags = '8zE2Waterlily cage';
            } else if (substr($realsku, 0, 4) == "WCNC") {
                $subflags = '8zE3Nest cage';
            } else if (substr($realsku, 0, 4) == "WCLC") {
                $subflags = '8zE4Long cage';
            } else if (substr($realsku, 0, 4) == "WCWV") {
                $subflags = '8zE5Wavy cage';
            } else if (substr($realsku, 0, 4) == "WCWV") {
                $subflags = '8zE5Wavy cage';
            } else if (substr($realsku, 0, 4) == "WCDE") {
                $subflags = '8zE6Dome cage';
            } else if (substr($realsku, 0, 4) == "WCBC") {
                $subflags = '8zE7Bird cage';
            } else if (substr($realsku, 0, 4) == "WCLD") {
                $subflags = '8zE8Long double side';
            } else if (substr($realsku, 0, 4) == "WCLO") {
                $subflags = '8zE9Luster cage';
            } else if (substr($realsku, 0, 4) == "WCFC") {
                $subflags = '8zE10Flower cage';
            } else if (substr($realsku, 0, 4) == "WCSR") {
                $subflags = '8zE11Step round cage';
            } else if (substr($realsku, 0, 4) == "WCDC") {
                $subflags = '8zE12Diamond cage';
            } else if (substr($realsku, 0, 4) == "WCDR") {
                $subflags = '8zE13Diamond cage rope';
            } else if (substr($realsku, 0, 4) == "WCBL") {
                $subflags = '8zE14Barrel cage';
            } else if (substr($realsku, 0, 4) == "WCSC") {
                $subflags = '8zE15Square cage';
            } else if (substr($realsku, 0, 4) == "WCTC") {
                $subflags = '8zE16Triangle cage';
            } else if (substr($realsku, 0, 4) == "WCBE") {
                $subflags = '8zE17Bottle cage';
            } else if (substr($realsku, 0, 4) == "WCWB") {
                $subflags = '8zE18wine bottle cage';
            } else if (substr($realsku, 0, 4) == "WCFP") {
                $subflags = '8zE26Flowerpot Shape';
            } else if (substr($realsku, 0, 4) == "WCMS") {
                $subflags = '8zE27Mug Shape';
            } else if (substr($realsku, 0, 4) == "WCWD") {
                $subflags = '8zE28Wide diamond cage';
            } else if (substr($realsku, 0, 4) == "WCCE") {
                $subflags = '8zE29Circle Shape';
            } else if (substr($realsku, 0, 4) == "WCND") {
                $subflags = '8zE30Narrow Diamond cage';
            } else if (substr($realsku, 0, 4) == "WCSD") {
                $subflags = '8zE31Sharp Diamond cage';
            } else if (substr($realsku, 0, 4) == "WCRN") {
                $subflags = '8zE32Rope Nest cage';
            } else if (substr($realsku, 0, 4) == "WCBT") {
                $subflags = '8zE33Small round Cage';
            } else if (substr($realsku, 0, 4) == "WCNB") {
                $subflags = '8zE34Net Bottle Cage';
            } else if (substr($realsku, 0, 4) == "WCLE") {
                $subflags = '8zE35Line Rectangle cage';
            } else if (substr($realsku, 0, 4) == "WCLS") {
                $subflags = '8zE36Line Square cage';
            }else if (substr($realsku, 0, 4) == "WCLT") {
                $subflags = '8zE37Line Trangle cage';
            }else if (substr($realsku, 0, 4) == "WCLR") {
                $subflags = '8zE38Line Round cage';
            }else if (substr($realsku, 0, 4) == "WCBX") {
                $subflags = '8zE39Box cage';
            }else if (substr($realsku, 0, 4) == "WCBN") {
                $subflags = '8zE40Balloon Cage';
            }else if (substr($realsku, 0, 4) == "WCGS") {
                $subflags = '8zE41Geomatric Shape';
            }else if (substr($realsku, 0, 4) == "WCNZ") {
                $subflags = '8zE42Flower cage';
            }else if (substr($realsku, 0, 4) == "WCNS") {
                $subflags = '8zE43Net Half round';
            }else if (substr($realsku, 0, 4) == "WCBW") {
                $subflags = '8zE44Bottle pattern cage';
            }else if (substr($realsku, 0, 4) == "WCSN") {
                $subflags = '8zE45Small 1cm Diamond cage';
            }else if (substr($realsku, 0, 4) == "WCSN") {
                $subflags = '8zE45Small 1cm Diamond cage';
            }else if (substr($realsku, 0, 4) == "WCHN") {
                $subflags = '8zE46Hip net lampshade';
            }else if (substr($realsku, 0, 4) == "WCTB") {
                $subflags = '8zE473 Bird Cage';
            }else if (substr($realsku, 0, 4) == "WCNR") {
                $subflags = '8zE48Inner Round Net Lampshade';
            }else if (substr($realsku, 0, 4) == "WCNM") {
                $subflags = '8zE49Nest cage 1cm hole';
            }else if (substr($realsku, 0, 4) == "WCMV") {
                $subflags = '8zE50Vase cage 1cm hole';
            }else if (substr($realsku, 0, 4) == "WCSL") {
                $subflags = '8zE51Small 4cm Diamond cage';
            }else if (substr($realsku, 0, 4) == "WCLN") {
                $subflags = '8zE52Long Diamond Cage';
            }else if (substr($realsku, 0, 4) == "WCDU") {
                $subflags = '8zE53Drum Cage';
            }else if (substr($realsku, 0, 4) == "WCRP") {
                $subflags = '8zE54rural pot shape';
            }else if (substr($realsku, 0, 4) == "WCBK") {
                $subflags = '8zE55Basket shape';
            }else if (substr($realsku, 0, 4) == "WCNI") {
                $subflags = '8zE56Nordic Bird nest';
            }else if (substr($realsku, 0, 4) == "WCSQ") {
                $subflags = '8zE57Square Nest';
            }else if (substr($realsku, 0, 4) == "WCRT") {
                $subflags = '8zE581cm rattan barrel Cage';
            }else if (substr($realsku, 0, 4) == "WCLB") {
                $subflags = '8zE591cm Long basket cage';
            }else if (substr($realsku, 0, 4) == "WCDT") {
                $subflags = '8zE60Diamond cage Type 2';
            }else if (substr($realsku, 0, 4) == "WCJR") {
                $subflags = '8zE61Jar Shape Cage';
            }else if (substr($realsku, 0, 4) == "WCTM") {
                $subflags = '8zE62Temble Shape';
            }else if (substr($realsku, 0, 4) == "WCST") {
                $subflags = '8zE63Star Cage';
            }
        } else if (strpos($flags, 'Transformer') !== false) {
            if (substr($realsku, 0, 5) == "5IP20") {
                $subflags = 'Transformer 1';
            } else if (substr($realsku, 0, 6) == "12IP20") {
                $subflags = 'Transformer 2';
            } else if (substr($realsku, 0, 6) == "24IP20") {
                $subflags = 'Transformer 3';
            } else if (substr($realsku, 0, 6) == "12IP67") {
                $subflags = 'Transformer 4';
            } else if (substr($realsku, 0, 6) == "24IP67") {
                $subflags = 'Transformer 5';
            } else if (substr($realsku, 0, 4) == "CCBC") {
                $subflags = 'Transformer 6';
            } else if (substr($realsku, 0, 4) == "CCBK") {
                $subflags = 'Transformer 7';
            } else if (substr($realsku, 0, 4) == "CCGN") {
                $subflags = 'Transformer 8';
            } else if (substr($realsku, 0, 4) == "12BO") {
                $subflags = 'Transformer 9';
            } else if (substr($realsku, 0, 8) == "12RPIP45") {
                $subflags = 'Transformer 10';
            } else if (substr($realsku, 0, 7) == "12SIP67") {
                $subflags = 'Transformer 11';
            } else if (substr($realsku, 0, 4) == "12AT") {
                $subflags = 'Transformer 13';
            } else if (substr($realsku, 0, 5) == "12SAN") {
                $subflags = 'Transformer 14';
            } else if (substr($realsku, 0, 6) == "CCIP67") {
                $subflags = 'Transformer 15';
            } else if (substr($realsku, 0, 4) == "12WC") {
                $subflags = 'Transformer 16';
            } else if (substr($realsku, 0, 7) == "12MIP20") {
                $subflags = 'Transformer 17';
            } else if (substr($realsku, 0, 8) == "12ASIP20") {
                $subflags = 'Transformer 18';
            } else if (substr($realsku, 0, 4) == "12CH") {
                $subflags = 'Transformer 19';
            }
        } else if (strpos($flags, 'Bulbs') !== false) {
            if (strpos($realsku, 'G125E27') !== false) {
                $subflags = 'Bulb E27 G125';
            }else if (strpos($realsku, 'G95E27') !== false) {
                $subflags = 'Bulb E27 G95';
            }else if (strpos($realsku, 'G80E27') !== false) {
                $subflags = 'Bulb E27 G80';
            }else if (strpos($realsku, 'ST64E27') !== false) {
                $subflags = 'Bulb E27 ST64';
            }else if (strpos($realsku, 'C35E14') !== false) {
                $subflags = 'Bulb E27 C35';
            }else if (strpos($realsku, 'T185E27') !== false) {
                $subflags = 'Bulb E27 T185';
            }else if (strpos($realsku, 'T130E27') !== false) {
                $subflags = 'Bulb E27 T130';
            }else if (strpos($realsku, 'T45E27') !== false) {
                $subflags = 'Bulb E27 T45';
            }else if (strpos($realsku, 'MUSHE27') !== false) {
                $subflags = 'Bulb E27 MUSH';
            }else if (strpos($realsku, 'G125B22') !== false) {
                $subflags = 'Bulb B22 G125';
            }else if (strpos($realsku, 'G95B22') !== false) {
                $subflags = 'Bulb B22 G95';
            }else if (strpos($realsku, 'G80B22') !== false) {
                $subflags = 'Bulb B22 G80';
            }else if (strpos($realsku, 'ST64B22') !== false) {
                $subflags = 'Bulb B22 ST64';
            }else if (strpos($realsku, 'T185B22') !== false) {
                $subflags = 'Bulb B22 T185';
            }else if (strpos($realsku, 'T130B22') !== false) {
                $subflags = 'Bulb B22 T130';
            }else if (strpos($realsku, 'T45B22') !== false) {
                $subflags = 'Bulb B22 T45';
            }else if (strpos($realsku, 'T300B22') !== false) {
                $subflags = 'Bulb B22 T300';
            }else if (strpos($realsku, 'MUSHB22') !== false) {
                $subflags = 'Bulb B22 MUSH';
            }
        }
    
        return $subflags;
    }

    // function getPostalService($sku, $flags, $orderQty, $orderTotal, $shippingCost, $channel, $con){
    function getPostalService($sku, $flags, $orderQty, $orderTotal, $shippingCost, $postalCode, $channel, $shippingservice, $con){
        $postalService = "Not Set Begin";
        $realsku = $sku;

        if (substr($sku, 0, 3) == "ENC") {
            $encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $sku . "'");
            $encrow = mysqli_fetch_array($encresult);
            $realsku = $encrow['originalsku'];
        }
    
           // for pack into qty
        $total = strlen($realsku);
        $lastTwo = substr($realsku, $total - 2, 2);
        
        if ($lastTwo == "PK") {
            $pkCount = substr($realsku, $total - 3, 1);
            
            if(substr($sku,0,2)=="CL"){
                if($pkCount=="5" || $pkCount=="A" || $pkCount=="F"){
                    $pkCount = 1;
                }
            }else{
                if ($pkCount == "A") {
                    $pkCount = 10;
                } else if ($pkCount == "C") {
                    $pkCount = 20;
                } else if ($pkCount == "E") {
                    $pkCount = 50;
                } else if ($pkCount == "F") {
                    $pkCount = 100;
                }
            }
        } else if ($lastTwo != "PK") {
            $pkCount = 1;
        }
        
        // total length is qty into pk count
        $totalLength = $orderQty * $pkCount;
    
        //-----------------START PACKING AREA--------------------------------------------------
        if($flags == "packing Area")
        {   // tag 1
            if(strpos($channel, 'AMAZON') !== false && ($orderTotal <= 60 && $orderTotal >= 20))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(substr($realsku, 0, 2) == "HL" && substr($realsku, $total - 2, 2) == "PK")
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(strpos($channel, 'AMAZON') === false && $totalLength <= 3 && ($realsku == "HRE2738BM2PK" || $realsku == "RPM40BM2PK" || $realsku == "HRE1428BM2PK" || $realsku == "HRE2738WH2PK" || $realsku == "HRE1428WH2PK" || $realsku == "RPM40WH2PK" || $realsku == "HRE2738BM" || $realsku == "CO123ACL" || $realsku == "RD20" || $realsku == "CO1215ACL" || $realsku == "BC3W80" || $realsku == "BC2W100" || $realsku == "BC3W50" || $realsku == "NT35PK" || $realsku == "CO125ACL" || $realsku == "NFCP9" || $realsku == "CO1210ACL" || $realsku == "RPR44WH2PK" || $realsku == "CO332AGY" || $realsku == "2GOSLDSCCR" || $realsku == "HK10GB" || $realsku == "CGMLBM5PK" || $realsku == "CO126ACL5PK" || $realsku == "SLW4" || $realsku == "SLW6" || $realsku == "CO1260ACL" || $realsku == "HKR10CH" || $realsku == "SDP80RL" || $realsku == "NT3" || $realsku == "SDP240RL" || $realsku == "CODL232AGY" || $realsku == "HK10CO" || $realsku == "SCRN70YB" || $realsku == "CODL332AGY" || $realsku == "GU10SKCR" || $realsku == "SDP80RL5PK" || $realsku == "CODL632AGY" || $realsku == "NFCP7" || $realsku == "CGOVCL" || $realsku == "CGROBM" || $realsku == "CGCEBM5PK" || $realsku == "CO1230ACL" || $realsku == "SCRN70CH" || $realsku == "RPR44WH5PK" || $realsku == "HK10WH" || $realsku == "NFMC15" || $realsku == "CRBT70" || $realsku == "SCRN70RO" || $realsku == "HKR10GB" || $realsku == "HK10BM" || $realsku == "DCCR"))
            {
                $postalService = "95g LL";
            }
            else if(strpos($channel, 'AMAZON') !== false && $totalLength <= 6 && ($realsku == "RPM40BM" || $realsku == "RPM40WH" || $realsku == "RPR44WH" || $realsku == "RPR44BM" || $realsku == "CO232AOR" || $realsku == "CO126ACL" || $realsku == "CGMLWH5PK"))
            {
                $postalService = "95g LL";
            }
            else if(strpos($channel, 'AMAZON') !== false && $totalLength <= 2  && (strpos($realsku, 'CBSF') !== false || strpos($realsku, 'CRBT') !== false))
            {
                $postalService = "95g LL";
            }
            else if(strpos($channel, 'AMAZON') !== false && (strpos($realsku, 'CBSF') !== false || strpos($realsku, 'CRBT') !== false) && ($totalLength <= 4 && $totalLength >= 3))
            {
                $postalService = "245g LL";
            }
            else if(strpos($channel, 'AMAZON') !== false && $totalLength == 1 && ($realsku == "COI9ABM" || $realsku == "CO123ACL5PK" || $realsku == "HLT696BM" || $realsku == "SLW2" || $realsku == "HLRK35BM" || $realsku == "HLSC128BM" || $realsku == "CO1210ACL5PK" || $realsku == "HLSC96BM" || $realsku == "CO1215ACL5PK" || $realsku == "CODL632AGY" || $realsku == "CO125ACL5PK" || $realsku == "COY9ABM B" || $realsku == "CODL332AGYAPK" || $realsku == "CODL332AGY5PK" || $realsku == "12CAT3" || $realsku == "CODL232AGY5PK" || $realsku == "SDP80RLAPK" || $realsku == "RPM40BM5PK"))
            {
                $postalService = "95g LL";
            }
            else if(strpos($channel, 'AMAZON') !== false && $totalLength == 1 && ($realsku == "CNP1000" || $realsku == "CNT900" || $realsku == "IMCWCPK" || $realsku == "RD205PK" || $realsku == "CMWHCPK"))
            {
                $postalService = "245g LL";
            }
            else if(strpos($channel, 'AMAZON') === false && $totalLength == 1 && substr($realsku, $total - 2, 2) != "PK" && (substr($realsku, 0, 4) == "HLCH" || substr($realsku, 0, 4) == "HLSC" || substr($realsku, 0, 6) == "HLRK45"))
            {
                $postalService = "95g LL";
            }
            else if(strpos($channel, 'AMAZON') === false && $totalLength == 2 && substr($realsku, $total - 2, 2) != "PK" && (substr($realsku, 0, 4) == "HLCH" || substr($realsku, 0, 4) == "HLSC" || substr($realsku, 0, 6) == "HLRK45"))
            {
                $postalService = "245g LL";
            }
            else if($totalLength == 1 && ($realsku == "WJB2" || $realsku == "SPUWBM" || $realsku == "HLLK75BM5PK" || substr($realsku, 0, 8) == "12MIP2025" || substr($realsku, 0, 7) == "COT9ABM" || substr($realsku, 0, 7) == "COY9ABM"  ))
            {
                $postalService = "Rm manual";
            }
            else if($totalLength == 1 && strpos($channel, 'AMAZON') === false && $realsku == "CO123ACLAPK" || substr($realsku, 0, 7) == "CNP1000")
            {
                $postalService = "245g LL";
            }
            else if($totalLength == 1 && ($realsku == "HLLK100BM" || $realsku == "HLLK75BM" || $realsku == "WJB3BW" || substr($realsku, 0, 7) == "COI9ABM" || substr($realsku, 0, 12) == "CO1210ACL5PK" || substr($realsku, 0, 11) == "CO224ACLAPK" || substr($realsku, 0, 8) == "HLRK45BM"))
            {
                $postalService = "95g LL";
            }
            else if($totalLength == 5 && ($realsku == 'WJB3BW' || substr($realsku, 0, 2) == "SL" || substr($realsku, 0, 2) == "DC" || substr($realsku, 0, 13) == "CODL332AGY5PK" || substr($realsku, 0, 13) == "CODL232AGY5PK" || substr($realsku, 0, 13) == "CODL932AGY5PK"))
            {
                $postalService = "95g LL"; 
            }
            else if($totalLength == 10 && (substr($realsku, 0, 13) == "CODL332AGY5PK")) 
            {
                $postalService = "95g LL"; 
            }
            else if(strpos($channel, 'AMAZON') === false && substr($postalCode, 0, 2) != "IM" && substr($postalCode, 0, 2) != "KW" && substr($postalCode, 0, 2) != "ST" && substr($postalCode, 0, 2) != "CR" && substr($postalCode, 0, 2) != "FK" && substr($postalCode, 0, 2) != "NW" && substr($postalCode, 0, 2) != "EN" && substr($postalCode, 0, 2) != "BT" && substr($postalCode, 0, 2) != "HR" && substr($postalCode, 0, 2) != "JE" && substr($postalCode, 0, 2) != "DH" && substr($postalCode, 0, 2) != "PO" && substr($postalCode, 0, 2) != "EH" && substr($postalCode, 0, 2) != "PA" && substr($postalCode, 0, 2) != "ZE" && substr($postalCode, 0, 2) != "PH" && substr($postalCode, 0, 2) != "GY" && substr($postalCode, 0, 2) != "IV")
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if($totalLength == 2 && ($realsku == 'HLBK30GB' || substr($realsku, 0, 8) == "HLBK30GB" || substr($realsku, 0, 9) == "HLLK100BM" || substr($realsku, 0, 6) == "PCRNSR")) // ADD THINU
            {
                $postalService = "Rm manual";
            }
            if(($totalLength >= 1 && $totalLength <= 6) && substr($realsku, 0, 2) == "RP")
            {
                $postalService = "95g LL";
            }
            if(($totalLength >= 1 && $totalLength <= 3) && substr($realsku, 0, 8) == "PCRN40TP" || substr($realsku, 0, 6) == "LCPC4C")
            {
                $postalService = "95g LL";
            }
            if(($totalLength >= 1 && $totalLength <= 3) && substr($realsku, 0, 9) == "STTA137CL" || substr($realsku, 0, 9) == "STTA500CL")
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength > 6 && substr($realsku, 0, 2) == "RP")
            {
                $postalService = "245g LL";
            }
            else if($totalLength >= 1 && $totalLength <= 15 && (substr($realsku, 0, 2) == "RW"))
            {
                $postalService = "95g LL";
            }
            else if($totalLength >= 1 && $totalLength <= 10 && (substr($realsku, 0, 2) == "IM" || substr($realsku, 0, 2) == "CM"))
            {
                $postalService = "95g LL";
            }
            else if($totalLength >= 11 && $totalLength <= 20 && (substr($realsku, 0, 2) == "IM" || substr($realsku, 0, 2) == "CM"))
            {
                $postalService = "245g LL";
            }
            else if($totalLength == 40 && (substr($realsku, 0, 2) == "IM" || substr($realsku, 0, 2) == "CM"))
            {
                $postalService = "Hermes Parcelshop Postable (Shop To Door) by My Hermes";
            }
            else if($totalLength == 100 && (substr($realsku, 0, 2) == "IM" || substr($realsku, 0, 2) == "CM"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 2 && $totalLength <= 5) && (substr($realsku, 0, 2) == "CNP" || substr($realsku, 0, 2) == "CNT"))
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if($totalLength == 1 && (substr($realsku, 0, 2) == "CNP" || substr($realsku, 0, 2) == "CNT"))
            {
                $postalService = "245g LL";
            }
            else if($totalLength == 1 && (substr($realsku, 0, 2) == "SP" || $realsku == 'HLHG120CH' || $realsku == 'COY9ABM'))
            {
                $postalService = "Rm manual";
            }
            else if(($totalLength >= 2 && $totalLength <= 5) && (substr($realsku, 0, 2) == "SP"))
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if($totalLength > 10 && (substr($realsku, 0, 2) == "SP"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 1 && $totalLength <= 6) && (substr($realsku, 0, 2) == "CB"))
            {
                $postalService = "95g LL";
            }
            else if(($totalLength >= 8 && $totalLength <= 10) && (substr($realsku, 0, 2) == "CB"))
            {
                $postalService = "245g LL";
            }
            else if(($totalLength >= 1 && $totalLength <= 5) && (substr($realsku, 0, 2) == "NT" || substr($realsku, 0, 2) == "WR" || substr($realsku, 0, 2) == "BC" || substr($realsku, 0, 3) == "SDP" || $realsku == "CODL932AGY" ))
            {
                $postalService = "95g LL";
            }
            else if(($totalLength >= 1 && $totalLength <= 10) && (substr($realsku, 0, 2) == "RD" || substr($realsku, 0, 2) == "CG" || substr($realsku, 0, 2) == "HK" || substr($realsku, 0, 2) == "HR"))
            {
                $postalService = "95g LL";
            }
            else if($totalLength >= 20 && (substr($realsku, 0, 2) == "BC"))
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(($totalLength >= 3 && $totalLength <= 5) && (substr($realsku, 0, 3) == "WJB"))
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(($totalLength >= 6 && $totalLength <= 20) && (substr($realsku, 0, 3) == "SDP"))
            {
                $postalService = "245g LL";
            }
            else if($totalLength >= 100 && (substr($realsku, 0, 3) == "SDP"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength >= 1 && $totalLength <=2 && ($realsku == 'HLLK60BM' || substr($realsku, 0, 8) == 'PCRN65TP' || substr($realsku, 0, 4) == 'SLW8' || substr($realsku, 0, 5) == 'NFMC8'))
            {
                $postalService = "95g LL";
            }
            else if($totalLength == 1 && ($realsku == 'SPSD78GD'))
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if($totalLength == 1 && (substr($realsku, 0,6) == 'GU10SK' || substr($realsku, 0, 8) == 'HLT396BM' || substr($realsku, 0, 8) == 'HLRK35BM'))
            {
                $postalService = "95g LL";
            }
            else if( $totalLength == 2 && (substr($realsku, 0, 12) == "CO1210ACL2PK"))
            {
                $postalService = "95g LL";
            }
            else if( $totalLength == 2 && (substr($realsku, 0, 8) == "HLLK75BM"))
            {
                $postalService = "245g LL";
            }
            else if($totalLength == 1 && (substr($realsku, 0, 10) == 'SDP60DCFPK'))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 2 && $totalLength <= 10) && (substr($realsku, 0, 7) == "CNP1000"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
        }
        //-----------------END PACKING AREA--------------------------------------------------
    
        //-----------------START LAMP SHADE AND LAMP SHADE ONLY------------------------------
        else if($flags == "Lampshade" || $flags == "Lampshade Shade Only")
        {    // tag 2
            if((substr($realsku, 0, 9) == "CRFF108SN" || substr($realsku, 0, 12) == "CRFF108SN+HK" || substr($realsku, 0, 9) == "CRSF108SN" || substr($realsku, 0, 12) == "CRSF108SN+HK" || substr($realsku, 0, 9) == "CRFF108BY" || substr($realsku, 0, 12) == "CRFF108BY+HK" || substr($realsku, 0, 8) == "CRSF108BY" || substr($realsku, 0, 12) == "CRSF108BY+HK" || substr($realsku, 0, 9) == "CRFF108GB" || substr($realsku, 0, 12) == "CRFF108GB+HK" || substr($realsku, 0, 9) == "CRSF108GB" || substr($realsku, 0, 12) == "CRSF108GB+HK" || substr($realsku, 0, 9) == "CRFF108BM" || substr($realsku, 0, 12) == "CRFF108BM+HK" || substr($realsku, 0, 9) == "CRSF108BM" || substr($realsku, 0, 12) == "CRSF108BM+HK" || substr($realsku, 0, 9) == "CRFF108CH" || substr($realsku, 0, 12) == "CRFF108CH+HK" || substr($realsku, 0, 9) == "CRSF108CH" || substr($realsku, 0, 12) == "CRSF108CH+HK" || substr($realsku, 0, 9) == "CRFF108YB" || substr($realsku, 0, 12) == "CRFF108YB+HK" || substr($realsku, 0, 9) == "CRSF108YB" || substr($realsku, 0, 12) == "CRSF108YB+HK" || substr($realsku, 0, 9) == "CRFF108CO" || substr($realsku, 0, 12) == "CRFF108CO+HK" || substr($realsku, 0, 9) == "CRSF108CO" || substr($realsku, 0, 12) == "CRSF108CO+HK" || substr($realsku, 0, 9) == "CRSF108YB" || substr($realsku, 0, 12) == "CRSF108YB+HK" || substr($realsku, 0, 9) == "CRFF108WH" || substr($realsku, 0, 12) == "CRFF108WH+HK" || substr($realsku, 0, 9) == "CRSF108WH" || substr($realsku, 0, 12) == "CRSF108WH+HK") && $totalLength == 1)
            {
                
                if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "Rm manual";
                }
                else if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "Rm manual";
                }
            
            }
            else if(($totalLength >= 2 && $totalLength <= 3) && (substr($realsku, 0, 9) == "CRFF108SN" || substr($realsku, 0, 12) == "CRFF108SN+HK" || substr($realsku, 0, 9) == "CRSF108SN" || substr($realsku, 0, 12) == "CRSF108SN+HK" || substr($realsku, 0, 9) == "CRFF108BY" || substr($realsku, 0, 12) == "CRFF108BY+HK" || substr($realsku, 0, 8) == "CRSF108BY" || substr($realsku, 0, 12) == "CRSF108BY+HK" || substr($realsku, 0, 9) == "CRFF108GB" || substr($realsku, 0, 12) == "CRFF108GB+HK" || substr($realsku, 0, 9) == "CRSF108GB" || substr($realsku, 0, 12) == "CRSF108GB+HK" || substr($realsku, 0, 9) == "CRFF108BM" || substr($realsku, 0, 12) == "CRFF108BM+HK" || substr($realsku, 0, 9) == "CRSF108BM" || substr($realsku, 0, 12) == "CRSF108BM+HK" || substr($realsku, 0, 9) == "CRFF108CH" || substr($realsku, 0, 12) == "CRFF108CH+HK" || substr($realsku, 0, 9) == "CRSF108CH" || substr($realsku, 0, 12) == "CRSF108CH+HK" || substr($realsku, 0, 9) == "CRFF108YB" || substr($realsku, 0, 12) == "CRFF108YB+HK" || substr($realsku, 0, 9) == "CRSF108YB" || substr($realsku, 0, 12) == "CRSF108YB+HK" || substr($realsku, 0, 9) == "CRFF108CO" || substr($realsku, 0, 12) == "CRFF108CO+HK" || substr($realsku, 0, 9) == "CRSF108CO" || substr($realsku, 0, 12) == "CRSF108CO+HK" || substr($realsku, 0, 9) == "CRSF108YB" || substr($realsku, 0, 12) == "CRSF108YB+HK" || substr($realsku, 0, 9) == "CRFF108WH" || substr($realsku, 0, 12) == "CRFF108WH+HK" || substr($realsku, 0, 9) == "CRSF108WH" || substr($realsku, 0, 12) == "CRSF108WH+HK"))
            {
                
                if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
                }
                else if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
                }
            
            }
            else if((substr($realsku, 0, 9) == "CRFF105GB" || substr($realsku, 0, 9) == "CRFF105CH" || substr($realsku, 0, 12) == "CRFF105CH+HK" || substr($realsku, 0, 9) == "CRFF105CO" || substr($realsku, 0, 12) == "CRFF105CO+HK" || substr($realsku, 0, 9) == "CRFF105RO" || substr($realsku, 0, 12) == "CRFF105RO+HK" || substr($realsku, 0, 9) == "CRFF105SN" || substr($realsku, 0, 12) == "CRFF105SN+HK" || substr($realsku, 0, 9) == "CRFF105YB" || substr($realsku, 0, 12) == "CRFF105YB+HK") && $totalLength == 1)
            {
                
                if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "245g LL";
                }
                else if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "245g LL";
                }
            
            }
            else if((substr($realsku, 0, 9) == "CRFF105GB" || substr($realsku, 0, 9) == "CRFF105CH" || substr($realsku, 0, 12) == "CRFF105CH+HK" || substr($realsku, 0, 9) == "CRFF105CO" || substr($realsku, 0, 12) == "CRFF105CO+HK" || substr($realsku, 0, 9) == "CRFF105RO" || substr($realsku, 0, 12) == "CRFF105RO+HK" || substr($realsku, 0, 9) == "CRFF105SN" || substr($realsku, 0, 12) == "CRFF105SN+HK" || substr($realsku, 0, 9) == "CRFF105YB" || substr($realsku, 0, 12) == "CRFF105YB+HK") && ($totalLength >= 2 && $totalLength <= 3))
            {
                
                if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
                }
                else if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
                }
            
            }
            else if(($totalLength >= 3 && $totalLength <= 4) && (substr($realsku, 0, 9) == "CRFF100GB" || substr($realsku, 0, 12) == "CRFF100GB+HK" || substr($realsku, 0, 9) == "CRSF100GB" || substr($realsku, 0, 12) == "CRSF100GB+HK" || substr($realsku, 0, 9) == "CRFF100FG" || substr($realsku, 0, 12) == "CRFF100FG+HK" || substr($realsku, 0, 9) == "CRSF100FG" || substr($realsku, 0, 12) == "CRSF100FG+HK" || substr($realsku, 0, 9) == "CRFF100CH" || substr($realsku, 0, 12) == "CRFF100CH+HK" || substr($realsku, 0, 9) == "CRSF100CH" || substr($realsku, 0, 12) == "CRSF100CH+HK" || substr($realsku, 0, 9) == "CRFF100BM" || substr($realsku, 0, 12) == "CRFF100BM+HK" || substr($realsku, 0, 9) == "CRSF100BM" || substr($realsku, 0, 12) == "CRSF100BM+HK" || substr($realsku, 0, 9) == "CRFF100CO" || substr($realsku, 0, 12) == "CRFF100CO+HK" || substr($realsku, 0, 9) == "CRSF100CO" || substr($realsku, 0, 12) == "CRSF100CO+HK" || substr($realsku, 0, 9) == "CRFF100YB" || substr($realsku, 0, 12) == "CRFF100YB+HK" || substr($realsku, 0, 9) == "CRSF100YB" || substr($realsku, 0, 12) == "CRSF100YB+HK" || substr($realsku, 0, 9) == "CRFF100WH" || substr($realsku, 0, 12) == "CRFF100WH+HK" || substr($realsku, 0, 9) == "CRSF100WH" || substr($realsku, 0, 12) == "CRSF100WH+HK" || substr($realsku, 0, 9) == "CRFF100RO" || substr($realsku, 0, 12) == "CRFF100RO+HK" || substr($realsku, 0, 9) == "CRSF100RO" || substr($realsku, 0, 12) == "CRSF100RO+HK" || substr($realsku, 0, 8) == "CRFF75SN" || substr($realsku, 0, 8) == "CRFF75CH" || substr($realsku, 0, 8) == "CRFF75YB" || substr($realsku, 0, 8) == "CRFF75CO" || substr($realsku, 0, 8) == "CRFF75RO"))
            {
                
                if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
                }
                else if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
                }
            
            }
            else if(($totalLength >= 1 && $totalLength <= 2) && (substr($realsku, 0, 9) == "CRFF100GB" || substr($realsku, 0, 12) == "CRFF100GB+HK" || substr($realsku, 0, 9) == "CRSF100GB" || substr($realsku, 0, 12) == "CRSF100GB+HK" || substr($realsku, 0, 9) == "CRFF100FG" || substr($realsku, 0, 12) == "CRFF100FG+HK" || substr($realsku, 0, 9) == "CRSF100FG" || substr($realsku, 0, 12) == "CRSF100FG+HK" || substr($realsku, 0, 9) == "CRFF100CH" || substr($realsku, 0, 12) == "CRFF100CH+HK" || substr($realsku, 0, 9) == "CRSF100CH" || substr($realsku, 0, 12) == "CRSF100CH+HK" || substr($realsku, 0, 9) == "CRFF100BM" || substr($realsku, 0, 12) == "CRFF100BM+HK" || substr($realsku, 0, 9) == "CRSF100BM" || substr($realsku, 0, 12) == "CRSF100BM+HK" || substr($realsku, 0, 9) == "CRFF100CO" || substr($realsku, 0, 12) == "CRFF100CO+HK" || substr($realsku, 0, 9) == "CRSF100CO" || substr($realsku, 0, 12) == "CRSF100CO+HK" || substr($realsku, 0, 9) == "CRFF100YB" || substr($realsku, 0, 12) == "CRFF100YB+HK" || substr($realsku, 0, 9) == "CRSF100YB" || substr($realsku, 0, 12) == "CRSF100YB+HK" || substr($realsku, 0, 9) == "CRFF100WH" || substr($realsku, 0, 12) == "CRFF100WH+HK" || substr($realsku, 0, 9) == "CRSF100WH" || substr($realsku, 0, 12) == "CRSF100WH+HK" || substr($realsku, 0, 9) == "CRFF100RO" || substr($realsku, 0, 12) == "CRFF100RO+HK" || substr($realsku, 0, 9) == "CRSF100RO" || substr($realsku, 0, 12) == "CRSF100RO+HK" || substr($realsku, 0, 8) == "CRFF75SN" || substr($realsku, 0, 8) == "CRFF75CH" || substr($realsku, 0, 8) == "CRFF75YB" || substr($realsku, 0, 8) == "CRFF75CO" || substr($realsku, 0, 8) == "CRFF75RO"))
            {
                
                if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") !== false)
                {
                    $postalService = "245g LL";
                }
                else if(substr_count($realsku, "+") > 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "ParcelDenOnline Standard Package";
                }
                else if(substr_count($realsku, "+") <= 1 && strpos($realsku, "+HK") === false)
                {
                    $postalService = "245g LL";
                }
            
            }
    
            if(strpos($realsku, 'CRSF2003BM') === false && $orderTotal <= 45 && $shippingCost<3)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($orderTotal <= 74 && $orderTotal >= 45)
            {
                $postalService = "ParcelDenOnline Standard Parcel";
            }
            else if($orderTotal >= 1 && $orderTotal <= 2 && (substr($realsku, 0, 8) == "SCRN70BM"))
            {
                $postalService = "95g LL";
            }
            
        }
        //-----------------END LAMP SHADE AND LAMP SHADE ONLY--------------------------------
    
        //-----------------START TRANSFORMER-------------------------------------------------
        else if($flags == "Transformer")
        {    // tag 3
            if((substr($realsku, 0, 10) == "12ASIP2060" || substr($realsku, 0, 11) == "12ASIP20100" || substr($realsku, 0, 11) == "12ASIP20150" || substr($realsku, 0, 8) == "12DE2P2A" || substr($realsku, 0, 8) == "12DE2P5A" || substr($realsku, 0, 8) == "12IP2060" || substr($realsku, 0, 8) == "12IP2072" || substr($realsku, 0, 8) == "12IP2080" || substr($realsku, 0, 8) == "12IP2096" || substr($realsku, 0, 9) == "12IP6760" || substr($realsku, 0, 9) == "12MIP2060" || substr($realsku, 0, 9) == "12MIP2080" || substr($realsku, 0, 10) == "12MIP20100" || substr($realsku, 0, 8) == "12UK3P6A" || substr($realsku, 0, 9) == "12UK3P10A" || substr($realsku, 0, 7) == "5IP2050" || substr($realsku, 0, 8) == "12UK3P8A" || substr($realsku, 0, 8) == "24IP6750" || substr($realsku, 0, 8) == "24IP6760" || substr($realsku, 0, 7) == "12SAN60" || substr($realsku, 0, 7) == "12IP2015" || substr($realsku, 0, 8) == "24IP6750" || substr($realsku, 0, 7) == "5IP2030" || substr($realsku, 0, 7) == "12IP2015" || $realsku == "12UK3P5A+SLW6" || substr($realsku, 0, 8) == "24IP6780") && $totalLength == 1)
            {  
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";   
            }
            else if((substr($realsku, 0, 11) == "12ASIP20200" || substr($realsku, 0, 11) == "12ASIP20300" || substr($realsku, 0, 11) == "5IP20100" || substr($realsku, 0, 9) == "12IP20480" || substr($realsku, 0, 8) == "12UK3P1A" || substr($realsku, 0, 9) == "24IP20120" || substr($realsku, 0, 9) == "24IP20400") && $totalLength == 1)
            {              
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 10) == "12ASIP2060" || substr($realsku, 0, 11) == "12ASIP20100" || substr($realsku, 0, 11) == "12ASIP20150" || substr($realsku, 0, 8) == "12DE2P2A" || substr($realsku, 0, 8) == "12DE2P5A" || substr($realsku, 0, 8) == "12IP2060" || substr($realsku, 0, 8) == "12IP2072" || substr($realsku, 0, 8) == "12IP2096" || substr($realsku, 0, 9) == "12IP20130" || substr($realsku, 0, 9) == "12IP20150" || substr($realsku, 0, 9) == "12IP20160" || substr($realsku, 0, 9) == "12IP20180" || substr($realsku, 0, 9) == "12IP20200" || substr($realsku, 0, 9) == "12IP20240" || substr($realsku, 0, 9) == "12IP20300" || substr($realsku, 0, 9) == "12IP20360" || substr($realsku, 0, 8) == "12UK3P8A" || substr($realsku, 0, 8) == "12UK3P6A" || substr($realsku, 0, 9) == "12UK3P10A" || substr($realsku, 0, 9) == "12MIP2060") && $totalLength == 2)
            {            
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(( $realsku == "24IP6720") && $totalLength >= 2 && $totalLength <= 5 )
            {               
                $postalService = "ParcelDenOnline Standard Package";  
            }
            else if(( $realsku == "24IP6720") && $totalLength == 1)
            {               
                $postalService = "245g LL";  
            }
            else if(( substr($realsku, 0, 6) == "12BO18") && $totalLength == 1)
            {               
                $postalService = "Rm manual";   
            }
            else if(( substr($realsku, 0, 8) == "CCBKNWE5" ) && $totalLength == 2)
            {               
                $postalService = "95g LL";    
            }
            else if($totalLength >= 3 && $totalLength <= 4 && (substr($realsku, 0, 8) == "CCBKNWE7"))
            {
                $postalService = "245g LL";
            }
            else if($totalLength >= 2 && $totalLength <= 4 && (substr($realsku, 0, 8) == "12IP2080"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength >= 2 && $totalLength <= 5 && (substr($realsku, 0, 6) == "12BO18"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 11) == "12ASIP20300" || substr($realsku, 0, 11) == "12IP20400" || substr($realsku, 0, 11) == "12IP20450" || substr($realsku, 0, 8) == "12IP20500" || substr($realsku, 0, 6) == "12IP20600" || substr($realsku, 0, 8) == "12IP20720" || substr($realsku, 0, 9) == "12IP67100" || substr($realsku, 0, 9) == "12IP67120" || substr($realsku, 0, 9) == "12IP67150" || substr($realsku, 0, 9) == "12IP67200" || substr($realsku, 0, 9) == "12IP67250" || substr($realsku, 0, 9) == "12IP67300" || substr($realsku, 0, 9) == "12IP67350" || substr($realsku, 0, 9) == "12IP67500" || substr($realsku, 0, 10) == "12MIP20120" || substr($realsku, 0, 10) == "12MIP20150" || substr($realsku, 0, 10) == "12MIP20180" || substr($realsku, 0, 10) == "12MIP20200" || substr($realsku, 0, 10) == "12MIP20240" || substr($realsku, 0, 10) == "12MIP20250" || substr($realsku, 0, 10) == "12MIP20360" || substr($realsku, 0, 9) == "12DE2P10A" || substr($realsku, 0, 9) == "12IP20150" || substr($realsku, 0, 9) == "12IP20180" || substr($realsku, 0, 9) == "12IP20240" || substr($realsku, 0, 9) == "12IP20360" || substr($realsku, 0, 9) == "24IP20240" || substr($realsku, 0, 9) == "24IP20200" || substr($realsku, 0, 9) == "12SIP6760" || substr($realsku, 0, 9) == "24IP67120") && $totalLength == 1)
            {         
                $postalService = "ParcelDenOnline Standard Package";  
            }
            else if((substr($realsku, 0, 8) == "12IP2030" || substr($realsku, 0, 8) == "12IP2036" || substr($realsku, 0, 8) == "12IP2040" || substr($realsku, 0, 8) == "12IP2050" || substr($realsku, 0, 8) == "12IP6740" || substr($realsku, 0, 8) == "12IP6745" || substr($realsku, 0, 8) == "12IP6720" || substr($realsku, 0, 8) == "12IP6724" || substr($realsku, 0, 9) == "12MIP2040" || substr($realsku, 0, 9) == "12MIP2015" || substr($realsku, 0, 8) == "12IP6715" || substr($realsku, 0, 8) == "12IP6710" || substr($realsku, 0, 8) == "12UK3P1A")  && $totalLength == 2)
            {     
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";           
            }
            else if((substr($realsku, 0, 9) == "CCBKNWE25" || substr($realsku, 0, 7) == "12CAT12" || substr($realsku, 0, 6) == "12CAT3" || substr($realsku, 0, 6) == "12WC12" || substr($realsku, 0, 6) == "12WC36" || substr($realsku, 0, 6) == "CCBC24" || substr($realsku, 0, 6) == "CCBC36" || substr($realsku, 0, 9) == "CCBKNWE18" || substr($realsku, 0, 6) == "12SAN7" || substr($realsku, 0, 6) == "12WC24" || substr($realsku, 0, 9) == "CCBKNWE50" || substr($realsku, 0, 9) == "CCORNWH24" || substr($realsku, 0, 9) == "CCRDNWE50"  || substr($realsku, 0, 9) == "CCBKNWE36" || substr($realsku, 0, 9) == "CCGNNWE36" || substr($realsku, 0, 8) == "CCBKNWE5" || substr($realsku, 0, 10) == "CCGNNSWE48" || $realsku == "CCBKNWE48") && $totalLength == 1)
            {            
                $postalService = "95g LL";          
            }
            else if((substr($realsku, 0, 8) == "12IP2036" || substr($realsku, 0, 8) == "12IP2040" || substr($realsku, 0, 8) == "12IP2050"  || substr($realsku, 0, 9) == "12IP20200" || substr($realsku, 0, 9) == "12IP20600" || substr($realsku, 0, 9) == "24IP20360" || substr($realsku, 0, 9) == "24IP20400" || substr($realsku, 0, 9) == "24IP20480") && $totalLength == 1)
            {      
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 8) == "12IP6720" || substr($realsku, 0, 8) == "12IP6724" || substr($realsku, 0, 9) == "12MIP2024" || substr($realsku, 0, 9) == "12MIP2025" || substr($realsku, 0, 8) == "12IP6730" || substr($realsku, 0, 8) == "24IP6715" || substr($realsku, 0, 8) == "12IP6736" || substr($realsku, 0, 7) == "12SAN15" || substr($realsku, 0, 8) == "12IP6736" || substr($realsku, 0, 7) == "12SAN35" || substr($realsku, 0, 8) == "12IP6715" || substr($realsku, 0,8) == "24IP6724" || substr($realsku, 0, 8) == "12IP6736" || substr($realsku, 0, 9) == "12IP6710") && $totalLength == 1)
            {   
                $postalService = "245g LL";  
            }
            else if((substr($realsku, 0, 7) == "12CAT12" || substr($realsku, 0, 6) == "12CAT3" || substr($realsku, 0, 9) == "CCORNWH24") && $totalLength == 3)
            {         
                $postalService = "245g LL"; 
            }
            else if((substr($realsku, 0, 8) == "12IP2030" || substr($realsku, 0, 8) == "12IP2024" || substr($realsku, 0, 6) == "12BO28" && $totalLength == 4 ||  substr($realsku, 0, 9) == "24IP67150" ||  substr($realsku, 0, 10) == "12MIP20360" || $realsku == "CCBKNWE48" || substr($realsku, 0, 9) == "24IP67120" && $totalLength == 2) )
            {    
                $postalService = "ParcelDenOnline Standard Package";          
            }
            else if((substr($realsku, 0, 8) == "12IP2015") && $totalLength == 6)
            {
                $postalService = "ParcelDenOnline Standard Package"; 
            }
            else if((substr($realsku, 0, 8) == "12IP2020" || substr($realsku, 0, 9) == "12MIP2015" || substr($realsku, 0, 6) == "12BO28") && $totalLength == 5)
            {   
                $postalService = "ParcelDenOnline Standard Package"; 
            }
            else if((substr($realsku, 0, 9) == "12MIP2015" || substr($realsku, 0, 9) == "12MIP2024" || substr($realsku, 0, 9) == "12MIP2025" || substr($realsku, 0, 9) == "12MIP2030" || substr($realsku, 0, 8) == "12IP2024") && $totalLength == 3)
            {               
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if((substr($realsku, 0, 8) == "12IP2015") && $totalLength == 5)
            {             
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if((substr($realsku, 0, 8) == "CCBKNWE3") && $totalLength == 6)
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes"; 
            }
            else if((substr($realsku, 0, 8) == "12IP2015" || substr($realsku, 0, 8) == "12UK3P5A" || substr($realsku, 0, 8) == "12IP2015") && ($totalLength >= 1 && $totalLength <= 3))
            {             
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if((substr($realsku, 0, 8) == "12IP2020" || substr($realsku, 0, 6) == "CCBC24" || substr($realsku, 0, 9) == "CCBKNWE18" || substr($realsku, 0, 8) == "12UK3P5A") && $totalLength == 4)
            {               
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";           
            }
            else if(($totalLength >= 2 && $totalLength <= 4) && (substr($realsku, 0, 6) == "12BO18" || substr($realsku, 0, 8) == "12IP6720" || substr($realsku, 0, 8) == "24IP6715"))
            {  
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(($totalLength >= 1 && $totalLength <= 3) && substr($realsku, 0, 6) == "12BO28")
            {              
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";           
            }
            else if(($totalLength >= 1 && $totalLength <= 2) && (substr($realsku, 0, 8) == "12IP6745" || substr($realsku, 0, 8) == "24IP2048" || substr($realsku, 0, 11) == "24IP20482PK" || substr($realsku, 0, 8) == "12IP2024" || substr($realsku, 0, 8) == "12IP2040"))
            {   
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(($totalLength >= 2 && $totalLength <= 3) && (substr($realsku, 0, 6) == "12WC12" || substr($realsku, 0, 8) == "12IP6730" || substr($realsku, 0, 8) == "CCBC24" || substr($realsku, 0, 9) == "CCBKNWE18"))
            {       
                $postalService = "245g LL";         
            }
            else if($totalLength == 2  && substr($realsku, 0, 6) == "12WC12" || substr($realsku, 0, 6) == "12WC36" || substr($realsku, 0, 6) == "12WC24" || substr($realsku, 0, 9) == "CCORNWH24")
            {  
                $postalService = "245g LL";
            }
            else if($totalLength >= 1 && $totalLength <= 3 && (substr($realsku, 0, 8) == "5IP20100" || substr($realsku, 0, 8) == "5IP20150" || substr($realsku, 0, 11) == "12RPIP45200" || substr($realsku, 0, 9) == "24IP20180"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength >= 1 && $totalLength <= 6 && (substr($realsku, 0, 8) == "12UK3P2A" || substr($realsku, 0, 8) == "12IP6750"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength >= 1 && $totalLength <= 3 && (substr($realsku, 0, 8) == "12IP2030" || substr($realsku, 0, 8) == "12IP2015" ))
            {
                $postalService = "Hermes Parcelshop Postable (Shop To Door) by My Hermes";
            }
            else if($totalLength >= 2 && $totalLength <= 3 && (substr($realsku, 0, 8) == "12IP2015"))
            {
                $postalService = "Hermes Parcelshop Postable (Shop To Door) by My Hermes";
            }
            else if(($totalLength >= 1 && $totalLength <= 2) && substr($realsku, 0, 8) == "24IP6710")
            {  
                $postalService = "245g LL";
            }
            else if(($totalLength >= 3 && $totalLength <= 6) && substr($realsku, 0, 8) == "24IP6710")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 1 && $totalLength <= 10) && substr($realsku, 0, 8) == "12IP2012")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 1 && $totalLength <= 4) && substr($realsku, 0, 9) == "12IP20100" || substr($realsku, 0, 10) == "12MIP20120" || substr($realsku, 0, 9) == "12IP20120")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 1 && $totalLength <= 5) && substr($realsku, 0, 8) == "24IP2072" || substr($realsku, 0, 11) == "12IP6760OL2" || substr($realsku, 0, 10) == "12RPIP4560" || substr($realsku, 0, 7) == "LHBRE27" || substr($realsku, 0, 10) == "12SIP67100" || substr($realsku, 0, 7) == "LHCNE27" || substr($realsku, 0, 7) == "LHSQE27" || substr($realsku, 0, 7) == "5IP2028" || substr($realsku, 0, 9) == "12IP6780" || substr($realsku, 0, 8) == "24IP6745"  || substr($realsku, 0, 7) == "12BO100" || substr($realsku, 0, 6) == "12BO48" || substr($realsku, 0, 9) == "24IP67100" || substr($realsku, 0, 8) == "24IP6736" || substr($realsku, 0, 9) == "24IP67200" || substr($realsku, 0, 8) == "24IP2024" || substr($realsku, 0, 6) == "12BO72")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 4 && $totalLength <= 6) && substr($realsku, 0, 9) == "CCBKNWE25")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(( $totalLength == 5) && substr($realsku, 0, 10) == "CCGNNSWE48")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(( $totalLength == 4) && substr($realsku, 0, 8) == "12IP6730")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }

        }
        //-----------------END TRANSFORMER-------------------------------------------------
    
        //-----------------START BULBS-----------------------------------------------------
        else if($flags == "Bulbs")
        {    // tag 4
            if($totalLength == 3 && substr($realsku, 0, 14) == "ICMUSHE27603PK" || substr($realsku, 0, 14) == "ICT185E27603PK" || substr($realsku, 0, 13) == "ICG95B22403PK" || substr($realsku, 0, 14) == "ICST64E27403PK" || substr($realsku, 0, 13) == "ICG95E27603PK" || substr($realsku, 0, 14) == "ICG125E27403PK")
            {
                $postalService = "900g parcel";
            }
            else if($totalLength == 6 &&  substr($realsku, 0, 14) == "ICT185E27603PK")      
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength >= 3 && $orderTotal <= 50)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 1 && $totalLength <= 5) && substr($realsku, 0, 7) == "LDWWB22" || substr($realsku, 0, 10) == "LDSHEAE274" || substr($realsku, 0, 10) == "LDMG95B228" || substr($realsku, 0, 10) == "LDMG80E274" || substr($realsku, 0, 8) == "LLROWB5W" || substr($realsku, 0, 8) == "LPBZCW5W")
            {  
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 3) == "LDM" || substr($realsku, 0, 2) == "LD" || substr($realsku, 0, 3) == "LLS" || substr($realsku, 0, 3) == "LLR" || substr($realsku, 0, 3) == "LPR" || substr($realsku, 0, 3) == "LPS" || substr($realsku, 0, 3) == "LPW" || substr($realsku, 0, 3) == "LPB" || substr($realsku, 0, 2) == "LQ") && $totalLength == 1)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength <= 5 && substr($realsku, 0, 2) == "IC")
            {
                $postalService = "900g parcel";
            }
            else if($totalLength > 6 && substr($realsku, 0, 2) == "IC")
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength == 1 && $realsku == 'LLROWB9W' || substr($realsku, 0, 12) == "LDCWE27252PK")
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength >= 1 && $totalLength <= 10 && substr($realsku, 0, 13) == "LDMA60E2742PK")
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            
        }
        //-----------------END BULBS-------------------------------------------------------
    
        //-----------------START LAMP HOLDERS----------------------------------------------
        else if($flags == "Lamp Holders")
        {    // tag 5
            if(substr($realsku, 0, 7) == "LHRIE14" || substr($realsku, 0, 9) == "LHRBE27WH" && $totalLength == 1)
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if((substr($realsku, 0, 7) == "LHRIE27" || substr($realsku, 0, 9) == "LHBGE27BM" || substr($realsku, 0, 9) == "LHBGE27BM" || substr($realsku, 0, 9) == "LHBPE27BM" || substr($realsku, 0, 9) == "LHRIE14WH" ) && $totalLength >= 1 && $totalLength <= 3)
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if((substr($realsku, 0, 7) == "LHNHE27"|| substr($realsku, 0, 9) == "LHTRE27CO" || substr($realsku, 0, 7) == "LHS3E27" ) && $totalLength == 1)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 9) == "LHC1E27WH" || substr($realsku, 0, 7) == "LHTHE27" || substr($realsku, 0, 7) == "LHTEE27" || substr($realsku, 0, 7) == "LHNHE27" || substr($realsku, 0, 7) == "LHAHE27" || substr($realsku, 0, 7) == "LHBHE27" || substr($realsku, 0, 7) == "LHBLE27" || substr($realsku, 0, 9) == "LHC5E27WH" || substr($realsku, 0, 9) == "LHC6E27WH" || substr($realsku, 0, 12) == "LHRIE14WH3PK" || substr($realsku, 0, 7) == "LHPVE27") && $totalLength == 1)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 12) == "LHC6E27WH3PK" || substr($realsku, 0, 9) == "LHDHE27SN" || substr($realsku, 0, 9) == "LHC1E27WH" || substr($realsku, 0, 9) == "LHBLE27CO")&& $totalLength == 2)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 7) == "LHTHE27"|| substr($realsku, 0, 7) == "LHCHE27" || substr($realsku, 0, 7) == "LHSBE27" || substr($realsku, 0, 7) == "LHS6E27"|| substr($realsku, 0, 7) == "LHPVE27") && $totalLength >= 1 && $totalLength <= 6)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if((substr($realsku, 0, 9) == "LHPHE27GB" )&& $totalLength == 10)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($realsku == "CRSF2003BM+PHRB1PBR40BM3PK+LSBS160OR3PK " )&& $totalLength == 1)
            {
                $postalService = "ParcelDenOnline Standard Pacel";
            }
            else if(($totalLength >= 1 && $totalLength <= 5) && (substr($realsku, 0, 7) == "LHRTE27" || substr($realsku, 0, 7) == "LHPHE27" || substr($realsku, 0, 7) == "LHLBB22" || substr($realsku, 0, 7) == "LHC2E27" || substr($realsku, 0, 16) == "LHRBE27BM+CGMLBM" || substr($realsku, 0, 7) == "LHC3E27" || substr($realsku, 0, 7) == "LHC4E27" || substr($realsku, 0, 7) == "LHSSE27" || substr($realsku, 0, 7) == "LHSHE27" || substr($realsku, 0, 7) == "LHSIE27"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 1 && $totalLength <= 8) && (substr($realsku, 0, 7) == "LHSWE27" || substr($realsku, 0, 7) == "LHBBE27" || substr($realsku, 0, 7) == "LHRHE27"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if(($totalLength >= 9 && $totalLength <= 10) && (substr($realsku, 0, 7) == "LHSWE27"))
            {
                $postalService = "ParcelDenOnline Standard Pacel";
            }
            else if(($totalLength >= 2 && $totalLength <= 10) && ( $realsku == "LHBPE27BM2PK+CGMLBM2PK")) 
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength == 3 || $totalLength == 6 || $totalLength == 9 && ($realsku == "LHLWB22WH3PK"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
        }
        //-----------------END LAMP HOLDERS-------------------------------------------------
    
        //-----------------START CABLES ---------------------------------------------------------
        else if($flags == "cables")
        {    // tag 6
            if(strpos($channel, 'AMAZON') !== false)
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(($totalLength >= 3 && $totalLength <= 15) && substr($realsku, 0, 6) == "CL3THE")
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if($totalLength >= 16 && (substr($realsku, 0, 6) == "CL3THE" || substr($realsku, 0, 4) == "CL2R" || substr($realsku, 0, 4) == "CL3R"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength == 2 && substr($realsku, 0, 6) == "CL3THE")
            {
                $postalService = "245g LL";
            }
            else if($totalLength == 1 && $realsku == "CL3THE5PK" || $realsku == "CL3RBK5PK" || $realsku == "CL3TBRAPK"  || $realsku == "CL3RGY5PK" || $realsku == "CL3THEAPK" || $realsku == "CL2RHEAPK" || $realsku == "CL3TBMAPK" || $realsku == "CL3RBRAPK")
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength == 1 && (substr($realsku, 0, 4) == "CL2R" || substr($realsku, 0, 4) == "CL3R" || substr($realsku, 0, 6) == "CL3THE" || substr($realsku, 0, 6) == "CL3RWM"))
            {
                $postalService = "95g LL";
            }
            else if(($totalLength >= 2 && $totalLength <= 3) && (substr($realsku, 0, 4) == "CL2R" || substr($realsku, 0, 4) == "CL3R"))
            {
                $postalService = "245g LL";
            }
            else if(($totalLength >= 4 && $totalLength <= 15) && (substr($realsku, 0, 4) == "CL2R" || substr($realsku, 0, 4) == "CL3R"))
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if($totalLength >= 21 && (substr($realsku, 0, 4) == "CL3T" || substr($realsku, 0, 4) == "CL2T"))
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            else if($totalLength == 1 && (substr($realsku, 0, 9) == 'CL2TGY5PK'  || substr($realsku, 0, 9) == "CL3TRO5PK" || substr($realsku, 0, 9) == 'CL3TWH5PK' || substr($realsku, 0, 9) == 'CL2TBM5PK' || substr($realsku, 0, 9) == 'CL2TGD5PK' || substr($realsku, 0, 9) == 'CL3TGL5PK' || substr($realsku, 0, 9) == 'CL3TGY5PK' || substr($realsku, 0, 9) == 'CL3TAG5PK' || substr($realsku, 0, 9) == 'CL3TGD5PK'))
            {
                $postalService = "245g LL";
            }
            else if(($totalLength >= 1 && $totalLength <= 3) && substr($realsku, 0, 4) == "CL2T")
            {
                $postalService = "95g LL";
            }
            else if(($totalLength >= 4 && $totalLength <= 6) && substr($realsku, 0, 4) == "CL2T")
            {
                $postalService = "245g LL";
            }
            else if(($totalLength >= 7 && $totalLength <= 20) && substr($realsku, 0, 4) == "CL2T"){
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(($totalLength >= 1 && $totalLength <= 2) && substr($realsku, 0, 4) == "CL3T")
            {
                $postalService = "95g LL";
            }
            else if(($totalLength >= 3 && $totalLength <= 5) && substr($realsku, 0, 4) == "CL3T")
            {
                $postalService = "245g LL";
            }
            else if(($totalLength >= 6 && $totalLength <= 20) && substr($realsku, 0, 4) == "CL3T")
            {
                $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
            }
            else if(strpos($realsku, '+LS') === false && strpos($realsku, '+PH') === false && strpos($realsku, '+WS') === false && strpos($realsku, '+PL') === false && strpos($realsku, '+LA') === false && substr($realsku, 0, 2) == "LH" && $orderTotal <= 50)
            {
                $postalService = "ParcelDenOnline Standard Package";
            }
            
           
        }
        //----------------- END CABLES ---------------------------------------------------------
    
        if (($realsku == "CRSF2003BM+PHNW1 RBM3PK" || $realsku == "CRSF2003BM+WCRN BM3PK") && $orderTotal <= 74) 
        {
            $postalService = "ParcelDenOnline Standard Parcel";
        }
        else if($totalLength <= 2 && ($realsku == "CCBC3" || $realsku == "CCBC7" || $realsku == "CCBC9" || $realsku == "CCBC12" || $realsku == "CCBC18" || $realsku == "CCBKNWE3" || $realsku == "CCBKNWE7" || $realsku == "CCBC5" || $realsku == "CCIP673" || $realsku == "CCIP675" || $realsku == "12CAT12" || $realsku == "12WE9" || $realsku == "CCBKNWE12" || $realsku == "12CAT3"))
        {
            $postalService = "95g LL";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && (substr($realsku, 0, 2) == "IC" || substr($realsku, 0, 8) == "Globe125") && $totalLength <= 3)
        {
            $postalService = "900g parcel";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, $total - 2, 2) != "HE" && substr($realsku, 0, 4) == "CL2T" && $totalLength <= 3)
        {
            $postalService = "95g LL";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, $total - 2, 2) != "HE" && substr($realsku, 0, 4) == "CL3T" && $totalLength <= 2)
        {
            $postalService = "95g LL";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, 0, 6) == "CL3TBD" || substr($realsku, 0, 6) == "CL3TGD" && $totalLength == 10)
        {
            $postalService = "ParcelDenOnline Standard Package";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, $total - 2, 2) != "HE" && substr($realsku, 0, 4) == "CL3T" && ($totalLength <= 6 || $totalLength >= 4))
        {
            $postalService = "245g LL";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, $total - 2, 2) != "HE" && substr($realsku, 0, 4) == "CL3T" && ($totalLength <= 5 || $totalLength >= 3))
        {
            $postalService = "245g LL";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, 0, 4) == "CL2R" && $totalLength == 1)
        {
            $postalService = "95g LL";
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, $total - 2, 2) != "HE" && substr($realsku, 0, 4) == "CL3R")
        {
            if($totalLength == 1)
            {
                $postalService = "95g LL";
            }
            else if($totalLength >= 2 && $totalLength <= 3)
            {
                $postalService = "245g LL";
            }
        }
        else if(substr($realsku, $total - 2, 2) != "PK" && substr($realsku, $total - 2, 2) != "HE" && substr($realsku, 0, 4) == "CL2R")
        {
            if($totalLength >= 2 && $totalLength <= 4)
            {
                $postalService = "245g LL";
            }
        }
        else if($totalLength == 1 && strpos($channel, 'AMAZON') === false && ($realsku == "12MIP2015" || $realsku == "IMRECPK" || $realsku == "IMCWCPK" || $realsku == "IMGRCPK" || $realsku == "CL3RGD5PK" || $realsku == "CRSF100BM"))
        {
            $postalService = "245g LL";
        }
        else if($totalLength == 2 && ($realsku == "CRSF100BM" || $realsku == "CRSF108GB" || $realsku == "CRSF100CH" || $realsku == "CRSF100BB" || $realsku == "CRSF100RR" || $realsku == "CRSF100BC" || $realsku == "CRSF100BS" || $realsku == "CRSF100GB" || $realsku == "CRSF100BL" || $realsku == "CRSF100CO" || $realsku == "CRSF100GY" || $realsku == "CRSF100YE" || $realsku == "CRSF100PI" || $realsku == "CRSF100YB"))
        {
            $postalService = "245g LL";
        }
        else if(($totalLength >= 2 && $totalLength <= 3) && ($realsku == "CL3RBM" || $realsku == "CL3RBD" || $realsku == "CL3RBW" || $realsku == "CL3RAG" || $realsku == "CL3RRW" || $realsku == "CL3RBL" || $realsku == "CL3RBT" || $realsku == "CL3RBR" || $realsku == "CL3RGD" || $realsku == "CL3RWH" || $realsku == "CL3RGL" || $realsku == "CL3RRH" || $realsku == "CL3RRO" || $realsku == "CL3RRE" || $realsku == "CL3RBU" || $realsku == "CL3RPI" || $realsku == "CL3RBK" || $realsku == "CL3RYE" || $realsku == "CL3RGR" || $realsku == "CL3RPU" || $realsku == "CL3ROR" || $realsku == "CL3RLG" || $realsku == "CL3RGY" || $realsku == "CL3RMU"))
        {
            $postalService = "245g LL";
        }
        else if(($totalLength >= 1 && $totalLength <= 5) && ($realsku == "LHETB22GD" || $realsku == "LHSBE27" || $realsku == "LHRIE27" || $realsku == "LHRIE14WH" || $realsku == "LHSIE27"))
        {
            $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
        }
        else if(strpos($channel, 'AMAZON') !== false && substr($postalCode, 0, 2) != "BT" && substr($postalCode, 0, 2) != "AB" && substr($postalCode, 0, 2) != "PA" && substr($postalCode, 0, 2) != "PH" && substr($postalCode, 0, 2) != "FK" && substr($postalCode, 0, 2) != "KA" && substr($postalCode, 0, 2) != "HS" && substr($postalCode, 0, 2) != "IV" && substr($postalCode, 0, 2) != "ZE" && substr($postalCode, 0, 2) != "WA" && substr($postalCode, 0, 2) != "WS" && substr($postalCode, 0, 2) != "KW" && substr($postalCode, 0, 2) != "IM" && substr($postalCode, 0, 2) != "NR" && substr($postalCode, 0, 2) != "CM" && substr($postalCode, 0, 2) != "TW" && substr($postalCode, 0, 2) != "MK" && substr($postalCode, 0, 2) != "PO" && substr($postalCode, 0, 2) != "SG" && substr($postalCode, 0, 2) != "BD" && substr($postalCode, 0, 2) != "CO" && substr($postalCode, 0, 2) != "LE" && $flags != "Lampshade" && $flags != "Lampshade Shade Only")
        {
            $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
        }
    
        if($orderTotal >= 75 && (substr($realsku, 0, 2) != "WA" || substr($realsku, 0, 2) != "WS" || substr($realsku, 0, 2) != "KW" || substr($realsku, 0, 2) != "IM" || substr($realsku, 0, 2) != "SG" || substr($realsku, 0, 2) != "NR" || substr($realsku, 0, 2) != "CM" || substr($realsku, 0, 2) != "TW" || substr($realsku, 0, 2) != "MK" || substr($realsku, 0, 2) != "PO" || substr($realsku, 0, 2) != "AB" || substr($realsku, 0, 2) != "PA" || substr($realsku, 0, 2) != "PH" || substr($realsku, 0, 2) != "FK" || substr($realsku, 0, 2) != "KA" || substr($realsku, 0, 3) != "HSI" || substr($realsku, 0, 2) != "IV" || substr($realsku, 0, 2) != "ZE" || substr($realsku, 0, 2) != "DA" || substr($realsku, 0, 2) != "MM" || substr($realsku, 0, 2) != "EH" || substr($realsku, 0, 2) != "SM" || substr($realsku, 0, 2) != "LE" || substr($realsku, 0, 2) != "BD" || substr($realsku, 0, 2) != "CO" || substr($realsku, 0, 2) != "BT"))
        {
            $postalService = "express24";
        }
    
        if($shippingservice=="first class" || $shippingservice=="firstclass"){
            $postalService = "firstclass";
        }
    
        if($shippingservice=="international"){
            $postalService = "international";
        }
    
        if($shippingservice=="prime"){
            $postalService = "prime";
        }
        return $postalService;
    }
    
    function createCommercialInvoice($order, $con, $key){
        // require('tcpdf/tcpdf_import.php');

        $sql = mysqli_query($con, "SELECT * FROM temporders where id='$order'");
        $row= mysqli_fetch_array($sql);
        
        $orderid = $row['orderID'];
      
      	$base64Invoice = "";
        $invoiceFile = "";
        
        $firstname = $row['firstname'];
        $addline1 = $row['shippingaddressline1'];
        $addline2 = $row['shippingaddressline2'];
        $addline3 = $row['shippingaddressline3'];
        $address = $row['shippingaddresscountry'];
        $shiping_cost = $row['shipping_cost'];
        $billto = $row['shippingaddressline3'];
        $city = $row['shippingaddresscity'];
        $postcode = $row['shippingaddresspostcode'];
        $countrycode = $row['shippingaddresscountry'];
        $mobilenumber = $row['telephone'];
        $tax_value = $row['total'];
        $date = $row['date'];
        
        $trackingno = $row['tracking_No'];
        $channel = $row['channel'];
        $code = $row['shippingaddresscountrycode'];
        
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        
        // set default header data
        //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH);
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        //----------remove the header---------------/
        //$pdf->setPrintHeader(false);
        
        // add a page
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', '', 11);
        
        $header ='<table style="width:400px">
            <tr align="center">
                <td><b>Commercial Invoice</b></td>
            </tr>
        </table>';
        
        $pdf->SetFont('helvetica', '', 18);
        
        $pdf->writeHTML($header,'\n', true, false, false, false, '');
        
        $sendby= '';
        
        $sendby .= '<table>
            <tr style="font-size:11px;">
                <td>
                    <table cellspacing="0" cellpadding="1" style="float:left; width:300px">
                    <tr align="left">
                        <td><b>Send By :</b></td>
                    </tr>
                        <tr align="left">
                            <td>Ledsone UK Ltd</td>
                        </tr>
                        <tr align="left">
                            <td>Unit 3</td>
                        </tr>
                        <tr align="left">
                            <td>marshbrook close</td>
                        </tr>
                        <tr align="left">
                            <td>coventry</td>
                        </tr>
                        <tr align="left">
                            <td>CV22NW</td>
                        </tr>
                        <tr align="left">
                            <td>United kingdom</td>
                        </tr>
                    </table>
                </td>';
                
        $sendby .= '<td>
                    <table cellspacing="0" cellpadding="1" style="float:right; width:300px">
                    <tr align="left">
                        <td><b>Send TO :</b></td>
                    </tr>';
                    
        if($firstname != ""){
            $sendby .= '<tr align="left"><td>'.$firstname.'</td></tr>';
        }
                    
        if($addline1 != ""){
            $sendby .= '<tr align="left"><td>'.$addline1.'</td></tr>';
        }
                    
        if($addline2 != ""){
            $sendby .= '<tr align="left"><td>'.$addline2.'</td></tr>';
        }
                    
        if($addline3 != ""){
            $sendby .='<tr align="left"><td>'.$addline3.'</td></tr>';
        }
        
        if($postcode != ""){
            $sendby .= '<tr align="left"><td>'.$postcode.'</td></tr>';
        }
        
        if($countrycode != ""){
            $sendby .= '<tr align="left"><td>'.$countrycode.'</td></tr> ';
        }
                    
        if($mobilenumber != ""){
            $sendby .='<tr align="left"><td>'.$mobilenumber.'</td></tr> ';
        }
                    
        $sendby .= '</table>
                </td>
            </tr>
        </table>';
        
        
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->writeHTML($sendby,'\n', true, false, false, false, '');
        
        $pdf->writeHTML('<hr>', true, false, false, false, '');
        
        $datee ='';
        
        $datee .= '<table align="left">
                <tr>
                    <th><b>Date</b></th>
                    <td>'.$date.'</td>
                    <th><b>VAT NO</b></th>
                    <td>287587828</td>
                    <th><b>Terms of Trade</b></th>
                    <td>Delivered Duty Paid</td>
                </tr>
            <br>
            <tr>
                <th><b>AWB No.</b></th>
                <td>'.$trackingno.'</td>
                <th><b>EORI No.</b></th>
                <td>GB287587828000</td>';
        
        // if($code=="DE"){
        //     $datee .='<td>DE743868660697579</td>';
        // }else{
        //     $datee .='<td> </td>';   
        // }
        
        $datee .='<th><b>IOSS No.</b></th>';
        
        $channel1 = explode('-',$channel);
            
        if(strtoupper($channel1[0]) == "EBAY"){
            $datee .='
            <td>IM2760000742</td>';
        }else if(strtoupper($channel1[0])=="AMAZON"){
        $datee .='
            <td>IM4420001201</td>';
        }else if(strtoupper($channel1[0])=="CDISCOUNT"){
            $datee .='
            <td>IM2500000295</td>';
        }else if(strtoupper($channel1[0])=="ETSY"){
            $datee .='
            <td>IM2760000742</td>';
        }else if(strtoupper($channel1[0])=="BOL"){
            $datee .='
            <td>IM2760000742</td>';
        }else if(strtoupper($channel1[0])=="ANKORSTORE"){
            $datee .='
            <td>IM2760000742</td>';
        }else{
            $datee .='<td> </td>';   
        }
        
        $datee .='</tr></table>';
        
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->writeHTML($datee,'\n', true, false, false, false, '');
        
        $pdf->writeHTML('<br>', true, false, false, false, '');
        $pdf->writeHTML('<hr>', true, false, false, false, '');
        
        
        // product table details
        $pdf->SetFont('helvetica', '', 16);
        
        $table= '';
        
        $pdf->SetFontSize(16);
        
        $table .= '<table style="text-align:center;" cellspacing="0" cellpadding="3" >
                <tr style="color:black; font-size:12pt; font-width:15px; height:200px;">
                    <th border="1"; width="230"><b>Description</b></th>
                    <th border="1"; width="100"><b>Commodity Code</b></th>
                    <th border="1"; width="80"><b>Country of Origin</b></th>
                    <th border="1"; width="80"><b>Quantity</b></th>
                    <th border="1"; width="80"><b>Unit Value</b></th>
                    <th border="1"; width="100"><b>Sub Total Value</b></th>
                </tr>';
        
        $pdf->SetFont('helvetica', '', 8);
        
        $sql = mysqli_query($con, "SELECT * FROM temporders where id='$order'");
        $row= mysqli_fetch_array($sql);
        
        if(strpos($row['flags'], 'packing Area') !== false){
            //if($ItemDescription == 'packing Area'){
                $row['flags'] = 'Ceiling Light Accessories';
        }

        // here added for price 
        if($row['ordertotal']>120){
            $row['quantity'] = 1;
            $row['ordertotal'] = 118.00;
        }

        $table .= '<tr>
                <td align="left"; border="1">'.$row['flags'].'</td>
                <td border="1">9405990090</td>
                <td border="1">GB</td>
                <td border="1">'.$row['quantity'].'</td>
                <td border="1">'.round($row['ordertotal'] / $row['quantity'], 2).'</td>
                <td border="1">'.round($row['ordertotal'], 2).'</td>
            </tr>
        </table>';
        
        
        $pdf->writeHTML($table, true, false, false, false, '');
        
        $totalvalue ='
        <table width="200"> 
            <tr>
              <th><b>Total Value : </b>'.round($row['ordertotal'], 2).'</th>
              <td><b></b></td>
            </tr>
        </table>';
        
        $pdf->writeHTML($totalvalue, true, false, false, false, '');
        $pdf->writeHTML('<br><br><br><br><br>', true, false, false, false, '');
        
        $Reason = '<table>  
            <tr style="font-size:11px;">
                <td border="1" width="310" height="50" text-align="center">
                    <b>Reason for Export :</b> Sales 
                </td>
                <td width="50" height="2" text-align="center">
                  
                </td>
                <td  border="1" width="310" height="50" text-align="center">
                    <b>Notes : </b>
                </td>
            </tr>
        </table>';
        
        $pdf->writeHTML($Reason, true, false, false, false, '');
        
        $footer = '<br><br>
            <table>  
                <tr>
                  <th>I declare that the above information is true and correct to the best of my knowledge and that the goods are of the origin specified above.</th>
                </tr>
            </table>';
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($footer, true, false, false, false, '');
        
        $footer2 ='<table>  
                <tr>
                  <th><b>Signed on Behalf of : </b> Ledsone UK Ltd</th>
                </tr>
            </table>';
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($footer2, true, false, false, false, '');
        
        // move pointer to last page
        $pdf->lastPage();
        
        $filename='Invoice_'.$key.'.pdf';
        
        $data=$pdf->Output(__DIR__.'/'.$filename,'F');
            
        if(!empty($data)) {
            $responseCode = 400;
            $response = "Error: invoice not printed.";
            
        }else if(empty($data)){
            $responseCode = 200;
            $response = "Success: invoice printed.";
            $invoiceFile = $filename;
        }
      
        if($responseCode == 200){
          $base64Invoice = base64_encode(file_get_contents($invoiceFile));
          
          unlink($invoiceFile);
        }
        
        return array("responseCode" => $responseCode, "response" => $response, "base64Invoice" => $base64Invoice);
    }

    // puvii added for selro 

    function getSelroOrders(){
        $URL = 'http://app6.selro.com/api/orders?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&status=Unshipped&pagesize=500&page=1';
        $METHOD = 'GET';
        $POST_FIELDS = '';
        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');

        $ContentType = 'JSON';
        $getOrders = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
        
        $ordersGot = false;
        $selroOrders = array();
        $selroOrderss = array();
    
        if($getOrders['responsecode']=="200"){
            $ordersGot = true;
            $getOrdersResponse = $getOrders['responsearray'];
    
            $selroOrdersPages = 6;
            $selroOrders = $getOrdersResponse['orders'];
            // array_push($selroOrders, $selroOrderss);
            
            for($iterat = 2; $iterat <= $selroOrdersPages; $iterat++){
                $URL_Loop = 'http://app6.selro.com/api/orders?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&status=Unshipped&pagesize=500&page='.$iterat;
    
                $getOrders_Loop = fetchDataFromURLByContentType($URL_Loop, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
                if($getOrders_Loop['responsecode']=="200"){
                    $getOrdersResponse_Loop = $getOrders_Loop['responsearray'];
    
                    $selroOrderss = $getOrdersResponse_Loop['orders'];
                    
                    if(empty($selroOrderss)){
                        break;
                    }
    
                    $selroOrders = array_merge($selroOrders, $selroOrderss);
                }
            }
        }else{
            $error = "Error in getting orders from selro.";
        }
        
        return array("ordersGot" => $ordersGot, "selroOrders" => $selroOrders, "error" => $error);
    }

    function CreateSelroOrder($orderNumber, $fullName, $street1, $street2, $street3, $city, $state, $postalCode, $country, $countryCode, $phone, $email, $sku, $name, $quantity, $unitPrice, $chosenPostal){        
        $URL = 'http://app6.selro.com/api/order?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570';
        $METHOD = 'POST';
        // purchaseDate must be in timestamps - done
        $POST_FIELDS = '{
            "orderId": "'.$orderNumber.'",
            "shipName": "'.$fullName.'",
            "shipPostalCode":"'.$postalCode.'",
            "shipAddress1":"'.$street1.'",
            "shipAddress2":"'.$street2.'",
            "shipAddress3":"'.$street3.'",
            "shipCity":"'.$city.'",
            "shipState": "'.$state.'",
            "shipCountry":"'.$country.'",
            "shipCountryCode":"'.$countryCode.'",
            "shipPhoneNumber": "'.$phone.'",
            "buyerEmail": "'.$email.'",
            "channelId": 25838,
            "channelSales": [
                {
                    "qty":'.$quantity.',
                    "title":"'.$name.'",
                    "sku":"'.$sku.'",
                    "price":'.round($unitPrice,2).',
                    "totalprice":'.round(($quantity*$unitPrice),2).',
                    "taxamount":0.00,
                    "channelId": 25838
                }
            ],
            "totalPrice":'.round(($quantity*$unitPrice),2).',
            "shippingMethod":"'.$chosenPostal.'"
        }';
        
        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');
        $ContentType = "JSON";
        
        $createReplacementOrder = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
        
        $orderCreated = false;
        $error = "";
        $selroOrderID = "";
        
        if($createReplacementOrder['responsecode']=="200"){
            $createReplacementOrderResponse = $createReplacementOrder['responsearray'];
            
            if(array_key_exists('message',$createReplacementOrderResponse)){
                if($createReplacementOrderResponse['message'] == ""){
                    $orderCreated = true;
                    $selroOrderID = $orderNumber;
                }else{
                    $error = $createReplacementOrderResponse['message'];
                }
            }
        }else{
            $error = "Error in Creating Order to selro.";
        }
        
        return array("orderCreated" => $orderCreated, "fullname" => $fullName, "selroOrderID" => $selroOrderID, "error" => $error);
    }

    function getSelroOrderStatusByID($orderId){
        $URL = 'http://app6.selro.com/api/order?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&order_id='.$orderId;
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');
        $ContentType = "JSON";

        $getOrderInfo = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);

        $orderInfoGot = false;
        $shipStationOrderOrderStatus = "";
        $error = "";

        if($getOrderInfo['responsecode']=="200"){
            $getOrderInfoResponse = $getOrderInfo['responsearray'];
            
            if(array_key_exists('orders',$getOrderInfoResponse)){
                
                $shipStationOrderOrderDetails = $getOrderInfoResponse['orders'];

                if(!empty($shipStationOrderOrderDetails)){
                    $orderInfoGot = true;
                    $shipStationOrderOrderStatus = $shipStationOrderOrderDetails[0]['orderStatus'];
                }else{
                    $error = "Check order id. It is not in selro.";
                }
            }
            
        }else{
            $error = "Error in getting order details from selro.";
        }
        
        return array("orderInfoGot" => $orderInfoGot, "shipStationOrderOrderStatus" => $shipStationOrderOrderStatus, "error" => $error);
    }

    function SelroMarkAsShipped($orderId, $postalService, $shipDate, $trackingNumber, $channel){
        $getSelroOrderStatusByID = getSelroOrderStatusByID($orderId);

        $markAsShipped = false;
        $error = "";

        if($getSelroOrderStatusByID['orderInfoGot'] && $getSelroOrderStatusByID['shipStationOrderOrderStatus'] != "Shipped"){
            if($postalService == "245g LL" || $postalService == "900g parcel" || $postalService == "95g LL" || $postalService == "BPL Royal Mail 1st Class Large Letter" || $postalService == "CRL Royal Mail 24 Large Letter" || $postalService == "CRL Royal Mail 24 Parcel" || $postalService == "TPN Royal Mail Tracked 24 Non Signature" || $postalService == "Rm manual"){
                $carrierCode = "Royal Mail";
                $carrierCode = str_replace(" ","%20",$carrierCode);
                // $shipping_method = str_replace(" ","%20",$postalService);
                
                if($postalService == "245g LL" || $postalService == "900g parcel" || $postalService == "Rm manual"){
                    $shipping_method = "Royal Mail 48 ";
                }else if($postalService == "95g LL"){
                    $shipping_method = "Royal Mail 24";
                }else if($postalService == "BPL Royal Mail 1st Class Large Letter"){
                    $shipping_method = "Royal Mail 1st Class";
                }else if($postalService == "CRL Royal Mail 24 Large Letter" || $postalService == "CRL Royal Mail 24 Parcel"){
                    $shipping_method = "Royal Mail 2nd Class";
                }else if($postalService == "TPN Royal Mail Tracked 24 Non Signature"){
                    $shipping_method = "Tracked 24";
                }
                
                $shipping_method = str_replace(" ","%20",$shipping_method);
            }else if($postalService == "ParcelDenOnline Standard Package" || $postalService == "ParcelDenOnline Standard Parcel" || $postalService == "Hermes ParcelShop Postable (Shop To Door) by MyHermes"){
                $carrierCode = "hermes";
                // $shipping_method = str_replace(" ","%20",$postalService);
                if(strpos($channel,"AMAZON") !== false){
                    $shipping_method = str_replace(" ","%20","standard");
                }else{
                    $shipping_method = str_replace(" ","%20",$carrierCode);
                }
            }else if($postalService == "express24"){
                $carrierCode = "parcelforce";
                // $shipping_method = str_replace(" ","%20",$postalService);
                $shipping_method = "express24";
                // $shipping_method = "express24/SND";
            }else if($postalService == "Etrak - Delivery Group"){
                $carrierCode = "customshipping";
                // $shipping_method = str_replace(" ","%20",$postalService);
                $shipping_method = "Etrak - Delivery Group";
                $shipping_method = str_replace(" ","%20",$shipping_method);
            }else if($postalService == "UPS"){
                $carrierCode = "UPS";
                // $shipping_method = str_replace(" ","%20",$postalService);
                $shipping_method = "Standard";
                // $shipping_method = "express24/SND";
            }

            // ebay, amazon, woocommerce, magento, shopify, bigcommerce, sears - $channel example
            
            if($channel == "REPLACEMENT-REPLACEMENT"){
                $URL = 'http://app6.selro.com/api/orders?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&order_id='.$orderId.'&status=Shipped&tracking_id='.$trackingNumber.'&carrier='.$carrierCode.'&shipping_method='.$shipping_method.'&shipment_date='.$shipDate;
            }else{
                $sourceOFOrder = explode("-",$channel);
                $URL = 'http://app6.selro.com/api/orders?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570&order_id='.$orderId.'&channel='.$sourceOFOrder[0].'&status=Shipped&tracking_id='.$trackingNumber.'&carrier='.$carrierCode.'&shipping_method='.$shipping_method.'&shipment_date='.$shipDate;
            }

            // yyyy-MM-dd'T'HH:mm:ss e.g 2018-01-28  =>  $shipDate
            $METHOD = 'PUT';
            
            $POST_FIELDS = '';
            $HTTP_HEADER = array();
            array_push($HTTP_HEADER, 'Content-Type: application/json');
            $ContentType = "JSON";
            
            $markAsShippedOrder = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);
            
            if($markAsShippedOrder['responsecode']=="200"){
                $markAsShippedOrderResponse = $markAsShippedOrder['responsearray'];
                
                if(array_key_exists('errors',$markAsShippedOrderResponse)){
                    $markAsShippedOrderResponseErrors = $markAsShippedOrderResponse['errors'];
                    if($markAsShippedOrderResponseErrors == ""){
                        $markAsShipped = true;
                        $markAsShippedOrderResponseOrderID = $orderId;
                    }else{
                        $error = $markAsShippedOrderResponseErrors;
                    }
                }
            }else{
                $error = "Error in mark as shipped to selro.";
            }
        }else if($getSelroOrderStatusByID['orderInfoGot'] && $getSelroOrderStatusByID['shipStationOrderOrderStatus'] == "Shipped"){
            $markAsShipped = true;
            $markAsShippedOrderResponseOrderID = $orderId;
        }else{
            $error = $getSelroOrderStatusByID['error'];
        }
        
        return array("markAsShipped" => $markAsShipped, "markAsShippedOrderResponseOrderID" => $markAsShippedOrderResponseOrderID, "error" => $error, "URL" => $URL);
    }

    function getChannelNameById($channelID){
        $URL = 'http://app6.selro.com/api/channels?key=c1bb27c8-21d4-4250-a981-a44f6f9e0494&secret=d4745cb9-88da-4a96-a018-c986f8df4570';
        $METHOD = 'GET';
        $POST_FIELDS = '';

        $HTTP_HEADER = array();
        array_push($HTTP_HEADER,'Content-Type: application/json');
        $ContentType = "JSON";

        $getChannels = fetchDataFromURLByContentType($URL, $METHOD, $POST_FIELDS, $HTTP_HEADER, $ContentType);

        $channelInfoGot = false;
        $error = "";
        $channelName = "";

        if($getChannels['responsecode']=="200"){
            $getChannelInfos = $getChannels['responsearray'];
            
            if(array_key_exists('channels',$getChannelInfos)){
                
                $getChannelInfoDetails = $getChannelInfos['channels'];

                if(!empty($getChannelInfoDetails)){
                    foreach ($getChannelInfoDetails as $key => $getChannelInf) {
                        if($getChannelInf['id'] == $channelID){
                            $channelInfoGot = true;
                            $channelName = $getChannelInf['name'];
                            break;
                        }else{
                            $error = "Channel id is incorrect. It is not in selro.";
                        }
                    }
                }else{
                    $error = "Channel details are empty.";
                }
            }
            
        }else{
            $error = "Error in getting order details from selro.";
        }
        
        return array("channelInfoGot" => $channelInfoGot, "channelName" => $channelName, "error" => $error);
    }

    // $orderId = "R1235";
    // $postalService = "Hermes ParcelShop Postable (Shop To Door) by MyHermes";
    // $shipDate = "2022-06-08";
    // $trackingNumber = "1236547899";
    // $channel = "REPLACEMENT-REPLACEMENT";

    // print_r(SelroMarkAsShipped($orderId, $postalService, $shipDate, $trackingNumber, $channel));

    // $orderNumber = "R12345";
    // $fullName = "Puvii Rajh";
    // $street1 = "Atchuvely North, Atchuvely";
    // $street2 = "Jaffna";
    // $street3 = "";
    // $city = "Jaffna";
    // $state = "";
    // $postalCode = "CV2 2NW";
    // $country = "United Kingdom";
    // $countryCode = "GB";
    // $phone = "778700929";
    // $email = "puvii.digitweb@gmail.com";
    // $sku = "testing products";
    // $name = "testing products";
    // $quantity = "2";
    // $unitPrice = "2.32";
    // $chosenPostal = "";

    // print_r(CreateSelroOrder($orderNumber, $fullName, $street1, $street2, $street3, $city, $state, $postalCode, $country, $countryCode, $phone, $email, $sku, $name, $quantity, $unitPrice, $chosenPostal));

    // $orderId = "100012";
    // print_r(getSelroOrderStatusByID($orderId));
    