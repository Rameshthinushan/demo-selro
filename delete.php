<?php 
session_start(); 
 $connect = mysqli_connect("localhost","root","","u525933064_dashboard");
 $message = '';
 $output  = '';  
 if(isset($_POST["order_id"]))  
 {  
      $query = "DELETE FROM temporders 
                WHERE id = '".$_POST["order_id"]."'";  
      if(mysqli_query($connect, $query))  
      {  
           $output      .= '<label class="text-success">' . $message . '</label>';  
           $select_query = "SELECT * 
                            FROM temporders 
                            ORDER BY total ASC, 
                            date ASC";
           if(isset($_SESSION['category']))
            {
                $category     = $_SESSION['category'];
                $select_query = "SELECT * 
                                 FROM temporders 
                                 WHERE flags='$category' 
                                 ORDER BY total ASC, 
                                 date ASC";
            }  
           $result = mysqli_query($connect, $select_query); 
                     if(isset($_SESSION['category']))
                     {
                        $output .= "<p style='text-align:center;'>
                                        <strong>". $_SESSION['category']." Orders Only</strong>
                                    </p>";
                        $output .= "<p style='text-align:center; color:red;'>
                                        <i>( Please select Select All in filter to see all orders )</i>
                                    </p>";
                     } 
           $output .= '  
                <table class="table table-bordered" id="pendingts">  
                    <tr>
                     <th width="5%">
                        <button type="button" id="pendingselectAll" class="main">
          		            <span class="sub"></span> Select All 
                        </button>
                     </th>  
                     <th width="15%">Image</th> 
                     <th width="5%">Order ID</th>
                     <th width="20%">SKU</th> 
                     <th width="20%">Address</th> 
                     <th width="20%">Flags</th>
                     <th width="5%">Edit</th>  
                     <th width="5%">View</th>
                     <th width="5%">Delete</th> 
                    </tr>';  
           while($row = mysqli_fetch_array($result))  
           {  
               if ($row["merge"]!="" && $row["merge"]!="Merged")
               {
                 continue;
               } 
               $ordersku        = $row["sku"];
               $mainimageresult = mysqli_query($connect,"SELECT * 
                                                         FROM comboproducts 
                                                         WHERE sku='" . $ordersku . "'");
               $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
               if(mysqli_num_rows($mainimageresult)>0)
               {
                $mainimageorder=$mainimagerow['comboproducts']['image'];
               }
               if(empty($mainimageorder))
               {
                    $mainimageresult              = mysqli_query($connect, "SELECT * 
                                                                            FROM products
                                                                            WHERE SKU='" . $ordersku . "'");
                    $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
                    $mainimageorder = '';
                    if(mysqli_num_rows($mainimageresult)>0){
                        $mainimageorder               =$mainimagerow['comboproducts']['Mainimage'];
                    }
                    
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
                         $mergequery  = "SELECT * 
                                         FROM temporders 
                                         WHERE merge='" . $mergeid . "'";
                         $mergeresult = mysqli_query($connect, $mergequery);
                         $row_cnt     = mysqli_num_rows($mergeresult);
                         $rowspanno   = ($row_cnt+1);
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
                          <td>' .$row["flags"]. '</td>
                          <td><input type="button" name="edit" value="Edit" id="'.$row["id"] .'" class="btn btn-info btn-xs edit_data" /></td>  
                          <td><input type="button" name="view" value="view" id="' . $row["id"] . '" class="btn btn-info btn-xs view_data" /></td> 
                          <td><input type="button" name="delete" value="delete" id="' . $row["id"] . '" class="btn btn-info btn-xs delete_data" /></td> 
                     </tr>  
                ';
           if($row["merge"]=="Merged")
            { 
            while($mergerow = mysqli_fetch_array($mergeresult))
            {
            $ordersku                     = $mergerow["sku"];
            $mainimageresult              = mysqli_query($connect, "SELECT * 
                                                                    FROM comboproducts 
                                                                    WHERE sku='" . $ordersku . "'");
            $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
            if(mysqli_num_rows($mainimageresult))
            {
            $mainimageorder               =$mainimagerow['comboproducts']['image'];
            }
            if(empty($mainimageorder))
            {
                $mainimageresult              = mysqli_query($connect, "SELECT * 
                                                                        FROM products 
                                                                        WHERE SKU='" . $ordersku . "'");
                $mainimagerow["comboproducts"]= mysqli_fetch_array($mainimageresult);
                $mainimageorder = '';
                if(mysqli_num_rows($mainimageresult)>0){
                    $mainimageorder               =$mainimagerow['comboproducts']['Mainimage'];
                }  
            } 
            $output .= '  
        <tr>
            <td style="text-align:center"><input type="checkbox" name="orders[]" value='.$mergerow["id"].'></td>
            <td><img style="width:100px; height:auto;" src='.$mainimageorder.'></td>
            <td>'.$mergerow["orderID"].'</td>
            <td>'.$mergerow["sku"].'<br>'.$mergerow["date"].'<br>'.$mergerow["channel"].'</td> 
            <td>'.$mergerow["flags"].'</td>
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