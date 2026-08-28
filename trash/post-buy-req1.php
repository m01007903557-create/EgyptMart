<?php
ob_start();
session_start();

include 'common.php';

$_SESSION['last_page']="post-buy-req.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];

if(isset($_SESSION['main_cat'])){	$main_cat=$_SESSION['main_cat'];	unset($_SESSION['main_cat']); }else{ $main_cat=""; }
if(isset($_SESSION['pc_id'])){	$pc_id=$_SESSION['pc_id'];	unset($_SESSION['pc_id']); }else{ $pc_id=""; }
if(isset($_SESSION['br_pc_id'])){	$br_pc_id=$_SESSION['br_pc_id'];	unset($_SESSION['br_pc_id']); }else{ $br_pc_id=""; }
if(isset($_SESSION['br_pd_name'])){	$br_pd_name=$_SESSION['br_pd_name'];	unset($_SESSION['br_pd_name']); }else{ $br_pd_name=""; }
if(isset($_SESSION['br_requirement'])){	$br_requirement=$_SESSION['br_requirement'];	unset($_SESSION['br_requirement']); }else{ $br_requirement=""; }
if(isset($_SESSION['br_estimate_qty'])){	$br_estimate_qty=$_SESSION['br_estimate_qty'];	unset($_SESSION['br_estimate_qty']); }else{ $br_estimate_qty=""; }
if(isset($_SESSION['br_estimate_qty_unit'])){	$br_estimate_qty_unit=$_SESSION['br_estimate_qty_unit'];	unset($_SESSION['br_estimate_qty_unit']); }else{ $br_estimate_qty_unit=""; }

class addProduct{
	
	var $msg;
	var $main_cat;
	var $pc_id;
	var $br_pc_id;
	var $br_u_id;
	var $br_pd_name;
	var $br_requirement;
	var $br_estimate_qty;
	var $br_estimate_qty_unit;
	var $br_preferred_supplier_location;
	
	
	function __construct($main_cat, $pc_id, $br_pc_id, $br_u_id, $br_pd_name, $br_requirement, $br_estimate_qty, $br_estimate_qty_unit, $br_preferred_supplier_location)
	{
		$this->main_cat=$main_cat;
		$this->pc_id=$pc_id;
		$this->br_pc_id=$br_pc_id;
		$this->br_u_id=$br_u_id;
		$this->br_pd_name=$br_pd_name;
		$this->br_requirement=$br_requirement;
		$this->br_estimate_qty=$br_estimate_qty;
		$this->br_estimate_qty_unit=$br_estimate_qty_unit;
		$this->br_preferred_supplier_location=$br_preferred_supplier_location;

		
		
		$_SESSION['main_cat']=$this->main_cat;
		$_SESSION['pc_id']=$this->pc_id;
		$_SESSION['br_pc_id']=$this->br_pc_id;
		$_SESSION['br_pd_name']=$this->br_pd_name;
		$_SESSION['br_requirement']=$this->br_requirement;
		$_SESSION['br_estimate_qty']=$this->br_estimate_qty;
		$_SESSION['br_estimate_qty_unit']=$this->br_estimate_qty_unit;
		$_SESSION['br_preferred_supplier_location']=$this->br_preferred_supplier_location;

	}

