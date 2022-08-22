<?php 
session_start();
// If the user is not logged in redirect to the login page...
// if (!isset($_SESSION['loggedin'])) {
// 	header('Location: ../index.html');
// 	exit();
// } 
 $connect = mysqli_connect("localhost","root","","u525933064_dashboard");
 $query = "SELECT * FROM temporders ORDER BY date DESC"; 
 if(isset($_POST["category"]))
{
     $category=$_POST["category"];
     $query = "SELECT * FROM temporders WHERE flags='$category' ORDER BY date DESC";
} 
 $result = mysqli_query($connect, $query); 
 ?>  
 <!DOCTYPE html>  
 <html>  
      <head>  
           <title>LEDSone Orders</title>           
          <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
           <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>  
           <link href="../style.css" rel="stylesheet" type="text/css"> 
           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />      
      </head>  
      <body> 
      <nav class="navtop" id="desktoponly" style="height:80px; position: fixed;">
<div>
				<h1>Orders</h1>
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
                     <div align="right" style="position: fixed; /* Set the navbar to fixed position */
  top: 90px; right:300px; /* Position the navbar at the top of the page */
">  
                         <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_filter" >Filter</button>
                         <button type="button" name="addcsv" id="addcsv" data-toggle="modal" data-target="#addcsv_data_Modal" class="btn btn-warning">Orders CSV</button> 
                          <button type="button" name="add" id="add" data-toggle="modal" data-target="#add_data_Modal" class="btn btn-warning">Add New Order</button>  
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_form" >Add Flags</button>
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_move" >Move to Open</button>
                          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_delete" style="background-color: #f44336;">Delete</button>
                     </div>  
                     <br />  
                     <div id="employee_table" style="margin-top:80px;">  
                          <table class="table table-bordered" id="pendingts">  
                               <tr>  
                               <th width="5%"><button type="button" id="pendingselectAll" class="main">
          				<span class="sub"></span> Select All </button></th>
                                   <th width="15%">Image</th> 
                                   <th width="5%">Order ID</th>
                                   <th width="15%">SKU</th> 
                                   <th width="20%">Address</th> 
                                    <th width="10%">Flags</th>
                                    <th width="15%">Inventory</th>
                                    <th width="5%">Edit</th>  
                                    <th width="5%">View</th>
                                    <th width="5%">Delete</th>     
                               </tr>  
                               <?php  
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

				if($quantityrow['product']['outofstock']=="Yes")
				{
					$invquantity="Out of Stock";
				}
				if(empty($quantityrow["product"]))
				{
					$invquantity="NA";
				}
				if(($invquantity=="Out of Stock")||($invquantity=="NA"))
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

                               ?>  
                               <tr>
                                   <td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $row["id"]; ?>" ></td>
                                    <td><?php echo "<img style='width:100px; height:auto;' src='".$mainimageorder."'>"; ?></td>
                                    <td><?php echo $row["orderID"];if($row["merge"]=="Merged") echo '<br>Merge'; ?></td>
                                    <td><?php echo $row["sku"].'<br>'.$row["date"].'<br>'.$row["channel"]; ?></td> 
                                    <td rowspan=<?php echo $rowspanno; ?>><?php echo $address; ?></td>
                                    <td><?php echo $row["flags"]; ?></td>
                                    
                                    <?php
							if($invflag=="true")
							{
						echo '<td style="text-align:center; background-color: #F00000; ">';
						echo $invalert.'<br>';
                        ?>
						</td>
                        <?php
							}
							else
							{
						echo '<td style="text-align:center;">';
						echo $invalert;
						echo '</td>';
							}
						?>
                                    <td><input type="button" name="edit" value="Edit" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs edit_data" /></td>  
                                    <td><input type="button" name="view" value="view" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs view_data" /></td> 
                                    <td><input type="button" name="delete" value="delete" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs delete_data" /></td> 
                               </tr>  
                               <?php 
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

				if($quantityrow['product']['outofstock']=="Yes")
				{
					$invquantity='Out of Stock';
				}
				if(empty($quantityrow["product"]))
				{
					$invquantity="NA";
				}
				if(($invquantity=='Out of Stock')||($invquantity=="NA"))
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
                               ?>  
                               <tr>
                                   <td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $mergerow["id"]; ?>" ></td>
                                    <td><?php echo "<img style='width:100px; height:auto;' src='".$mainimageorder."'>"; ?></td>
                                    <td><?php echo $mergerow["orderID"]; ?></td>
                                    <td><?php echo $mergerow["sku"].'<br>'.$mergerow["date"].'<br>'.$mergerow["channel"]; ?></td> 
                                    <td><?php echo $mergerow["flags"]; ?></td>
                                    <?php
							if($invflag=="true")
							{
						echo '<td style="text-align:center; background-color: #F00000; ">';
						echo $invalert;
						echo '</td>';
							}
							else
							{
						echo '<td style="text-align:center;">';
						echo $invalert;
						echo '</td>';
							}
						?>
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
                     </div>  
                </div>  
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
                          <input type="date" name="csvdate" id="csvdate" class="form-control" />  
                          <br />  
                          <label>Booking</label>
                          <select name="booking" id="booking" class="form-control">
						<option value="1st Booking">1st Booking</option>
						<option value="2nd Booking">2nd Booking</option>
						<option value="New Post">New Post</option>
					</select>  
                         <br />  
                          <label>Upload CSV</label>  
                          <input type="file" name="file" id="file" class="form-control" accept=".csv"/>  
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
                          <br />  
                          <label>Enter Channel</label>  
                          <input type="text" name="channel" id="channel" class="form-control" />  
                          <br />  
                          <label>Enter Date</label>  
                          <input type="date" name="date" id="date" class="form-control" />  
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
 <div id="inv_data_Modal" class="modal fade">  
      <div class="modal-dialog">  
           <div class="modal-content">  
                <div class="modal-header">  
                     <button type="button" class="close" data-dismiss="modal">&times;</button>  
                     <h4 class="modal-title">Quantity Details</h4>  
                </div>  
                <div class="modal-body">  
                    <form method="post" id="inv_form" name="inv_form" action=""> 
                     <div id="quantity_table"></div>
                     <input type="submit" name="quantitychange" class="update_inventory" id="quantitychange"  value="Quantity" >
                     </form>
                </div>  
                <div class="modal-footer">  
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>  
                </div>  
           </div>  
      </div>  
 </div>
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
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="column_name">Flags</label>
                                <select class="form-control" id="category" name="category" aria-label="category" style="width:100%;">
                                <option value="Select">Select</option>
                                <option value = 'packing Area'> Packing Area </option>
                                <option value = 'cables'> Cables </option>
                                <option value = 'Lamp Holders'> Lamp Holders </option>
                                <option value = 'Bulbs'> Bulbs </option>
                                <option value = 'Transformer'> Transformer </option>
                                <option value = 'Lampshade'> Lampshade </option>
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
 $(document).ready(function(){  
      $('#add').click(function(){  
           $('#insert').val("Insert");  
           $('#insert_form')[0].reset();  
      });  
      $(document).on('click', '.edit_data', function(){  
           var order_id = $(this).attr("id");  
           $.ajax({  
                url:"fetch.php",  
                method:"POST",  
                data:{order_id:order_id}, 
                dataType:"json",  
                success:function(data){  
                     $('#sku').val(data.sku);  
                     $('#channel').val(data.channel); 
                     $('#date').val(data.date);  
                     $('#order_id').val(data.id);  
                     $('#insert').val("Update");  
                     $('#add_data_Modal').modal('show');  
                }  
           });  
      });
      $(document).on('click', '.change_inv', function(){  
           var inv_id = $(this).attr("id");  
           $.ajax({  
                url:"fetchnew.php",  
                method:"POST",  
                data:{inv_id:inv_id},  
                success:function(data){   
                     $('#inv_data_Modal').modal('show'); 
                     $('#quantity_table').html(data);   
                }  
           });  
      });   
      $(document).on('click', '.delete_data', function(){ 
          event.preventDefault();  
           var order_id = $(this).attr("id");  
           $.ajax({  
                     url:"deletenew.php",  
                     method:"POST",
                     data:{order_id:order_id},   
                     success:function(data){  
                          $('#insert_form')[0].reset();  
                          $('#add_data_Modal').modal('hide');  
                          $('#employee_table').html(data);  
                     }  
                });  
      });  
      $('#insert_form').on("submit", function(event){  
           event.preventDefault();  
           if($('#sku').val() == "")  
           {  
                alert("SKU is required");  
           }  
           else if($('#channel').val() == '')  
           {  
                alert("Channel is required");  
           }    
           else  
           {  
                $.ajax({  
                     url:"insertnew.php",  
                     method:"POST",  
                     data:$('#insert_form').serialize(),  
                     beforeSend:function(){  
                          $('#insert').val("Inserting");  
                     },  
                     success:function(data){  
                          $('#insert_form')[0].reset();  
                          $('#add_data_Modal').modal('hide');  
                          $('#employee_table').html(data);  
                     }  
                });  
           }  
      });  
      $(document).on('click', '.update_inventory', function() {
if(confirm("Are you sure want to change the quantity ?")) {
  $("#inv_form").on("submit", function(event){
        event.preventDefault();
        var formValues= $(this).serializeArray();
        $.post("quantitychange.php?action=outofstock", formValues, function(data){
          $('#inv_form')[0].reset();
          $('#inv_data_Modal').modal('hide');
          $('#employee_table').html(data);
        });
    });
}
});
      $(document).on('click', '.view_data', function(){  
           var order_id = $(this).attr("id");  
           if(order_id != '')  
           {  
                $.ajax({  
                     url:"select.php",  
                     method:"POST",  
                     data:{order_id:order_id},  
                     success:function(data){  
                          $('#employee_detail').html(data);  
                          $('#dataModal').modal('show');  
                     }  
                });  
           }            
      });  
      $('#modal_form').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget); // Button that triggered the modal
  var recipient = button.data('whatever'); // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var vals	= $('input:checkbox[name="orders[]"]').map(function() {
							return this.checked ? this.value : undefined;
						}).get();
  var modal = $(this);
  modal.find('.modal-title').text("Select the Flags which you want to assign"); // just for fun.
  modal.find(".modal-body input[name='list_check']").val(vals);
});

