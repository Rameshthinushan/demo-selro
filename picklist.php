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
<!DOCTYPE html>
<html>
<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<style>
.w3-button {width:150px;}
		@media screen and (max-width: 900px) {
  #desktoponly {
    display: none;
  }
			.desktoponly {
    display: none;
  }
}
</style>
</head>

<body class="loggedin">
		<nav class="navtop" id="desktoponly" >
			<div>
				<h1>Website Title</h1>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="products.php"><i class="fas fa-database"></i>Products</a>
				<a href="skutool.php"><i class="fas fa-database"></i>New SKU tool</a>
				<a href="SKUUpdate.php"><i class="fas fa-database"></i>SKU Update</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
<div id="Unpick" class="tabcontent">
	<table cellpadding="0" cellspacing="0" border="0" id="orders" class="display">
<?php
if (isset($_POST["picklist"]))
	{
	$rowCount = count($_POST["orders"]);
	?>
	<button class="w3-button w3-deep-orange" onclick="window.location.href='mobilepicklist.php'">Mobile Picklist</button>
		<button class="w3-button w3-deep-orange" id="desktoponly" onclick="window.location.href='picklistpdf.php'">Print PDF</button>
	<?php
	for($i=0;$i<$rowCount;$i++)
	{
		$orderresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$i] . "'");
		$orderrow[$i]= mysqli_fetch_array($orderresult);
		//echo '<tr><td>';
		//echo $orderrow[$i]['sku'];
		//echo '</td><td>';
		//echo $orderrow[$i]['quantity'];
		//echo '</td><tr>';
		$ordersku=$orderrow[$i]['sku'];
		if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
			$encrow[$i]= mysqli_fetch_array($encresult);
			$ordersku=$encrow[$i]['originalsku'];
		}
		$skus = explode ("+", $ordersku);
		$skuno=count($skus);
		if($skuno>0)
		{
		$l=($skuno-1);
		for ($m = 0; $m <= $l; $m++)
		{
		$sku=$skus[$m];
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
			$flag="false";
			for($c=0;$c<count($pick);$c++)
			{
				if($pick[$c][0]==$sku)
				   {
					   $pick[$c][1]=$pick[$c][1]+$quantity;
						$flag="true";
					   break;
				   }
			}
				if($flag!="true")
				   {
						$a=count($pick);
						$pick[$a][0]=$sku;
						$pick[$a][1]=$quantity;
				   }
		}
		}
		}

		// else + others
		else
		{
			$ordersku=$orderrow[$i]['sku'];
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow[$i]= mysqli_fetch_array($encresult);
				$ordersku=$encrow[$i]['originalsku'];
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
			$flag="false";
			for($c=0;$c<count($pick);$c++)
			{
				if($pick[$c][0]==$sku)
				   {
					   $pick[$c][1]=$pick[$c][1]+$quantity;
						$flag="true";
					   break;
				   }
			}
				if($flag!="true")
				   {
						$a=count($pick);
						$pick[$a][0]=$sku;
						$pick[$a][1]=$quantity;
				   }
		}
	}
	}
		?>
		<table cellpadding="0" cellspacing="0" border="0" id="products" class="display">
					<thead>
						<tr>
						<th>Image</th>
						<th class="desktoponly" >SKU</th>
						<th>Quantity</th>
						<th class="desktoponly">Pick</th>
						</tr>
					</thead>
		<?php
		array_multisort($pick);
		// print multidimension array
			for($x=0;$x<count($pick);$x++)
			{
		  		echo'<tr>';
				$imageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$imagerow["product"]= mysqli_fetch_array($imageresult);
				echo '<td style="width:200px; max-width:200px;">';
				echo "<img style='width:200px; height:200px; align:middle;' src='".$imagerow['product']['Mainimage']."'></td>";
				echo '<td class="desktoponly">';
				echo $pick[$x][0];
				echo '</td>';
				echo '<td>';
				echo $pick[$x][1];
				echo '</td>';
				echo '<td class="desktoponly"></td>';
				echo '</tr>';
		}
				$_SESSION['mobilepick']=$pick;
	}

