<?php
include 'common.php';

$_SESSION['last_page'] = "post-buy-req.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	$_SESSION['postKeyword'] = $_POST['keywords'];
	$_SESSION['textAreaVal'] = $_POST['textAreaField'];
	header("Location:sign-in.php");
}
if(isset($_POST['keywords'])){
	$postKeyword = $_POST['keywords'];
	$textAreaVal = $_POST['textAreaField'];
}else if($_SESSION['postKeyword'] != ''){
	$postKeyword = $_SESSION['postKeyword'];
	$textAreaVal = $_SESSION['textAreaVal'];
	//unset($_SESSION['postKeyword']);
	//unset($_SESSION['textAreaVal']);
}
if(isset($_POST['recommendation'])){
	if($_POST['recommendation'] == 1) {
		$sql_key="select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id where pd_title = '".$postKeyword."' and pc_status='1'";
		$query_key = mysql_query($sql_key);
		$row_key=mysql_fetch_object($query_key);
		$key_cat_id = $row_key->pc_id;
		$sql_ins="insert into selloffer_alert_category
		set
			sac_usr_id='".$_SESSION['uid_indm']."',
			sac_pc_id='".$key_cat_id."',
			sac_updated_date=now()";
		mysqli_query($con, $sql_ins);
	}
}
//echo $postKeyword;
//echo '<pre>';print_r($_SESSION);exit;
$uid = $_SESSION['uid_indm'];


