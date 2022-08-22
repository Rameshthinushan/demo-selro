<?php  
 //delete.php  
 $connect = mysqli_connect("localhost","root","","u525933064_dashboard");
 if(isset($_POST["order_id"]))  
 {  
      $query = "DELETE FROM temporders WHERE id = '".$_POST["order_id"]."'";  
      if(mysqli_query($connect, $query))  
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