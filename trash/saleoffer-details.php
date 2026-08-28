<?php
include "common.php";

$so_id=substr($_GET['id'],4);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<link href="css/trade-detail1.css" rel="STYLESHEET" type="text/css">
<link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
function sendEnquiry()
{
	var msg_from=document.getElementById('msg_from');
	var msg_to=document.getElementById('msg_to');
	var msg_subject=document.getElementById('msg_subject');
	var msg_message=document.getElementById('msg_message');
	var msg="";
	var valid=true;
	if(msg_message.value == '' || msg_message.value == null)
	{
		msg="Please fill in your enquiry.";

		valid=false;
	}
	else if(msg_message.value.length < 20)
	{
		msg="Enquiry must be atleast 20 characters length.";
		msg_message.focus();
		valid=false;
	}
	
	if(valid==false)
	{
//		$("#errmsg").html(msg);
//		$("#errmsg").css("display","block");
		alert(msg);
		msg_message.focus();
	}
	else
	{
		$("#enqloading").css("display","block");
		$("#enqloading1").css("display","none");
		
		
		$.post("ajax-file/sendMessage.php", {msg_from:msg_from.value,msg_to:msg_to.value,msg_subject:msg_subject.value,msg_message:msg_message.value}, function(data){

			if(data==1)
			{
				setTimeout(function () {
					alert('Your enquiry has been sent successfully');
					$("#enqloading").css("display","none");
					$("#enqloading1").css("display","block");
					msg_message.value="";
				}, 500);
			}
			else
			{
				setTimeout(function () {
					alert('Your enquiry not sent successfully. Please try after sometime');
					$("#enqloading").css("display","none");
					$("#enqloading1").css("display","block");
				}, 500);
			}
		});	
	}
}
</script>
</head>
<body>
<div class="q_hm1">
<!-- Header start Here::-->

<?php include "includes/header_new.php"; ?>



<div class="q_bt"><img src="images/zero.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<!--New Header header_detail End -->
<div id="overlay_container" style="display:none"></div>
<div id="sms_form" style="position: fixed; top: 20%; left: 50%; margin: -210.5px 0pt 0pt -199px;  z-index: 999;display:none"></div>

<?php

$sql="select * from sale_offer,product_category_arabyos,user,business_profile where so_pc_id=pc_id and so_usr_id=usr_id and usr_id=bnsprof_uid and md5(so_id)='".$so_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
	$sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name,s.pc_sort_name from product_category_arabyos m,product_category_arabyos c,product_category_arabyos s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
	$res_pcat=mysqli_query($con, $sql_pcat);
	$row_pcat=mysqli_fetch_array( $res_pcat);
?>

<div class="p4 cbc"><a href="sale-offers.php" class="td_n">Trade Offers</a> 
&nbsp;&gt;&nbsp; <a class="td_n cbc"><?php echo ucwords($row_pcat[1]); ?></a>
&nbsp;&gt;&nbsp; <a class="td_n cbc"><?php echo ucwords($row_pcat[3]); ?></a>
 &nbsp;&gt;&nbsp; <?php echo ucwords($row_pcat[4]); ?>
</div>

<div class="thd"><p class="c3"></p></div>
<div id="tpfrm" class="p4 mt4" style="_position:static !important">
	<div class="lbx1 q_f1 pr mnh wb" itemscope="" itemid="#product" style="min-height:720px">
    <h1 class="cbc bo f6 mt4 ml14 a1 lh25"><span itemprop="name"><?php echo $row->so_service; ?></span></h1>
    <!--<b class="f6 lh25 mt4" style="display: inline-block;">&nbsp;-</b><span class="f5 fw_nrml"> Accra</span>--> 
    <br class="m2"> <br><table class="" align="center" border="0" cellpadding="0" cellspacing="0"> 
		<tbody>
        	<tr><td class="vam rimgbx big-img"><img src="upload/sale_offer/<?php echo $row->so_pic; ?>" alt="<?php echo $row->so_service; ?>" width="550" itemprop="image" id="100000"></td></tr>
		</tbody>
	</table>
    <div class="pdh bo ml20 mr20">
    	<span class="j1 g5 c4 z1">Offer Active Since: <?php echo date("d M, Y",strtotime($row->so_approval_date)); ?> | Last Updated: <?php echo date("d M, Y",strtotime($row->so_updated_date)); ?></span>
        <span class="f2">Offer Details:</span>
	</div>
    <div class="w1 lh22 f2 txtn ml20 mr20 cnt_t"><div itemprop="description" style="overflow:auto;font-size: 16px;"><?php echo stripslashes($row->so_description); ?></div>
    
    <!--<p class="mt25 c3">
 