?>
<!--$(document).ready(function() {

function showCategory()
{
	var pc_id=document.getElementById('main_cat').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	});
}
function showSubcat(id)
{
	 $.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#br_pc_id').html(data);	});
}
});-->
<?php
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
		$resrpl = mysql_query($sqlrpl);
		while($rowrpl = mysql_fetch_object($resrpl))
		{
			$letters[] = strtoupper($rowrpl->bd_word);
		}
		$br_name=strtoupper($this->br_pd_name);
		$requirement=strtoupper($this->br_requirement);


		$valid=true;
		if(!$postKeyword)
		{
			if($this->main_cat =="")
			{
				$this->msg='<font color="#FF0000">Kindly select Main Category.</font>';
				$valid=false;
			}
		}
		if(!$postKeyword)
		{
			if($this->pc_id =="")
			{
				$this->msg='<font color="#FF0000">Kindly select Category.</font>';
				$valid=false;
			}
		}
		if($this->br_pc_id =="")
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
		$res_tbi=mysql_query($sql_tbi);
		if(mysql_num_rows($res_tbi)>0)
		{
			$row_tbi=mysql_fetch_object($res_tbi);
			$imgFile=$row_tbi->tbi_image;
			mysql_query("delete from temp_buyrequirement_image where tbi_usr_id='".$this->br_u_id."'");
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
				br_status='1',
				br_posting_date =now(),
				br_updated_date=now()";

	 mysql_query($sql); 
	 
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

$typeofselection = $_POST['typeofselection'];
 $keywordsFilter = $_POST['keywordsFilter1'];
     $valid = false ;
  if($typeofselection){
     $valid = true ;
 if($keywordsFilter=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Keyword.';
	$valid=false;
}


 $searchedproducts = $_SESSION['searchedproducts'];

if(!$searchedproducts && !array_key_exists($keywordsFilter,$searchedproducts))  {
	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
}
     $keywordsFilter =  explode(">>",$keywordsFilter)   ;

$keywordsFilter1 = end($keywordsFilter);
$tnd_pc_id = $searchedproducts[$keywordsFilter1];
$_POST['br_pc_id'] = $tnd_pc_id;
$_POST['pc_id'] = $searchedproducts[$keywordsFilter[1]];
$_POST['main_cat'] = $searchedproducts[$keywordsFilter[0]];
if(!$tnd_pc_id){
 	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
}

}


	$adn=new addProduct(addslashes(trim($_POST['main_cat'])),addslashes(trim($_POST['pc_id'])), addslashes(trim($_POST['br_pc_id'])),  addslashes(trim($_POST['br_u_id'])),addslashes(trim($_POST['br_pd_name'])),addslashes(trim($_POST['br_requirement'])),addslashes(trim($_POST['br_estimate_qty'])), addslashes(trim($_POST['br_estimate_qty_unit'])), addslashes(trim($_POST['br_preferred_supplier_location'])));


	$key_cat_id = $_POST['br_pc_id'];
	$uid = $_SESSION['uid_indm'];

	$query = "SELECT * FROM selloffer_alert_category WHERE sac_pc_id='$key_cat_id' AND sac_usr_id='$uid'";	
	$r=mysql_query($query);	
	if(mysql_num_rows($r) == 0){		
		$SQL_BUY_ALERT="INSERT  INTO selloffer_alert_category SET 
										  sac_usr_id=".$uid.",
										  sac_pc_id=".$key_cat_id.",
										  sac_updated_date=now()";
		$r=mysql_query($SQL_BUY_ALERT) or die('Error in query while saving');
	}

	if($adn->valid() || $valid)
	{
		$adn->add();
		$sql_exist="select * from selloffer_alert_category where sac_usr_id='".$_SESSION['uid_indm']."' AND sac_pc_id='".$_POST['br_pc_id']."'";
		$res12=mysqli_query($con, $sql_exist);
		if($res12->num_rows==0){
			$sql="insert into selloffer_alert_category
			set
				sac_usr_id='".$_SESSION['uid_indm']."',
				sac_pc_id='".$_POST['br_pc_id']."',
				sac_updated_date=now()";
			mysqli_query($con, $sql);
		}
		
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
<body class="search-show-box post-buy-req">

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
setTimeout(function(){
$(".mybs").attr("selected","true");
$( ".mybs" ).change();
	}, 1000);
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
	 	var typeofselection=document.getElementById('typeofselection');
	var keywordsFilter1=document.getElementById('keywordsFilter1');
	var br_pd_name=document.getElementById('br_pd_name');
    var br_requirement=document.getElementById('br_requirement');
	var br_estimate_qty=document.getElementById('br_estimate_qty');
	var br_estimate_qty_unit=document.getElementById('br_estimate_qty_unit');

	var message="";
    var valid=true;
     typeofselectionvalue = typeofselection.value *1;
	if(typeofselectionvalue==0){
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
    }
     else if(typeofselectionvalue && keywordsFilter1.value=='')    {

	{
		message="Kindly enter valid Search for category";
		keywordsFilter1.focus();
		valid=false;
	}

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
	function showTempPhoto(usr)
{
	$.get("ajax-file/showTempBuyRequirementImage.php", {usr:usr},	function(data){
		$("#img_disp").html('');
		$("#img_disp").html('<img src="'+data+'" alt="" height="100" width="125"/>');
	});
}
var imageBasket = [];
function usePhotoToUpload(id){

 //imageBasket.push(id);
 if(jQuery.inArray(id,imageBasket) != -1){
   
  imageBasket= $.grep(imageBasket, function(value) {
  return value != id;
    });
  }else{
    imageBasket.push(id);
    }
   //alert(imageBasket);
 }
 
function usePhoto(id)
{
	var tbl='temp_buyrequirement_image';
	var usr=document.getElementById('br_u_id').value;
	if(imageBasket.length > 0){  
		id = imageBasket.pop();
	}
	$.post("ajax-file/addNewImgFrmGallery.php", {id:id,usr:usr,tbl:tbl}, function(data){

		$('#cboxClose').click();

		$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="100" width="100"/>');

		setTimeout(function (){

		showTempPhoto(usr);

         }, 500);
	});
}

</script>
<?php
if($postKeyword)
{
	$keywords = $postKeyword;
	//echo $keywords;die;
	$sql_key="select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id where pd_title = '".$keywords."' and pc_status='1'";
	//echo $sql_key;die;
	$query_key = mysql_query($sql_key);
	$row_key=mysql_fetch_object($query_key);
	$key_cat_id = $row_key->pc_id;
	$key_cat_name = $row_key->pc_name;
	 $br_pd_name = $postKeyword;
	 $br_requirement = $_POST['specs'];
	 ?>
     <?php
	/*
	//echo $postKeyword;die;
	$keywords = $postKeyword;
	$sql_pc1="select * from product_category_arabyos where pc_name like '".$keywords."' and pc_status='1'";
	//echo $sql_pc1;die;
	$query_pc1 = mysql_query($sql_pc1);
	$non_parent1 = false;
	while($row1 = mysql_fetch_array($query_pc1))
	{
		if($row1['pc_parent_id']!='0')
		{
		$non_parent1 = true;
		$pid1 = $row1['pc_parent_id'];
		$id1 = $row1['pc_id'];
		//echo $id1;die;
		}
	}
	if($non_parent1==true)
	{
		$sql_pc2="select * from product_category_arabyos where pc_id='".$pid1."' and pc_status='1'";
		//echo $sql_pc2;die;
		$query_pc2 = mysql_query($sql_pc2);
		$non_parent2 = false;
		while($row2 = mysql_fetch_array($query_pc2))
		{
			if($row2['pc_parent_id']!='0')
			{
			$non_parent2 = true;
			$pid2 = $row2['pc_parent_id'];
			$id2 = $row2['pc_id'];
			//echo $id2;die;
			}
		}
		if($non_parent2==true)
		{
				$sql_pc4="select * from product_category_arabyos where pc_id='".$pid2."' and pc_status='1'";
				$query_pc4 = mysql_query($sql_pc4);
				$row4 = mysql_fetch_array($query_pc4);
				$main_cat =  $row4['pc_id'];
				//echo $main_cat;die;
				?>
				<script type="text/javascript">
				$(document).ready(function() {
											var pc_id=<?php echo $row4['pc_id']; ?>;
										$.post("ajax-file/showSubcat.php?id=<?php echo $id2;?>",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	});
											 $.post("ajax-file/showSubcat.php?id=<?php echo $id1; ?>",{id:<?php echo $id2;?>},	function(data){	$('#br_pc_id').html(data);	});

										   });
				</script>
				<?php
		}
		else
		{
			
			$row5 = mysql_fetch_array($query_pc2);
			$main_cat =  $row5['pc_id'];
			
			?>
				<script type="text/javascript">
				$(document).ready(function() {
											var pc_id=<?php echo $row5['pc_id']; ?>;
										$.post("ajax-file/showSubcat.php?id=<?php echo $id1;?>",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	});
											 $.post("ajax-file/showSubcat.php",{id:<?php echo $id1;?>},	function(data){	$('#br_pc_id').html(data);	});

										   });
				</script>
				<?php
		}
		
	}
	else
	{
		$sql_pc3="select * from product_category_arabyos where  pc_name='".$keywords."' and pc_parent_id='0' and pc_status='1'";
		$query_pc3 = mysql_query($sql_pc3);
		$row3 = mysql_fetch_array($query_pc3);
		$main_cat =  $row3['pc_id'];
		?>
        <script type="text/javascript">
		$(document).ready(function() {
								   	var pc_id=<?php echo $row3['pc_id']; ?>;
								$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	});
								   });
		</script>
        <?php
	}
*/}
?>

<div id="tpnav" style="display:block;margin-top: 55px;" class="mt20">


  <?php include 'includes/header_menu.php';?>
  </div>
<div class="efpr" id="blempform" style="display:none"><nobr>You do not have privilege to access this section</nobr></div>
<div id="blform">
<div style="margin:0px !important" class="hd fs5 c3 fw" id="fmHd">Tell us your Buy Requirement, Get Multiple Quotes ..
 <div class="eto-bg bp1 fmHd_a"></div></div>
	<form name="postForm" method="post" action="" onsubmit="return validRequest();">
        <input type="hidden" id="br_u_id" name="br_u_id" value="<?php echo $_SESSION['uid_indm']; ?>" />


         <script type="text/javascript">
function searchcat()
{
	$("#scs").removeClass("tabclose").addClass("tabopen");
	$("#bcs").removeClass("tabopen").addClass("tabclose");
        $('#typeofselection').val(1);
	$(".bcc").css("display","none");
	$(".scc").removeAttr('style');
}
function beowswcat()
{
	$("#bcs").removeClass("tabclose").addClass("tabopen");
	$("#scs").removeClass("tabopen").addClass("tabclose");
    $('#typeofselection').val(0);
	$(".scc").css("display","none");
	$(".bcc").removeAttr('style');;
}

</script>
         <input type="hidden" value="0"  id="typeofselection" name="typeofselection" />
           <style>
           .tabopen{border-collapse:collapse;border:1px solid #6500CA;border-bottom:0px;color:#9D0000;font-family:arial;font-size:15px;font-weight:bold;text-align:center;padding-top:4px;padding-bottom:4px;background-color:#FAF4FF;}
.tabclose{border-collapse:collapse;border:1px solid #C2E6FF;background-color:#D2ECFF;color:#2161B8;font-family:arial;font-size:15px;font-weight:bold;text-align:center;padding-top:4px;padding-bottom:4px;cursor:pointer;}
.tabborder{border-collapse:collapse;border-bottom:1px solid #6500CA;}
.border_bottom{border-collapse:collapse;border:1px solid #6500CA;border-top:0px solid #6500CA;}
#blform input, select, textarea { height: 40px !important;vertical-align: middle;}
.label{line-height: 3;}
input[type='radio'] { transform: scale(2);}
.ass_sub_radio label{padding: 10px;font-size: 15px; }
#drop{background:#fff;cursor: pointer; padding: 10px;margin-left: 10px;margin-right: 10px;color: #207bc2;    }
           </style>


		<div id="error_msg"  style="display:<?php if(isset($msg) && $msg!=''){?>block<?php }else{ ?>none<?php } ?>;position:relative;padding: 10px 15px;; margin: 5px; text-align: left;font-family: arial; font-size: 12px;" class="mt20"><?php echo $msg; ?></div>
		<div class="frm fl" style="background:#fff; width: calc(100% - 355px);padding:10px;">
			<div id="req">


              <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"><tbody>
 <tr><!--<td valign="TOP" width="19"><img src="images/zero.gif" height="6" width="1"><br><img src="images/11.gif" height="15" width="19"></td>--><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
 <tr>
 <td class="tabclose" onclick="searchcat()" id="scs" width="152">Search Categories</td>
 <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
 <td class="tabopen" onclick="beowswcat()" id="bcs" width="155">Browse Categories</td>
 <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
 </tr>
 </tbody></table></td></tr></tbody></table>
				<ul style="margin-top: 12px;">

                 <li class="scc" style="display: none;">
                <p class="label"><label for="buytitle" style="width:100%;">Enter Keyword For category</label></p>
						<p class="wdh">
                          <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" name="keywordsFilter1" id="keywordsFilter1" style="float: left;width:100%;"class="add_post_buy_input" type="text" maxlength="60" size="33" >
						</p>
                </li>
                 <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>


<script type="text/javascript">
$(document).ready(function($113){
	lostFocus();
	$113('#keywordsFilter1').unbind().live('keyup',function() {
		var type11='Products';
		$113("#keywordsFilter1").autocomplete("autocomplete.php", {
			selectFirst: true,
			extraParams: {type:type11},
			width: 407
		})
		.result(function(event, data, formatted) {
 			$("input#keywordsFilter1").val(data);
		});
	});
});
</script>
<?php 
if(!$postKeyword)
{
	?>
                <li class="bcc">
                <p class="label"><label for="buytitle" style="width:170px;">Main Category</label></p>
						<p class="wdh">
                        <select id="main_cat" name="main_cat" class="ui-placeholder-input add_post_buy_input" onchange="showCategory()" style="width:100%">
                        <option value="">--Select Main-Category--</option>
                        <?php  $people=['JUNIOR','SENIOR','SPONSER']; if(in_array(strtok($icon['mst_name']," "), $people)){$selected_member='class="mybs"';}else{$selected_member='';} ?>
                       	<?php
							$sql_mpc="select * from product_category_arabyos where pc_parent_id='0' and pc_status='1'";
							$res_mpc=mysql_query($sql_mpc);
							
							while($row_mpc=mysql_fetch_object($res_mpc)){
								$selected = '';
								if(@$_GET['select'] == 'bs'){
									if(ucfirst($row_mpc->pc_name)=='Business Services'){
										$selected = 'selected="selected"';
									}
								}
						?>
               <option value="<?php echo $row_mpc->pc_id; ?>" <?php echo $selected; ?> <?php if(ucfirst($row_mpc->pc_name)=='Business Services'){echo $selected_member;} ?> ><?php echo $row_mpc->pc_name; ?></option>
                        <?php	}	?>
                        </select>
						</p>
                </li>
                <?php
}
?>
	                <li class="bcc">
                    	<p class="label"><label for="buytitle" style="width:170px;">Category</label></p>
						<p class="wdh">
                        <?php 
						if(!$postKeyword)
						{
						?>
                        <select id="pc_id" name="pc_id" class="ui-placeholder-input" onchange="showSubcat(this.value)">
                        <option value="">--Select Category--</option>
                       	<?php
							$sql_pc="select * from product_category_arabyos where pc_parent_id='".$main_cat."' and pc_parent_id!='0' and pc_status='1'";
							$res_pc=mysql_query($sql_pc);
							while($row_pc=mysql_fetch_object($res_pc)){
						?>
                        	<option value="<?php echo $row_pc->pc_id; ?>"><?php echo $row_pc->pc_name; ?></option>
                        <?php	}	?>
                        </select>
                        <select name="br_pc_id" id="br_pc_id" class="ui-placeholder-input" style="width:52%">
                        <option value="" >--Select Sub-Category--</option>
                        <?php
							$sql_spc="select * from product_category_arabyos where pc_parent_id='".$pc_id."' and pc_parent_id!='0' and pc_status='1'";
							$res_spc=mysql_query($sql_spc);
							while($row_spc=mysql_fetch_object($res_spc)){
						?>
                        	<option value="<?php echo $row_spc->pc_id; ?>"><?php echo $row_spc->pc_name; ?></option>
                        <?php	}	?>
                        </select>
                        <?php
						}
						else
						{
						?>
                             <input type="hidden" name="keywords" value="<?php echo $postKeyword; ?>">
                             <input type="hidden" name="specs" value="<?php echo $_POST['specs']; ?>">

                         <select name="br_pc_id" id="br_pc_id" class="ui-placeholder-input">
                        <option value="<?php echo $key_cat_id; ?>"><?php echo $key_cat_name; ?></option>
                        </select>
                        <?php
						}
						?>
						</p>
					</li>

					<li>
                    	<p class="label"><label for="buytitle" style="width:170px;">Product / Service</label></p>
						<p class="wdh"><input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" class="ui-placeholder-input" placeholder="Enter product/service you want to buy..." name="br_pd_name" id="br_pd_name" maxlength="100" style="width:100%;" type="text" value="<?php echo $br_pd_name; ?>"/><br>
<span id="err_title" style="display:none" class="em_tips clb">Please enter the product / service you want to buy.</span></p>
<span id="ttip_title" style="display:none" class="alrt"><span class="arw1"></span>Please enter the correct &amp; accurate name of Product / Service you want to buy.</span>
					</li>
					<li style="margin-bottom:3px !important">
						<p class="label fl"><label for="br_requirement" style="width:170px;">Requirement in detail</label></p>
						<p class="fl wdh"><textarea class="ttp2" name="br_requirement" id="br_requirement" maxlength="4000" style="width:100%;height:300px !important;resize:none"><?php echo $textAreaVal?$textAreaVal:$br_requirement; ?></textarea>
<span class="fr cb c5"><font id="Charcount" class="c4">4000</font> Characters Remaining </span>
<span id="err_desc" style="display:none" class="em_tips fl mb10"> Minimum 50 Characters.</span>
			</p>
 <span id="ttip_desc" style="display:none; width:200px" class="alrt"><span class="arw1"></span>
Please enter details like:<br> - Product Size / Dimension<br>- Grade / Quality Standard<br>- Material
- Product Packaging<br>- Application of Product<br>- Any special Requirement</span>
					</li>
					<li>
						<p class="label"><label for="qty">Estimated Quantity</label></p>
					    <p class="wdh"><input name="br_estimate_qty" id="br_estimate_qty" maxlength="200" style="width:25%;" type="text" value="<?php echo $br_estimate_qty; ?>"/>

	<select style="width:25%;margin-left:8px" name="br_estimate_qty_unit" id="br_estimate_qty_unit" >
	<?php
		$sql_mu="select * from measurement_unit_arabyos where mu_status='1'";
		$res_mu=mysql_query($sql_mu);
	?>
    	<option selected="selected" value="">--Select Unit--</option>
    <?php
		while($row_mu=mysql_fetch_object($res_mu)){	?>
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
                        <div style="vertical-align:bottom" class="ass_sub_radio">
                            <span class="ass-radio-loaction"><input type="radio" id="br_preferred_supplier_location_1" name="br_preferred_supplier_location" value="abroad"/><label style="top:0px;">Abroad Only</label></span>
                        &nbsp;&nbsp;
                        <span class="ass-radio-loaction"><input type="radio" id="br_preferred_supplier_location_2" name="br_preferred_supplier_location" value="any" checked="checked"/><label style="top:0px;">Abroad + Domestic</label></span>
                        &nbsp;&nbsp;
                        <span class="ass-radio-loaction"><input type="radio" id="br_preferred_supplier_location_3" name="br_preferred_supplier_location" value="domestic"/><label style="top:0px;">Domestic Only</label></span>
                        &nbsp;&nbsp;
                        <span class="ass-radio-loaction"><input type="radio" id="br_preferred_supplier_location_4" name="br_preferred_supplier_location" value="my_city"/><label style="top:0px;">My City Only</label></span>
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
		<img src="upload/buy_requirement/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image-eng.png";	} ?>" id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125" >
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
											//$(".ajax").colorbox({width:"72%"});
											$('.ajax').on('click', function() {
											  $.colorbox({href:$(this).attr('href'), open:true});
											  return false;
											});
											$(".inline").colorbox({inline:true, width:"50%"});
											//Example of preserving a JavaScript event for inline calls.
											$("#click").click(function(){
												$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
												return false;
											});
											});
									</script>
            <a class="ajax add_color_page" href="popup-imagegallery.php" style=;font-weight:bold;"text-decoration:none;color:#0000ff;">Select from Image Gallery</a>
    </td>
    </tr>
</table>



</p>
</li>



</ul>
</div>
<p class="cb"></p>
<div id="submitdiv"><input name="frmsubmitbutton" value="login" type="hidden"><input name="submitBuyReqButt" id="login" value="" type="SUBMIT" style="height:63px !important;"></div>
<div class="fl shd eto-bg">
<p class="sh1 eto-bg"></p>
</div>
</div>
<div class="bnf fl mt10">

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
<!--<h4 class="fs4 c2 fwn">Over <span class="fs5 fw c3">5 Million</span> Satisfied
Buyers Worldwide.</h4>-->
<p class="eto-bg hdbg mb10"></p>
<div id="slideshow">



<?php
$sql_testi="select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
$res_testi=mysql_query($sql_testi);
if(mysql_num_rows($res_testi)>0){

$n=1;
while($row_testi=mysql_fetch_object($res_testi)){
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
<?php include 'includes/footer.php'; 
//unset($_SESSION['postKeyword']);
//unset($_SESSION['textAreaVal']);
?>