//orderlist
if (isset($_POST["orderlist"]))
{
	$ordercount=0;
	$rowCount = count($_POST["orders"]);
	$merge=array();
	for($i=0;$i<$rowCount;$i++)
	{
		$orderresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$i] . "'");
		$orderrow[$i]= mysqli_fetch_array($orderresult);
		$ordersku=$orderrow[$i]['sku'];
		if (in_array($orderrow[$i]['id'], $merge))
		{
			continue;
		}
		$ordercount=$ordercount+1;
		$total=$orderrow[$i]["ordertotal"];
		unset($pick);
		$pick = array();
		echo "<table style='margin-top:50px;' nobr='true'>";
		?>

		<tr>
			<td style="width:200px; border: 1px solid black;"><?php echo $orderrow[$i]["orderID"]; ?><br>
			<?php echo $orderrow[$i]["date"]; ?><br>
		<?php echo $orderrow[$i]["channel"]; ?><br>
			<?php
			if(strpos($orderrow[$i]['flags'], "Merged") !== false)
			{
	    echo 'MERGE ORDER';
	}
	echo '<td>';
				$clientname="Name : ".$orderrow[$i]["firstname"]." ".$orderrow[$i]["lastname"];
				$address="";
				if(!empty($clientname))
				{
					$address=$address.$clientname."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscompany"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresscompany"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline1"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressline1"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline2"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressline2"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline3"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressline3"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressregion"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressregion"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscity"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresscity"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresspostcode"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresspostcode"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscountry"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresscountry"]."<br>";
				}

				$mainimageresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
				$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
				$mainimageorder=$mainimagerow['comboproducts']['image'];
				$instruction=$mainimagerow['comboproducts']['instruction'];
				
				if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
				{
					$mainimageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
					$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
					$mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
				}
				echo '<td style="width:800px; border: 1px solid black;"><div style="width:800px; padding:5px; border: 2px solid; display: inline-block;">';
				echo "<img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'>";
				//packquantity
				$ordersku=$orderrow[$i]['sku'];
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'" );
					$encrow[$i]= mysqli_fetch_array($encresult);
					$ordersku=$encrow[$i]['originalsku'];
				}
			$sku=$ordersku;
			$quantity=$orderrow[$i]['quantity'];
			if((substr($sku, -2)=="PK")&&($orderrow[$i]['flags']!="Lampshade"))
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
					$quantity=$quantity." <b style='color:black; font-size:25px'> ( ". $orderrow[$i]['quantity']." * ". $pknumber. " pack ) </b>";
					if(($instruction!='')&&($orderrow[$i]["channel"]=="AMAZON-ledsone amazon"))
					{
						$quantity.='<a href="'.$instruction.'" target="_blank"><button class="btn-success">Instruction</button></a>';
					}
				}
			?>
			<b style="margin-left:200px; color:red; font-size:25px"> X <?php echo $quantity; ?></b>
            <?php
            if(($instruction!='')&&($orderrow[$i]["channel"]=="AMAZON-ledsone amazon"))
			{
					echo '<a href="'.$instruction.'" target="_blank"><button class="btn-success">Instruction</button></a>';
			}
            ?>
            <br>
			<?php echo $orderrow[$i]["name"]; ?></br>
			<b>SKU: </b><?php echo $orderrow[$i]["sku"]; ?>
			<?php
			if ((strpos($orderrow[$i]["sku"], '+') === false)&&($orderrow[$i]['flags']=="Lampshade") )
			{
				$skucolorcode=substr($orderrow[$i]["sku"],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con, $colorsql); 
				if($colorresult->num_rows === 0)
				{
					$color="N/A";
				}
				else
				{
				while($colorrow = mysqli_fetch_array($colorresult))
				{
					$color=$colorrow["color"];
				}
				}
				echo '<br>Color: ';
				echo $color;
			}
			$string = explode(')', (explode('(', $orderrow[$i]["name"])[1]))[0];
			if($string!="")
			{
				$string= str_ireplace("Yes","With Bulb",$string);
				$string= str_ireplace("No","Without Bulb",$string);
				$string= str_replace(",","<br>\n",$string);
				if($orderrow[$i]['flags']!="Lampshade")
				{
				echo "</br><b>Option : ".$string."</b></br>";
				}
			}
			$string = explode(']', (explode('[', $orderrow[$i]["name"])[1]))[0];
			if($string!="")
			{
				$string= str_ireplace("Yes","With Bulb",$string);
				$string= str_ireplace("No","Without Bulb",$string);
				$string= str_replace(",","<br>\n",$string);
				if($orderrow[$i]['flags']!="Lampshade")
				{
				echo "</br><b>Option : ".$string."</b></br>";
				}
			}
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow[$i]= mysqli_fetch_array($encresult);
				$ordersku=$encrow[$i]['originalsku'];
			}
			$skus = explode ("+", $ordersku);
			$skuno=count($skus);
			if($skuno>0)
			{
				$l=($skuno-1);
				for ($m = 0; $m <= $l; $m++)
				{
				$sku=$skus[$m];
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
			}
			else
			{
				$ordersku=$orderrow[$i]['sku'];
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
					$encrow[$i]= mysqli_fetch_array($encresult);
					$ordersku=$encrow[$i]['originalsku'];
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
		if(count($pick)>1)
		{
		echo '<div style="border-bottom: solid; border-width: thin;">';
		for($x=0;$x<count($pick);$x++)
		{
			$imageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
			$imagerow["product"]= mysqli_fetch_array($imageresult);
			$skucolorcode=substr($pick[$x][0],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con, $colorsql); 
				if($colorresult->num_rows === 0)
				{
					$color="N/A";
				}
				else
				{
				while($colorrow = mysqli_fetch_array($colorresult))
				{
					$color=$colorrow["color"];
				}
				}
			echo "<div id='combobox' style='float:left; padding: 10px; border-right:solid; border-width:thin; margin-top:5px;'>";
			echo "<img style='width:100px; height:100px; align:middle;' src='".$imagerow['product']['Mainimage']."'>";
			echo '<b> X '.$pick[$x][1].'</b><br>';
			echo $pick[$x][0];
			echo '<br>Color: ';
				echo $color;
			echo "</div>";
		}
		echo '</div>';
	}
	echo '</div>';
	for($j=$i+1;$j<$rowCount;$j++)
	{
	$mergeresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$j] . "'");
	$mergerow[$j]= mysqli_fetch_array($mergeresult);
	  $clientname="Name : ".$mergerow[$j]["firstname"]." ".$mergerow[$j]["lastname"];
	  $addressnew="";
	      if(!empty($clientname))
	      {
	        $addressnew=$addressnew.$clientname."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscompany"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresscompany"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline1"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressline1"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline2"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressline2"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline3"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressline3"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressregion"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressregion"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscity"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresscity"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresspostcode"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresspostcode"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscountry"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresscountry"]."<br>";
	      }
	  if($addressnew==$address)
	  {
			$total=$total+$mergerow[$j]["ordertotal"];
	  $mergeid=$mergerow[$j]['id'];
	  if(empty($merge))
	      {
	      $merge = array($mergeid);
	      }
	    else
	      {
	      $v=count($merge);
	      $merge[$v]=$mergeid;
	      }
	      $ordersku=$mergerow[$j]['sku'];
	      unset($pick);
	  		$pick = array();
	      $mainimageresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "'");
	      $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
	      $mainimageorder=$mainimagerow['comboproducts']['image'];
	      $instruction=$mainimagerow['comboproducts']['instruction'];
	      if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
	      {
	        $mainimageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
	        $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
	        $mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
	      }
		  echo "<div style='border: 2px solid; display: inline-block; width:800px; padding:5px;'><img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'>";
		  //merge order quantity
		  $ordersku=$mergerow[$j]['sku'];
					if(substr($ordersku,0,3)=="ENC")
					{
						$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
						$encrow[$i]= mysqli_fetch_array($encresult);
						$ordersku=$encrow[$i]['originalsku'];
					}
				$sku=$ordersku;
				$quantity=$mergerow[$j]['quantity'];
				if((substr($sku, -2)=="PK")&&($mergerow[$j]['flags']!="Lampshade"))
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
						$quantity=$quantity." <b style='color:black; font-size:25px'> ( ". $mergerow[$j]['quantity']." * ". $pknumber. " pack ) </b>";
						if(($instruction!='')&&($orderrow[$i]["channel"]=="AMAZON-ledsone amazon"))
						{
							$quantity.='<a href="'.$instruction.'" target="_blank"><button class="btn-success">Instruction</button></a>';
						}
					}
	      ?>
				<b style="margin-left:200px; color:red; font-size:25px"> X  <?php echo $quantity.$packquantity; ?></b><br>
				<?php echo $mergerow[$j]["name"]; ?></br>
				<b>SKU: </b><?php echo $mergerow[$j]["sku"]; ?>
	      <?php
		  $string = explode(')', (explode('(', $mergerow[$j]["name"])[1]))[0];
	  if($string!="")
	  {
		  $string= str_ireplace("Yes","With Bulb",$string);
		  $string= str_ireplace("No","Without Bulb",$string);
		  $string= str_replace(",","<br>\n",$string);
		  if($mergerow[$j]['flags']!="Lampshade")
		  {
		  echo "</br><b>Option : ".$string."</b></br>";
		  }
	  }
	  $string = explode(']', (explode('[', $mergerow[$j]["name"])[1]))[0];
	  if($string!="")
	  {
		  $string= str_ireplace("Yes","With Bulb",$string);
		  $string= str_ireplace("No","Without Bulb",$string);
		  $string= str_replace(",","<br>\n",$string);
		  if($mergerow[$j]['flags']!="Lampshade")
		  {
		  echo "</br><b>Option : ".$string."</b></br>";
		  }
	  }
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
					$encrow[$i]= mysqli_fetch_array($encresult);
					$ordersku=$encrow[$i]['originalsku'];
				}
				$skus = explode ("+", $ordersku);
				$skuno=count($skus);
				if($skuno>0)
				{
					$l=($skuno-1);
					for ($m = 0; $m <= $l; $m++)
					{
					$sku=$skus[$m];
					$quantity=$mergerow[$j]['quantity'];
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
							$quantity=($mergerow[$j]['quantity']*$pknumber);
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
					$ordersku=$mergerow[$j]['sku'];
					if(substr($ordersku,0,3)=="ENC")
					{
						$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
						$encrow[$i]= mysqli_fetch_array($encresult);
						$ordersku=$encrow[$i]['originalsku'];
					}
				$sku=$ordersku;
				$quantity=$mergerow[$j]['quantity'];
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
						$quantity=($mergerow[$j]['quantity']*$pknumber);
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
			if(count($pick)>1)
			{
			echo '<div style="border-bottom: solid; border-width: thin;">';
			for($x=0;$x<count($pick);$x++)
			{
				$imageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$imagerow["product"]= mysqli_fetch_array($imageresult);
				$skucolorcode=substr($pick[$x][0],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con, $colorsql); 
				if($colorresult->num_rows === 0)
				{
					$color="N/A";
				}
				else
				{
				while($colorrow = mysqli_fetch_array($colorresult))
				{
					$color=$colorrow["color"];
				}
				}
				echo "<div id='combobox' style='float: left; padding: 10px; border-right:solid; border-width: thin; margin-top:5px;'>";
				echo "<img style='width:100px; height:100px; align:middle;' src='".$imagerow['product']['Mainimage']."'>";
				echo '<b> X '.$pick[$x][1].'</b><br>';
				echo $pick[$x][0];
				echo '<br>Color: ';
echo $color;
				echo "</div>";
			}
			echo '</div>';
		}
		echo '</div>';
	}
	}
		?>
	</td>
			<th style="text-align:center; width:200px; max-width:250px; border: 1px solid black;"><?php echo $address; ?></th>
			<th style="text-align:center; width:50px; max-width:50px; border: 1px solid black;"><?php echo $total; ?></th>
			</tr>
	<?php
		echo '</table>';
		
	}
	echo '<br><h3>Total Orders : '.$ordercount.'</h3>';
}
//orderlisttest
if (isset($_POST["orderlisttest"]))
{
	$ordercount=0;
	$rowCount = count($_POST["orders"]);
	$merge=array();
	for($i=0;$i<$rowCount;$i++)
	{
		$orderresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$i] . "'");
		$orderrow[$i]= mysqli_fetch_array($orderresult);
		$ordersku=$orderrow[$i]['sku'];
		if (in_array($orderrow[$i]['id'], $merge))
		{
			continue;
		}
		$ordercount=$ordercount+1;
		$total=$orderrow[$i]["ordertotal"];
		unset($pick);
		$pick = array();
		echo "<table style='margin-top:50px;' nobr='true'>";
		?>

		<tr>
			<td style="width:200px; border: 1px solid black;"><?php echo $orderrow[$i]["orderID"]; ?><br>
			<?php echo $orderrow[$i]["date"]; ?><br>
		<?php echo $orderrow[$i]["channel"]; ?><br>
			<?php
			if(strpos($orderrow[$i]['flags'], "Merged") !== false)
			{
	    echo 'MERGE ORDER';
	}
	echo '<td>';
				$clientname="Name : ".$orderrow[$i]["firstname"]." ".$orderrow[$i]["lastname"];
				$address="";
				if(!empty($clientname))
				{
					$address=$address.$clientname."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscompany"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresscompany"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline1"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressline1"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline2"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressline2"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline3"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressline3"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressregion"]))
				{
					$address=$address.$orderrow[$i]["shippingaddressregion"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscity"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresscity"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresspostcode"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresspostcode"]."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscountry"]))
				{
					$address=$address.$orderrow[$i]["shippingaddresscountry"]."<br>";
				}

				$mainimageresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
				$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
				$mainimageorder=$mainimagerow['comboproducts']['image'];
				$instruction=$mainimagerow['comboproducts']['instruction'];
				if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
				{
					$mainimageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
					$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
					$mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
				}
				echo '<td style="width:800px; border: 1px solid black;"><div style="width:800px; padding:5px; border: 2px solid; display: inline-block;">';
				echo "<img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'>";
			?>
			<b style="margin-left:200px;"> X </b> 
			<?php 

			echo $orderrow[$i]["quantity"]; 

			if(($instruction!='')&&($orderrow[$i]["channel"]=="AMAZON-ledsone amazon"))
			{
					echo '<a href="'.$instruction.'" target="_blank"><button class="btn-success">Instruction</button></a>';
			}
			?>
			<br>
			<?php echo $orderrow[$i]["name"]; ?></br>
			<b>SKU: </b><?php echo $orderrow[$i]["sku"]; ?>
			<?php
			$string = explode(')', (explode('(', $orderrow[$i]["name"])[1]))[0];
			if($string!="")
			{
				$string= str_ireplace("Yes","With Bulb",$string);
				$string= str_ireplace("No","Without Bulb",$string);
				//echo "</br><b>Option : ".$string."</b></br>";
			}
			$string = explode(']', (explode('[', $orderrow[$i]["name"])[1]))[0];
			if($string!="")
			{
				$string= str_ireplace("Yes","With Bulb",$string);
				$string= str_ireplace("No","Without Bulb",$string);
				//echo "</br><b>Option : ".$string."</b></br>";
			}
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow[$i]= mysqli_fetch_array($encresult);
				$ordersku=$encrow[$i]['originalsku'];
			}
			$skus = explode ("+", $ordersku);
			$skuno=count($skus);
			if($skuno>0)
			{
				$l=($skuno-1);
				for ($m = 0; $m <= $l; $m++)
				{
				$sku=$skus[$m];
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
			}
			else
			{
				$ordersku=$orderrow[$i]['sku'];
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
					$encrow[$i]= mysqli_fetch_array($encresult);
					$ordersku=$encrow[$i]['originalsku'];
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
		if(count($pick)>1)
		{
		echo '<div style="border-bottom: solid; border-width: thin;">';
		for($x=0;$x<count($pick);$x++)
		{
			$imageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
			$imagerow["product"]= mysqli_fetch_array($imageresult);
			$skucolorcode=substr($pick[$x][0],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con, $colorsql); 
				if($colorresult->num_rows === 0)
				{
					$color="N/A";
				}
				else
				{
				while($colorrow = mysqli_fetch_array($colorresult))
				{
					$color=$colorrow["color"];
				}
				}
			echo "<div id='combobox' style='float:left; padding: 10px; border-right:solid; border-width:thin; margin-top:5px;'>";
			echo "<img style='width:100px; height:100px; align:middle;' src='".$imagerow['product']['Mainimage']."'>";
			echo '<b> X '.$pick[$x][1].'</b><br>';
			echo $pick[$x][0];
			echo '<br>Color: ';
				echo $color;
			echo "</div>";
		}
		echo '</div>';
	}
	echo '</div>';
	for($j=$i+1;$j<$rowCount;$j++)
	{
	$mergeresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$j] . "'");
	$mergerow[$j]= mysqli_fetch_array($mergeresult);
	  $clientname="Name : ".$mergerow[$j]["firstname"]." ".$mergerow[$j]["lastname"];
	  $addressnew="";
	      if(!empty($clientname))
	      {
	        $addressnew=$addressnew.$clientname."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscompany"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresscompany"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline1"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressline1"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline2"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressline2"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline3"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressline3"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressregion"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddressregion"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscity"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresscity"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresspostcode"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresspostcode"]."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscountry"]))
	      {
	        $addressnew=$addressnew.$mergerow[$j]["shippingaddresscountry"]."<br>";
	      }
	  if($addressnew==$address)
	  {
			$total=$total+$mergerow[$j]["ordertotal"];
	  $mergeid=$mergerow[$j]['id'];
	  if(empty($merge))
	      {
	      $merge = array($mergeid);
	      }
	    else
	      {
	      $v=count($merge);
	      $merge[$v]=$mergeid;
	      }
	      $ordersku=$mergerow[$j]['sku'];
	      unset($pick);
	  		$pick = array();
	      $mainimageresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
	      $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
	      $mainimageorder=$mainimagerow['comboproducts']['image'];
	      $instruction=$mainimagerow['comboproducts']['instruction'];
	      if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
	      {
	        $mainimageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
	        $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
	        $mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
	      }
	      echo "<div style='border: 2px solid; display: inline-block; width:800px; padding:5px;'><img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'>";
	      ?>
				<b style="margin-left:200px;"> X </b> 
				<?php 
				echo $mergerow[$j]["quantity"];
				if(($instruction!='')&&($orderrow[$i]["channel"]=="AMAZON-ledsone amazon"))
				{
						echo '<a href="'.$instruction.'" target="_blank"><button class="btn-success">Instruction</button></a>';
				}
				?>
				<br>
				<?php echo $mergerow[$j]["name"]; ?></br>
				<b>SKU: </b><?php echo $mergerow[$j]["sku"]; ?>
	      <?php
		  $string = explode(')', (explode('(', $mergerow[$i]["name"])[1]))[0];
	  if($string!="")
	  {
		  $string= str_ireplace("Yes","With Bulb",$string);
		  $string= str_ireplace("No","Without Bulb",$string);
		  //echo "</br><b>Option : ".$string."</b></br>";
	  }
	  $string = explode(']', (explode('[', $mergerow[$i]["name"])[1]))[0];
	  if($string!="")
	  {
		  $string= str_ireplace("Yes","With Bulb",$string);
		  $string= str_ireplace("No","Without Bulb",$string);
		  //echo "</br><b>Option : ".$string."</b></br>";
	  }
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
					$encrow[$i]= mysqli_fetch_array($encresult);
					$ordersku=$encrow[$i]['originalsku'];
				}
				$skus = explode ("+", $ordersku);
				$skuno=count($skus);
				if($skuno>0)
				{
					$l=($skuno-1);
					for ($m = 0; $m <= $l; $m++)
					{
					$sku=$skus[$m];
					$quantity=$mergerow[$j]['quantity'];
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
							$quantity=($mergerow[$j]['quantity']*$pknumber);
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
					$ordersku=$mergerow[$j]['sku'];
					if(substr($ordersku,0,3)=="ENC")
					{
						$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
						$encrow[$i]= mysqli_fetch_array($encresult);
						$ordersku=$encrow[$i]['originalsku'];
					}
				$sku=$ordersku;
				$quantity=$mergerow[$j]['quantity'];
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
						$quantity=($mergerow[$j]['quantity']*$pknumber);
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
			if(count($pick)>1)
			{
			echo '<div style="border-bottom: solid; border-width: thin;">';
			for($x=0;$x<count($pick);$x++)
			{
				$imageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$imagerow["product"]= mysqli_fetch_array($imageresult);
				$skucolorcode=substr($pick[$x][0],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con, $colorsql); 
				if($colorresult->num_rows === 0)
				{
					$color="N/A";
				}
				else
				{
				while($colorrow = mysqli_fetch_array($colorresult))
				{
					$color=$colorrow["color"];
				}
				}
				echo "<div id='combobox' style='float: left; padding: 10px; border-right:solid; border-width: thin; margin-top:5px;'>";
				echo "<img style='width:100px; height:100px; align:middle;' src='".$imagerow['product']['Mainimage']."'>";
				echo '<b> X '.$pick[$x][1].'</b><br>';
				echo $pick[$x][0];
				echo '<br>Color: ';
echo $color;
				echo "</div>";
			}
			echo '</div>';
		}
		echo '</div>';
	}
	}
		?>
	</td>
			<th style="text-align:center; width:200px; max-width:250px; border: 1px solid black;"><?php echo $address; ?></th>
			<th style="text-align:center; width:50px; max-width:50px; border: 1px solid black;"><?php echo $total; ?></th>
			</tr>
	<?php
		echo '</table>';
		/*
		//mysqli_query($con, "UPDATE orders set status='Completed' WHERE id='" . $orderrow[$i]['id'] . "'");
		//merge order code start
		for($j=$i+1;$j<$rowCount;$j++)
	{
		$orderresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$j] . "'");
		$orderrow[$j]= mysqli_fetch_array($orderresult);
		$clientname="Name : ".$orderrow[$j]["firstname"]." ".$orderrow[$j]["lastname"];
		$addressnew="";
				if(!empty($clientname))
				{
					$addressnew=$addressnew.$clientname."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddresscompany"]))
				{
					$addressnew=$addressnew.$orderrow[$j]["shippingaddresscompany"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddressline1"]))
				{
					$addressnew=$addressnew.$orderrow[$i]["shippingaddressline1"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddressline2"]))
				{
					$addressnew=$addressnew.$orderrow[$i]["shippingaddressline2"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddressline3"]))
				{
					$addressnew=$addressnew.$orderrow[$i]["shippingaddressline3"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddressregion"]))
				{
					$addressnew=$addressnew.$orderrow[$i]["shippingaddressregion"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddresscity"]))
				{
					$addressnew=$addressnew.$orderrow[$i]["shippingaddresscity"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddresspostcode"]))
				{
					$addressnew=$addressnew.$orderrow[$i]["shippingaddresspostcode"]."<br>";
				}
				if(!empty($orderrow[$j]["shippingaddresscountry"]))
				{
					$addressnew=$addressnew.$orderrow[$j]["shippingaddresscountry"]."<br>";
				}
		if($addressnew==$address)
		{
		$mergeid=$orderrow[$j]['id'];
		if(empty($merge))
				{
				$merge = array($mergeid);
				}
			else
				{
				$v=count($merge);
				$merge[$v]=$mergeid;
				}
		$ordersku=$orderrow[$j]['sku'];
		unset($pick);
		$pick = array();
		echo "<table border='1' style='margin-top:50px;'>";
		?>

		<tr>
			<th style="text-align:center; width:100px; max-width:100px;"><?php echo $orderrow[$j]["orderID"]; ?></th>
			<th style="text-align:center; width:100px; max-width:100px;"><?php echo $orderrow[$j]["date"]; ?></th>
			<th style="text-align:center; width:500px; max-width:250px;"><?php echo $orderrow[$j]["name"]; ?></th>
			<?php

				$mainimageresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
				$mainimageorder=$mainimagerow['comboproducts']['image'];
				if(empty($mainimageorder))
				{
					$mainimageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
					$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
					$mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
				}
				echo '<td style="width:150px;max-width:150px;">';
				echo "<img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'></td>";
			?>
			<th style="text-align:center; width:400px; max-width:400px;"><?php echo $orderrow[$j]["sku"]; ?></th>
			<th style="text-align:center; width:50px; max-width:50px;"><?php echo $orderrow[$j]["quantity"]; ?></th>
			<th style="text-align:center; width:200px; max-width:100px;"><?php echo $orderrow[$j]["channel"]; ?></th>
			<th style="text-align:center; width:200px; max-width:250px;"><?php echo $addressnew; ?></th>
			<th style="text-align:center; width:50px; max-width:50px;"><?php echo $orderrow[$j]["ordertotal"]; ?></th>
			</tr>
		<?php
		if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
			$encrow[$j]= mysqli_fetch_array($encresult);
			$ordersku=$encrow[$j]['originalsku'];
		}
		$skus = explode ("+", $ordersku);
		$skuno=count($skus);
		if($skuno>0)
		{
			$l=($skuno-1);
			for ($m = 0; $m <= $l; $m++)
			{
			$sku=$skus[$m];
			$quantity=$orderrow[$j]['quantity'];
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
					$quantity=($orderrow[$j]['quantity']*$pknumber);
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
			$ordersku=$orderrow[$j]['sku'];
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow[$j]= mysqli_fetch_array($encresult);
				$ordersku=$encrow[$j]['originalsku'];
			}
		$sku=$ordersku;
		$quantity=$orderrow[$j]['quantity'];
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
				$quantity=($orderrow[$j]['quantity']*$pknumber);
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
		echo '</table>';
		echo "<table border='1' style='border-collapse: collapse;'>";
	for($x=0;$x<count($pick);$x++)
	{
				$imageresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$imagerow["product"]= mysqli_fetch_array($imageresult);
				echo '<td>';
				echo "<img style='width:100px; height:100px; align:middle;' src='".$imagerow['product']['Mainimage']."'></td>";
				echo '<td>';
				echo $pick[$x][0];
				echo '</td>';
				echo '<td>';
				echo $pick[$x][1];
				echo '</td>';
	}
		echo '</table>';
		}
	}
	*/
	}
	echo '<br><h3>Total Orders : '.$ordercount.'</h3>';
}
//updateinventory
if (isset($_POST["updateinventory"]))
{
	// puvii added here this to store low stock details as json by new quantity is less than 10 - start
	$lowStockDetails = array();
	// puvii added here this to store low stock details as json by new quantity is less than 10 - end

$rowCount = count($_POST["orders"]);
echo "<table border='1'>";
echo '<tr><th>Order ID</th>
<th>SKU</th>
<th>Quantity</th></tr>';
for($i=0;$i<$rowCount;$i++)
{
	{
		mysqli_query($con, "UPDATE orders set status='Completed' WHERE id='" . $_POST["orders"][$i] . "'");
		
		$orderresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$i] . "'");
		$orderrow[$i]= mysqli_fetch_array($orderresult);
		$ordersku=$orderrow[$i]['sku'];

		// puvii added to change order status
		if($orderrow[$i]['channel'] == "REPLACEMENT-REPLACEMENT"){
			$orderIDwithNo = $orderrow[$i]['orderID'];
			$orderIDArr = explode(":",$orderIDwithNo,2);
			$orderIDD = $orderIDArr[0];
			mysqli_query($con, "UPDATE `ukreplacementorders` SET status = 'Completed', TrackingNumber = '".$orderrow[$i]['TrackingNumber']."', PostalService = '".$orderrow[$i]['PostalService']."' WHERE `linnworkid` = '".$orderIDD."'");
		}

		unset($pick);
		$pick = array();

		if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
			$encrow[$i]= mysqli_fetch_array($encresult);
			$ordersku=$encrow[$i]['originalsku'];
		}
		$skus = explode ("+", $ordersku);
		$skuno=count($skus);
		if($skuno>0)
		{
			$l=($skuno-1);
			for ($m = 0; $m <= $l; $m++)
			{
			$sku=$skus[$m];
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
		}
		else
		{
			$ordersku=$orderrow[$i]['sku'];
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow[$i]= mysqli_fetch_array($encresult);
				$ordersku=$encrow[$i]['originalsku'];
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
	for($x=0;$x<count($pick);$x++)
	{
				echo '<tr>';
				echo '<td>';
				echo $orderrow[$i]['orderID'];
				echo '</td><td>';
				echo $pick[$x][0];
				echo '</td>';
				echo '<td>';
				echo $pick[$x][1];
				echo '</td></tr>';
			
		//insert into sale
		// puvii added this if condition to ignore replacement orders added in sales
		if($orderrow[$i]['channel'] != "REPLACEMENT-REPLACEMENT"){
			$insertsql = "INSERT into sales (orderID, date, sku, quantity) values ('" . $orderrow[$i]['orderID'] . "','" . $orderrow[$i]['date'] . "','" . $pick[$x][0] . "','" . $pick[$x][1] . "')";
            $insertresult = mysqli_query($con, $insertsql);
		}

	//Update Quantity in products
	$productresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
	$productrow["product"]= mysqli_fetch_array($productresult);
	$currentquantity=$productrow['product']['Quantity'];
	$newquantity=$currentquantity-($pick[$x][1]);

	$updatesql = "Update products Set
		Quantity = '$newquantity'
		WHERE
		SKU='" . $pick[$x][0] . "' ";
		//option to deduct the stock when mapping issue added done by ramanan
		if(!empty($productrow['product']['mappingsku']))
		{
			$mappingsku=$productrow['product']['mappingsku'];
			$mappingresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $mappingsku . "'");
			$mappingrow= mysqli_fetch_array($mappingresult);
			$currentquantity=$mappingrow['Quantity'];
			$newquantity=$currentquantity-($pick[$x][1]);

			$updatesql = "Update products Set
				Quantity = '$newquantity'
				WHERE
				SKU='" . $mappingsku . "' ";
		}
     	mysqli_query($con, $updatesql);

		// puvii added here this to store low stock details as json by new quantity is less than 10 - start
		if(!empty($productrow['product']['mappingsku'])){
			$currentSKU = $mappingsku;
		}else{
			$currentSKU = $pick[$x][0];
		}

		if($newquantity < 10){
			$single = array("Sku" => $currentSKU, "quantity" => $newquantity);
			array_push($lowStockDetails, $single);
		}
		// puvii added here this to store low stock details as json by new quantity is less than 10 - end
	}
	}
}
	echo "</table>";

	// create the inventory alert

	//$invquery = 'SELECT sales.sku, SUM(sales.quantity) productsales, products.Mainimage, products.Quantity FROM sales LEFT JOIN products ON sales.sku = products.SKU WHERE sales.date >= date_sub(now(), INTERVAL 30 day) AND products.Quantity<(sales.quantity*1.5)';

	$invdetails =array();

	$invprcount=0;

	$invquery='SELECT sales.sku, SUM(sales.quantity) productsales FROM sales LEFT JOIN products ON sales.sku = products.SKU WHERE sales.date >= date_sub(now(), INTERVAL 30 day) AND products.Quantity<(sales.quantity*1.5) AND products.outofstock!="Yes" AND products.unit1 IS NULL AND products.mappingsku="" GROUP BY sales.sku ORDER BY productsales DESC';

	$invresult = mysqli_query($con, $invquery); 

	while($invrow = mysqli_fetch_array($invresult))
	{

		$invprcount++;

		$prarray=array(
			'prsku' => $invrow["sku"],
			'sales' => $invrow["productsales"],
		);

		array_push($invdetails, $prarray);

	}

	/*

	$invdetails = array(
    array(
        'prsku' => '1213',
        'sales' => 'sales1'
    ),
    array(
        'prsku' => '1214',
        'sales' => 'sales2'
    )
);

*/

