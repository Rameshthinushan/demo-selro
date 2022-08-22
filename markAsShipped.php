<?php
    include "labelGeneration/refreshStore.php";
    // ini_set('max_execution_time', 0);
    
    $DATABASE_HOST   = 'localhost';
    $DATABASE_USER   = 'root';
    $DATABASE_PASS   = '';
    $DATABASE_NAME = 'u525933064_dashboard';

    $connect = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }else{
        $query = "SELECT * FROM temporders";
        $result = mysqli_query($connect, $query);

        $markAsShippedOrders = 0;
        $tempOrders = 0;

        $errorInfo = "";

        while ($row = mysqli_fetch_array($result)) {
            $MarkAsShippedStatus = false;
            $tempOrders = $tempOrders + 1;

            $orderIdd = "";

            $orderIddInfo = explode(":", $row["orderID"]);
            $orderNumb = $orderIddInfo[0];

            if (array_key_exists(1,$orderIddInfo)){
                $orderIdd = $orderIddInfo[1];
            }

            $postalService = $row["postal_service"];
            $shipDate = date('Y-m-d');
            $trackingNumber = trim($row["tracking_No"]);
            $channel = $row["channel"];

            if($row["label_B64"] != "" OR (($postalService == "express24" OR $postalService == "UPS") AND $trackingNumber != "")){

                if($orderIdd != ""){
                    if($channel == "SHOPIFY-LEDSone UK Ltd"){
                        $orderIdd = substr($orderIdd, 3); // contains LED so remove it, default not contains in selro
                    }else if($channel == "SHOPIFY-Electrical sone"){
                        $orderIdd = substr($orderIdd, 2); // contains ES so remove it, default not contains in selro
                    }else if($channel == "SHOPIFY-Vintagelite"){
                        $orderIdd = substr($orderIdd, 2); // contains VL so remove it, default not contains in selro
                    }else if($channel == "SHOPIFY-Ledsone DE"){
                        $orderIdd = substr($orderIdd, 4); // contains LSDE so remove it, default not contains in selro
                    }

                    if($row["shippedStatus"] != "true"){
                        // $MarkAsShipped = MarkAsShipped($orderIdd, $postalService, $shipDate, $trackingNumber, $channel);
                        $MarkAsShipped = SelroMarkAsShipped($orderIdd, $postalService, $shipDate, $trackingNumber, $channel);

                        $MarkAsShippedStatus = $MarkAsShipped['markAsShipped'];
                    }else if($row["shippedStatus"] == "true"){
                        $MarkAsShippedStatus = true;
                    }
                }
            }

            if($MarkAsShippedStatus){
                // shippedStatus
                $org_totalquery = "UPDATE temporders SET shippedStatus='true' WHERE id='".$row["id"]."'";
                $updating = mysqli_query($connect, $org_totalquery);

                $markAsShippedOrders = $markAsShippedOrders + 1;
            }else{
                $errorInfo .= $orderNumb." ".$orderIdd." ".$trackingNumber." ".$postalService."<br>";
            }
        }

        if($markAsShippedOrders == 0 && $errorInfo == ""){
            echo "Mark as shipped failed.";
        }else if($markAsShippedOrders == $tempOrders){
            echo "Mark as shipped completed.";
            echo "<script>setTimeout(function(){
                window.top.close();
            }, 3000);</script>";
        }else{
            echo "Orders mark as shipped. Missing order numbers.<br><br>";
            echo $errorInfo;
        }
        
        // if($errorInfo != ""){
        //     $from = "puvirajh@digitweb-jf.com";
        //     $to = "mugunthini@digitweb-jf.com";
        //     $Msg = $errorInfo;
        //     sendMail($from, $to, $Msg);
        // }
    }