	function valid()
	{
		include "language.php";
		
		$sqlrpl = "select bd_word from bad_word";
		$resrpl = mysqli_query($con, $sqlrpl);
		while($rowrpl = mysqli_fetch_object($resrpl))
		{		
			$letters[] = strtoupper($rowrpl->bd_word);
		}
		$br_name=strtoupper($this->br_pd_name);
		$requirement=strtoupper($this->br_requirement);
		
		
		$valid=true;
		if($this->main_cat =="")
		{
			$this->msg='<font color="#FF0000">Kindly select Main Category.</font>';
			$valid=false;
		}
		else if($this->pc_id =="")
		{
			$this->msg='<font color="#FF0000">Kindly select Category.</font>';
			$valid=false;
		}
		else if($this->br_pc_id =="")
		{
			$this->msg='<font color="#FF0000">Kindly select Sub-Category.</font>';
			$valid=false;
		}
		else if($this->br_pd_name =="")
		{
			$this->msg='<font color="#FF0000">Kindly enter Products / Services you are looking for.</font>';
			$valid=false;
		}
		else if($this->br_pd_name != "")
		{		
			foreach($letters as $val){
				$pos = strpos($br_name, $val);
				if ($pos !== false) {
					$this->msg= "<font color='#FF0000'>You can't post words like '".$val."' in Product / Service Name.</font>";
					$valid=false;
				} 
			}
			
		}
		else if($this->br_requirement == "")
		{
			$this->msg= '<font color="#FF0000">Kindly describe your Buying Requirements in detail.</font>';
			$valid=false;
		}
		else if($this->br_requirement != "")
		{		
			foreach($letters as $val){
				$pos = strpos($requirement, $val);
				if ($pos !== false) {
					$this->msg= "<font color='#CC0000'>You can't post words like '".$val."' in Requirement.</font>";
					$valid=false;
				} 
			}
			
		}
		else if($this->br_estimate_qty == "")
		{
			$this->msg= '<font color="#FF0000">Kindly enter Estimated Quantity.</font>';
			$valid=false;
		}
		else if(!is_numeric($this->br_estimate_qty))
		{
			$this->msg= '<font color="#FF0000">Kindly enter valid Estimated Quantity.</font>';
			$valid=false;
		}
		else if($this->br_estimate_qty_unit == "")
		{
			$this->msg= '<font color="#FF0000">Kindly enter Estimated Quantity Measurement Unit.</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function add()
	{	
		$imgFile="";
		
		$sql_tbi="select * from temp_buyrequirement_image where tbi_usr_id='".$this->br_u_id."'";
		$res_tbi=mysqli_query($con, $sql_tbi);
		if(mysqli_num_rows($res_tbi)>0)
		{
			$row_tbi=mysqli_fetch_object($res_tbi);
			$imgFile=$row_tbi->tbi_image;
			mysqli_query($con, "delete from temp_buyrequirement_image where tbi_usr_id='".$this->br_u_id."'");
		}
	
		$sql="insert into buy_requirement
			set
				br_pc_id='".$this->br_pc_id."',
				br_u_id='".$this->br_u_id."',
				br_pd_name ='".$this->br_pd_name."',
				br_requirement ='".$this->br_requirement."',
				br_estimate_qty ='".$this->br_estimate_qty."',
				br_estimate_qty_unit ='".$this->br_estimate_qty_unit."',
				br_preferred_supplier_location='".$this->br_preferred_supplier_location."',
				br_pic='".$imgFile."',
				br_posting_date =now(),
				br_updated_date=now()";
			
		mysqli_query($con, $sql);
		
		$br_id=mysql_insert_id();
		$_SESSION['new_br_id']=$br_id;
				
		$brf_br_id=mysql_insert_id();

		unset($_SESSION['main_cat']);
		unset($_SESSION['pc_id']);
		unset($_SESSION['br_pc_id']);
		unset($_SESSION['br_pd_name']);
		unset($_SESSION['br_requirement']);
		unset($_SESSION['br_estimate_qty']);
		unset($_SESSION['br_estimate_qty_unit']);
		unset($_SESSION['br_preferred_supplier_location']);

		
		$this->msg='<font color="#009900">Buy Request posted successfully.</font>';

	}	
}
if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}
if(isset($_POST['submitBuyReqButt']))
{ 	
	
	$adn=new addProduct(addslashes(trim($_POST['main_cat'])),addslashes(trim($_POST['pc_id'])), addslashes(trim($_POST['br_pc_id'])),  addslashes(trim($_POST['br_u_id'])),addslashes(trim($_POST['br_pd_name'])),addslashes(trim($_POST['br_requirement'])),addslashes(trim($_POST['br_estimate_qty'])), addslashes(trim($_POST['br_estimate_qty_unit'])), addslashes(trim($_POST['br_preferred_supplier_location'])));	

	if($adn->valid())
	{	
		$adn->add();
		header("Location:post-buy-req-info.php");
	}
	else
	{
		$_SESSION['msg']=$adn->msg;
		header("Location:post-buy-req.php");
	}
}
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

<link href="css/eto-post-buy.css" type="text/css" rel="STYLESHEET">
</head>
<body>

<div class="q_hm1">
<?php include "includes/header_new.php"; ?>


<p class="cb"></p>

<script type="text/javascript">
function showCategory()
{
	var pc_id=document.getElementById('main_cat').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	}); 
}
function showSubcat(id)
{
	 $.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#br_pc_id').html(data);	}); 
}
$(document).ready(function() {
//    $('input[type="text"]').addClass("idleField");
    $('input[type="text"]').focus(function() {
        $(this).addClass("blfs");
        /*if (this.value == this.defaultValue){
            this.value = '';
        }
        if(this.value != this.defaultValue){
            this.select();
        }*/
    });
	$('#br_requirement').focus(function() {
        $(this).addClass("blfs");
    });
	$('select').focus(function() {
        $(this).addClass("blfs");
    });
    $('input[type="text"]').blur(function() {
        $(this).removeClass("blfs");
    });
	$('#br_requirement').blur(function() {
        $(this).removeClass("blfs");
		var length = $(this).val().length;
		if(length<50)
		{
			$('#err_desc').css('display','block');
		}
    });
	$('select').blur(function(){
        $(this).removeClass("blfs");
		
    });
	
	$(document).on('keyup', '#br_requirement', function(e){
		var msgSpan = $(this).parents('li').find('#Charcount');
		var length = $(this).val().length;
		var msg =4000 - length;
		msgSpan.empty().html(msg);
    });
	showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
	
});
function validRequest()
{
	var main_cat=document.getElementById('main_cat');
	var pc_id=document.getElementById('pc_id');
	var br_pc_id=document.getElementById('br_pc_id');
	
	var br_pd_name=document.getElementById('br_pd_name');
    var br_requirement=document.getElementById('br_requirement');
	var br_estimate_qty=document.getElementById('br_estimate_qty');
	var br_estimate_qty_unit=document.getElementById('br_estimate_qty_unit');

	var message="";
    var valid=true;

 	if(main_cat.value=='')
	{
		message="Kindly select Main Category.";
		main_cat.focus();
		valid=false;
	}	
 	else if(pc_id.value=='')
	{
		message="Kindly select Category.";
		pc_id.focus();
		valid=false;
	}
	else if(br_pc_id.value=='')
	{
		message="Kindly select Sub-Category.";
		br_pc_id.focus();
		valid=false;
	}
   	else if(br_pd_name.value=='')
	{
		message="Kindly enter Products / Services you are looking for.";
		br_pd_name.focus();
		valid=false;
	}
	else if(!isNaN(br_pd_name.value))
	{
		message="Kindly enter valid Products / Services you are looking for.";
		br_pd_name.focus();
		valid=false;
	}
	else if(br_requirement.value == "" || br_requirement.value == null)
	{
		message="Kindly describe your Buying Requirements in detail.";
		br_requirement.focus();
		valid=false;
	}
	else if(br_requirement.value.length<50)
	{
		message="Your Buy Requirement description should not be less than 50 characters.";
		br_requirement.focus();
		valid=false;
	}
	else if(br_estimate_qty.value=='')
	{
		message="Kindly enter Estimated Quantity.";
		br_estimate_qty.focus();
		valid=false;
	}
	else if(isNaN(br_estimate_qty.value))
	{
		message="Kindly enter valid Estimated Quantity.";
		br_estimate_qty.value='';
		br_estimate_qty.focus();
		valid=false;
	}
	else if(br_estimate_qty_unit.value=='')
	{
		message="Kindly select Estimated Quantity Unit.";
		br_estimate_qty_unit.focus();
		valid=false;
	}
	if(!valid)
	{
		alert(message);
		/*document.getElementById('error_msg').style.display="block";
		document.getElementById('error_msg').style.color = "red";
		document.getElementById('error_msg').innerHTML = message;*/
	}
	return valid;
}
function usePhoto(id)
{
	var tbl='temp_buyrequirement_image';
	var usr=document.getElementById('br_u_id').value;
	$.post("ajax-file/addNewImgFrmGallery.php", {id:id,usr:usr,tbl:tbl}, function(data){

		$('#cboxClose').click();

		$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="100" width="100"/>');
		
		setTimeout(function (){

		showTempPhoto(usr);

         }, 500);
	});
}
function showTempPhoto(usr)
{
	$.get("ajax-file/showTempBuyRequirementImage.php", {usr:usr},	function(data){
		$("#img_disp").html('');												 
		$("#img_disp").html('<img src="'+data+'" alt="" height="100" width="125"/>');
	});
}
</script>

