<?php
    $DATABASE_HOST   = 'localhost';
    $DATABASE_USER   = 'root';
    $DATABASE_PASS   = '';
    $DATABASE_NAME = 'u525933064_dashboard';

    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }

    $downloadcsvtype = $_POST["downloadcsvtype"];
    $orderNumIdsArray = explode(',', $_POST["list_check"]);

    $orderNumIdsCount = count($orderNumIdsArray);

    if($orderNumIdsCount >= 0){
        $delimiter = ",";
        $filename = $downloadcsvtype."_" . date('Y-m-d') . ".csv";

        // Create a file pointer
        $f = fopen('php://memory', 'w');

        if($downloadcsvtype == "parcelden2"){
            $header = array("DeliveryAdd1", "DeliveryAdd2", "DeliveryAdd3", "DeliveryAdd4", "DeliveryAdd5", "DeliveryPostcode*", "DeliveryFirstName", "DeliverySurname*", "DeliverySafePlace", "DeliverySpecialInstructions", "DeliveryPhone", "DeliveryEmail*", "Compensation", "OrderNo/SKU", "YourReference", "Contents*");
            fputcsv($f, $header, $delimiter);
        }else if($downloadcsvtype == "parcelden5"){
            $header = array("DeliveryAdd1", "DeliveryAdd2", "DeliveryAdd3", "DeliveryAdd4", "DeliveryAdd5", "DeliveryPostcode*", "DeliveryFirstName", "DeliverySurname*", "Weight(grams)*", "DeliverySafePlace", "DeliverySpecialInstructions", "DeliveryPhone", "DeliveryEmail*", "Compensation", "OrderNo/SKU", "YourReference", "Contents*");
            fputcsv($f, $header, $delimiter);
        }else if($downloadcsvtype == "default"){
            $header = array("First Name", "Last Name", "Phone", "Email", "Product Name", "SKU", "Address Line1", "Address Line2", "Address Line3", "City", "Region", "Postcode", "Country", "Country Code");
            fputcsv($f, $header, $delimiter);
        }else if($downloadcsvtype == "parcel2go"){
            // $header = array("Forename", "Surname", "Email", "BillingOrganisation", "BillingProperty", "BillingStreet", "BillingLocality", "BillingTown", "BillingCounty", "BillingPostcode", "BillingCountry", "InvoiceNumber", "PaymentMethod", "DeliveryName", "DeliveryOrganisation", "DeliveryProperty", "DeliveryStreet", "DeliveryLocality", "DeliveryTown", "DeliveryCounty", "DeliveryPostcode", "DeliveryCountry", "Date", "Reference", "ServiceName", "Extras", "Parcels", "NetAmount", "VatAmount");
            $header = array("Item Name", "Value", "Weight", "Length", "Width", "Height", "Name", "Property", "Street", "Town", "County", "PostCode", "Country", "Telephone", "ServiceName");
            fputcsv($f, $header, $delimiter);
        }else if($downloadcsvtype == "allTracking" || $downloadcsvtype == "tracking"){
            $header = array("orginalId", "orderID", "orderNum", "Channel", "Tracking Number", "Postal Service");
            fputcsv($f, $header, $delimiter);
        }else if($downloadcsvtype == "parcelforce"){
            $header = array("Business Name", "Address Line 1", "Address Line 2", "Address Line 3", "Postcode", "Post Town", "Recipient Phone", "Email Address");
            fputcsv($f, $header, $delimiter);
        }else if($downloadcsvtype == "amazonfbalabel"){
            $header = array("MerchantFulfillmentOrderID", "DisplayableOrderID", "DisplayableOrderDate", "MerchantSKU", "Quantity", "MerchantFulfillmentOrderItemID", "GiftMessage", "DisplayableComment" , "PerUnitDeclaredValue", "DisplayableOrderComment", "DeliverySLA", "AddressName", "AddressFieldOne", "AddressFieldTwo", "AddressFieldThree", "AddressCity", "AddressCountryCode", "AddressStateOrRegion", "AddressPostalCode", "AddressPhoneNumber", "NotificationEmail", "FulfillmentAction", "MarketplaceID");
            fputcsv($f, $header, $delimiter);
        }

        if($downloadcsvtype != "allTracking" && $downloadcsvtype != "tracking" && $orderNumIdsCount > 0){
            $i = 1;
            // Output each row of the data, format line as csv and write to file pointer
            foreach ($orderNumIdsArray as $key => $orderNumId) {
                $orderResult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $orderNumId . "'");
                $orderRow = mysqli_fetch_array($orderResult);

                $postCode = $orderRow['shippingaddresspostcode'];
                if(strlen($postCode) < 5){
                    $postCode = "0".$postCode;
                }

                if($orderRow['merge'] == "" or $orderRow['merge'] == "Merged"){
                    if($downloadcsvtype == "parcelden2"){
                        $shippingaddressline1 = $orderRow['shippingaddressline1'];
                        $shippingaddressline2 = $orderRow['shippingaddressline2'];
                        $shippingaddressline3 = $orderRow['shippingaddressline3'];

                        $shippingaddressCity = $orderRow['shippingaddresscity'];
                        $shippingaddressRegion = $orderRow['shippingaddressregion'];
                        $shippingaddressPostCode = $orderRow['shippingaddresspostcode'];
                        $shippingaddressCountry = $orderRow['shippingaddresscountry'];

                        $firName = "";
                        $surName = $orderRow['firstname'];

                        $deliverySafePlace = "";
                        $deliverySpecialInstructions = "";
                        $deliveryPhone = $orderRow['telephone'];
                        $deliveryEmail = $orderRow['email'];
                        $compensation = "";
                        $orderNoORSKU = $orderRow['email'];
                        $yourReference = "";
                        $contents = $orderRow['name'];

                        $lineData = array($shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressCity, $shippingaddressRegion, $shippingaddressPostCode, $firName, $surName, $deliverySafePlace, $deliverySpecialInstructions, $deliveryPhone, $deliveryEmail, $compensation, $orderNoORSKU, $yourReference, $contents);
                    }else if($downloadcsvtype == "parcelden5"){
                        $shippingaddressline1 = $orderRow['shippingaddressline1'];
                        $shippingaddressline2 = $orderRow['shippingaddressline2'];
                        $shippingaddressline3 = $orderRow['shippingaddressline3'];

                        $shippingaddressCity = $orderRow['shippingaddresscity'];
                        $shippingaddressRegion = $orderRow['shippingaddressregion'];
                        $shippingaddressPostCode = $orderRow['shippingaddresspostcode'];
                        $shippingaddressCountry = $orderRow['shippingaddresscountry'];

                        $firName = "";
                        $surName = $orderRow['firstname'];

                        $weight = "5000g";
                        $deliverySafePlace = "";
                        $deliverySpecialInstructions = "";
                        $deliveryPhone = $orderRow['telephone'];
                        $deliveryEmail = $orderRow['email'];
                        $compensation = "";
                        $orderNoORSKU = $orderRow['email'];
                        $yourReference = "";
                        $contents = $orderRow['name'];

                        $lineData = array($shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressCity, $shippingaddressRegion, $shippingaddressPostCode, $firName, $surName, $weight, $deliverySafePlace, $deliverySpecialInstructions, $deliveryPhone, $deliveryEmail, $compensation, $orderNoORSKU, $yourReference, $contents);
                    }else if($downloadcsvtype == "default"){
                        $firName = $orderRow['firstname'];
                        $surName = "";
                        $deliveryPhone = $orderRow['telephone'];
                        $deliveryEmail = $orderRow['email'];

                        $prodName = $orderRow['name'];
                        $orderNoORSKU = $orderRow['sku'];

                        $shippingaddressline1 = $orderRow['shippingaddressline1'];
                        $shippingaddressline2 = $orderRow['shippingaddressline2'];
                        $shippingaddressline3 = $orderRow['shippingaddressline3'];

                        $shippingaddressCity = $orderRow['shippingaddresscity'];
                        $shippingaddressRegion = $orderRow['shippingaddressregion'];
                        $shippingaddressPostCode = $orderRow['shippingaddresspostcode'];
                        $shippingaddressCountry = $orderRow['shippingaddresscountry'];
                        $shippingaddressCountryCode = $orderRow['shippingaddresscountrycode'];

                        $lineData = array($firName, $surName, $deliveryPhone, $deliveryEmail, $prodName, $orderNoORSKU, $shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressCity, $shippingaddressRegion, $shippingaddressPostCode, $shippingaddressCountry, $shippingaddressCountryCode);
                    }else if($downloadcsvtype == "parcel2go"){
                        // $invoiceNumber = "";
                        // $paymentMethod = "";
                        // $firName = $orderRow['firstname'];
                        // $shippingaddressCompany = $orderRow['shippingaddresscompany'];
                        // $prodName = $orderRow['name'];

                        // $shippingaddressline1 = $orderRow['shippingaddressline1'];
                        // $shippingaddressline2 = $orderRow['shippingaddressline2'];
                        // $shippingaddressline3 = $orderRow['shippingaddressline3'];

                        // $shippingaddressCity = $orderRow['shippingaddresscity'];
                        // $shippingaddressRegion = $orderRow['shippingaddressregion'];
                        // $shippingaddressPostCode = $orderRow['shippingaddresspostcode'];
                        // $shippingaddressCountry = $orderRow['shippingaddresscountry'];

                        // $date = $orderRow['csvdate'];
                        // $reference = "";
                        // $serviceName = $orderRow['postal_service'];
                        // $extras = "";
                        // $parcels = "1";
                        // $netAmount = $orderRow['total'];
                        // $vatAmount = "0.0";

                        // $lineData = array("Dan", "Chen", "admin@ledsone.co.uk", "LEDSone UK Ltd", "Unit 18,", "Lythal Lane, Lythal Lane Industrial Estate", "", "COVENTRY", "CV6 6FL", "CV6 6FL", "GB", $invoiceNumber, $paymentMethod, $firName, $shippingaddressCompany, $prodName, $shippingaddressline1, $shippingaddressline2, $shippingaddressCity, $shippingaddressRegion, $shippingaddressPostCode, $shippingaddressCountry, $date, $reference, $serviceName, $extras, $parcels, $netAmount, $vatAmount);

                        $prodName = $orderRow['name'];
                        $serviceName = $orderRow['postal_service'];

                        $value = $orderRow['total'];

                        if($serviceName == "Hermes ParcelShop Postable (Shop To Door) by MyHermes"){
                            $Weight = "1";
                            $Length = "35";
                            $Width = "23";
                            $Height = "2.5";
                        }else if($serviceName == "Return 2kg Hermes"){
                            $Weight = "2"; 
                            $Length = "45"; 
                            $Width = "35"; 
                            $Height = "16"; 
                        }

                        $firName = $orderRow['firstname'];
                        $Property = $orderRow['shippingaddressline1']; // yet not complete

                        $shippingaddressline1 = $orderRow['shippingaddressline2'];

                        $shippingaddressCity = $orderRow['shippingaddresscity'];
                        $shippingaddressRegion = $orderRow['shippingaddressregion'];
                        $shippingaddressPostCode = $orderRow['shippingaddresspostcode'];
                        $shippingaddressCountryCode = $orderRow['shippingaddresscountrycode'];
                        $Telephone = $orderRow['telephone'];

                        $lineData = array($prodName, $value, $Weight, $Length, $Width, $Height, $firName, $Property, $shippingaddressline1, $shippingaddressCity, $shippingaddressRegion, $shippingaddressPostCode, $shippingaddressCountryCode, $Telephone, $serviceName);
                    }else if($downloadcsvtype == "parcelforce"){
                        $firName = $orderRow['firstname'];
                        $deliveryPhone = $orderRow['telephone'];
                        $deliveryEmail = $orderRow['email'];

                        $shippingaddressline1 = $orderRow['shippingaddressline1'];
                        $shippingaddressline2 = $orderRow['shippingaddressline2'];
                        $shippingaddressline3 = $orderRow['shippingaddressline3'];

                        $shippingaddressregion = $orderRow['shippingaddressregion'];

                        if($shippingaddressline3 != ""){
                            $shippingaddressline3 .= " ";
                        }
                        
                        $shippingaddressline3 .= $shippingaddressregion;

                        $shippingaddressCity = $orderRow['shippingaddresscity'];
                        $shippingaddressPostCode = $orderRow['shippingaddresspostcode'];

                        $lineData = array($firName, $shippingaddressline1, $shippingaddressline2, $shippingaddressline3, $shippingaddressPostCode, $shippingaddressCity, $deliveryPhone, $deliveryEmail);
                    }else if($downloadcsvtype == "amazonfbalabel"){
                        $MerchantFulfillmentOrderID = $DisplayableOrderID = $orderRow['orderID'];
                        
                        // $DisplayableOrderDate = $orderRow['date'];
                        $DisplayableOrderDate = date('Y-m-d\TH:i:s', strtotime($orderRow['date']) );
                        
                        $MerchantSKU = $orderRow['FBA_merchantSKU'];
                        $Quantity = $orderRow['quantity'];
                        $GiftMessage = $DisplayableComment = $orderRow['notes'];
                        $PerUnitDeclaredValue = round(($orderRow['ordertotal'] / $orderRow['quantity']), 2);
                        $DisplayableOrderComment = "Thank you for ordering. We appreciate your order.";

                        if($orderRow['shippingservice'] == "firstclass"){
                            $DeliverySLA = "Express";
                        }else{
                            $DeliverySLA = "Standard";
                        }

                        $AddressName = $orderRow['firstname'];

                        $AddressFieldOne = $orderRow['shippingaddressline1'];
                        $AddressFieldTwo = $orderRow['shippingaddressline2'];
                        $AddressFieldThree = $orderRow['shippingaddressline3'];

                        $AddressCity = $orderRow['shippingaddresscity'];
                        $AddressCountryCode = $orderRow['shippingaddresscountrycode'];
                        $AddressStateOrRegion = $orderRow['shippingaddressregion'];
                        $AddressPostalCode = $orderRow['shippingaddresspostcode'];

                        $AddressPhoneNumber = $orderRow['telephone'];
                        $NotificationEmail = $orderRow['email'];
                        $FulfillmentAction = "";
                        $MarketplaceID = "";

                        $lineData = array($MerchantFulfillmentOrderID, $DisplayableOrderID, $DisplayableOrderDate, $MerchantSKU, $Quantity, "1", $GiftMessage, $DisplayableComment, $PerUnitDeclaredValue, $DisplayableOrderComment, $DeliverySLA, $AddressName, $AddressFieldOne, $AddressFieldTwo, $AddressFieldThree, $AddressCity, $AddressCountryCode, $AddressStateOrRegion, $AddressPostalCode, $AddressPhoneNumber, $NotificationEmail, $FulfillmentAction, $MarketplaceID);
                    }

                    fputcsv($f, $lineData, $delimiter);

                    $i++;
                }
            }
        }else{
            if($downloadcsvtype != "allTracking"){
                $query2 = "SELECT DISTINCT(`orderID`), `id`, `channel`, `tracking_No`, `postal_service` FROM `temporders` WHERE 1 and `postal_service` != 'express24'";
            }else{
                $query2 = "SELECT `id`, `orderID`, `channel`, `tracking_No`, `postal_service` FROM `temporders` WHERE 1";
            }

            $result2 = mysqli_query($con, $query2);

            while ($row = mysqli_fetch_array($result2)) {
                $orgID = $row['id'];
                $orderID = $row['orderID'];
                $channel = $row['channel'];
                $tracking_No = $row['tracking_No'];
                $postalService = $row['postal_service'];

                $orderIdArr = explode(":",$orderID);

                $lineData = array($orgID, $orderIdArr[0], $orderIdArr[1], $channel, $tracking_No, $postalService);

                fputcsv($f, $lineData, $delimiter);
            }
        }

        // Move back to beginning of file
        fseek($f, 0);

        // Set headers to download file rather than displayed
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        //output all remaining data on a file pointer
        fpassthru($f);
    }else{
        echo "Please choose atleast one order to generate template. automatically close this tab after few seconds.";
        echo "<script>setTimeout(function(){
            window.top.close();
          }, 3000);</script>";
    }
    exit;
?>