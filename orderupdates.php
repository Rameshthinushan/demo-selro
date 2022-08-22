<?php
// include "functions.php";
include "labelGeneration/refreshStore.php";

//require_once('authenticate.php');
$DATABASE_HOST   = 'localhost';
$DATABASE_USER   = 'root';
$DATABASE_PASS   = '';
$DATABASE_NAME = 'u525933064_dashboard';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}
if (isset($_POST["csvbutton"])) {

    $fileName = $_FILES["file"]["tmp_name"];
	$booking=$_POST["booking"];
  	$csvtype = $_POST["csvtype"];
    $csvdate=$_POST["csvdate"];

    if ($_FILES["file"]["size"] > 0) {
      $ordercsvrows=0;
      $orderaddedrows=0;
      $germanorders=0;

        $file = fopen($fileName, "r");
		$g=0;
		$flag = true;
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
			  if($flag) { $flag = false; continue; }
              $ordercsvrows=$ordercsvrows+1;
          
          	  if($csvtype=="linnworks"){
                $orderID=$column[0];
                $date=date_create($column[1]);
                $date=date_format($date,"Y-m-d H:i:s");
                $channel = $column[9]."-".$column[10];
                $firstname=$column[3];
                $firstname=str_replace("'", "\'", $firstname);
                //$lastname=$column[5];
                //$telephone=$column[6];
                $email=$column[5];
                $currency=$column[6];
                $ordertotal = $column[7]+$column[24];
                $total = $ordertotal;
                $name = $column[8];
                $name =str_replace("'","", $name);

                $sku=$column[11];

                if($column[9]=="AMAZON")
                {
                  $sku=$column[21];
                  if($sku=="")
                  {
                    $sku=$column[11];
                  }
                }

                $sku = removeDashAfterTxt($sku);

                $quantity=$column[12];
                //$orderquantity=$column[13];
                //$lineitemtotal=$column[14];
                //$flags = $column[15];
                $shippingservice=$column[22];
                $shippingservice =str_replace("'","", $shippingservice);
                $shippingaddresscompany="";
                $shippingaddressline1=$column[13];
                $shippingaddressline1 =str_replace("'","", $shippingaddressline1);
                $shippingaddressline2=$column[14];
                $shippingaddressline2 =str_replace("'","", $shippingaddressline2);
                $shippingaddressline3=$column[15];
                $shippingaddressline3 =str_replace("'","", $shippingaddressline3);
                $shippingaddressregion=$column[17];
                $shippingaddresscity=$column[16];
                $shippingaddresscity =str_replace("'","", $shippingaddresscity);
                $shippingaddresspostcode=$column[18];
                $shippingaddresscountry=$column[19];
                $shippingaddresscountrycode=$column[20];
                $shippingcost=$column[24];
              }else if($csvtype=="zenstores"){
                $orderID=$column[0];
                $date=date_create($column[2]);
                $date=date_format($date,"Y-m-d H:i:s");

                $channel = str_replace(":", "-", $column[3]);
                $channelArr = explode("-",$channel,2);

                // Zenstore

                // Amazon : Amazon SRM
                // Amazon : Amazon ledsone de
                // Amazon : Cottage Lighting US
                // Amazon : Cottage Lighting CA
                // Amazon : Vintagelight Amazon
                // Amazon : LEDSone Amazon
                // Amazon : bestbringer@hotmail.com
                // Amazon : dcVoltage Amazon 
                // Amazon : LEDSone CA

                // ManualOrder

                // Ebay : electricalsone
                // Ebay : vintageinterior
                // Ebay : dctransformer
                // Ebay : ledpedia
                // Ebay : so_926407
                // Ebay : kani-5107
                // Ebay : re6865
                // Ebay : bestbringer
                // Ebay : longtek020
                // Ebay : led_sone
                // Ebay : dcvoltage
                // Ebay : light-effect
                // Ebay : vintage-flame132
                // Ebay : lighting_sone
                // Ebay : ledsonede
                // Ebay : cottagelighting
                // Ebay : coventrylights
                // Ebay : huettenlampen

                // Woocommerce : ledsone.co.uk

                // Etsy : CottageLightingShop
                // Etsy : Retrolightdesigns
                // Etsy : BarnlightingCanada

                // Shopify : vintage-light-web.myshopify.com
                // Shopify : ledsone-de.myshopify.com
                // Shopify : ledsone.myshopify.com
                // Shopify : electricalsoneuk.myshopify.com
                // Shopify : relicelectrical.myshopify.com
                
                $channelName = trim($channelArr[1]);
                if($channelName == "Amazon SRM"){
                  $channelName = "amazon SRM Amazon";
                }else if($channelName == "Amazon ledsone de"){
                  $channelName = "amazon Ledsonede";
                }else if($channelName == "Cottage Lighting US"){
                  $channelName = "amazon Cottage lighting";
                }else if($channelName == "Vintagelight Amazon"){
                  $channelName = "amazon Vintage light";
                }else if($channelName == "LEDSone Amazon"){
                  $channelName = "amazon Ledsone";
                }else if($channelName == "bestbringer@hotmail.com"){
                  $channelName = "Bestbringer";
                }else if($channelName == "dcVoltage Amazon"){
                  $channelName = "amazon Dcvoltage";
                }else if($channelName == "so_926407"){
                  $channelName = "sunsone";
                }else if($channelName == "kani-5107"){
                  $channelName = "kani_5107";
                }else if($channelName == "re6865"){
                  $channelName = "retroled";
                }else if($channelName == "light-effect"){
                  $channelName = "light_effect";
                }else if($channelName == "vintage-flame132"){
                  $channelName = "vintage_flame132";
                }else if($channelName == "CottageLightingShop"){
                  $channelName = "Etsy Cottage CA";
                }else if($channelName == "vintage-light-web.myshopify.com"){
                  $channelName = "Vintagelite";
                }else if($channelName == "ledsone-de.myshopify.com"){
                  $channelName = "Ledsone DE";
                }else if($channelName == "ledsone.myshopify.com"){
                  $channelName = "LEDSone UK Ltd";
                }else if($channelName == "electricalsoneuk.myshopify.com"){
                  $channelName = "Electrical sone";
                }else if($channelName == "relicelectrical.myshopify.com"){
                  $channelName = "Relicelectrical";
                }else if($channelName == "Retrolightdesigns"){
                  $channelName = "Retrolight";
                }

                // ManualOrder
                $channelNew = strtoupper(trim($channelArr[0]))."-".$channelName;

                if($channelNew == "MANUALORDER-Manual Orders"){
                  $channelNew = "MANUAL";
                }

                $channel = $channelNew;

                $firstname=$column[4]." ".$column[5];
                $firstname=str_replace("'", "\'", $firstname);
                //$lastname=$column[5];
                //$telephone=$column[6];
                $email=$column[7];
                $currency=$column[8];
                $ordertotal = $column[14];
                $total = $column[14];

                $zenstoresOrderTotal = $column[9];

                $name = $column[10];
                $name =str_replace("'","", $name);

                $sku=$column[11];

                $sku = removeDashAfterTxt($sku);

                $quantity=$column[13];
                //$orderquantity=$column[13];
                //$lineitemtotal=$column[14];
                //$flags = $column[15];
                $shippingservice=$column[19];
                $shippingservice =str_replace("'","", $shippingservice);
                $shippingaddresscompany=$column[20];
                $shippingaddressline1=$column[21];
                $shippingaddressline1 =str_replace("'","", $shippingaddressline1);
                $shippingaddressline2=$column[22];
                $shippingaddressline2 =str_replace("'","", $shippingaddressline2);
                $shippingaddressline3=$column[23];
                $shippingaddressline3 =str_replace("'","", $shippingaddressline3);
                $shippingaddressregion=$column[24];
                $shippingaddresscity=$column[25];
                $shippingaddresscity =str_replace("'","", $shippingaddresscity);
                $shippingaddresspostcode=$column[26];
                $shippingaddresscountry=$column[27];

                if($shippingaddresscountry=='Deutschland'){
                  $shippingaddresscountry = 'Germany';
                  $shippingaddresscountrycode = 'DE';
                }

                $shippingaddresscountrycode=$column[28];
                $shippingcost=0;

                if(trim($column[4]) == "" && trim($column[5]) == ""){
                  $lastInsertOrderResult = mysqli_query($con, "SELECT * FROM temporders ORDER BY id DESC LIMIT 1");
                  $lastInsertOrderResultRow = mysqli_fetch_array($lastInsertOrderResult);

                  if(trim($date) == ""){
                    $date = $lastInsertOrderResultRow['date'];;
                  }

                  if(trim($channel) == ""){
                    $channel = $lastInsertOrderResultRow['channel'];;
                  }

                  if(trim($firstname) == ""){
                    $firstname = $lastInsertOrderResultRow['firstname'];;
                  }

                  if(trim($email) == ""){
                    $email = $lastInsertOrderResultRow['email'];;
                  }

                  if(trim($currency) == ""){
                    $currency = $lastInsertOrderResultRow['currency'];;
                  }

                  if(trim($total) == ""){
                    $total = $lastInsertOrderResultRow['total'];;
                  }

                  if(trim($shippingaddressline1) == ""){
                    $shippingaddressline1 = $lastInsertOrderResultRow['shippingaddressline1'];;
                  }

                  if(trim($shippingaddressline2) == ""){
                    $shippingaddressline2 = $lastInsertOrderResultRow['shippingaddressline2'];;
                  }

                  if(trim($shippingaddressline3) == ""){
                    $shippingaddressline3 = $lastInsertOrderResultRow['shippingaddressline3'];;
                  }

                  if(trim($shippingaddressregion) == ""){
                    $shippingaddressregion = $lastInsertOrderResultRow['shippingaddressregion'];;
                  }
                  
                  if(trim($shippingaddresscity) == ""){
                    $shippingaddresscity = $lastInsertOrderResultRow['shippingaddresscity'];;
                  }

                  if(trim($shippingaddresspostcode) == ""){
                    $shippingaddresspostcode = $lastInsertOrderResultRow['shippingaddresspostcode'];;
                  }

                  if(trim($shippingaddresscountry) == ""){
                    $shippingaddresscountry = $lastInsertOrderResultRow['shippingaddresscountry'];;
                  }

                  if(trim($shippingaddresscountrycode) == ""){
                    $shippingaddresscountrycode = $lastInsertOrderResultRow['shippingaddresscountrycode'];;
                  }

                  if(trim($zenstoresOrderTotal) == ""){
                    $zenstoresOrderTotal = $lastInsertOrderResultRow['zenstoresOrderTotal'];;
                  }
                }
                
                if($shippingaddresscountry != "United Kingdom" && $shippingaddresscountry != "Great Britain"){
                  $shippingservice="international";
                }else{
                  $shippingservice="csv";
                }
                
                if($shippingaddresscountry == "United Kingdom" && $shippingcost > 3){
                  $shippingservice="firstclass";
                }
                
                $shippingservice =str_replace("'","", $shippingservice);
              }else if($csvtype=="avasam"){
                $orderID=$column[0];
                $date=date_create($column[11]);
                $date=date_format($date,"Y-m-d H:i:s");
                $channel = "AVASAM";
                $firstname=$column[2];
                $firstname=str_replace("'", "\'", $firstname);
                //$lastname=$column[5];
                // $telephone=$column[3];
                $email="";
                $currency=$column[12];
                $ordertotal = $column[17]+$column[19];
                $total = $ordertotal;
                $name = $column[14];
                $name =str_replace("'","", $name);

                $sku=$column[13];

                $sku = removeDashAfterTxt($sku);

                $quantity=$column[15];
                //$orderquantity=$column[13];
                //$lineitemtotal=$column[14];
                //$flags = $column[15];
                $shippingaddresscompany="";
                $shippingaddressline1=$column[4];
                $shippingaddressline1 =str_replace("'","", $shippingaddressline1);
                $shippingaddressline2=$column[5];
                $shippingaddressline2 =str_replace("'","", $shippingaddressline2);
                $shippingaddressline3=$column[6];
                $shippingaddressline3 =str_replace("'","", $shippingaddressline3);
                $shippingaddressregion=$column[8];
                $shippingaddresscity=$column[7];
                $shippingaddresscity =str_replace("'","", $shippingaddresscity);
                $shippingaddresspostcode=$column[10];
                $shippingaddresscountry=$column[9];
                $shippingaddresscountrycode = findCountryCode($shippingaddresscountry);
                $shippingcost=$column[19];
                
                if($shippingaddresscountry != "United Kingdom" && $shippingaddresscountry != "Great Britain"){
                  $shippingservice="international";
                }else{
                  $shippingservice="csv";
                }
                
                if($shippingaddresscountry == "United Kingdom" && $shippingcost > 4){
                  $shippingservice="firstclass";
                }
                
                $shippingservice =str_replace("'","", $shippingservice);
              }else if($csvtype=="manomano"){
                $orderID=$column[1];
                $date=date_create($column[0]);
                $date=date_format($date,"Y-m-d H:i:s");
                $channel = "MANOMANO";
                $firstname=$column[3]." ".$column[4];
                $firstname=trim(str_replace("'", "\'", $firstname));
                $email=$column[2];
                // 39 ingle price
                // 36 qty
                // 38 shipping cost
                $ordertotal = ($column[39] * $column[36])  + ($column[38] * $column[36] * 1.2);
                $total = $ordertotal;
                $name = $column[34];
                $name =str_replace("'","", $name);

                $sku=$column[35];

                $sku = removeDashAfterTxt($sku);

                $quantity=$column[36];
                //$orderquantity=$column[13];
                //$lineitemtotal=$column[14];
                //$flags = $column[15];
                $shippingaddresscompany="";
                $shippingaddressline1=$column[6];
                $shippingaddressline1 =str_replace("'","", $shippingaddressline1);
                $shippingaddressline2=$column[7];
                $shippingaddressline2 =str_replace("'","", $shippingaddressline2);
                $shippingaddressline3=$column[8];
                $shippingaddressline3 =str_replace("'","", $shippingaddressline3);
                $shippingaddressregion="";
                $shippingaddresscity=$column[10];
                $shippingaddresscity =str_replace("'","", $shippingaddresscity);
                $shippingaddresspostcode=$column[9];
                $shippingaddresscountrycode = $column[12];
                $shippingaddresscountry = findCountryByCountryCode($shippingaddresscountrycode);
                $shippingcost=($column[38] * $column[36] * 1.2);
                
                // added
                $currency=findCurrencyByCountryCode($shippingaddresscountrycode);

                // $shippingservice="csv";
                
                if($shippingaddresscountry != "United Kingdom" && $shippingaddresscountry != "Great Britain"){
                  $shippingservice="international";
                }else{
                  $shippingservice="csv";
                }
                
                if($shippingaddresscountry == "United Kingdom" && $shippingcost > 5){
                  $shippingservice="firstclass";
                }
                
                $shippingservice =str_replace("'","", $shippingservice);
              }else if($csvtype=="FBA"){
                $customerNotes = $shippingaddressline1 = $shippingaddressline2 = $shippingaddressline3 = $shippingaddressregion = $shippingaddresscity = $shippingaddresspostcode = $shippingaddresscountry = $shippingaddresscountrycode = $shipping_cost = $postal_service = $shippingaddresscompany = $shipPhoneNumber = $email = $currency = $itemsImageUrl = '';

                $ordertotal = $shippingcost = $total = 0;

                if (date('H') < 10) {
                    $booking = "1st Booking";
                }else{
                    $booking = "2nd Booking";
                }

                $shippingservice = "fba";

                $status = 'pending';
                $date = $csvdate = date("Y-m-d");
                $unit = 'unit2';

                $channel = trim($column[4]).' (WAREHOUSE TRANSFER)';
                // get from stock transfer list
                $orderID = "Shipment id: ".trim($column[5])."-"."Plan id: ".trim($column[6]);
                $name = "ASIN Number ".trim($column[3]);
                $sku = trim($column[0]);
                $mapped_sku =  trim($column[2]);
                $firstname = trim($column[7]);
                $quantity = trim($column[1]);
              }
          
              $status='Pending';
              $unit='unit2';
		
          	  // flags
              $flags = getFlags($sku);
              //Flags end

              $postalService = getPostalService($sku, $flags, $quantity, $ordertotal, $shippingcost, $shippingaddresspostcode, $channel, $shippingservice, $con);

              $subflags = getSubFlags($sku,$flags,$con);

          if($shippingaddresscountry=="Germany")
          {
            $germanorders=$germanorders+1;
          }

          // As per Muguntha akka's request and Pratheepan's confirmation removing germany filter
          //if($shippingaddresscountry!="Germany")
          //{
            if($csvtype=="linnworks"){
              $sql = "INSERT into temporders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, subflags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, shipping_cost, postal_service, booking, csvdate, unit, total) values ('". $orderID ."','". $status ."','". $date ."','". $channel ."','". $firstname ."','". $email ."','". $currency ."','". $ordertotal ."','". $name ."','". $sku ."','". $quantity ."','". $flags ."','". $subflags ."','". $shippingservice ."','". $shippingaddressline1 ."','". $shippingaddressline2 ."','". $shippingaddressline3 ."','". $shippingaddressregion ."','". $shippingaddresscity ."','". $shippingaddresspostcode ."','". $shippingaddresscountry ."','". $shippingaddresscountrycode  ."','". $shippingcost  ."','". $postalService ."','". $booking ."','". $csvdate ."','". $unit ."', '". $total ."')";
            }else if($csvtype=="zenstores"){
              $sql = "INSERT into temporders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, subflags, shippingservice, shippingaddresscompany, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, shipping_cost, postal_service, booking, csvdate, unit, total, zenstoresOrderTotal) values ('". $orderID ."','". $status ."','". $date ."','". $channel ."','". $firstname ."','". $email ."','". $currency ."','". $ordertotal ."','". $name ."','". $sku ."','". $quantity ."','". $flags ."','". $subflags ."','". $shippingservice ."','". $shippingaddresscompany ."','". $shippingaddressline1 ."','". $shippingaddressline2 ."','". $shippingaddressline3 ."','". $shippingaddressregion ."','". $shippingaddresscity ."','". $shippingaddresspostcode ."','". $shippingaddresscountry ."','". $shippingaddresscountrycode  ."','". $shippingcost  ."','". $postalService ."','". $booking ."','". $csvdate ."','". $unit ."', '". $total ."', '". $zenstoresOrderTotal ."')";
            }else if($csvtype=="avasam" && $column[23] == "Created"){
              $sql = "INSERT into temporders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, subflags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, shipping_cost, postal_service, booking, csvdate, unit, total) values ('". $orderID ."','". $status ."','". $date ."','". $channel ."','". $firstname ."','". $email ."','". $currency ."','". $ordertotal ."','". $name ."','". $sku ."','". $quantity ."','". $flags ."','". $subflags ."','". $shippingservice ."','". $shippingaddressline1 ."','". $shippingaddressline2 ."','". $shippingaddressline3 ."','". $shippingaddressregion ."','". $shippingaddresscity ."','". $shippingaddresspostcode ."','". $shippingaddresscountry ."','". $shippingaddresscountrycode  ."','". $shippingcost  ."','". $postalService ."','". $booking ."','". $csvdate ."','". $unit ."', '". $total ."')";
            }else if($csvtype=="manomano"){
              $sql = "INSERT into temporders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, subflags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, shipping_cost, postal_service, booking, csvdate, unit, total) values ('". $orderID ."','". $status ."','". $date ."','". $channel ."','". $firstname ."','". $email ."','". $currency ."','". $ordertotal ."','". $name ."','". $sku ."','". $quantity ."','". $flags ."','". $subflags ."','". $shippingservice ."','". $shippingaddressline1 ."','". $shippingaddressline2 ."','". $shippingaddressline3 ."','". $shippingaddressregion ."','". $shippingaddresscity ."','". $shippingaddresspostcode ."','". $shippingaddresscountry ."','". $shippingaddresscountrycode  ."','". $shippingcost  ."','". $postalService ."','". $booking ."','". $csvdate ."','". $unit ."', '". $total ."')";
            }else if($csvtype=="FBA"){
              $postalService = trim($column[4]);

              $sql = "INSERT into temporders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, orgSku, quantity, flags, subflags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, shipping_cost, postal_service, booking, csvdate, unit, total) values ('". $orderID ."','". $status ."','". $date ."','". $channel ."','". $firstname ."','". $email ."','". $currency ."','". $ordertotal ."','". $name ."','". $sku ."','". $mapped_sku ."','". $quantity ."','". $flags ."','". $subflags ."','". $shippingservice ."','". $shippingaddressline1 ."','". $shippingaddressline2 ."','". $shippingaddressline3 ."','". $shippingaddressregion ."','". $shippingaddresscity ."','". $shippingaddresspostcode ."','". $shippingaddresscountry ."','". $shippingaddresscountrycode  ."','". $shippingcost  ."','". $postalService ."','". $booking ."','". $csvdate ."','". $unit ."', '". $total ."')";
            }   

            if(mysqli_query($con, $sql))
            {
              $orderaddedrows=$orderaddedrows+1;
            }
            else
            {
              $missedorder[$g]=$orderID;
              $g=$g+1;
            }
          //}
        }
        
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
                    $clientname="Name : ".$orderrow["firstname"];
                    $address="";
                    if(!empty($clientname))
                    {
                        $address=$address.$clientname."<br>";
                    }
                    if(!empty($orderrow["shippingaddresscompany"]))
                    {
                        $address=$address.$orderrow["shippingaddresscompany"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddressline1"]))
                    {
                        $address=$address.$orderrow["shippingaddressline1"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddressline2"]))
                    {
                        $address=$address.$orderrow["shippingaddressline2"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddressline3"]))
                    {
                        $address=$address.$orderrow["shippingaddressline3"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddressregion"]))
                    {
                        $address=$address.$orderrow["shippingaddressregion"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddresscity"]))
                    {
                        $address=$address.$orderrow["shippingaddresscity"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddresspostcode"]))
                    {
                        $address=$address.$orderrow["shippingaddresspostcode"]."<br>";
                    }
                    if(!empty($orderrow["shippingaddresscountry"]))
                    {
                        $address=$address.$orderrow["shippingaddresscountry"]."<br>";
                    }
        for($j=$i+1;$j<$rowCount;$j++)
        {
        $mergeresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $ids_array[$j] . "'");
        $mergerow= mysqli_fetch_array($mergeresult);
          $clientname="Name : ".$mergerow["firstname"];
          $addressnew="";
              if(!empty($clientname))
              {
                $addressnew=$addressnew.$clientname."<br>";
              }
              if(!empty($mergerow["shippingaddresscompany"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddresscompany"]."<br>";
              }
              if(!empty($mergerow["shippingaddressline1"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddressline1"]."<br>";
              }
              if(!empty($mergerow["shippingaddressline2"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddressline2"]."<br>";
              }
              if(!empty($mergerow["shippingaddressline3"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddressline3"]."<br>";
              }
              if(!empty($mergerow["shippingaddressregion"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddressregion"]."<br>";
              }
              if(!empty($mergerow["shippingaddresscity"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddresscity"]."<br>";
              }
              if(!empty($mergerow["shippingaddresspostcode"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddresspostcode"]."<br>";
              }
              if(!empty($mergerow["shippingaddresscountry"]))
              {
                $addressnew=$addressnew.$mergerow["shippingaddresscountry"]."<br>";
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

        // check shipping included or not for zenstores orders - start
        if($csvtype=="zenstores"){
          $totalquery = "SELECT * FROM temporders WHERE merge='' ORDER BY ordertotal ASC, date ASC"; 
          $totalResult = mysqli_query($con, $totalquery);
          while($totalRow = mysqli_fetch_array($totalResult))
          {
            if($totalRow["zenstoresOrderTotal"] > $totalRow["ordertotal"]){
              $shippCost = $totalRow["zenstoresOrderTotal"] - $totalRow["ordertotal"];

              $newOrdTotal = $totalRow["ordertotal"] + $shippCost;
              $newOrdTotalQuery = "  
                  UPDATE temporders   
                  SET shipping_cost='$shippCost', ordertotal='$newOrdTotal', total='$newOrdTotal'
                  WHERE id='".$totalRow["id"]."'";
              mysqli_query($con, $newOrdTotalQuery);
            }
          }
        }
        // check shipping included or not for zenstores orders - end

       //mergetotal update
       $totalmergequery = "SELECT * FROM temporders WHERE merge='Merged'ORDER BY ordertotal ASC, date ASC"; 
       $totalresult = mysqli_query($con, $totalmergequery);
       while($totalrow = mysqli_fetch_array($totalresult))
       {
        $mergeid=$totalrow["date"]."-".$totalrow["orderID"];
        $mergequery = "SELECT * FROM temporders WHERE merge='" . $mergeid . "' ORDER BY ordertotal ASC, date ASC";
        $mergeresult = mysqli_query($con, $mergequery);
        $mergetotal=$totalrow["ordertotal"];
        while($mergerow = mysqli_fetch_array($mergeresult))
        {
          $mergetotal=$mergetotal+$mergerow["ordertotal"];
        }

        if($csvtype=="zenstores"){
          if($totalrow["zenstoresOrderTotal"] > $mergetotal){
            $shippingCost = $totalrow["zenstoresOrderTotal"] - $mergetotal;

            $mergetotal = $totalrow["zenstoresOrderTotal"];

            $newOrderTotal = $totalrow["ordertotal"] + $shippingCost;
            $newOrderTotalQuery = "  
                UPDATE temporders   
                SET shipping_cost='$shippingCost', ordertotal='$newOrderTotal'
                WHERE id='".$totalrow["id"]."'";
            mysqli_query($con, $newOrderTotalQuery);
          }
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

        // $postalServiceM = getPostalService($totalrow["sku"], $totalrow["flags"], $totalrow["quantity"], $mergetotal, $totalrow["shipping_cost"], $totalrow["shippingaddresspostcode"], $totalrow["channel"], $totalrow["shippingservice"], $con);

        // $mergetotalquery2 = "UPDATE temporders SET postal_service = '$postalServiceM' WHERE merge='".$mergeid."'";
        // mysqli_query($con, $mergetotalquery2);
        
        // $totalquery2 = "UPDATE temporders SET postal_service = '$postalServiceM' WHERE id='".$totalid."'";
        // mysqli_query($con, $totalquery2);
       }
       /*
		echo('<h2>Please check the below orders which did not update under the system</h2>');
        echo('<table border="1"><tr><th>Order ID</th></tr>');
        for($i=0;$i<count($missedorder);$i++) {
          echo('<tr>');
          echo('<td>' . $missedorder[$i] . '</td>');
          echo('</tr>');
        }
        echo('</table>');
        */
    }
    $message=$orderaddedrows." out of ".$ordercsvrows. " orders are added successfully";
    if($germanorders>0)
    {
      $message .= '\nGerman Orders - '.$germanorders;
    }
    if (!empty($missedorder))
    {
    $message .= '\nPlease Check the below missing orders';
    for($i=0;$i<count($missedorder);$i++) {
      $message .='\n'.$missedorder[$i];
    }
    }
    echo "<script type='text/javascript'>
   alert('$message');
   document.location.href='index.php';
</script>";
}
