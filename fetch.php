<?php  
 //fetch.php  
 $connect = mysqli_connect("localhost","root","","u525933064_dashboard");
 if(isset($_POST["order_id"]))  
 {  
      $query = "SELECT * FROM temporders WHERE id = '".$_POST["order_id"]."'";  
      $result = mysqli_query($connect, $query);  
      $row = mysqli_fetch_array($result);  
      echo json_encode($row);  
 }  
 
 ?>