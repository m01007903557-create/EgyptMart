<?php 
include "../common.php";
$id=substr($_GET['token'],4);

$sql="select * from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id and md5(br_id)='".$id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
?>
	<?php include "includes/admin-top.php" ?>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>Buy Requirement Details</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<br />
<div class="x2-layout" style="width:980px; height:auto;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
	
    <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:240px;"><h2>Name: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto; font-family: Arial, Helvetica, sans-serif; font-size:13px;">
                 <?php echo $row->br_pd_name; ?>
			</div>
		</div>
        
		
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:240px;"><h2>Details: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
                 <?php echo $row->br_requirement; ?>
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Estimated Quantity:</h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                 <?php echo $row->br_estimate_qty." ".$row->mu_name; ?> 
			</div>
		</div>
        
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Approximate order value: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_apprx_order_currency." ".$row->br_apprx_order_value; ?>
			</div>
		</div>
        
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Product application/ usage: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_description; ?>
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Website: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_website; ?>
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Need quotations: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_need_quote_for; ?>
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Preferred supplier location: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_preferred_supplier_location; ?>
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Why need this: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_need_for; ?>
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Requirement Frequency: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php echo $row->br_requirement_frequency; ?>
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