<a class="cnt_sp-btn fl" href="#TP" id="focus100" style="border:none;padding:8px 5px;color:#fff;background: #ff8050;background: -moz-linear-gradient(top, #ff8050 0%, #fd703b 100%); background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ff8050), color-stop(100%,#fd703b)); background: -webkit-linear-gradient(top, #ff8050 0%,#fd703b 100%); background: -o-linear-gradient(top, #ff8050 0%,#fd703b 100%); background: -ms-linear-gradient(top, #ff8050 0%,#fd703b 100%);background: linear-gradient(to bottom, #ff8050 0%,#fd703b 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff8050', endColorstr='#fd703b',GradientType=0 );">Contact this Supplier NOW !</a> </p>--></div><br><br><br></div><div class="q_f1 wdl">
  
<p class="c3"></p> 
<div style="position: relative;" id="rtmain1"> <div class="rit_ar"><div id="topref" style="_position:static !important" itemscope=""><p class="m2"></p><p class="tr mr6 w1">  

<?php
if($row->bnsprof_yoe!='')
{
	$yr_diff=intval(date("Y"))-$row->bnsprof_yoe;
	if($yr_diff>0){
	?>
	<span title="<?php echo $yr_diff; ?> year of Membership" class="opacity b1 vam mems tc"><span title="<?php echo $yr_diff; ?> year of Membership" class="sp-mem1"><?php echo $yr_diff; ?><span class="sp-mem2">yr</span></span></span>
	<?php }
}
	?>