$json_ques = json_encode($invdetails);

mysqli_query($con, "insert into lowinv(details, total) values ('$json_ques', '$invprcount')");


// puvii added here this to store low stock details as json by new quantity is less than 10 - start
if(count($lowStockDetails) > 0){
	$jsonDataa = json_encode($lowStockDetails);
	mysqli_query($con, "INSERT INTO lowStockDetails (lowStock) VALUES ('$jsonDataa')");
}
// puvii added here this to store low stock details as json by new quantity is less than 10 - end

echo "Record inserted successfully.";

	//end of inventory alert in database
}

if (isset($_POST["manualinventory"]))
{
echo '<form name="frminventory" method="post" action="">';
$rowCount = count($_POST["orders"]);
echo "<table border='1'>";
echo '<tr><th>Order ID</th>
<th>SKU</th>
<th>Quantity</th>
<th>Inventory Quantity</th></tr>';
for($i=0;$i<$rowCount;$i++)
{
	{
		mysqli_query($con, "UPDATE orders set status='Completed' WHERE id='" . $_POST["orders"][$i] . "'");
		$orderresult = mysqli_query($con, "SELECT * FROM orders WHERE id='" . $_POST["orders"][$i] . "'");
		$orderrow[$i]= mysqli_fetch_array($orderresult);
		$ordersku=$orderrow[$i]['sku'];
		unset($pick);
		$pick = array();

		if(substr($ordersku,0,3)=="ENC")
		{
			$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
			$encrow[$i]= mysqli_fetch_array($encresult);
			$ordersku=$encrow[$i]['originalsku'];
		}
		$skus = explode ("+", $ordersku);
		$skuno=count($skus);
		if($skuno>0)
		{
			$l=($skuno-1);
			for ($m = 0; $m <= $l; $m++)
			{
			$sku=$skus[$m];
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
		}
		else
		{
			$ordersku=$orderrow[$i]['sku'];
			if(substr($ordersku,0,3)=="ENC")
			{
				$encresult = mysqli_query($con, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
				$encrow[$i]= mysqli_fetch_array($encresult);
				$ordersku=$encrow[$i]['originalsku'];
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
	for($x=0;$x<count($pick);$x++)
	{
				echo '<tr>';
				echo '<td>';
				echo $orderrow[$i]['orderID'];
				echo '</td><td>';
				echo $pick[$x][0];
				echo '</td>';
				echo '<td>';
				echo $pick[$x][1];
				?>
				<?php
				$productresult = mysqli_query($con, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$productrow["product"]= mysqli_fetch_array($productresult);
				?>
				<td><input type="number" name="quantity[]" value="<?php echo $productrow['product']['Quantity']; ?>"></td>
				<input type="hidden" name="sku[]" value="<?php echo $productrow['product']['SKU']; ?>">
				<?php
				echo '</td>';
				echo '</tr>';
	}
	}
}
	echo '<td colspan="2"><input type="submit" name="submit" value="Submit" class="btnSubmit"></td>';
	echo "</table></form>";
}
if (isset($_POST["delete"]))
{
	$delrowCount = count($_POST["orders"]);
	for($z=0;$z<$delrowCount;$z++)
	{
		$delsalesresult = mysqli_query($con, "SELECT * FROM orders WHERE id='".$_POST["orders"][$z]."'");
		$delrow["order"]= mysqli_fetch_array($delsalesresult);
		$delorderid=$delrow['order']['orderID'];
		$deldate=$delrow['order']['date'];
		mysqli_query($con, "DELETE FROM sales WHERE orderID='".$delorderid."' AND date='".$deldate."'");
		mysqli_query($con, "DELETE FROM orders WHERE id='".$_POST["orders"][$z]."'");
	}
	header("Location:orderscsv.php");
}
if (isset($_POST["moveunit2"]))
{
$rowCount = count($_POST["orders"]);
for($i=0;$i<$rowCount;$i++)
{
mysqli_query($con, "UPDATE orders set unit='unit2' WHERE id='" . $_POST["orders"][$i] . "'");
}
header("Location:orderscsv.php");
}
if (isset($_POST["moveunit1"]))
{
$rowCount = count($_POST["orders"]);
for($i=0;$i<$rowCount;$i++)
{
mysqli_query($con, "UPDATE orders set unit='unit1' WHERE id='" . $_POST["orders"][$i] . "'");
}
header("Location:orderscsv.php");
}
if (isset($_POST["moveopen"]))
{
$rowCount = count($_POST["orders"]);
for($i=0;$i<$rowCount;$i++)
{
mysqli_query($con, "UPDATE orders set status='Open' WHERE id='" . $_POST["orders"][$i] . "'");
}
header("Location:orderscsv.php");
}
if (isset($_POST["movepending"]))
{
$rowCount = count($_POST["orders"]);
for($i=0;$i<$rowCount;$i++)
{
mysqli_query($con, "UPDATE orders set status='Pending' WHERE id='" . $_POST["orders"][$i] . "'");
}
header("Location:orderscsv.php");
}
if(isset($_POST["submit"]))
{
	$productCount = count($_POST["sku"]);
	for($i=0;$i<$productCount;$i++) {
	mysqli_query($con, "UPDATE products set Quantity='" . $_POST["quantity"][$i] . "' WHERE SKU='" . $_POST["sku"][$i] . "'");
	}
	header("Location:orderscsv.php");
}

?>
    <div id="labelError"></div>
</div>

</div>
</body>
</html>
