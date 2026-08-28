<?php 
include "../common.php";
$id=substr($_GET['token'],4);

$sql=mysqli_query($con, "select * from contact_us where md5(cu_id)='".$id."'");
$row=mysqli_fetch_array( $sql);
?>

	<?php include "includes/admin-top.php" ?>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>Contact Details</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<br />
<div class="x2-layout" style="width:980px; height:auto;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
	
    <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:150px;"><h2>Name: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto; font-family: Arial, Helvetica, sans-serif; font-size:13px;">
                      <?php echo ucwords($row['cu_name']); ?>
			</div>
		</div>
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:150px;"><h2>Email: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
                      <?php echo $row['cu_email']; ?>
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:150px;"><h2>Message:</h2></label>
			<div class="formInputBox" style="width:727px;height:auto;">
                       <?php echo $row['cu_messages']; ?> 
			</div>
		</div>
        
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:150px;"><h2>Contact Number: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo $row['cu_contactnumber']; ?>
			</div>
		</div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:150px;"><h2>Country/State: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo $row['cu_country'].'-'.$row['cu_state']; ?>
			</div>
		</div>
        
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:150px;"><h2>Date: </h2></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <?php echo date('M d, Y', strtotime($row['cu_updated_date'])); ?>
			</div>
		</div>
</td>
</tr>
</tbody></table></div></div> </div>  		   
					    
	</form>    
 			<br clear="all"/>
		</div>
			
	</div>
	</div>
  	<br clear="all" />   	
</div>
<?php include "includes/footer.php" ?>
</body>
</html>