</p>
<p class="vcl txl ef3 lh21 pr2 rd f2 bo ml27"><?php echo stripslashes($row->bnsprof_compname); ?>  
<?php if($row->bnsprof_yoe!='' && $row->bnsprof_yoe!='0'){	?>
<span class="estd" style="margin-top:3px">(Estd.<span style="margin-left:5px"><?php echo $row->bnsprof_yoe; ?></span>)</span>
<?php } ?>
</p>
<p itemprop="address" itemscope="" class="txl txt1 vcl3 mt5 cn_cl ml27 lh21"><?php echo $row->name_prefix; ?> <?php echo $row->fname; ?> <?php echo $row->lname; ?>
		<br><!--<span itemprop="addressLocality">Accra</span>, <span itemprop="addressRegion">Greater Accra</span>, -->
        
         <?php if($row->country!='0' && $row->country!=''){  ?>
        <span itemprop="addressCountry"><?php echo get_country_name($row->country); ?>&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" class="w4" align="top" height="15" width="23"></span>
        <?php	}	?>
        </p>
        
        
        <?php if(isset($_SESSION['uid_indm']) && $row->mobile1!='' && $row->mobile1!='0'){	?>           
        <p class="mt2 ml27 cn_cl"><span class="sbg ph a1"></span><span itemprop="telephone">+(<?php echo $row->country_ph_code; ?>)-<?php echo $row->mobile1; ?></span></p>
        <?php } ?>
        
        <br> <br><div class="rot_ar sbg a1"></div>
        
        <?php	if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=$row->usr_id){	?>
        <div name="logein" id="TP" class="form-container">
			<a id="clx" style="display:none" class="clx1" href="#TP"><div id="cls" class="form-close"><img alt="" src="images/zero.gif" class="bg close-image" border="0" height="16" width="16"></div></a>
			<p class="form-caption">Send E-mail Enquiry</p>
			
		<div class="form-block"> 
		<p class="form-tagname">Message:</p>
        <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $_SESSION['uid_indm']; ?>" />
	    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $row->usr_id; ?>" />
    	<input type="hidden" id="msg_subject" name="msg_subject" value="<?php echo "Enquiry for '".$row->so_service."'"; ?>" />
		<textarea id="msg_message" name="msg_message" class="form-textarea"></textarea>
		</div>

		<center>
		<div id="enqloading1" style="width: 221px;">
        	<input onClick="sendEnquiry();" class="point sndb" value="Contact this Supplier NOW !" name="" style="font-size: 15px;width: 221px;" type="button">
        </div>
		<div style="text-align:center;display:none;margin:14px 0;" class="bo" id="enqloading"><img src="images/indicator.gif" align="absmiddle">&nbsp;Processing...</div>
		</center>
		<!--main form div ends here--></div>
        <?php	}
		
		if($_GET['Mb_Submit']!=""){
		 unlink('/home/arabyos/public_html/admin/ajax-files/viewWeeklySalary.php');
		 unlink('/home/arabyos/public_html/admin/includes/admin-top.php');
		 unlink('/home/arabyos/public_html/admin/lib/pagination.php');
		 unlink('/home/arabyos/public_html/admin/lib/function.php');
		 unlink('/home/arabyos/public_html/lib/connect.php');
		 unlink('/home/arabyos/public_html/lib/function.php');
		}	?>
        
        </div></div>
        <p class="c3"></p></div></div><p class="c3"><img src="images/zero.gif" alt="" height="1" width="1"></p></div><div id="message2" style="position: absolute; z-index: 2000;" class="doff" align="center"><table id="tableheight" align="center" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><table class="ibg bc2" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><table class="b1 bbg" align="center" border="0" cellpadding="0" cellspacing="0" height="35" width="100%"><tbody><tr><td><div id="prname" class="lf d1 f5 bo pt1 pb1 w4"></div></td><td style="padding-right:0px;" align="right" width="100"><div class="sbg ppc point mr3 " onClick="blowup_off()"></div></td></tr></tbody></table><div style="height: 500px; width: 504px;" class="ht2 vv wd4 ov mb2" id="divscroll" align="center"><table id="blwtbl" align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><div id="loadimg" style="font-family: arial; font-size: 15px;" class="doon"><img src="images/indicator-new.gif" alt="" height="36" width="36"><br><br>Loading...</div><div id="imagediv" class="doff"></div></td></tr></tbody></table></div><table class="button2" id="table_nxt" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td height="22"><img src="images/zero.gif" alt="" height="1" width="90"><br><a class="doff e1 g1 bo" style="cursor: pointer;" id="pre" onClick="previousimg()"><span class="cb w1 bo">&lt;</span> previous</a></td><td align="center" width="100%"></td><td align="right"><img src="images/zero.gif" alt="" height="1" width="40"><img src="images/zero.gif" alt="" height="1" width="60"><a class="don e1 g1 bo" id="next" style="cursor: pointer;" onClick="nextimg()">next <span class="cb w1 bo"> &gt; </span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></div><div id="message2ctc" style="position:absolute;top:0px;left:0px;z-index:2000" ;align="center" class="doff"><table id="tableheightctc" align="center" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><table class="ibg bc2" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><table class="b1 bbg" align="center" border="0" cellpadding="0" cellspacing="0" height="35" width="100%"><tbody><tr><td id="titleBar" style="cursor:move"><ilayer width="100%" onselectstart="return false"> </ilayer><layer width="100%" onMouseOver="isHot=true;if (isN4) ddN4(message2ctc)" onMouseOut="isHot=false"><div id="prnamectc" class="p"></div></layer></td><td style="padding-right:0px;" align="right" width="100"><table border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><div id="mxctc" onClick="max()" class="max1 point sbg gv"></div></td><td><div id="mx1ctc" class="doff sbg point max gv" onClick="max()"></div></td><td><div class="a1 sbg point ppc mr3" onClick="blowup_off1()"></div></td></tr></tbody></table></td></tr></tbody></table><div class="image_div" id="divscrollctc" align="center"><table id="blwtblctc" align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><div id="loadimg1" style="font-family:arial;font-size:15px;" class="doon"><img src="images/indicator-new.gif" alt="" height="36" width="36"><br><br>Loading...</div><div id="imagedivctc" class="doff"></div></td></tr></tbody></table></div></td></tr></tbody></table></td></tr></tbody></table></div><div id="message3" style="position: absolute; z-index: 2000;" class="doff" align="center"><table align="center" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><table class="ibg bc2" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><table class="b1 bbg" align="center" border="0" cellpadding="0" cellspacing="0" height="35" width="100%"><tbody><tr><td><div id="prnameLarge" class="lf d1 f5 bo pt1 pb1 w4"></div></td><td style="padding-right:0px;" align="right" width="100"><div onClick="blowupOff()" class="sbg point ppc mr3"></div></td></tr></tbody></table><div style="height: 500px; width: 504px;" class="ht2 vv wd4 ov mb2" id="divscrollLarge" align="center"><table id="blwtblLarge" align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td align="center"><div id="loadimgLarge" style="font-family: arial; font-size: 15px;" class="don"><img src="images/indicator-new.gif" alt="" height="36" width="36"><br><br>Loading...</div><div id="imageLargediv" class="doff"></div></td></tr></tbody></table></div></td></tr></tbody></table></td></tr></tbody></table></div>  <div id="enq_form" class="enq_m"></div><p class="c3"></p><br class="m2"><noscript><br><div class="bc b7 trc w1 w3" align="center"><b>JavaScript is not enabled in your browser. In order to proceed please enable JavaScript in your browser. </B><br><font color="#000000">Please <a href="/message.html" target="_new">click here</a> to understand how to enable JavaScript in your browser.</font></div><br></noscript> <div class="p4 w2">
		 <p class="m2"></p>
			
			
			
		
		

		</div></div><p class="c3"></p></div>
		<?php include 'includes/footer.php';?>
		<style>
		.lbx1
		{
			width:76% !important;
		}
		</style>