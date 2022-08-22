<?php  
 $connect = mysqli_connect("localhost","root","","u525933064_dashboard");
 $insertflag="false";
 if(!empty($_POST))  
 {  
      $output = '';  
      $message = '';  
      $sku = mysqli_real_escape_string($connect, $_POST["sku"]);  
      $channel = mysqli_real_escape_string($connect, $_POST["channel"]);  
      if($_POST["order_id"] != '')  
      { 
        $query = "  
        UPDATE temporders   
        SET sku='$sku',   
        channel='$channel'
        WHERE id='".$_POST["order_id"]."'";  
        $message = 'Data Updated';
      }
      elseif($_POST["list_check"] != '')
      {
          $orderflags=implode(', ', $_POST["flags"]);
          $orderids = explode(',', $_POST["list_check"]);
          foreach ($orderids as $value) {
               $query = "  
          UPDATE temporders   
          SET flags='$orderflags'
          WHERE id='".$value."'";
          mysqli_query($connect, $query);  
           }     
           $message = 'Flags Updated';
           $insertflag="true";  
      }
      elseif($_POST["idlist"] != '')
      {
          $ids = explode(',', $_POST["idlist"]);
          foreach ($ids as $value) {
        $result = mysqli_query($connect, "SELECT * FROM temporders WHERE id='".$value."'");
        $row= mysqli_fetch_array($result);
        $status="Pending";
        $phpdate = strtotime($row['date']);
     $date = date( 'Y-m-d', $phpdate );
     $flags=$row['flags'];
     if($row['merge']=="Merged")
     {
          $flags=$row['flags'].", Merged";
     }
     $firstname=$row['firstname'];
     $firstname=str_replace("'", "\'", $firstname);
        $sql = "INSERT into orders (orderID, status, date, channel, firstname, email, currency, ordertotal, name, sku, quantity, flags, shippingservice, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, booking, csvdate, unit) values ('". $row['orderID'] ."','". $status ."','". $date ."','". $row['channel'] ."','". $firstname ."','". $row['email'] ."','". $row['currency'] ."','". $row['ordertotal'] ."','". $row['name'] ."','". $row['sku'] ."','". $row['quantity'] ."','". $flags ."','". $row['shippingservice'] ."','". $row['shippingaddressline1'] ."','". $row['shippingaddressline2'] ."','". $row['shippingaddressline3'] ."','". $row['shippingaddressregion'] ."','". $row['shippingaddresscity'] ."','". $row['shippingaddresspostcode'] ."','". $row['shippingaddresscountry'] ."','". $row['shippingaddresscountrycode'] ."','". $row['booking'] ."','". $row['csvdate'] ."','". $row['unit'] ."')";
        //$sql = "INSERT into orders (orderID, status, date)
				  // values ('". $row['orderID'] ."','". $status ."','". $row['date'] ."')";
         // $deletesql = "DELETE FROM temporders WHERE id = '".$value."'";
          mysqli_query($connect, $sql); 
          //mysqli_query($connect, $deletesql);
     }
     $message = 'Orders Moved to Open';
           $insertflag="true";
  ?>
  
  <script type="text/javascript">window.open( "../orderscsvtest.php" )</script> 
    <?php 
      }
      elseif($_POST["iddelete"] != '')
      {
          $ids = explode(',', $_POST["iddelete"]);
          foreach ($ids as $value) {
         $deletesql = "DELETE FROM temporders WHERE id = '".$value."'";
          mysqli_query($connect, $deletesql);
     }
          $message = 'Orders Deleted';
           $insertflag="true";
      }
      
      else  
      {  
           $query = "  
           INSERT INTO temporders(sku, channel)  
           VALUES('$sku', '$channel');  
           ";  
           $message = 'Data Inserted'.$_POST["order_id"];  
      }  
      if(($insertflag=="true") OR (mysqli_query($connect, $query))) 
      {  
           $output .= '<label class="text-success">' . $message . '</label>';  
           $select_query = "SELECT * FROM temporders ORDER BY date DESC";  
           $result = mysqli_query($connect, $select_query);  
           $output .= '  
                <table class="table table-bordered" id="pendingts">  
                     <tr>
                     <th width="5%"><button type="button" id="pendingselectAll" class="main">
          		<span class="sub"></span> Select All </button></th>  
                    <th width="15%">Image</th> 
                    <th width="5%">Order ID</th>
                    <th width="15%">SKU</th> 
                    <th width="20%">Address</th> 
                     <th width="10%">Flags</th>
                    <th width="10%">Inventory</th>
                     <th width="5%">Edit</th>  
                     <th width="5%">View</th>
                     <th width="5%">Delete</th> 
                     </tr>  
           ';  
           while($row = mysqli_fetch_array($result))  
           {  
               if ($row["merge"]!="" && $row["merge"]!="Merged")
               {
                    continue;
               } 
                                                //inventory alert start
	unset($pick);
	$pick = array();
	$invflag="false";
	$ordersku=$row["sku"];
	if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
			$encrow= mysqli_fetch_array($encresult);
			$ordersku=$encrow['originalsku'];
		}
		$skus = explode ("+", $ordersku);
		$skuno=count($skus);
		if($skuno>0)
		{
			$l=($skuno-1);
			for ($m = 0; $m <= $l; $m++)
			{
			$sku=$skus[$m];
			$quantity=$row['quantity'];
			if(substr($sku, -2)=="PK")
				{
					$pknumber=substr($sku, -3, -2);
					if($pknumber=="A")
					{
						$pknumber=10;
					}
					elseif($pknumber=="B")
					{
						$pknumber=15;
					}
					elseif($pknumber=="C")
					{
						$pknumber=20;
					}
					elseif($pknumber=="D")
					{
						$pknumber=30;
					}
					elseif($pknumber=="E")
					{
						$pknumber=50;
					}
					elseif($pknumber=="F")
					{
						$pknumber=100;
					}
					$pknumber=(int)$pknumber;
					$sku=substr($sku, 0, -3);
					$quantity=($row['quantity']*$pknumber);
				}
			if(empty($pick))
				{
				$pick = array(array($sku,$quantity));
				}
			else
				{
				$a=count($pick);
				$pick[$a][0]=$sku;
				$pick[$a][1]=$quantity;
				}
			}
		}
		else
		{
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow= mysqli_fetch_array($encresult);
				$ordersku=$encrow['originalsku'];
			}
		$sku=$ordersku;
		$quantity=$row['quantity'];
		if(substr($sku, -2)=="PK")
			{
				$pknumber=substr($sku, -3, -2);
				if($pknumber=="A")
				{
					$pknumber=10;
				}
				elseif($pknumber=="B")
				{
					$pknumber=15;
				}
				elseif($pknumber=="C")
				{
					$pknumber=20;
				}
				elseif($pknumber=="D")
				{
					$pknumber=30;
				}
				elseif($pknumber=="E")
				{
					$pknumber=50;
				}
				elseif($pknumber=="F")
				{
					$pknumber=100;
				}
				$pknumber=(int)$pknumber;
				$sku=substr($sku, 0, -3);
				$quantity=($row['quantity']*$pknumber);
			}
			if(empty($pick))
					{
					$pick = array(array($sku,$quantity));
					}
				else
					{
					$a=count($pick);
					$pick[$a][0]=$sku;
					$pick[$a][1]=$quantity;
					}
	}
	$invalert="";
	for($x=0;$x<count($pick);$x++)
	{

				$quantityresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$quantityrow["product"]= mysqli_fetch_array($quantityresult);
				$invquantity=$quantityrow['product']['Quantity'];

				if(($invquantity<0)||($quantityrow['product']['outofstock']=="Yes"))
				{
					$invquantity=0;
				}
				if(empty($quantityrow["product"]))
				{
					$invquantity="NA";
				}
				if(($invquantity<$pick[$x][1])||($invquantity=="NA"))
				{
					$invflag="true";
				}
				$invalert=$invalert.$pick[$x][0].": ".$pick[$x][1]."/".$invquantity."<br>";

	}
    //invalert end
               $ordersku= $row["sku"];
               $mainimageresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
               $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
               $mainimageorder=$mainimagerow['comboproducts']['image'];
               if(empty($mainimageorder))
               {
                    $mainimageresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                    $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
                    $mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
               } 
               $clientname="Name : ".$row["firstname"];
                    $address="";
                    if(!empty($clientname))
                    {
                        $address=$address.$clientname."<br>";
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
                    if($row["merge"]=="Merged")
                    {
                         $mergeid=$row["date"]."-".$row["orderID"];     
                         $mergequery = "SELECT * FROM temporders WHERE merge='" . $mergeid . "'";
                         $mergeresult = mysqli_query($connect, $mergequery);
                         $row_cnt = mysqli_num_rows($mergeresult);
                         $rowspanno=($row_cnt+1);
                    }
                    else
                    {
                    $rowspanno=1;
                    }
                $output .= '  
                     <tr> 
                         <td style="text-align:center"><input type="checkbox" name="orders[]" value='.$row["id"].'></td> 
                         <td><img style="width:100px; height:auto;" src='.$mainimageorder.'></td>
                         <td>'.$row["orderID"]; 
                         if($row["merge"]=="Merged")
                         {
                              $output .= '<br>Merge';
                         } 
                         $output .= '
                         </td>
                                    <td>'.$row["sku"].'<br>'.$row["date"].'<br>'.$row["channel"].'</td> 
                                    <td rowspan='. $rowspanno. '>'. $address.'</td>
                          <td>' .$row["flags"]. '</td>';
							if($invflag=="true")
							{
                                $output .= '<td style="text-align:center; background-color: #F00000;">'.$invalert.'</td>';
							}
							else
							{
                                $output .= '<td style="text-align:center;">'.$invalert.'</td>';
							}
                          $output .= '
                          <td><input type="button" name="edit" value="Edit" id="'.$row["id"] .'" class="btn btn-info btn-xs edit_data" /></td>  
                          <td><input type="button" name="view" value="view" id="' . $row["id"] . '" class="btn btn-info btn-xs view_data" /></td> 
                          <td><input type="button" name="delete" value="delete" id="' . $row["id"] . '" class="btn btn-info btn-xs delete_data" /></td> 
                     </tr>  
                ';
           if($row["merge"]=="Merged")
                                   { 
                                   while($mergerow = mysqli_fetch_array($mergeresult))
                                   {
                                                                                                               // merge inventory alert start
	unset($pick);
	$pick = array();
	$invflag="false";
	$ordersku=$mergerow["sku"];
	if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
			$encrow= mysqli_fetch_array($encresult);
			$ordersku=$encrow['originalsku'];
		}
		$skus = explode ("+", $ordersku);
		$skuno=count($skus);
		if($skuno>0)
		{
			$l=($skuno-1);
			for ($m = 0; $m <= $l; $m++)
			{
			$sku=$skus[$m];
			$quantity=$mergerow['quantity'];
			if(substr($sku, -2)=="PK")
				{
					$pknumber=substr($sku, -3, -2);
					if($pknumber=="A")
					{
						$pknumber=10;
					}
					elseif($pknumber=="B")
					{
						$pknumber=15;
					}
					elseif($pknumber=="C")
					{
						$pknumber=20;
					}
					elseif($pknumber=="D")
					{
						$pknumber=30;
					}
					elseif($pknumber=="E")
					{
						$pknumber=50;
					}
					elseif($pknumber=="F")
					{
						$pknumber=100;
					}
					$pknumber=(int)$pknumber;
					$sku=substr($sku, 0, -3);
					$quantity=($mergerow['quantity']*$pknumber);
				}
			if(empty($pick))
				{
				$pick = array(array($sku,$quantity));
				}
			else
				{
				$a=count($pick);
				$pick[$a][0]=$sku;
				$pick[$a][1]=$quantity;
				}
			}
		}
		else
		{
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow= mysqli_fetch_array($encresult);
				$ordersku=$encrow['originalsku'];
			}
		$sku=$ordersku;
		$quantity=$mergerow['quantity'];
		if(substr($sku, -2)=="PK")
			{
				$pknumber=substr($sku, -3, -2);
				if($pknumber=="A")
				{
					$pknumber=10;
				}
				elseif($pknumber=="B")
				{
					$pknumber=15;
				}
				elseif($pknumber=="C")
				{
					$pknumber=20;
				}
				elseif($pknumber=="D")
				{
					$pknumber=30;
				}
				elseif($pknumber=="E")
				{
					$pknumber=50;
				}
				elseif($pknumber=="F")
				{
					$pknumber=100;
				}
				$pknumber=(int)$pknumber;
				$sku=substr($sku, 0, -3);
				$quantity=($mergerow['quantity']*$pknumber);
			}
			if(empty($pick))
					{
					$pick = array(array($sku,$quantity));
					}
				else
					{
					$a=count($pick);
					$pick[$a][0]=$sku;
					$pick[$a][1]=$quantity;
					}
	}
	$invalert="";
	for($x=0;$x<count($pick);$x++)
	{

				$quantityresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$quantityrow["product"]= mysqli_fetch_array($quantityresult);
				$invquantity=$quantityrow['product']['Quantity'];

				if(($invquantity<0)||($quantityrow['product']['outofstock']=="Yes"))
				{
					$invquantity=0;
				}
				if(empty($quantityrow["product"]))
				{
					$invquantity="NA";
				}
				if(($invquantity<$pick[$x][1])||($invquantity=="NA"))
				{
					$invflag="true";
				}
				$invalert=$invalert.$pick[$x][0].": ".$pick[$x][1]."/".$invquantity."<br>";

	}
    //invalert end
                                   $ordersku= $mergerow["sku"];
                                   $mainimageresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
                                   $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
                                   $mainimageorder=$mainimagerow['comboproducts']['image'];
                                   if(empty($mainimageorder))
                                   {
                                        $mainimageresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                                        $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
                                        $mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
                                   } 
                                   $output .= '  
                               <tr>
                                   <td style="text-align:center"><input type="checkbox" name="orders[]" value='.$mergerow["id"].'></td>
                                   <td><img style="width:100px; height:auto;" src='.$mainimageorder.'></td>
                                    <td>'.$mergerow["orderID"].'</td>
                                    <td>'.$mergerow["sku"].'<br>'.$mergerow["date"].'<br>'.$mergerow["channel"].'</td> 
                                    <td>'.$mergerow["flags"].'</td>';
                                    if($invflag=="true")
							{
                                $output .= '<td style="text-align:center; background-color: #F00000;">'.$invalert.'</td>';
							}
							else
							{
                                $output .= '<td style="text-align:center;">'.$invalert.'</td>';
							}
                          $output .= '
                                    <td><input type="button" name="edit" value="Edit" id="'.$mergerow["id"].'" class="btn btn-info btn-xs edit_data" /></td>  
                                    <td><input type="button" name="view" value="view" id="'.$mergerow["id"].'" class="btn btn-info btn-xs view_data" /></td> 
                                    <td><input type="button" name="delete" value="delete" id="'.$mergerow["id"].'" class="btn btn-info btn-xs delete_data" /></td> 
                               </tr>';  
                                   }
                                   }  
           }  
           $output .= '</table>';  
      }  
      echo $output;  
 }  
 ?>
 