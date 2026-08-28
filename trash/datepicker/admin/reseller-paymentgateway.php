<?php 
ob_start();
session_start(); 
include "../common.php";
echo $reseller_id=$_SESSION['reseller_id'];

$sqlchk= "select * from payment_gateway where pg_status='1' ";	
$reschk=mysqli_query($con, $sqlchk);
?>

<?php include "includes/admin-top.php" ?>
	
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<script>
function addgateway(gateway){
    	var id=gateway.split(',');
	var i;
	var total = '';
        var total_gateway = '';
        for (i = 0; i < id.length; i++) {
		var resl_pg_cardno=$('input#resl_pg_cardno'+id[i]).val();
                var dt = resl_pg_cardno.trim();
                if(dt != "")
                    {
                 total=''+id[i]+':'+dt+'';
		 total_gateway=total_gateway+'||'+total;
                    }       
	}
        if(total_gateway!="")
        {    
        $.get("update_gateway.php", {total_gateway:total_gateway},
	function(data){
	//alert(data);
	});
        }
}
</script>	
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Reseller&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Payment Gateway</h2>
<strong><font color="#CC0000"><label id='err_msg' style="width:200px; color:#D00;"><?php echo $msg;?></label></font></strong><br />
<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>
<tr class="formSectionRow">
<td  style="width:678px">

<?php 
$gateid="";
while($rowchk=mysqli_fetch_object($reschk)) { 
$gateid=$gateid.$rowchk->pg_id.","; 
$sqlchkk="select * from reseller_payment_gateway where resl_pg_resellerid='".$reseller_id."' and resl_pg_gateway='".$rowchk->pg_id."'";
$reschkk=mysqli_query($con, $sqlchkk);
$rowchkk=mysqli_fetch_object($reschkk);
?>       
<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:120px;"><?php echo $rowchk->pg_name;?>: </label>
<div class="formInputBox" style="width:440px;height:auto;">
<input name="resl_pg_cardno<?php echo $rowchk->pg_id; ?>" id="resl_pg_cardno<?php echo $rowchk->pg_id; ?>" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $rowchkk->resl_pg_cardno;?>" />
</div>
</div>
<?php 
} 
$gateid5=substr($gateid,0,-1);
?>                   
</td>
</tr>
</tbody></table></div></div> </div>  		  																																		<div class="row buttons">
<input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;" onclick="addgateway('<?php echo $gateid5;?>');"> 
</div>						    
	  
 			<br clear="all"/>
		</div>
			
	</div>
	</div>
  	<br clear="all" />   	
</div>
<?php include "includes/footer.php" ?>
</body>
</html>