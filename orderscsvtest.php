<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit();
}
//require_once('authenticate.php');
$DATABASE_HOST   = 'localhost';
$DATABASE_USER   = 'root';
$DATABASE_PASS   = '';
$DATABASE_NAME = 'u525933064_dashboard';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// We don't have the password or email info stored in sessions so instead we can get the results from the database.
$stmt = $con->prepare('SELECT password, email FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($password, $email);
$stmt->fetch();
$stmt->close();
?>
<style>
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}

/* Style the close button */
.topright {
  float: right;
  cursor: pointer;
  font-size: 28px;
}

.topright:hover {color: red;}

	@media screen and (max-width: 900px) {
  .desktoponly {
    display: none;
  }
		#desktoponly {
    display: none;
  }
	.mobileview
		{
			width:90%;
			height:100px;
			margin:30px;
		}
}
</style>
<!DOCTYPE html>
<html>
<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<link href="style.css" rel="stylesheet" type="text/css">
		<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
		<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
		<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="loggedin">
		<nav class="navtop" id="desktoponly">
			<div>
				<h1>Orders</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="products.php"><i class="fas fa-database"></i>Products</a>
				<a href="skutool.php"><i class="fas fa-database"></i>New SKU tool</a>
				<a href="SKUUpdate.php"><i class="fas fa-database"></i>SKU Update</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>

			</div>
		</nav>
		<div class="content">
		<div id="desktoponly" class="tab">
<button class="tablinks" id="desktoponly" onclick="opentab(event, 'CSV')">CSV</button>
<button class="tablinks" id="desktoponly" onclick="opentab(event, 'pendingorders')">Pending Orders</button>
<button class="tablinks" onclick="opentab(event, 'orders')" id="defaultOpen">Open Orders</button>
<button class="tablinks" id="desktoponly" onclick="opentab(event, 'completedorders')">Completed Orders</button>
</div>

<div id="CSV" class="tabcontent">
<span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
		<?php
