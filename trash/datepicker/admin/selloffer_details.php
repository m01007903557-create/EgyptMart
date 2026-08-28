<?php 
include "../common.php";
$id=substr($_GET['token'],4);

$sql="select * from sale_offer where md5(so_id)='".$id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

if(isset($_POST['btnApprove']))
{
	
	$so_id=addslashes(trim($_POST['so_id']));

	$sql="update sale_offer set
			so_approval_status='1',
			so_approval_date=now()
		where
			so_id='".$so_id."'";
	mysqli_query($con, $sql);
	
	$_SESSION['msg']='<font color="#009900">Sale Offer Approved successfully.</font>';
	header("Location:selloffer_details.php?token=".rand(1000,9999).md5($so_id));
}
if(isset($_POST['btnDisApprove']))
{
	
	$so_id=addslashes(trim($_POST['so_id']));

	$sql="update sale_offer set 
			so_approval_status='2',
			so_approval_date=now()
		where
			so_id='".$so_id."'";
	mysqli_query($con, $sql);
	
	$_SESSION['msg']='<font color="#CC0000">Sale Offer Disapproved successfully.</font>';
	header("Location:selloffer_details.php?token=".rand(1000,9999).md5($so_id));
}
?>
	<?php include "includes/admin-top.php" ?>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>Sell Offer Details</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
<input type="hidden" id="so_id" name="so_id" value="<?php echo $row->so_id; ?>" />
<br />
<div id="message"><?php echo $msg;?></div>
<div class="x2-layout" style="width:980px; height:auto;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
	
    <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:240px;"><h2>Service: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto; font-family: Arial, Helvetica, sans-serif; font-size:13px;">
                      <?php echo $row->so_service; ?>
			</div>
		</div>
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:240px;"><h2>Description: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
                      <?php echo $row->so_description; ?>
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
         <?php
		 	$sql_pc="select c.pc_name,s.pc_name from product_category c,product_category s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$res_pc=mysqli_query($con, $sql_pc);
			$row_pc=mysqli_fetch_array( $res_pc);
		 ?>
			<label style="width:240px;"><h2>Category:</h2></label>
			<div class="formInputBox" style="width:450px;height:auto;">
                       <?php echo $row_pc[0]." &raquo; ".$row_pc[1]; ?> 
			</div>
		</div>
        
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Validity: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                      <?php if($row->so_validity=='365'){	echo "1 Year";	}else if($row->so_validity=='90'){echo "3 Months";	}else{	echo "1 Month";	} ?>
			</div>
		</div>
        
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Posting Date: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                <?php echo date("d-M-Y",strtotime($row->so_posting_date)); ?>
			</div>
		</div>
            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Picture: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
            	<?php if($row->so_pic!=''){ ?>
            <img src="../upload/sale_offer/<?php echo $row->so_pic; ?>" width="100px;" height="90px;" />
            <?php	}else{	?>
            <img src="../upload/sale_offer/no-image.png" width="100px;" height="90px;" />
            <?php } ?>
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px; font-family: Arial, Helvetica, sans-serif;font-size:13px;">
			<label style="width:240px;"><h2>Approval Status: </h2></label>
			<div class="formInputBox" style="width:357px;height:auto;">
                <?php 
				if($row->so_approval_status=='1'){ echo "Approved";	}
				if($row->so_approval_status=='0'){	echo "Pending Approval"; }
				if($row->so_approval_status=='2'){	echo "Disapproved"; } 
				?>
			</div>
		</div>
        

        
       
</td>
</tr>
</tbody></table></div></div> </div> 

<div class="row buttons">
<?php	if($row->so_approval_status=='0'){ ?>
<input type="submit" name="btnApprove" id="btnApprove" value="Approve" class="x2-button" style="margin-right:10px;margin-top:5px;" />&nbsp;<input type="submit" name="btnDisApprove" id="btnDisApprove" value="Disapprove" class="x2-button" style="margin-right:10px;margin-top:5px;">
<?php } ?>
</div>
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