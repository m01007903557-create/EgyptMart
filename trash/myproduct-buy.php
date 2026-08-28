<?php
include 'common.php';
$uid=$_SESSION['uid_indm'];

if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg'];	unset($_SESSION['msg']); }   else{ $msg=""; }
if(isset($_POST['btnUpdate']))
{
    $pdby_title=$_POST['pdby_title'];
	$usid=$_POST['usid'];
	$aid=$_POST['aid'];
	
	$reschk=mysqli_query($con, "select * from product_buy where pdby_uid='".$usid."' order by pdby_id desc");
	if(mysqli_num_rows($reschk)>0){
		mysqli_query($con, "delete from product_buy where pdby_uid='$usid'");
		 foreach($pdby_title as $val){
			if($val!=""){
				$key=$val;
				//$sql_key="select * from buy_requirement join product_category_arabyos on product_category_arabyos.pc_id=buy_requirement.br_pc_id where br_pd_name   like '%".$key."%' and pc_status='1'";
				$sql_key="select * from product_category_arabyos where pc_name = '".$key."' and pc_status='1'";
				//echo $sql_key;die;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				$key_cat_id = $row_key->pc_id;

				$query = "SELECT * FROM selloffer_alert_category WHERE sac_pc_id=".$key_cat_id." AND sac_usr_id=".$uid;	
				$r=mysql_query($query);	
				if(mysql_num_rows($r) == 0){		
					$SQL_BUY_ALERT="insert  into selloffer_alert_category SET 
													  sac_usr_id=".$uid.",
													  sac_pc_id=".$key_cat_id.",
													  sac_updated_date=now()";
					$r=mysql_query($SQL_BUY_ALERT) or die('Error in query while saving');
				}

				
			$sql="insert into product_buy set pdby_uid='".$usid."', pdby_title='".$val."' ";
			mysqli_query($con, $sql);
			}
		 }
	 }else{
	 	$count = 0;
		foreach($pdby_title as $val){
			if($val!=""){
				$key=$val;
				
				
				//$sql_key="select * from buy_requirement join product_category_arabyos on product_category_arabyos.pc_id=buy_requirement.br_pc_id where br_pd_name   like '%".$key."%' and pc_status='1'";
				$sql_key="SELECT * FROM product_category_arabyos WHERE pc_name='$key' and pc_status='1'";
				//echo $sql_key;die;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				$key_cat_id = $row_key->pc_id;

				$query = "SELECT * FROM selloffer_alert_category WHERE sac_pc_id='$key_cat_id' AND sac_usr_id='$uid'";	
				$r=mysql_query($query);	
				if(mysql_num_rows($r) == 0){		
					$SQL_BUY_ALERT="insert into selloffer_alert_category SET 
													  sac_usr_id='".$uid."',
													  sac_pc_id='".$key_cat_id."',
													  sac_updated_date=now()";
					$r=mysql_query($SQL_BUY_ALERT) or die('Error in query while updating - ' . $key_cat_id);
				}

			$sql="insert into product_buy set pdby_uid='".$usid."', pdby_title='".$val."' ";
			mysqli_query($con, $sql);
			}
			$count++;
		}
	 }
	$msg='<div class="save bnr mt12" id="savemsg"><strong>Products / Services you Buy saved successfully!</strong></div>';
	$_SESSION['msg']=$msg;
	header("location:myproduct-buy.php");
}
$aid="";
$pname="";
$res=mysqli_query($con, "select * from product_buy where pdby_uid='".$uid."' order by pdby_id desc");
while($row=mysqli_fetch_object($res))
{
 $pname= $row->pdby_title.",".$pname;
 $aid= $row->pdby_id.",".$aid;
}
$pname=substr($pname,0,-1);
$proname=explode(",",$pname);
$aid=substr($aid,0,-1);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<!-- meta start -->
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/b-v-7.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<style>.label { color: #000 !important; } </style>
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
function chkalldetails()
{
	var bnsprof_regno=document.getElementById('bnsprof_regno');
	var bnsprof_regauthority=document.getElementById('bnsprof_regauthority');
    var message="";
    var valid=true;
	
	if(bnsprof_regauthority.value!= '' && bnsprof_regno.value== '')
	{
		message='Registration Number is compulsory with Registration Authority.';
		bnsprof_regno.focus();
		valid=false;
	}
	else if(bnsprof_regno.value!="" && bnsprof_regauthority.value == '')
	{
		message="Registration Authority is compulsory with Registration Number.";
		bnsprof_regauthority.focus();
		valid=false;	
	}
	if(!valid)
	{
		document.getElementById('updatemessage').style.color = "red";
		document.getElementById('updatemessage').innerHTML = message;	
	}
	return valid;
}
</script>

<script>
function add_more(id)
{	
$('#hidetbl').hide();
$('#field1').show();
}
</script>   
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
<?php include "includes/header_new.php"; ?> 
	<br><br>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>


<!-- Header End Here::-->
<div class="inner_wrapper">
    		<?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
		<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->
        <div class="w56b f1 p2b p14 blr">
	<div>
	<!--<div class="bc f11">Company Profile &raquo;</div>-->
	<h1>Business Profile</h1>
	</div>
	<?php include 'includes/business-panel.php';?>
	<div id="re_link" class="utab"><span style="font-size: 14px;" class="f1">Add Products / Services you usually Buy & Get Sell Offer Alerts to your mailbox.</span></div>
	<div class="tbox" id="div_succ" style="display: none;"><strong style="color:#000;">Saved Successfully!</strong></div>
    <div style="text-align:left;width:489px;" class="" id="updatemessage"><?php echo $msg; ?></div>
	<div class="mt5">
	<!--Company Registration form:start-->
	<form action="" name="form1" class="f12" method="post" onsubmit="return chkalldetails();">
	<div class="frm_a clb" style="background-color:#FAF4FF">
		<table align="left" border="0" cellpadding="4" cellspacing="0" width="100%">			
			<tbody><tr>
			<td class="label" valign="top">* Add Products / Services<br>you usually Buy to get Sell Offer Alerts.</td><td>
			<table border="0" cellpadding="4" cellspacing="0" width="70%">
			<tbody>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[0]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[1]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[2]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[3]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[4]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[5]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[6]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[7]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[8]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[9]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
			</tbody></table>
			<table id="field1" class="" border="0" cellpadding="4" cellspacing="0" width="70%" style="display:none;">
			<tbody>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[10]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[11]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[12]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[13]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[14]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[15]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[16]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[17]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
            <tr>
            <td><input name="pdby_title[]" value="<?php echo $proname[18]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			<td><input name="pdby_title[]" value="<?php echo $proname[19]?>" class="a_f s_u" id="pdby_title" tabindex="29" maxlength="60" type="text"></td>
			</tr>
			</tbody></table>

			<div class="f1 m5" id="hidetbl"><a class="f_l" onclick="add_more(<?php echo $uid;?>); " id="ad_more1" style="cursor:pointer;">+ Add More</a></div>
			</td>
			</tr>
			<tr>
			<td>&nbsp;</td>
			<td align="left">
				<table><tbody><tr><td width="118px;">
                <input type="hidden" name="aid" id="aid" value="<?php echo $aid;?>">
                <input type="hidden" name="usid" id="usid" value="<?php echo $uid;?>" >
				<input name="btnUpdate" value="Update Details" class="saps mt5" tabindex="31" type="submit"></td><td> <span id="pf_save" style="display:none;margin-left:15px;margin-top:6px;"><img src="https://my.imimg.com/gifs-new/loading.gif" alt="" border="0" width="16" height="11"></span> </td></tr></tbody></table>
			</td>
			</tr>
			</tbody></table>
			<div class="clb">&nbsp;</div>
			</div>
	</form>
	<!--Company Registration form:ends-->
	
	</div>
	</div>
		<div class="c3">&nbsp;</div></div>
</div>
		<!--footer:start-->
	 <script type="text/javascript">
		 		 $(document).ready(function($113){
					lostFocus();
					$113('.a_f').unbind().live('keyup',function() {
						var type11='Products';
						$113(this).autocomplete("autocomplete.php", {
							selectFirst: true,
							extraParams: {type:type11},
							width: 410
						})
						.result(function(event, data, formatted) {
							var arr=data[0].split('>>');
							$(this).val(arr[arr.length-1]);
						});
					});
				});
                  </script>
		<?php include 'includes/footer.php'; ?>