if (isset($_POST["import"])) {

    $fileName = $_FILES["file"]["tmp_name"];
	$booking=$_POST["booking"];
	$csvdate=$_POST["csvdate"];

    if ($_FILES["file"]["size"] > 0) {

        $file = fopen($fileName, "r");
		$g=0;
		$flag = true;
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
			if($flag) { $flag = false; continue; }
			$orderID=$column[0];
			$date = $column[2];
			$channel = $column[3];
			$firstname=$column[4];
			$lastname=$column[5];
			$telephone=$column[6];
			$email=$column[7];
			$currency=$column[8];
			$ordertotal = $column[9];
			$name = $column[10];
			$sku=$column[11];
			$quantity=$column[13];
			$orderquantity=$column[13];
			$lineitemtotal=$column[14];
			$flags = $column[15];
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
			$shippingaddresscountrycode=$column[28];
			$status='Pending';
			$unit='unit2';

		if (strpos($flags, 'Lampshade') !== true)
		{
			$unit='unit1';
		}
		//unit filter
		if (strpos($flags, 'Lampshade') !== false)
		{
		$ordersku=$sku;
		if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
		$linesku=$skus[$m];
		if(substr($linesku, -2)=="PK")
			{
				$pknumber=substr($linesku, -3, -2);
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
				$linesku=substr($linesku, 0, -3);
				$quantity=$quantity*$pknumber;
			}
		$tempproduct = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $linesku . "'");
		$temprow["product"]= mysqli_fetch_array($tempproduct);
		$unitquantity = $temprow['product']['unit2'];
			if($unitquantity<1)
			{
				$unit='unit1';
				break;
			}
			else
			{
				$unit='unit2';
			}


			/* unit inventory check up start
		if(empty($temp))
		{
			$temp = array(array($linesku,$unitquantity));
			if($quantity>$unitquantity)
			{
				$unit='unit1';
				break;
			}
			else
			{
				$unit='unit2';
				$unitquantity=$unitquantity-$quantity;
				$temp = array(array($linesku,$unitquantity));
			}
		}
		else
		{
			$unitflag="false";
			for($c=0;$c<count($temp);$c++)
			{
				if($temp[$c][0]==$linesku)
				   {
							$unitflag="true";
							if($quantity>$temp[$c][1])
							{
								$unit='unit1';
								break 2;
							}
							else
							{
								$unit='unit2';
								$temp[$c][1]=$temp[$c][1]-$quantity;
							}
					   break;
				   }
			}
				if($unitflag!="true")
				   {
						$a=count($temp);
						$temp[$a][0]=$linesku;
						$temp[$a][1]=$unitquantity;
						if($quantity>$unitquantity)
							{
								$unit='unit1';
								break;
							}
							else
							{
								$unit='unit2';
								$temp[$a][1]=$temp[$a][1]-$quantity;
							}
				   }
		}
		*/
		}
		}

		// else + others
		else
		{
			$ordersku=$sku;
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow= mysqli_fetch_array($encresult);
				$ordersku=$encrow['originalsku'];
			}
		$linesku=$ordersku;
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
				$linesku=substr($linesku, 0, -3);
				$quantity=$quantity*$pknumber;
			}
			$tempproduct = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $linesku . "'");
			$temprow["product"]= mysqli_fetch_array($tempproduct);
			$unitquantity = $temprow['product']['unit2'];
			if($unitquantity<1)
			{
				$unit='unit1';
				break;
			}
			else
			{
				$unit='unit2';
			}

		/*unit inventory check up start
		if(empty($temp))
		{
			$temp = array(array($linesku,$unitquantity));
			if($quantity>$unitquantity)
			{
				$unit='unit1';
				break;
			}
			else
			{
				$unit='unit2';
				$unitquantity=$unitquantity-$quantity;
				$temp = array(array($linesku,$unitquantity));
			}
		}
		else
		{
			$unitflag="false";
			for($c=0;$c<count($temp);$c++)
			{
				if($temp[$c][0]==$linesku)
				   {
						$unitflag="true";
							if($quantity>$temp[$c][1])
							{
								$unit='unit1';
								break 2;
							}
							else
							{
								$unit='unit2';
								$temp[$c][1]=$temp[$c][1]-$quantity;
							}
					   break;
				   }
			}
				if($unitflag!="true")
				   {
						$a=count($temp);
						$temp[$a][0]=$linesku;
						$temp[$a][1]=$unitquantity;
						if($quantity>$unitquantity)
							{
								$unit='unit1';
								break;
							}
							else
							{
								$unit='unit2';
								$temp[$a][1]=$temp[$a][1]-$quantity;
							}
				   }
		}
		*/
	}

		}
		//unit filter end
		$sql = "INSERT into orders (orderID, status, date, channel, firstname, lastname, telephone, email, currency, ordertotal, name, sku, quantity, lineitemtotal, flags, shippingservice, shippingaddresscompany, shippingaddressline1, shippingaddressline2, shippingaddressline3, shippingaddressregion, shippingaddresscity, shippingaddresspostcode, shippingaddresscountry, shippingaddresscountrycode, booking, csvdate, unit)
				   values ('". $orderID ."','". $status ."','". $date ."','". $channel ."','". $firstname ."','". $lastname ."','". $telephone ."','". $email ."','". $currency ."','". $ordertotal ."','". $name ."','". $sku ."','". $orderquantity ."','". $lineitemtotal ."','". $flags ."','". $shippingservice ."','". $shippingaddresscompany ."','". $shippingaddressline1 ."','". $shippingaddressline2 ."','". $shippingaddressline3 ."','". $shippingaddressregion ."','". $shippingaddresscity ."','". $shippingaddresspostcode ."','". $shippingaddresscountry ."','". $shippingaddresscountrycode ."','". $booking ."','". $csvdate ."','". $unit ."')";
            $result = mysqli_query($con, $sql);
		//}

            if (! empty($result)) {
                $type = "success";
				header( 'Location:products.php' ) ;
                $message = "CSV Data Imported into the Database";
            } else {
				$missedorder[$g]=$orderID;
				$g=$g+1;
                $type = "error";
                $message = "Problem in Importing CSV Data";
            }
        }
		echo('<h2>Please check the below orders which did not update under the system</h2>');
        echo('<table border="1"><tr><th>Order ID</th></tr>');
        for($i=0;$i<count($missedorder);$i++) {
          echo('<tr>');
          echo('<td>' . $missedorder[$i] . '</td>');
          echo('</tr>');
        }
        echo('</table>');
    }
}
?>
			<h1>Update your orders</h1>
			<form class="form-horizontal" action="" enctype='multipart/form-data' method="post" name="uploadCSV">
    <div class="input-row">
		<table>
			<tr><th>Date</th><td><input type="date" name="csvdate" id="csvdate"></td></tr>
			<tr><th>Booking</th>
				<td>
					<select name="booking">
						<option value="1st Booking">1st Booking</option>
						<option value="2nd Booking">2nd Booking</option>
						<option value="New Post">New Post</option>
					</select>
				</td>
			</tr>
			<tr><th>CSV</th><td><input type="file" name="file" id="file" accept=".csv"></td></tr>

		</table>
        <button type="submit" id="submit" name="import" class="btn-submit">Import</button>
        <br />
		</form>
