<?php  
 //fetch.php  
 $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

 if(isset($_POST["inv_id"]))  
 {  
     $output = '';
     $query = "SELECT * FROM temporders WHERE id = '".$_POST["inv_id"]."'";  
      $result = mysqli_query($connect, $query);  
      $row = mysqli_fetch_array($result);  
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
                    $output .= '<label>'.$pick[$x][0] .'</label><br />';  
				$quantityresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $pick[$x][0] . "'");
				$quantityrow["product"]= mysqli_fetch_array($quantityresult);
                    $productID=$quantityrow['product']['ProductID'];
				$invquantity=$quantityrow['product']['Quantity'];
                    $output .= '<input type="hidden" name="productID[]" class="form-control" value="'.$productID .'"/><br />
                    <input type="text" name="quantity[]" class="form-control" value="'.$invquantity .'"/><br />';
	}
     echo $output;      
 }  
 ?>