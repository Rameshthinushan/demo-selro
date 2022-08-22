<?php
     ini_set('max_execution_time', '0');

     session_start();
     // If the user is not logged in redirect to the login page...
     if (!isset($_SESSION['loggedin'])) {
          header('Location: ../index.html');
          exit();
     }

     $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

     if(isset($_POST["category"]))
     {
          $_SESSION['category']=$_POST["category"];
          if($_POST["category"]=="Select")
          {
               unset($_SESSION['category']);
          }
     }

     if(isset($_SESSION['category']))
     {
          $category=$_SESSION['category'];
          $query = "SELECT * FROM temporders WHERE flags='$category' ORDER BY total ASC, date ASC";
     }else{
          $query = "SELECT * FROM temporders ORDER BY total ASC, date ASC"; 
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
                         <button type="button" class="btn btn-success" id="packlist" >Generate Packlist</button>
                         <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_delete" style="background-color: #f44336;">Delete</button>
                     </div> 
                     <br />  
                     <div id="employee_table" style="margin-top:80px;"> 
                     <?php
                     if(isset($_SESSION['category']))
                     {
                          echo "<p style='text-align:center;'><strong>". $_SESSION['category']." Orders Only</strong></p>";
                          echo "<p style='text-align:center; color:red;'><i>( Please select Select All in filter to see all orders )</i></p>";
                     }
                     ?>  
                    <form name="frmOrders" id="frmOrders" method="post" target="_blank" action="">
                         <table class="table table-bordered" id="pendingts">  
                              <tr>  
                                   <th width="5%">
                                        <button type="button" id="pendingselectAll" class="main">
          				          <span class="sub"></span> Select All </button>
                                   </th>
                                   <th width="15%">Image</th> 
                                   <th width="5%">Order ID</th>
                                   <th width="20%">SKU</th> 
                                   <th width="20%">Address</th> 
                                   <th width="20%">Flags</th>
                                   <th width="5%">Edit</th>  
                                   <th width="5%">View</th>
                                   <th width="5%">Delete</th>     
                               </tr>  
                               <?php  
                               $g=0;
                               $multiple = array();
                               while($row = mysqli_fetch_array($result))  
                               {
                                   if ($row["merge"]!="" && $row["merge"]!="Merged")
                                   {
                                        continue;
                                   } 
                                   $ordersku= $row["sku"];
                                   $mainimageresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "'");
                                   $mainimageorder = null;
                                   $rowCount = mysqli_num_rows($mainimageresult);

                                   if($rowCount>0){
                                        $mainimagerow = mysqli_fetch_array($mainimageresult);
                                        $mainimageorder = $mainimagerow['image'];
                                   }
                                   
                                   if(empty($mainimageorder) && (strpos($ordersku, '+') === false))
                                   {
                                        $mainimageresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                                        $mainimagerow= mysqli_fetch_array($mainimageresult);
                                        $mainimageorder=$mainimagerow['Mainimage'];
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
                                    <td><?php echo "<img id='img".$row["id"]."' style='width:100px; height:auto;' src='".$mainimageorder."'>"; ?></td>
                                    <td><?php echo $row["orderID"];if($row["merge"]=="Merged") echo '<br>Merge'; ?></td>
                                    <td><p id=<?php echo $row["id"];?>><?php echo $row["sku"].'</p>'.$row["date"].'<br>'.$row["channel"]; ?></td> 
                                    <td rowspan=<?php echo $rowspanno; ?>><?php echo $address; ?></td>
                                    <td><?php echo $row["flags"]; ?></td>
                                    <td><input type="button" name="edit" value="Edit" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs edit_data" /></td>  
                                    <td><input type="button" name="view" value="view" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs view_data" /></td> 
                                    <td><input type="button" name="delete" value="delete" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs delete_data" /></td> 
                               </tr>  
                               <?php 
                                   if($row["merge"]=="Merged")
                                   {
                                   while($mergerow = mysqli_fetch_array($mergeresult))
                                   {
                                        if($row["channel"]!=$mergerow["channel"])
                                        {
                                             $multiple[$g]=$row["shippingaddresspostcode"];
                                             $g=$g+1;
                                        }
                                   $ordersku= $mergerow["sku"];
                                   $mainimageresult = mysqli_query($connect, "SELECT * FROM comboproducts WHERE sku='" . $ordersku . "' OR originalsku='" . $ordersku . "' ");
                                   $mainimageorder = null;
                                   $rowCount = mysqli_num_rows($mainimageresult);

                                   if($rowCount>0){
                                        $mainimagerow = mysqli_fetch_array($mainimageresult);
                                        $mainimageorder = $mainimagerow['image'];
                                   }
                                   
                                   if(empty($mainimageorder)&&(strpos($ordersku, '+') === false))
                                   {
                                        $mainimageresult = mysqli_query($connect, "SELECT * FROM products WHERE SKU='" . $ordersku . "'");
                                        $mainimagerow= mysqli_fetch_array($mainimageresult);
                                        $mainimageorder=$mainimagerow['Mainimage'];
                                   } 
                               ?>  
                               <tr>
                                   <td style="text-align:center"><input type="checkbox" name="orders[]" value="<?php echo $mergerow["id"]; ?>" ></td>
                                    <td><?php echo "<img id='img".$mergerow["id"]."' style='width:100px; height:auto;' src='".$mainimageorder."'>"; ?></td>
                                    <td><?php echo $mergerow["orderID"]; ?></td>
                                    <td><p id=<?php echo $mergerow["id"];?>><?php echo $mergerow["sku"].'</p>'.$mergerow["date"].'<br>'.$mergerow["channel"]; ?></td> 
                                    <td><?php echo $mergerow["flags"]; ?></td>
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
                     </form>
                     </div>  
                </div>
                <?php
                if(!empty($multiple))
                {
               ?>
                <div>
                    <table class="table table-dark">
                    <thead>
                    <tr>
                         <th scope="col">Merge Different Channels Post Code</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($multiple as $value)
                    {
                    echo '<tr><td>'.$value.'</td></tr>';
                    }
                    ?>
                    </tbody>
                    </table> 
               </div> 
               <?php
               }
               ?>
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
                                <option value="Select">Select All</option>
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
     $('#zero_config_test').DataTable( {
          "processing": true,
          "serverSide": true,
          "ajax": "../functions/server_processing.php?forwhich=demoOrders"
     } );

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
      $(document).on('click', '.delete_data', function(){ 
          event.preventDefault();  
           var order_id = $(this).attr("id");  
           $.ajax({  
                     url:"delete.php",  
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
                     url:"inserttest.php",  
                     method:"POST",  
                     data:$('#insert_form').serialize(),  
                     beforeSend:function(){  
                          $('#insert').val("Inserting");  
                     },  
                     success:function(response){
                         var values = $.parseJSON(response);  
                          $('#insert_form')[0].reset();  
                          $('#add_data_Modal').modal('hide');
                          var imgid="#img"+values.id; 
                          document.getElementById(values.id).innerHTML = values.sku;
                          $(imgid).attr("src",values.image);

                     }  
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
                     url:"insert.php",  
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
                     url:"insert.php",  
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
                     url:"insert.php",  
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

 $(document).on('click', '#packlist', function(){ 

if(confirm("Are you sure want to create packlist for this products")) {
document.frmOrders.action = "packlist.php";
document.frmOrders.submit();
}
});  
 </script>