<div id="tpnav" style="display:block" class="mt20">
  
  
  <?php include 'includes/header_menu.php';?>
  </div>
<div class="efpr" id="blempform" style="display:none"><nobr>You do not have privilege to access this section</nobr></div>
<div id="blform">
<div style="margin:0px !important" class="hd fs5 c3 fw" id="fmHd">Tell us your Buy Requirement <div class="eto-bg bp1 fmHd_a"></div></div>
	<form name="postForm" method="post" action="" onsubmit="return validRequest();">
        <input type="hidden" id="br_u_id" name="br_u_id" value="<?php echo $_SESSION['uid_indm']; ?>" />

		<div id="error_msg"  style="display:<?php if(isset($msg) && $msg!=''){?>block<?php }else{ ?>none<?php } ?>;position:relative;padding: 10px 15px;; margin: 5px; text-align: left;font-family: arial; font-size: 12px;" class="mt20"><?php echo $msg; ?></div>
		<div class="frm fl">
			<div id="req">
				<ul>
                <li>
                <p class="label"><label for="buytitle" style="width:170px;">Main Category</label></p> 
						<p class="wdh">
                        <select id="main_cat" name="main_cat" class="ui-placeholder-input" onchange="showCategory()">
                        <option value="">--Select Main-Category--</option>
                       	<?php
							$sql_mpc="select * from product_category where pc_parent_id='0' and pc_status='1'";
							$res_mpc=mysqli_query($con, $sql_mpc);
							while($row_mpc=mysqli_fetch_object($res_mpc)){
						?>
                        	<option value="<?php echo $row_mpc->pc_id; ?>" <?php if($row_mpc->pc_id==$main_cat){ ?> selected="selected"<?php } ?> ><?php echo $row_mpc->pc_name; ?></option>
                        <?php	}	?>
                        </select>
						</p>
                </li>
	                <li>
                    	<p class="label"><label for="buytitle" style="width:170px;">Category</label></p> 
						<p class="wdh">
                        <select id="pc_id" name="pc_id" class="ui-placeholder-input" onchange="showSubcat(this.value)">
                        <option value="">--Select Category--</option>
                       	<?php
							$sql_pc="select * from product_category where pc_parent_id='".$main_cat."' and pc_parent_id!='0' and pc_status='1'";
							$res_pc=mysqli_query($con, $sql_pc);
							while($row_pc=mysqli_fetch_object($res_pc)){
						?>
                        	<option value="<?php echo $row_pc->pc_id; ?>"><?php echo $row_pc->pc_name; ?></option>
                        <?php	}	?>
                        </select>
                        <select name="br_pc_id" id="br_pc_id" class="ui-placeholder-input">
                        <option value="">--Select Sub-Category--</option>
                        <?php
							$sql_spc="select * from product_category where pc_parent_id='".$pc_id."' and pc_parent_id!='0' and pc_status='1'";
							$res_spc=mysqli_query($con, $sql_spc);
							while($row_spc=mysqli_fetch_object($res_spc)){
						?>
                        	<option value="<?php echo $row_spc->pc_id; ?>"><?php echo $row_spc->pc_name; ?></option>
                        <?php	}	?>
                        </select>
						</p>
					</li>
                    
					<li>
                    	<p class="label"><label for="buytitle" style="width:170px;">Product / Service</label></p> 
						<p class="wdh"><input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" class="ui-placeholder-input" placeholder="Enter product/service you want to buy..." name="br_pd_name" id="br_pd_name" maxlength="100" style="width:465px;" type="text" value="<?php echo $br_pd_name; ?>"/><br>