$("#flagform").on("submit", function(event){
	event.preventDefault();
  $.ajax({  
                     url:"insertnew.php",  
                     method:"POST",
                     data:$('#flagform').serialize(),   
                     success:function(data){ 
                          $('#flagform')[0].reset();  
                          $('#modal_form').modal('hide');  
                          $('#employee_table').html(data);  
                     }  
                });  
});
$('#modal_move').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget); // Button that triggered the modal
  var recipient = button.data('whatever'); // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var vals	= $('input:checkbox[name="orders[]"]').map(function() {
							return this.checked ? this.value : undefined;
						}).get();
  var modal = $(this);
  modal.find('.modal-title').text("Confirm"); // just for fun.
  modal.find(".modal-body input[name='idlist']").val(vals);
});
$('#modal_delete').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget); // Button that triggered the modal
  var recipient = button.data('whatever'); // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var vals	= $('input:checkbox[name="orders[]"]').map(function() {
							return this.checked ? this.value : undefined;
						}).get();
  var modal = $(this);
  modal.find('.modal-title').text("Confirm"); // just for fun.
  modal.find(".modaldeleteform input[name='iddelete']").val(vals);
});

$("#moveform").on("submit", function(event){
	event.preventDefault();
  $.ajax({  
                     url:"insertnew.php",  
                     method:"POST",
                     data:$('#moveform').serialize(),   
                     success:function(data){ 
                          $('#moveform')[0].reset();  
                          $('#modal_move').modal('hide');  
                          $('#employee_table').html(data);  
                     }  
                });  
})
$("#deleteform").on("submit", function(event){
	event.preventDefault();
  $.ajax({  
                     url:"insertnew.php",  
                     method:"POST",
                     data:$('#deleteform').serialize(),   
                     success:function(data){ 
                          $('#deleteform')[0].reset();  
                          $('#modal_delete').modal('hide');  
                          $('#employee_table').html(data);  
                     }  
                });  
})
$('body').on('click', '#pendingselectAll', function () {
		if ($(this).hasClass('allChecked')) {
			 $('input[type="checkbox"]', '#pendingts').prop('checked', false);
		} else {
		 $('input[type="checkbox"]', '#pendingts').prop('checked', true);
		 }
		 $(this).toggleClass('allChecked');
	 })

      $(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });
 });  
 </script>