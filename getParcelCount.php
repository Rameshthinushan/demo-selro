<?php
    $DATABASE_HOST   = 'localhost';
    $DATABASE_USER   = 'root';
    $DATABASE_PASS   = '';
    $DATABASE_NAME = 'u525933064_dashboard';
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }
    
    function getParceCounting($con){
        $message = "";

        $variable = array("245g LL", "900g parcel", "95g LL", "BPL Royal Mail 1st Class Large Letter", "CRL Royal Mail 24 Large Letter", "CRL Royal Mail 24 Parcel", "TPN Royal Mail Tracked 24 Non Signature", "express24", "Hermes ParcelShop Postable (Shop To Door) by MyHermes", "ParcelDenOnline Standard Package", "ParcelDenOnline Standard Parcel", "Rm manual");
        foreach ($variable as $key => $value) {
            $parcelCount = 0;
            $query = "SELECT COUNT(id) AS counting FROM `temporders` WHERE `postal_service` LIKE '".$value."' and (merge='' or merge='merged')";
            $result = mysqli_query($con, $query);

            $row = mysqli_fetch_array($result);
            $parcelCount = $row['counting'];

            $queryTotal = "SELECT COUNT(id) AS counting FROM `temporders` WHERE `postal_service` NOT LIKE 'international' and (merge='' or merge='merged')";
            $resultTotal = mysqli_query($con, $queryTotal);

            $rowTotal = mysqli_fetch_array($resultTotal);
            $totalParcelCount = $rowTotal['counting'];

            if($key == 0){
                $message .= "Total Parcel Count is ".$totalParcelCount."\n\n";
            }

            $message .= $value." - ".$parcelCount."\n";
        }

        return $message;
    }

    echo (getParceCounting($con));