<span id="err_title" style="display:none" class="em_tips clb">Please enter the product / service you want to buy.</span></p>
<span id="ttip_title" style="display:none" class="alrt"><span class="arw1"></span>Please enter the correct &amp; accurate name of Product / Service you want to buy.</span>
					</li>
					<li style="margin-bottom:3px !important">
						<p class="label fl"><label for="br_requirement" style="width:170px;">Requirement in detail</label></p>
						<p class="fl wdh"><textarea class="ttp2" name="br_requirement" id="br_requirement" maxlength="4000" style="width:465px;height:100px;resize:none"><?php echo $br_requirement; ?></textarea>
<span class="fr cb c5"><font id="Charcount" class="c4">4000</font> Characters Remaining </span>
<span id="err_desc" style="display:none" class="em_tips fl mb10"> Minimum 50 Characters.</span>
			</p>
 <span id="ttip_desc" style="display:none; width:200px" class="alrt"><span class="arw1"></span>
Please enter details like:<br> - Product Size / Dimension<br>- Grade / Quality Standard<br>- Material
- Product Packaging<br>- Application of Product<br>- Any special Requirement</span>
					</li>
					<li>
						<p class="label"><label for="qty">Estimated Quantity</label></p> 
					    <p class="wdh"><input name="br_estimate_qty" id="br_estimate_qty" maxlength="200" style="width:173px;" type="text" value="<?php echo $br_estimate_qty; ?>"/>

	<select style="width:120px;margin-left:8px" name="br_estimate_qty_unit" id="br_estimate_qty_unit">
	<?php
		$sql_mu="select * from measurement_unit where mu_status='1'";
		$res_mu=mysqli_query($con, $sql_mu);
	?>
    	<option selected="selected" value="">--Select Unit--</option>
    <?php	
		while($row_mu=mysqli_fetch_object($res_mu)){	?>
		<option style="color: rgb(0, 0, 0);" value="<?php echo $row_mu->mu_id;?>" <?php if($br_estimate_qty_unit==$row_mu->mu_id){ ?> selected="selected"<?php } ?>><?php echo $row_mu->mu_name;	?></option>
    <?php	}	?>      
	</select>
        <span style="display:none" id="QTY_LIST"><input style="width:117px;float:right;margin-right:20px" name="QTY_LIST_VAL_OTHER" id="QTY_LIST_VAL_OTHER" maxlength="50" class="" type="text"></span></p>
