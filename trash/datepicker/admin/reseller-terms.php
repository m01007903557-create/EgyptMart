<?php 
//ob_start();
//session_start(); 
include "../common.php";
$reseller_id=$_SESSION['reseller_id'];
$pid=substr($_GET['pid'],4);
//echo get_currency_symbol('currency-symbol');
//check_user_login();
$cursql=mysqli_query($con, "select * from site_settings_arabyos where st_field ='currency-symbol'");
$currow=mysqli_fetch_object($cursql);

$sqlmchk= "select * from reseller where reseller_id='".$reseller_id."' ";	
$resmchk=mysqli_query($con, $sqlmchk);
$rowmchk=mysqli_fetch_object($resmchk);

$tsql= "select * from cms_arabyos_arabyos where cms_id='3' ";	
$tres=mysqli_query($con, $tsql);
$trow=mysqli_fetch_object($tres);

class editproduct{
	var $pd_id;	
	var $msg;		
	var $reseller_terms;	

	function __construct($reseller_id){
		$this->reseller_id=$reseller_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from products where md5(pd_id)='".$this->pd_id."' ";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid(){	
		$valid=true;
		if($this->reseller_terms == "")
		{
		   $this->msg= '<font color="#CC0000">Please enter terms</font>';
		   $valid=false;
		}	
		return $valid;
	}
	
	function update() 
	{
		global $con;
		     $sql2="update reseller set				
		     reseller_terms ='".$this->reseller_terms."'  where reseller_id='".$this->reseller_id."' ";
		mysqli_query($con, $sql2) or die(mysql_error());				
		$this->msg='<font color="#009900">Terms updated successfully.</font>';	
	}	
}

if(isset($_SESSION['msg'])){
	$msg=$_SESSION['msg'];
	unset($_SESSION['msg']);
}

$ob=new editproduct($reseller_id);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){
	
	$ob->reseller_terms=trim(addslashes($_POST['reseller_terms']));								
	if($ob->valid()){
		$ob->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ob->msg;
	
	header("location:reseller-terms.php");
}

?>

	<?php include "includes/admin-top.php" ?>
	
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>

<!-- /TinyMCE -->	
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Reseller Terms&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Update terms</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
 <strong><font color="#CC0000"><label id='err_msg' style="width:200px; color:#D00;"><?php echo $msg;?></label></font></strong><br />

<div class="x2-layout" style="width:650px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>
<tr class="formSectionRow" style="width:500px;">
<td  style="width:278px">

       
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Terms:</label>
			<div class="formInputBox" style="width:440px;height:260px;">
<textarea name="reseller_terms" id="reseller_terms" class="reg_txtarea" rows="10" cols="40"><?php if($rowmchk->reseller_terms!=""){ echo ucwords($rowmchk->reseller_terms); } else { echo $trow->cms_content;}?></textarea>
			</div>
        </div>


        
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
	
	<div class="formInputBox" style="width:187px;height:auto;">
	<input type="hidden" name="uid" id="uid" value="<?php echo $reseller_id;?>">
	</div>
        </div>            
</td>
</tr>
</tbody></table></div></div> </div>  		    																																					
	<div class="row buttons">
  	<input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;"> 		</div>						    
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
