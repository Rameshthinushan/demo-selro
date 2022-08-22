<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
// if (!isset($_SESSION['loggedin_'])) {
// 	header('Location: index.html');
// 	exit();
// }

function changToLower($str){
	$str = strtolower($str);
	$str = trim($str);

	return $str;
}

//require_once('authenticate.php');
$DATABASE_HOST   = 'localhost';
$DATABASE_USER   = 'root';
$DATABASE_PASS   = '';
$DATABASE_NAME = 'u525933064_dashboard';

$con_hostinger = $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

//$con_hostinger = mysqli_connect("145.14.154.4", "u525933064_ledsone_dashb", "Soxul%36951Dash", "u525933064_dashboard");

// $con_hostinger = mysqli_connect("localhost", "root", "", "u525933064_dashboard");

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
		<title>Packlist</title>
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
<div class="content">
<div id="Unpick" class="tabcontent">
	<table cellpadding="0" cellspacing="0" border="0" id="orders" class="display">
<?php
	$packquantity = '';
	$ordercount   = 0;
	$rowCount     = count($_POST["orders"]);
	$merge=array();
	if($rowCount > 0){
	for($i=0;$i<$rowCount;$i++)
	{
		$orderresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $_POST["orders"][$i] . "'");
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
		echo "<table style='margin-top:50px;'>";
		?>

		<tr>
			<td style="width:200px; border: 1px solid black;"><?php echo $orderrow[$i]["orderID"]; ?><br>
				<?php echo $orderrow[$i]["date"]; ?><br>
				<?php $channelInfo = $orderrow[$i]["channel"]; echo $channelInfo; ?><br>
				<?php
				if(strpos($orderrow[$i]['flags'], "Merged") !== false)
				{
					echo 'MERGE ORDER';
				}
				echo '<td>';
				
				$clientname="Name : ".changToLower($orderrow[$i]["firstname"])." ".changToLower($orderrow[$i]["lastname"]);
				$clientname = trim($clientname);
				$address="";
				$mergeaddress="";
				if(!empty($clientname))
				{
					$address=$address.$clientname."<br>";
					$mergeaddress=$mergeaddress.$clientname."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscompany"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddresscompany"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline1"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddressline1"])."<br>";
					$mergeaddress=$mergeaddress.changToLower($orderrow[$i]["shippingaddressline1"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline2"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddressline2"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressline3"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddressline3"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddressregion"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddressregion"])."<br>";
					$mergeaddress=$mergeaddress.changToLower($orderrow[$i]["shippingaddressregion"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscity"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddresscity"])."<br>";
					$mergeaddress=$mergeaddress.changToLower($orderrow[$i]["shippingaddresscity"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresspostcode"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddresspostcode"])."<br>";
					$mergeaddress=$mergeaddress.changToLower($orderrow[$i]["shippingaddresspostcode"])."<br>";
				}
				if(!empty($orderrow[$i]["shippingaddresscountry"]))
				{
					$address=$address.changToLower($orderrow[$i]["shippingaddresscountry"])."<br>";
					$mergeaddress=$mergeaddress.changToLower($orderrow[$i]["shippingaddresscountry"])."<br>";
				}
				$mainimageorder = '';
				$instruction = '';
				$mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
				$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
				if(mysqli_num_rows($mainimageresult)>0)
				{
					$mainimageorder=$mainimagerow['comboproducts']['image'];
					$instruction=$mainimagerow['comboproducts']['instruction'];
				}
				
				if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
				{
					$mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
					$mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
					$mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
				}
				echo '<td style="width:800px; border: 1px solid black;"><div style="width:800px; padding:5px; border: 2px solid; display: inline-block;">';
				echo "<img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'>";
				//packquantity
				$ordersku=$orderrow[$i]['sku'];
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
					$encrow[$i]= mysqli_fetch_array($encresult);
					$ordersku=$encrow[$i]['originalsku'];
				}
			$sku=$ordersku;
			$quantity=$orderrow[$i]['quantity'];
			// puvii commeneted and added for some request
			// if((substr($sku, -2)=="PK")&&($orderrow[$i]['flags']!="Lampshade"))
			if((substr($sku, -2)=="PK")&&(strpos($sku, '+') === false))
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
					// here is pack * qty
				}
				if(($instruction!='')&&($orderrow[$i]["channel"]=="AMAZON-ledsone amazon"))
						{
							$quantity.='<a href="'.$instruction.'" target="_blank"><button class="btn-success" style="background-color:#30fc03">Instruction</button></a>';
						}
			?>
			<b style="margin-left:200px; color:red; font-size:25px"> X <?php echo $quantity; ?></b><br>
			<?php
			if($orderrow[$i]["notes"] != ""){
				echo '<span style="color: red; font-weight: 900; font-size: medium; text-transform: uppercase;">'.$orderrow[$i]["notes"].'</span></br>';
			}
			?>
			<?php echo $orderrow[$i]["name"]; ?></br>
			<b>SKU: </b><?php echo $orderrow[$i]["sku"]; ?>
			<?php
			if ((strpos($orderrow[$i]["sku"], '+') === false)&&($orderrow[$i]['flags']=="Lampshade") )
			{
				$skucolorcode=substr($orderrow[$i]["sku"],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con_hostinger, $colorsql); 
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
			// puvii changed for erro logs 19-05-2022 if string contains some add id
	        $string =  '';
	        if(strpos($orderrow[$i]["name"], '(') !== false && strpos($orderrow[$i]["name"], ')') !== false){
                $string = explode(')', (explode('(', $orderrow[$i]["name"])[1]))[0];
            }
			//$string = explode(')', (explode('(', $orderrow[$i]["name"])[1]))[0];
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
				$encresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
					$encresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
				else {
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
			$imageresult = mysqli_query($con_hostinger, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");

			$imagerow["product"]= mysqli_fetch_array($imageresult);
			$skucolorcode=substr($pick[$x][0],-2);

			$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
			$colorresult = mysqli_query($con_hostinger, $colorsql); 
			if($colorresult->num_rows === 0)
			{
				$color="N/A";
			}
			else
			{
				while($colorrow = mysqli_fetch_array($colorresult))
				{
					$color = $colorrow["color"];
				}
			}
			$finalImage = '';
			if(mysqli_num_rows($imageresult)>0) {
				$finalImage = $imagerow['product']['Mainimage'];
			}

			// here is add yo check alternative
			echo "<div id='combobox' style='float:left; padding: 10px; border-right:solid; border-width:thin; margin-top:5px;'>";
			echo "<img style='width:100px; height:100px; align:middle;' src='".$finalImage."'>";
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
	$mergeresult = mysqli_query($con, "SELECT * FROM temporders WHERE id='" . $_POST["orders"][$j] . "'");
	$mergerow[$j]= mysqli_fetch_array($mergeresult);
		$channelInfonew = $mergerow[$j]["channel"];
	  $clientname="Name : ".changToLower($mergerow[$j]["firstname"])." ".changToLower($mergerow[$j]["lastname"]);
	  $clientname = trim($clientname);
	  $addressnew="";
	  $mergeaddressnew="";
	      if(!empty($clientname))
	      {
	        $addressnew=$addressnew.$clientname."<br>";
			$mergeaddressnew=$mergeaddressnew.$clientname."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscompany"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddresscompany"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline1"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddressline1"])."<br>";
			$mergeaddressnew=$mergeaddressnew.changToLower($mergerow[$j]["shippingaddressline1"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline2"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddressline2"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressline3"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddressline3"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddressregion"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddressregion"])."<br>";
			$mergeaddressnew=$mergeaddressnew.changToLower($mergerow[$j]["shippingaddressregion"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscity"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddresscity"])."<br>";
			$mergeaddressnew=$mergeaddressnew.changToLower($mergerow[$j]["shippingaddresscity"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresspostcode"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddresspostcode"])."<br>";
			$mergeaddressnew=$mergeaddressnew.changToLower($mergerow[$j]["shippingaddresspostcode"])."<br>";
	      }
	      if(!empty($mergerow[$j]["shippingaddresscountry"]))
	      {
	        $addressnew=$addressnew.changToLower($mergerow[$j]["shippingaddresscountry"])."<br>";
			$mergeaddressnew=$mergeaddressnew.changToLower($mergerow[$j]["shippingaddresscountry"])."<br>";
	      }
	  if($mergeaddressnew==$mergeaddress)
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
		  $mainimageorder = '';	
		  $instruction = '';  
	      $mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
	      $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
		  if(mysqli_num_rows($mainimageresult))
		  {
			$mainimageorder=$mainimagerow['comboproducts']['image'];
			$instruction=$mainimagerow['comboproducts']['instruction'];
		  }
	      
	      if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
	      {
	        $mainimageresult = mysqli_query($con_hostinger, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
	        $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
	        $mainimageorder=$mainimagerow['comboproducts']['Mainimage'];
	      }
		  echo "<div style='border: 2px solid; display: inline-block; width:800px; padding:5px;'><img style='width:140px; height:140px; align:middle;' src='".$mainimageorder."'>";
		  //merge order quantity
		  $ordersku=$mergerow[$j]['sku'];
					if(substr($ordersku,0,3)=="ENC")
					{
						$encresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
						$encrow[$i]= mysqli_fetch_array($encresult);
						$ordersku=$encrow[$i]['originalsku'];
					}
				$sku=$ordersku;
				$quantity=$mergerow[$j]['quantity'];
				// puvii commeneted and added for some request
				// if((substr($sku, -2)=="PK")&&($mergerow[$j]['flags']!="Lampshade"))
				if((substr($sku, -2)=="PK")&&(strpos($sku, '+') === false))
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
						// here is pack * qty
					}
					if(($instruction!='')&&($mergerow[$j]["channel"]=="AMAZON-ledsone amazon"))
						{
							$quantity.='<a href="'.$instruction.'" target="_blank"><button class="btn-success" style="background-color:#30fc03" >Instruction</button></a>';
						}
	      ?>
				<b style="margin-left:200px; color:red; font-size:25px"> X  <?php echo $quantity.$packquantity; ?></b><br>
				<?php
				if($mergerow[$j]["notes"] != ""){
					echo '<span style="color: red; font-weight: 900; font-size: medium; text-transform: uppercase;">'.$mergerow[$j]["notes"].'</span></br>';
				}
				?>
				<?php echo $mergerow[$j]["name"]; ?></br>
				<b>SKU: </b><?php echo $mergerow[$j]["sku"]; ?>
	      <?php
		   $string =  '';
		   if(strpos($mergerow[$j]["name"], '(') !== false && strpos($mergerow[$j]["name"], ')') !== false){
			   $string = explode(')', (explode('(', $mergerow[$j]["name"])[1]))[0];
		   }
		//   $string = explode(')', (explode('(', $mergerow[$j]["name"])[1]))[0];
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
	  if($channelInfo != $channelInfonew){
		echo "</br><b>Diff Channel : ".$channelInfonew."</b></br>";
	  }
				if(substr($ordersku,0,3)=="ENC")
				{
					$encresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
						$encresult = mysqli_query($con_hostinger, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "'");
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
				$imageresult = mysqli_query($con_hostinger, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$imagerow["product"]= mysqli_fetch_array($imageresult);
				$skucolorcode=substr($pick[$x][0],-2);
				$colorsql = 'SELECT * from colors WHERE code="'.$skucolorcode.'"';  
				$colorresult = mysqli_query($con_hostinger, $colorsql); 
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
	}else{
		echo "Please choose atleast one order to generate packlist. automatically close this tab after few seconds.";
		echo "<script>setTimeout(function(){
			window.top.close();
		}, 3000);</script>";
	}
?>
    <div id="labelError"></div>
</div>

</div>
</body>
</html>