<span id="ttip_qty" class="alrt lft" style="display:none"><span class="arw1"></span>Enter Estimated Order Quantity and select Units from the list.<br>
e.g. 50 Ton or 1000 Pieces </span>
<div class="cb mb10"></div>
					</li>
                    <li>
                    	<p class="label"><label for="buytitle" style="width:170px;">Location Preferences</label></p> 
						<p class="wdh">
                        <div style="vertical-align:bottom">
                        <input type="radio" id="br_preferred_supplier_location_1" name="br_preferred_supplier_location" value="abroad"/><label style="top:0px;">Abroad Only</label>
                        &nbsp;&nbsp;
                        <input type="radio" id="br_preferred_supplier_location_2" name="br_preferred_supplier_location" value="any" checked="checked"/><label style="top:0px;">Abroad + Domestic</label>
                        &nbsp;&nbsp;
                        <input type="radio" id="br_preferred_supplier_location_3" name="br_preferred_supplier_location" value="domestic"/><label style="top:0px;">Domestic Only</label>
                        &nbsp;&nbsp;
                        <input type="radio" id="br_preferred_supplier_location_4" name="br_preferred_supplier_location" value="my_city"/><label style="top:0px;">My City Only</label>
                        </div>
                        <br>
</p>

					</li>
				</ul>
			</div>
            
			<div style="display:block" id="contact_dtl">
				<ul>

