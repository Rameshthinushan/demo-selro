<?php
    include 'functionsToCreateLabels.php'; 
    
    $con = mysqli_connect("localhost","root","","u525933064_dashboard");
    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }
    
    $id = "17110";
    $key = 0;
	$createInvoice = createCommercialInvoice($id, $con, $key);

    print_r($createInvoice);
    
?>