</div>
 </div>
 <div id="pendingorders" class="tabcontent">
 <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
 <form name="frmfilter" method="post" action="">
 <input class="mobileview" type="submit" name="lampshade" value="Lampshade"  />
 <input class="mobileview" type="submit" name="others" value="Others"  />
 <input class="desktoponly" type="submit" name="Transformer" value="Transformer"  />
 <input class="desktoponly" type="submit" name="packingArea" value="Packing Area"  />
 <input class="desktoponly" type="submit" name="LampHolders" value="Lamp Holders"  />
 <input class="desktoponly" type="submit" name="Bulbs" value="Bulbs"  />
 <input class="desktoponly" type="submit" name="cables" value="Cables"  />
 </form>
 	<form name="frmpicklist" method="post" action="picklist.php" target="_blank">
 	<input class="mobileview" type="submit" name="picklist" value="Generate Picklist"  />
 	<input class="desktoponly" type="submit" name="orderlist" value="Generate Packlist"  />
 	<input class="desktoponly" type="submit" name="orderlisttest" value="Packlist Test"  />
	<input class="desktoponly" type="submit" name="updateinventory" value="Update Inventory"  />
 	<input class="desktoponly" type="submit" name="moveunit2" value="Move to Unit 2"  />
 	<input class="desktoponly" type="submit" name="moveunit1" value="Move to Unit 1"  />
	<input class="desktoponly" type="submit" name="moveopen" value="Move to Open"  />
 	<input class="desktoponly" type="submit" name="delete" value="Delete"  />
 	<table cellpadding="0" cellspacing="0" border="1" id="pendingts" class="display">
   <thead>
 						<tr>
						<th><button type="button" id="pendingselectAll" class="main">
          				<span class="sub"></span> Select All </button></th>
 						<th style="text-align:center">Order ID</th>
 						<th class="desktoponly" style="text-align:center">Date</th>
 						<th class="desktoponly" style="text-align:center">Channel</th>
 						<th class="desktoponly" style="text-align:center">Name</th>
 						<th class="desktoponly" style="text-align:center">SKU</th>
 						<th class="desktoponly" style="text-align:center">Order Total</th>
 						<th class="desktoponly" style="text-align:center">Quantity</th>
 						<th class="desktoponly" style="text-align:center">Flags</th>
 						<th class="desktoponly" style="text-align:center">Unit</th>
 						<th class="desktoponly" style="text-align:center">CSV Date</th>
 						<th class="desktoponly" style="text-align:center">Booking</th>
 						<th style="text-align:center">Inventory</th>
 						</tr>
 </thead>
 						<?php
 						$ordersql = "select * from orders WHERE status = 'Pending'";
 						if(isset($_POST['lampshade']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags LIKE '%Lampshade%'";
 						}
 						if(isset($_POST['others']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags NOT LIKE '%Lampshade%'";
 						}
 						if(isset($_POST['Transformer']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags LIKE '%Transformer%'";
 						}
 						if(isset($_POST['packingArea']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags LIKE '%packing Area%'";
 						}
 						if(isset($_POST['LampHolders']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags LIKE '%Lamp Holders%'";
 						}
 						if(isset($_POST['Bulbs']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags LIKE '%Bulbs%'";
						 }
						 if(isset($_POST['cables']))
 						{
 							$ordersql = "select * from orders WHERE status = 'Pending' AND flags LIKE '%cables%'";
 						}
 						$orderresult = $con->query($ordersql);
 						while($orderrow = $orderresult->fetch_assoc()){
 	//inventory alert start
 	unset($pick);
 	$pick = array();
 	$invflag="false";
 	$ordersku=$orderrow["sku"];
 	if(substr($ordersku,0,3)=="ENC")
 		{
 			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
 			$quantity=$orderrow['quantity'];
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
 					$quantity=($orderrow['quantity']*$pknumber);
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
 				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
 				$encrow= mysqli_fetch_array($encresult);
 				$ordersku=$encrow['originalsku'];
 			}
 		$sku=$ordersku;
 		$quantity=$orderrow[$i]['quantity'];
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
 				$quantity=($orderrow[$i]['quantity']*$pknumber);
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

 				$quantityresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
 				$quantityrow["product"]= mysqli_fetch_array($quantityresult);
 				$invquantity=$quantityrow['product']['Quantity'];

 				if($invquantity<0)
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
 							//inventory alert end
 						?>
 						<tr>
 						<td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $orderrow["id"]; ?>" ></td>
 						<td style="text-align:center;"><?php echo $orderrow["orderID"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["date"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["channel"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["name"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["sku"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["ordertotal"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["quantity"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["flags"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["unit"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["csvdate"]; ?></td>
 						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["booking"]; ?></td>
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
 						</tr>
 						<?php } ?>
 	</thead>
   </table>
 	</form>
 </div>
<div id="orders" class="tabcontent">
<span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
<form name="frmfilter" method="post" action="">
<input class="mobileview" type="submit" name="lampshade" value="Lampshade"  />
 <input class="mobileview" type="submit" name="others" value="Others"  />
 <input class="desktoponly" type="submit" name="Transformer" value="Transformer"  />
 <input class="desktoponly" type="submit" name="packingArea" value="Packing Area"  />
 <input class="desktoponly" type="submit" name="LampHolders" value="Lamp Holders"  />
 <input class="desktoponly" type="submit" name="Bulbs" value="Bulbs"  />
 <input class="desktoponly" type="submit" name="cables" value="Cables"  />
</form>
	<form name="frmpicklist" method="post" action="picklist.php" target="_blank">
	<input class="mobileview" type="submit" name="picklist" value="Generate Picklist"  />
	<input class="desktoponly" type="submit" name="orderlist" value="Generate Packlist"  />
	<input class="desktoponly" type="submit" name="updateinventory" value="Update Inventory"  />
	<input class="desktoponly" type="submit" name="manualinventory" value="Manual Inventory"  />
	<input class="desktoponly" type="submit" name="orderlisttest" value="Packlist Test"  />
	<input class="desktoponly" type="submit" name="moveunit2" value="Move to Unit 2"  />
	<input class="desktoponly" type="submit" name="moveunit1" value="Move to Unit 1"  />
	<input class="desktoponly" type="submit" name="movepending" value="Move to Pending"  />
	<input class="desktoponly" type="submit" name="delete" value="Delete"  />
	<table cellpadding="0" cellspacing="0" border="1" id="ts" class="display">
  <thead>
						<tr>
						<th><button type="button" id="selectAll" class="main">
          				<span class="sub"></span> Select All </button></th>
						<th style="text-align:center">Order ID</th>
						<th class="desktoponly" style="text-align:center">Date</th>
						<th class="desktoponly" style="text-align:center">Channel</th>
						<th class="desktoponly" style="text-align:center">Name</th>
						<th class="desktoponly" style="text-align:center">SKU</th>
						<th class="desktoponly" style="text-align:center">Order Total</th>
						<th class="desktoponly" style="text-align:center">Quantity</th>
						<th class="desktoponly" style="text-align:center">Flags</th>
						<th class="desktoponly" style="text-align:center">Unit</th>
						<th class="desktoponly" style="text-align:center">CSV Date</th>
						<th class="desktoponly" style="text-align:center">Booking</th>
						<th style="text-align:center">Inventory</th>
						<th style="text-align:center" class="desktoponly">Change</th>
						</tr>
</thead>
						<?php
						$ordersql = "select * from orders WHERE status = 'Open'";
						if(isset($_POST['lampshade']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags LIKE '%Lampshade%'";
						}
						if(isset($_POST['others']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags NOT LIKE '%Lampshade%'";
						}
						if(isset($_POST['Transformer']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags LIKE '%Transformer%'";
						}
						if(isset($_POST['packingArea']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags LIKE '%packing Area%'";
						}
						if(isset($_POST['LampHolders']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags LIKE '%Lamp Holders%'";
						}
						if(isset($_POST['Bulbs']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags LIKE '%Bulbs%'";
						}
						if(isset($_POST['cables']))
						{
							$ordersql = "select * from orders WHERE status = 'Open' AND flags LIKE '%cables%'";
						}
						$orderresult = $con->query($ordersql);
						while($orderrow = $orderresult->fetch_assoc()){
	//inventory alert start
	unset($pick);
	$pick = array();
	$invflag="false";
	$ordersku=$orderrow["sku"];
	if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
			$quantity=$orderrow['quantity'];
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
					$quantity=($orderrow['quantity']*$pknumber);
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
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow= mysqli_fetch_array($encresult);
				$ordersku=$encrow['originalsku'];
			}
		$sku=$ordersku;
		$quantity=$orderrow[$i]['quantity'];
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
				$quantity=($orderrow[$i]['quantity']*$pknumber);
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

				$quantityresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$quantityrow["product"]= mysqli_fetch_array($quantityresult);
				$invquantity=$quantityrow['product']['Quantity'];

				if($invquantity<0)
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
							//inventory alert end
						?>
						<tr>
						<td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $orderrow["id"]; ?>" ></td>
						<td style="text-align:center;"><?php echo $orderrow["orderID"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["date"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["channel"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["name"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["sku"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["ordertotal"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["quantity"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["flags"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["unit"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["csvdate"]; ?></td>
						<td class="desktoponly" style="text-align:center;"><?php echo $orderrow["booking"]; ?></td>
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
						<td class="desktoponly"><a class="btn btn-success" href="orderstatusupdate.php<?php echo '?completeid='.$orderrow['id']; ?>"><input type="button" style="height:50px; text-align:center; border-radius:5px; "value="Complete"/></a></td>
						</tr>
						<?php } ?>
	</thead>
  </table>
	</form>
</div>
<div id="completedorders" class="tabcontent">
<span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
	<table cellpadding="0" cellspacing="0" border="1" id="completedts" class="display">
  <thead>
						<tr>
						<th><button type="button" id="completedselectAll" class="main">
          				<span class="sub"></span> Select All </button></th>
						<th style="text-align:center">Order ID</th>
						<th style="text-align:center">Date</th>
						<th style="text-align:center">Channel</th>
						<th style="text-align:center">Name</th>
						<th style="text-align:center">SKU</th>
						<th style="text-align:center">Order Total</th>
						<th style="text-align:center">Quantity</th>
						<th style="text-align:center">Flags</th>
						<th style="text-align:center">CSV Date</th>
						<th style="text-align:center">Status</th>
						<th style="text-align:center">Change</th>
						</tr>
</thead>
						<?php
						$ordersql = "select * from orders WHERE status='Completed' AND csvdate >= DATE(NOW()) + INTERVAL -6 DAY
   AND csvdate <  NOW()       + INTERVAL  0 DAY";
						$orderresult = $con->query($ordersql);
						while($orderrow = $orderresult->fetch_assoc()){
						?>
						<tr>
						<td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $orderrow["id"]; ?>" ></td>
						<td style="text-align:center"><?php echo $orderrow["orderID"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["date"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["channel"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["name"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["sku"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["ordertotal"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["quantity"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["flags"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["csvdate"]; ?></td>
						<td style="text-align:center"><?php echo $orderrow["status"]; ?></td>
						<td><a class="btn btn-success" href="orderstatusupdate.php<?php echo '?id='.$orderrow['id']; ?>"><input type="button" style="height:50px; text-align:center; border-radius:5px; "value="Move to Open"/></a></td>
						</tr>
						<?php } ?>
	</thead>
  </table>
</div>
</div>
</body>
<script>

	function picklist() {
if(confirm("Are you sure want to create picklist for these orders")) {
document.frmpicklist.action = "picklist.php";
document.frmpicklist.submit();
}
}

function opentab(evt, optName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(optName).style.display = "block";
  evt.currentTarget.className += " active";
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
	$(document).ready(function() {
		$('#ts').DataTable( {
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
		"iDisplayLength": 25
    } );

    $('#ts tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    } );


    $('#button').click( function () {
        table.row('.selected').remove().draw( false );
    } );

		$('body').on('click', '#selectAll', function () {
      if ($(this).hasClass('allChecked')) {
         $('input[type="checkbox"]', '#ts').prop('checked', false);
      } else {
       $('input[type="checkbox"]', '#ts').prop('checked', true);
       }
       $(this).toggleClass('allChecked');
     })


} );
$(document).ready(function() {
	$('#pendingts').DataTable( {
			"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
	"iDisplayLength": 25
	} );

	$('#pendingts tbody').on( 'click', 'tr', function () {
			if ( $(this).hasClass('selected') ) {
					$(this).removeClass('selected');
			}
			else {
					table.$('tr.selected').removeClass('selected');
					$(this).addClass('selected');
			}
	} );


	$('#button').click( function () {
			table.row('.selected').remove().draw( false );
	} );

	$('body').on('click', '#pendingselectAll', function () {
		if ($(this).hasClass('allChecked')) {
			 $('input[type="checkbox"]', '#pendingts').prop('checked', false);
		} else {
		 $('input[type="checkbox"]', '#pendingts').prop('checked', true);
		 }
		 $(this).toggleClass('allChecked');
	 })


} );

	$(document).ready(function() {
		$('#completedts').DataTable( {
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
		"iDisplayLength": 25
    } );

    $('#completedts tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    } );


    $('#button').click( function () {
        table.row('.selected').remove().draw( false );
    } );

		$('body').on('click', '#completedselectAll', function () {
      if ($(this).hasClass('allChecked')) {
         $('input[type="checkbox"]', '#completedts').prop('checked', false);
      } else {
       $('input[type="checkbox"]', '#completedts').prop('checked', true);
       }
       $(this).toggleClass('allChecked');
     })


} );

function picklist() {
if(confirm("Are you sure want to create picklist for these orders")) {
document.frmpicklist.action = "picklist.php";
document.frmpicklist.submit();
}
}
</script>

</html>