<li><p class="label"><label for="country_name">Image</label></p>
<p class="wdh">
<table>
<tr><td>
	<div style="padding-left:5px;padding-top:0px;" id="img_disp">
		<img src="upload/buy_requirement/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image.png";	} ?>" id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125">
	</div>
    </td>
    <td>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
				<script type="text/javascript">
					jQuery(function(){
						jQuery('#file_upload').uploadifive({
							'auto'     : true,
							'formData' : {'usr' : '<?php echo $_SESSION['uid_indm']; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'ajax-file/addTempBuyReqImg.php',
							'onAddQueueItem' : function(file) {
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
								$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
							},
							'onUploadComplete' : function(file,data) {
								showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
							}
						});
					});
				</script>
    <div id="drop" style="padding-left:10px;float:right">
         <input type="file" id="file_upload" name="file_upload" style="border:none;"/>
    </div>
	<div id="queue"></div>
    </td>
    <td>
    <link rel="stylesheet" href="css/colorbox.css" />
										<script src="js/jquery.colorbox.js"></script>
                                       <script>
											$(document).ready(function(){
											//Examples of how to assign the ColorBox event to elements
											$(".ajax").colorbox({width:"72%"});
											$(".inline").colorbox({inline:true, width:"50%"});
											//Example of preserving a JavaScript event for inline calls.
											$("#click").click(function(){ 
												$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
												return false;
											});
											});
									</script>
            <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">Select from Image Gallery</a>
    </td>
    </tr>
</table>



</p>
</li>



</ul>
</div>
<p class="cb"></p>
<div id="submitdiv"><input name="frmsubmitbutton" value="login" type="hidden"><input name="submitBuyReqButt" id="login" value="" type="SUBMIT"></div>
</div>
<div class="bnf fl mt10">
<div class="fl shd eto-bg">
<p class="sh1 eto-bg"></p>
</div>
<div class="fl bnft fs1 mt10 c2">
<h4 class="fs4 c3 fw">Benefits for Buyers</h4>
<p class="eto-bg hdbg mb10"></p>
<ul>
	<li class="eto-bg bp2 ff"><strong class="c5 fs3 fwn">Save Time</strong><br>
	in search of Suppliers
    </li>
    <li class="eto-bg bp3 ff"><strong class="c5 fs3 fwn">Responses</strong><br>
	directly from Verified suppliers
    </li>
	<li class="eto-bg bp4 ff"><strong class="c5 fs3 fwn">Compare &amp; Evaluate</strong><br>
	the quotes
    </li>
</ul>
</div>


<div class="fl bnft fs1 c2" style="position:relative">
<h4 class="fs4 c2 fwn">Over <span class="fs5 fw c3">5 Million</span> Satisfied
Buyers Worldwide.</h4>
<p class="eto-bg hdbg mb10"></p>
<div id="slideshow">



<?php
$sql_testi="select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
$res_testi=mysqli_query($con, $sql_testi);
if(mysqli_num_rows($res_testi)>0){

$n=1;
while($row_testi=mysqli_fetch_object($res_testi)){
	$len=strlen($row_testi->testi_details);
?>
<div class="xx1 lh" style="display: block;">
<div class="fl" style="padding: 0px 5px"><img style='border-radius: 30px;-webkit-box-shadow: 2px 24px 14px -15px rgba(50, 50, 50, 0.9);-moz-box-shadow: 2px 24px 14px -15px rgba(50, 50, 50, 0.9);box-shadow: 2px 24px 14px -15px rgba(50, 50, 50, 0.9);' src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>" width="55" height="60"/></div>
<strong class="fs2"><?php echo $row_testi->testi_name; ?></strong><br>
<?php echo get_country_name($row_testi->testi_cn_id); ?>
<p class="mt10 fs6"><em><?php echo substr($row_testi->testi_details,0,120); ?></em></p>
<?php if($len>120){	?><p class="c3 pa1 rm tr"><a class=" fw c3" href="testimonial.php" target="_blank"> Read More...</a></p><?php } ?>
</div>
<?php	}	
 } ?>


</div>
</div>


</div>
<div style="clear:both;"></div>
</form>
<p class="cb"></p>
</div>

 <!-- MY TD ENDS -->
</div>
<div class="cb"></div>
<!-- Footer Start Here::-->
<?php include 'includes/footer.php'; ?>