<?php 
//ob_start();
//session_start(); 
include "../common.php";
	

check_user_login();
class editProduct{
	
	var $msg;
	var $mp_id;
	var $mp_name;
	var $mp_rate;
	var $mp_quotesPerMonth;
	
	function __construct($mp_id){
		$this->mp_id=$mp_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from membership_plan where mp_id=".$this->mp_id;
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid(){
	
		$valid=true;
		if($this->mp_name == "")
		{
			$this->msg= '<font color="#CC0000">Please enter plan name.</font>';
			$valid=false;
		}
		else if($this->mp_rate == "" || $this->mp_rate == " ")
		{
			$this->msg= '<font color="#CC0000">Please enter plan rate</font>';
			$valid=false;
		}
		else if($this->mp_quotesPerMonth == "" || $this->mp_quotesPerMonth == " ")
		{
			$this->msg= '<font color="#CC0000">Please enter number of quotes per month</font>';
			$valid=false;
		}
			
		return $valid;
	}
	
	function update()
	{		
	   global $con;
		$sql="update membership_plan
				set					
					mp_name ='".$this->mp_name."',
					mp_rate ='".$this->mp_rate."',
					mp_quotesPerMonth ='".$this->mp_quotesPerMonth."',								
					mp_updated_date=now()
				where
					mp_id='".$this->mp_id."'";
					
		mysqli_query($con, $sql) or die(mysql_error());
															
		$this->msg='<font color="#009900">Plan updated successfully</font>';	
	}	
}

if(isset($_SESSION['msg'])){
	$msg=$_SESSION['msg'];
	unset($_SESSION['msg']);
}

$ob=new editProduct($_GET['fid']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){

	$ob->mp_name=addslashes(trim($_POST['mp_name']));
	$ob->mp_rate=addslashes(trim($_POST['mp_rate']));
	$ob->mp_quotesPerMonth=addslashes(trim($_POST['mp_quotesPerMonth']));	
	
	if($ob->valid()){
		$ob->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ob->msg;
	
	header("location:memplan-edit.php?fid=".$ob->mp_id);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Administrative Panel</title>
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<script src="js/common.js" language="javascript"></script><!-- Common Validation Function -->		
<link href="style/style.css" type="text/css" rel="stylesheet"/>
</head>

<body>
<div class="main">
	<?php include "includes/admin-top.php" ?>
	<div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div class="bodyRightCon" style="margin-bottom:45px;">
        	<div class="bodyRightightCon_inner">
				<div class="bcMenuCon">
    				<div class="bcMenu">
      					<ul>
					        <li>&rsaquo;&nbsp;&nbsp;Membership Plan Management</li>
					        <li>&rsaquo;&nbsp;&nbsp;Edit</li>
						</ul>
					    <ul class="right">
					        <li><a href="memplan-view.php">Plan List</a></li>
      					</ul>
      					<div class="clr"></div>
    				</div>  
                 <br clear="all"/>
				</div>  
   					<div>   
						<!--<div class="admin-hdr-bg">   
							<div class="eID"><strong>&nbsp;</strong></div>
							<div class="eID"><strong><?php //echo $msg;?></strong></div>
							<div class="clr"></div>
						</div>-->
						<div class="admin-dtls">
							<form action="" id="test_edit" name="test_edit" method="post" enctype="multipart/form-data" onsubmit="return filling();">
								<ul>	
									<li style="line-height:12px">
										<div class="eID">&nbsp;</div>
										<div class="eID" style="width:500px"><strong><?php echo $msg;?></strong></div>		
										<div class="clr"></div>
									</li>                                    
                                    <li>
										<div class="eID"><strong>Plan Name:</strong></div>
										<div class="eID" style="width:400px; padding-top:5px;">
											<input type="text" name="mp_name" id="mp_name" class="reg_txtfld" value="<?php echo stripslashes($row->mp_name); ?>" />
										</div>
										<div class="clr"></div>
									</li>
                                    <li>
										<div class="eID"><strong>Rate per month:</strong></div>
										<div class="eID" style="width:400px; padding-top:5px;">
											<input type="text" name="mp_rate" id="mp_rate" class="reg_txtfld" value="<?php echo stripslashes($row->mp_rate); ?>" />
										</div>
										<div class="clr"></div>
									</li>
                                    <li>
										<div class="eID"><strong>Quotes per month:</strong></div>
										<div class="eID" style="width:400px; padding-top:5px;">
											<input type="text" name="mp_quotesPerMonth" id="mp_quotesPerMonth" class="reg_txtfld" value="<?php echo stripslashes($row->mp_quotesPerMonth); ?>" />
										</div>
										<div class="clr"></div>
									</li>
									<li style="text-align:center">										
										<input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="butt" style="margin-right:10px; margin-top:5px;" />								
										<div class="clr"></div>
									</li>									    
								</ul>
							</form>
						</div>
					</div>

			</div>			
		</div>
        <br clear="all" />
	</div>  	   	
</div>
<?php include "includes/footer.php" ?>
</body>
</html>
