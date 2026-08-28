<?php
include 'common.php';

$_SESSION['last_page']="product-edit.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];
$token=substr($_GET['token'],4);

if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}

mysql_query("update products set pd_pushed_top='0' where md5(pd_id) ='".$token."'");

class Editcat{
	public $cid;
	public $mcat_id;
	public $cat_id;
	public $pd_subcat_id;
	public $pd_title;
	public $pd_code;
	public $pd_min_order_qty;
	public $pd_unit;
	public $pd_fob_price;
	public $pd_fob_price2;
	public $pd_currency;
	public $pd_preferred_buyer_location;
	public $pd_desc;
	
	public $msg;
	
	function __construct($cid)
	{
		$this->cid=$cid;
	}
	function cmsdetailsObj(){
		$sql="select * from products,product_category where pd_subcat_id=pc_id and md5(pd_id)='".substr($this->cid,4)."'";
		$res=mysql_query($sql);                                    
		return mysql_fetch_object($res);
	}
	function valid()
	{
		$sqlrpl = "select bd_word from bad_word";
		$resrpl = mysql_query($sqlrpl);
		while($rowrpl = mysql_fetch_object($resrpl))
		{		
			$letters[] = strtoupper($rowrpl->bd_word);
		}
		$title    = strtoupper($this->pd_title);
		$desc    = strtoupper($this->pd_desc);
		
		$valid=true;
		
		if($this->mcat_id =='')
		{
			$this->msg='<font color="#CC0000"></font>';
			$valid=false;
		}
		else if($this->cat_id == "")
		{
			$this->msg='<font color="#CC0000"></font>';
			$valid=false;
		}
		else if($this->pd_subcat_id == "")
		{
			$this->msg='<font color="#CC0000"></font>';
			$valid=false;
		}
		else if($this->pd_title == "")
		{
			$this->msg='<font color="#CC0000"></font>';
			$valid=false;
		}
		else if($this->pd_title != "")
		{		
			foreach($letters as $val){
				$pos = strpos($title, $val);
				if ($pos !== false)
				{
					$this->msg="<font color='#CC0000'>You can't post words like '".$val."' in Product Name.</font>";
					$valid=false;
				} 
			}
		}
		else if($pd_min_order_qty == "")
		{
			$this->msg='<font color="#CC0000">Please enter Minimum Order Quantity.</font>';
			$valid=false;
		}
		else if($pd_unit == "")
		{
			$this->msg='<font color="#CC0000">Please choose Measurement Unit for Minimum Order Quantity.</font>';
			$valid=false;
		}
		/*else if($pd_fob_price == "")
		{
			$this->msg='<font color="#CC0000">Please enter FOB / Wholesale Price.</font>';
			$valid=false;
		}
		else if($pd_fob_price2 == "")
		{
			$this->msg='<font color="#CC0000">Please enter FOB / Wholesale Price.</font>';
			$valid=false;
		}*/
		else if($pd_currency == "")
		{
			$this->msg='<font color="#CC0000">Please choose Currency.</font>';
			$valid=false;
		}
		
		else if(strlen($this->pd_desc)>4000)
		{
			$this->msg='<font color="#CC0000">Please check that Product Description cannot have more than 4000 characters.</font>';
			$valid=false;
		}
		else if($this->pd_desc != "")
		{		
			foreach($letters as $val){
				$pos = strpos($desc, $val);
				if ($pos !== false) {
					$this->msg="<font color='#CC0000'>You can't post words like '".$val."' in Product Description.</font>";
					$valid=false;
				} 
			}
			
		}
		return $valid;   
	}
	
	function update()
	{
		$sql="update products
		set
			pd_subcat_id='".$this->pd_subcat_id."',
			pd_title='".$this->pd_title."',
			pd_code='".$this->pd_code."',
			pd_min_order_qty='".$this->pd_min_order_qty."',
			pd_unit='".$this->pd_unit."',
			pd_fob_price='".$this->pd_fob_price."',
			pd_fob_price2='".$this->pd_fob_price2."',
			pd_currency='".$this->pd_currency."',
			pd_preferred_buyer_location='".$this->pd_preferred_buyer_location."',
			pd_desc='".$this->pd_desc."',
			pd_date=now()
		where
			md5(pd_id) ='".substr($this->cid,4)."'";
	
		mysql_query($sql) or die(mysql_error());
		$tnd=substr($this->cid,4);	
	   
		$this->msg= '<font color="green">!  تم تحديث المنتـج وتم إرسال إشعار الى كل المهتمين به بنجــاح </font>';	
  }	
}


$ecms=new Editcat($_GET['token']);
$pdrow=$ecms->cmsdetailsObj();
if(isset($_POST['btnUpdate']))
{

	$ecms->mcat_id=trim(addslashes($_POST['mcat_id']));
 	$ecms->cat_id=trim(addslashes($_POST['cat_id']));
	$ecms->pd_subcat_id=trim(addslashes($_POST['pd_subcat_id']));
	$ecms->pd_title=trim(addslashes($_POST['pd_title']));
	$ecms->pd_code=trim(addslashes($_POST['pd_code']));
	$ecms->pd_desc=addslashes(trim($_POST['pd_desc']));
	
	$ecms->pd_min_order_qty=addslashes(trim($_POST['pd_min_order_qty']));
	$ecms->pd_unit=addslashes(trim($_POST['pd_unit']));
	$ecms->pd_fob_price=addslashes(trim($_POST['pd_fob_price']));
	$ecms->pd_fob_price2=addslashes(trim($_POST['pd_fob_price2']));
	$ecms->pd_currency=addslashes(trim($_POST['pd_currency']));
	$ecms->pd_preferred_buyer_location=addslashes(trim($_POST['pd_preferred_buyer_location']));
	
	if($ecms->valid()){

		$ecms->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ecms->msg;//exit;
	header("Location:product-email.php?token=".$_GET['token']);
	//header("location:product-edit.php?token=".$ecms->cid);
}

/*$catsql= mysql_query("select * from product_category where pc_id='".$pdrow->pd_subcat_id."' and pc_status ='1'");
$catrow = mysql_fetch_object($catsql); */
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

<link href="css/pro.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">


<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<!-- TinyMCE -->
<!-- <script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({

		// General options
		mode : "textareas",
		theme : "advanced",
		
		plugins : "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		
		/*theme_advanced_disable: "image,advimage",*/

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],		 
		forced_root_block : false,
		force_p_newlines : false,
		remove_linebreaks : false,
		force_br_newlines : true,
		remove_trailing_nbsp : false,
		verify_html : false
			 	
	});
	
	
	
</script> -->
 
<script>
var imageBasket = [];
var imageBasketlogo = [];
//console.log(imageBasket);

 function usePhotoToUpload(id){
 //alert(imageBasket);
 
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
 
 
 function usePhotoToUploadlogo(id){
 //alert(imageBasketlogo );
    if(jQuery.inArray(id,imageBasketlogo ) != -1){
   
  imageBasketlogo = $.grep(imageBasketlogo , function(value) {
  return value != id;
    });
  }else{
    imageBasketlogo .push(id);
    }
 
 //alert(imageBasketlogo);

 }
   </script>
<!-- /TinyMCE -->
<script type="text/javascript">
 
function show_photo(id)
{
	$.get("ajax-file/showProductImage.php", {id:id},	function(data){
		$("#img_disp").html('');												 
		$("#img_disp").html('<img src="'+data+'" alt="" style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
	});
}
function showTempPhotoLogo(id){
$.get("ajax-file/showProductImagelogo.php", {id:id},	function(data){
		$("#img_disp_logo").html('');												 
		$("#img_disp_logo").html('<img src="'+data+'" alt="" style="width: 43px;    height: 46px;margin: -27px 0px 0px -3px;"/>');
	});
}
function usePhoto()
{          
         var imgArr = imageBasket;
	var tbl='products_edit';
	var typ='product';
	var pd_id=document.getElementById('pd_id').value;
	$.post("ajax-file/addNewImgFrmGallery.php", {imgArr:imgArr,pd_id:pd_id,tbl:tbl,typ:typ}, function(data){

		jQuery('#cboxOverlay').remove();
    jQuery('#colorbox').remove();

		$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
		
		setTimeout(function (){

		show_photo(pd_id);

         }, 500);
	});
}

function usePhotoForLogo()
{         
        var imgArr = imageBasketlogo;
	var tbl='products_edit';
	var typ1='logo';
	var pd_id=document.getElementById('pd_id').value;
	$.post("ajax-file/addNewImgFrmGallery.php", {imgArr:imgArr,pd_id:pd_id,tbl:tbl,typ:typ1}, function(data){
console.log(data);
		 jQuery('#cboxOverlay').remove();
    jQuery('#colorbox').remove();
		$("#img_disp_logo").html('<img src="images/loader.gif" alt="Uploading...." style="width: 43px;    height: 46px;margin: -27px 0px 0px -3px;"/>');
		
		setTimeout(function (){

		showTempPhotoLogo(pd_id);

         }, 500);
	});
}
function additem()
{
	var mcat_id=document.getElementById('mcat_id');
	var cat_id=document.getElementById('cat_id');
	var pd_subcat_id=document.getElementById('pd_subcat_id');
	var pd_title=document.getElementById('pd_title');
	var pd_desc=document.getElementById('pd_desc');
	
	var pd_min_order_qty=document.getElementById('pd_min_order_qty');
	var pd_unit=document.getElementById('pd_unit');
	var pd_fob_price=document.getElementById('pd_fob_price');
	var pd_fob_price2=document.getElementById('pd_fob_price2');
	var pd_currency=document.getElementById('pd_currency');
	
    var message="";
    var valid=true;
	
   	if(mcat_id.value=='')
	{
		message="";
		cat_id.focus();
		valid=false;
	}
	else if(cat_id.value=='')
	{
		message="";
		cat_id.focus();
		valid=false;
	}
	else if(pd_subcat_id.value=='')
	{
		message="";
		pd_subcat_id.focus();
		valid=false;
	}
	else if(pd_title.value=='')
	{
		message="";
		pd_title.focus();
		valid=false;
	}
	else if(pd_min_order_qty.value=='' || pd_min_order_qty.value==null || pd_min_order_qty.value=='0' || pd_min_order_qty.value=='00')
	{
		message="";
		pd_min_order_qty.value='';
		pd_min_order_qty.focus();
		valid=false;
	}
	else if(isNaN(pd_min_order_qty.value))
	{
		message="";
		pd_min_order_qty.value='';
		pd_min_order_qty.focus();
		valid=false;
	}
	else if(pd_unit.value=='')
	{
		message="";
		pd_unit.focus();
		valid=false;
	}
	/*else if(pd_fob_price.value=='' || pd_fob_price.value==null || pd_fob_price.value=='0' || pd_fob_price.value=='0.00')
	{
		message="Please enter FOB / Wholesale Price.";
		pd_fob_price.value='';
		pd_fob_price.focus();
		valid=false;
	}
	else if(pd_fob_price2.value=='' || pd_fob_price2.value==null || pd_fob_price2.value=='0' || pd_fob_price2.value=='0.00')
	{
		message="Please enter FOB / Wholesale Price.";
		pd_fob_price2.value='';
		pd_fob_price2.focus();
		valid=false;
	}
	else if(isNaN(pd_fob_price.value))
	{
		message="Please enter valid FOB / Wholesale Price.";
		pd_fob_price.value='';
		pd_fob_price.focus();
		valid=false;
	}*/
	else if(pd_currency.value=='')
	{
		message="Please choose Currency.";
		pd_currency.focus();
		valid=false;
	}
	else if(pd_desc.value.length>4000)
	{
		message="Please check that Product Description cannot have more than 4000 characters.";
		pd_desc.focus();
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
 <script type="text/javascript">
 function showCategory()
{
	var pc_id=document.getElementById('mcat_id').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#cat_id').html(data);	showsubcat();	}); 
}
function showsubcat()
{ 
//	var cid=document.getElementById('cat_id').value;
//	$.post("ajax-file/chkSubcat.php",{cid:cid},  function(data){	  $('#sbcat').html(data);	}); 
	
	var pc_id = $('select#cat_id option:selected').val();
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pd_subcat_id').html(data);	}); 
}
 
 	function mecount()
	{
		var cnt=$("#pd_desc").val().length;	
		$("#cn").html(cnt);
	}
	
	function showadditional() 
	{
		$("[id^=t3]").attr('class', 'tab-sel2 f1');
		$("#t1").attr('class', 'tab_p f1');	
		$("#bafrm").hide('fast');	
		$("#addef").show('fast');
		$("#adpre").hide();
		$("#adfrm").show();
	}
	
	function showmedit() 
	{
		$("[id^=t1]").attr('class', 'tab-sel f1 fw');
		$("#t3").attr('class', 'tab_p1 f1');	
		$("#bafrm").show('fast');	
		$("#addef").hide('fast');
	}
	
	function additionaldet_update(pid)
	{
	/*tinyMCE.triggerSave();*/
		var pd_hot = $('[name=pd_hot]:checked').val();
		var pd_payment = $("input[name=pd_payment]:checked").map(function () {return this.value;}).get().join(",");
		// var pd_payment = $('#pd_payment').val().join(",");
		var pd_pod=$("#pd_pod").val();
		var pd_pn_capct=$("#pd_pn_capct").val();
		var pd_dlv_time=$("#pd_dlv_time").val();
		var pd_pck_dets=$("#pd_pck_dets").val();
		var brand_name = $("#pd_brand_name").val();
		$.get("ajax-file/additionaldet-edit.php",{pid:pid,pd_hot:pd_hot,pd_payment:pd_payment,pd_pod:pd_pod,pd_pn_capct:pd_pn_capct,pd_dlv_time:pd_dlv_time,pd_pck_dets:pd_pck_dets,pd_brand:brand_name},
		function(data){
		var e=data.split("||");
		if(e[1]==0)
		{
		alert(e[0]);	
		}
		else
		{
		$("#adfrm").hide();
		$("#adpre").show();
		}
		}); 
	}
	
	function cancel_update()
	{
		$("#adfrm").hide();
		$("#adpre").show();
	}
	
	function packdetcount()
	{
		var pd_pck_dets=$("#pd_pck_dets").val().length;	
		$("#pckdet").html(pd_pck_dets);	
	}
	mecount();
	packdetcount();
	
	
	
	
	
	
 </script>
 
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>
 
<div class="inner_wrapper">
    <?php include 'includes/header_menu.php';?> 
		<!--left navigation:start-->
		<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends--> 
        <div class="w56b f1 p2b p14 blr"><h1> عـدل على المنتج</h1>
	<div class="mt5"><!--manage products/groups:start-->
		<div class="ap1">
		<a href="product-list.php" class="f1 ap2"title=" Go Back to Manage Products  ">إرجع إلى قائمة المنتجات  >> </a> 
         <p class="f1 loading" style="display:none;" id="loading"><img src="images/my2-loading.gif" class="loading_m2">&nbsp;Loading...&nbsp;</p>
		<p class="f2 mt12" id="page_str"></p>
		<div class="c3"></div>
		</div>




	<!--you are here:start-->
			<p class="urh"><span class="f1" style="font-size:14px;"></span>&nbsp;&nbsp;<strong style="color:#444444"></strong></p><!--manage product:start-->
		<div class="apfc1">
		<div>&nbsp;</div>	
		<div class="tab_2">
			<span id="t1" class="tab-sel f1 fw"><a onclick="showmedit();"title="Edit Product ">عــدل المنتج</a></span>
			<span id="t3" class="tab_p1 f1"><a onclick="showadditional();"title=" Additional Details">تعديل البيانات الإضافية</a></span>
            <!--<span id="t4" class="tab_p1 f1">Promote on <?php /*echo getWebSiteName();*/ ?></span>-->
		  <div class="c3"></div>		
		</div>
		
		<div class="tab_brd" style="background-color:#FAF4FF"title=" كتابة - بيانات منتجاتك بشكل صحيح - يجعلها سهلة الظهور - فى محركات البحث  ">
		<!--item basic info:starts-->
	<a name="ba"></a>
	<div class="p-irt" id="bapre" style="display:none;"title="Well written product information boosts your product visibility in search results  ">
	<p class="bulb">كتابة تفاصيل منتجك بإتقان وإستفاضة يساعد على ظهوره فى نتائج البحث كثيرا </p>
	<ul>
	<li class="f1 msi" id="view_image_small">
    <img src="images/panasonic-logo-250x250.png" alt="abc" onload="resize(this,'http://3.imimg.com/data3/XG/RU/MY-9061497/panasonic-logo-250x250.png',200,200)" border="0" width="200" height="200"></li>

	

	<li class="f2 c3">

		<a href="javascript:;" style="position:relative;top:25px;padding:4px 0" class="tab_p4 f2" id="pgnext">Additional Details</a>
		<a href="javascript:;" style="position:relative;top:25px;padding:4px 0" class="tab_p4 f2" id="banext">Group Your Product</a>
	</li>
	<li class="c3">
		<a href="javascript:;" class="saps f11 fw f1" id="baedit">Edit</a>
	</li>

	</ul>
	</div>
	<!--item basic info:ends-->

	<!--item basic info-edit:starts-->
	<div style="display: block;" class="mse2 p-irt" id="bafrm">
		<p class="bulb">كتابة تفاصيل منتجك بإتقان وإستفاضة يساعد على ظهوره فى نتائج البحث كثيرا</p>
		<div class="c3"></div>
        
        <div class="pia f1">
		<div style="position:absolute" id="tempwarning_add"></div>
		<p id="img_form" style="margin-top: 2px;"></p>
		<div id="img_disp" class="cssie1 mover">
		  <!--<iframe framespacing="0" marginwidth="0" marginheight="0" style="margin-top:2px;" src="update-product-image.php?pdid=<?php echo $pdrow->pd_id;?>" class="mt5" scrolling="NO" width="123" frameborder="0" height="123"></iframe>-->
		  <?php $imgonepro=explode(',',$pdrow->pd_image);
		   ?>
          <img src="upload/myproduct/<?php if($pdrow->pd_image==''){ echo "add-image.gif"; }else{	echo $imgonepro[0];	}	?>" style="width:125px;height:125px;margin-top:1px;margin-left:1px;" title="ADD product Image ">
		</div>
        
        <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
				<script type="text/javascript">
					jQuery(function(){
						jQuery('#product_upload').uploadifive({
							'auto'     : true,
							'formData' : {'id' : '<?php echo $pdrow->pd_id; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'ajax-file/editProdImg.php',
							'onAddQueueItem' : function(file) {
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
								$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
							},
							'onUploadComplete' : function(file,data) {
								show_photo(<?php echo $pdrow->pd_id; ?>);
							}
						});
					});
				</script>
                <div>
                <div id="drop" style="padding-left:10px;float:right">
		            <input type="file" id="product_upload" name="product_upload" title="ADD Product Image"/>
	            </div>
	            <div id="queue"></div>
                </div>
                <div>
                <link rel="stylesheet" href="css/colorbox.css" />
					<script src="js/jquery.colorbox.js"></script>
                    <script>
						$(document).ready(function(){
							//Examples of how to assign the ColorBox event to elements
							$('.ajax').live('click', function() {
							if ($("#colorbox").css("display")=="block") {  
  jQuery('#cboxOverlay').remove();
    jQuery('#colorbox').remove();
}
							  $.colorbox({href:$(this).attr('href'), open:true});
							  return false;
							});
							//$(".ajax").colorbox({width:"72%"});
							$(".inline").colorbox({inline:true, width:"50%"});
							//Example of preserving a JavaScript event for inline calls.
							$("#click").click(function(){ 
							$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
							return false;
							});
						});
					</script>
            <a class="ajax" href="show_productedit_img.php?pid=<?php echo $pdrow->pd_id;?>" style="text-decoration:none;"title=" Select Image from Gallery "> صوة من الجاليرى</a>
                </div>
        <p id="img_form" style="    margin-top: 40px;"></p>
		
<div id="imglogo_disps" class="cssie1 mover" style="position: relative;border: 1px solid #ccc;">
		<!--<iframe framespacing="0" marginwidth="0" marginheight="0" style="margin-top:2px;" src="upload-product-image.php" class="mt5" scrolling="NO" width="123" frameborder="0" height="123"></iframe>-->
        <img src="upload/myproduct/logo_upload2.jpg" title="ADD Logo,Brand,Discount,Sign" style="width:121px;height:125px;margin-top:1px;margin-left:1px;">
<?php $imgonelogo=explode(',',$pdrow->pd_imagelogo);?>
<div id="img_disp_logo" class="cssie1" style="width: 43px;    height: 28px;left: 5px;bottom: 4px;position: absolute;">
<img src="upload/myproduct/<?php if($pdrow->pd_imagelogo==''){ echo "add-image.gif"; }else{ echo $imgonelogo[0];} ?>" title="Add Brand Logo"  style="width: 43px;    height: 46px;margin: -27px 0px 0px -3px;<?php if($pdrow->pd_imagelogo==''){ echo "display:none;"; };?>">
</div>
		</div>

        <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
				<script type="text/javascript">
					jQuery(function(){
						jQuery('#productlogo_upload').uploadifive({
							'auto'     : true,
							'formData' : {'id' : '<?php echo $pdrow->pd_id; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'ajax-file/editProdlogo.php',
							'onAddQueueItem' : function(file) {
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
								$("#img_disp_logo").html('<img src="images/loader.gif" alt="Uploading...." style="width:43px;height:46px;margin-top:-29px;margin-left:-3px;"/>');
							},
							'onUploadComplete' : function(file,data) {
								showTempPhotoLogo(<?php echo $pdrow->pd_id; ?>);
							}
						});
					});
				</script>
                <div>
                <div id="drop" style="font-width:200">
		            <input type="file" id="productlogo_upload" name="productlogo_upload" style="font-width:200"/>
	            </div>
	            <div id="queue"></div>
                </div>
                <div>
                <link rel="stylesheet" href="css/colorbox.css" />
					<script src="js/jquery.colorbox.js"></script>
                    <script>
						$(document).ready(function(){
							//Examples of how to assign the ColorBox event to elements
							$('.ajaxa').on('click', function() {
							  $.colorbox({href:$(this).attr('href'), open:true});
							  return false;
							});
							//$(".ajax").colorbox({width:"72%"});
							$(".inline").colorbox({inline:true, width:"50%"});
							//Example of preserving a JavaScript event for inline calls.
							$("#click").click(function(){ 
							$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
							return false;
							});
						});
					</script>
            <a class="ajaxa" href="show_product_edit_logo.php?pid=<?php echo $pdrow->pd_id;?>" style="text-decoration:none;" title="ADD side image as   Logo,Brand,Discount,Sign Image" > حمل صورة جانبية  </a>


                </div>
            
		<div id="remove_image" class="dn mt5">
			<a href="javascript:remove_small_image('add');"><img src="images/remove.gif" align="absmiddle" width="44" height="10"></a>
		</div>
		</div>

		<form action="" method="POST" enctype="multipart/form-data" onsubmit="return additem();" >
		<div class="fside f1" style="width:82%;">
			 <div id="updatemessage"><?php echo $msg; ?></div>
			<div style="clear:both"></div>
            
            <div class="fs1 f1" style="width:100%"title="Main Category 1  ">
    <?php
	$mcat_sql="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$pdrow->pc_parent_id."' and pc_status='1') and pc_status='1'";
	$mcat_res=mysql_query($mcat_sql);
	$mcat_row=mysql_fetch_object($mcat_res);
	?>
    
            <p><span style="line-height: 12px;">*</span>   إختار التصنيف العام للمنتج </p>
            <input type="hidden" id="pd_id" name="pd_id" value="<?php echo $pdrow->pd_id; ?>" />
            <?php
			$sql_mcat="select * from product_category where pc_parent_id='0' and pc_status='1'";
			$res_mcat=mysql_query($sql_mcat);
			?>
            <select name="mcat_id" id="mcat_id" onChange="showCategory(this.value)" class="a_f pf1" style="width:280px;">
            <?php
			
			while($row_mcat=mysql_fetch_object($res_mcat))
			{	?>
				<option value="<?php echo $row_mcat->pc_id; ?>" <?php if($row_mcat->pc_id==$mcat_row->pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_mcat->pc_name; ?></option>
	    	<?php	}	?>
            </select><br>
            <span></span>
            </div>
            <div class="fs1 f1"></div>
            <div class="fs1 f1"title="  Category   ">
            <p><span style="line-height: 12px;">*</span>إختار التصنيف الرئيسى للمنتج </p>
            <?php
			$catsqlk="select * from product_category where pc_parent_id='".$mcat_row->pc_id."' and pc_status='1'";
			?>
            <select name="cat_id" id="cat_id" onChange="showsubcat()" class="a_f pf1" style="width:280px;">
            <option value="">Select</option>
            <?php
            
            $catresk=mysql_query($catsqlk);
            while($catrwk=mysql_fetch_array($catresk)){
            ?>
    <option value="<?php echo $catrwk['pc_id']?>" <?php if($pdrow->pc_parent_id==$catrwk['pc_id']) { ?> selected="selected"<?php } ?>><?php echo $catrwk['pc_name']?></option>
            <?php } ?>
            </select><br>
            <span></span>
            </div>
            
            <div id="sbcat">
            <div class="fs1 f1"title=" Sub Category " >
            <p><span style="line-height: 12px;">*</span>إختار التصنيف الفرعى للمنتج</p>
            <select name="pd_subcat_id" id="pd_subcat_id" class="a_f pf1" style="width:280px;">
            <option value="">Select</option>
            <?php
			$subcatsqlk="select * from product_category where pc_parent_id = '".$pdrow->pc_parent_id."' and pc_status = '1'";
			$subcatresk=mysql_query($subcatsqlk);
            while($subcatrwk=mysql_fetch_array($subcatresk)){
            ?>
<option value="<?php echo $subcatrwk['pc_id']?>" <?php if($pdrow->pd_subcat_id==$subcatrwk['pc_id']) { ?> selected="selected"<?php } ?>><?php echo $subcatrwk['pc_name']?></option>
            <?php } ?>
            </select><br>
            <span></span>
            </div>
            </div>
            

			<div class="fs1 f1"title="  Product Heading " >
			<p><span>*</span> أكتب عنوان المنتج باللغة العربية أو الإنجليزية حسب مكان  بيع المنتج</p>
			<input value="<?php echo $pdrow->pd_title; ?>" name="pd_title" id="pd_title" maxlength="60" class="a_f pf1" type="text">
			<span></span>
			</div>
	
			<div class="fs2 f1"title=" Product Item Code   " >
			<p>أكتب كود المنتج إختيارى</p>
			<input value="<?php echo $pdrow->pd_code; ?>" name="pd_code" id="pd_code" maxlength="60" class="a_f pf1" type="text">
			<span></span>
			</div>
            
            <div class="fs1 f1"title=" Minimum Order Quantity  " >
			<p><span>*</span> أكتب أقل كمية لبيع المنتج </p>
			<input name="pd_min_order_qty" id="pd_min_order_qty" maxlength="60" class="a_f pf1" type="text" value="<?php echo $pdrow->pd_min_order_qty;?>">
			<span></span>
			</div>
	
			<div class="fs2 f1"title=" Measurement Unit ">
			<p>إختار وحدة قياس المنتج </p>
			<select size="1" name="pd_unit" id="pd_unit" class="a_f pf1">
              <option value="">-Select Unit Type-</option>
              <?php                
				$unitsql=mysql_query("select * from measurement_unit where mu_status='1'");
				while($unitrow=mysql_fetch_object($unitsql)){
				?>
              <option value="<?php echo $unitrow->mu_id; ?>" <?php if($pdrow->pd_unit==$unitrow->mu_id){ ?> selected="selected" <?php } ?> ><?php echo $unitrow->mu_name;?></option>
				<?php } ?>
	        </select>
            <span></span>
			</div>
            
			<div class="fs1 f1"title="FOB / Wholesale Price">
			<p>  محلى أو تصدير  ( الى : من )  أكتب سعر المنتج   </p>

			<span" title=" From " >  من  :  <input name="pd_fob_price" id="pd_fob_price" maxlength="60" class="a_f pf1" style="width: 45%;" type="text" value="<?php echo $pdrow->pd_fob_price;?>">
           <span" title=" To   " >     الى  :    <input name="pd_fob_price2" id="pd_fob_price2" maxlength="60" class="a_f pf1" style="width: 42%;" type="text" value="<?php echo $pdrow->pd_fob_price2;?>"><br />
			<span style="margin-left: 40px;"></span>
			</div>
	
			<div class="fs2 f1" style="margin-top:4px;"title=" إختار عملة البيع - حسب البلد التى تبيع لها المنتج">
			<p>&nbsp;</p>
			<select size="1" name="pd_currency" id="pd_currency" class="a_f pf1">
                <option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysql_query("select * from country where cn_status='1'");
				while($currencyrow=mysql_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if($pdrow->pd_currency==$currencyrow->cn_id || user_info($uid,'country')== $currencyrow->cn_id){ ?> selected="selected" <?php } ?> >
				<?php echo $currencyrow->cn_currency;?> 
                </option>
				<?php } ?>
            </select>
            <span></span>
			</div>
            
			<div style="clear:both;line-height: 21px;*line-height: 10px;">&nbsp;</div>
			<div class="fs1 f1" style="margin-top:5px;"title="Location Preferences  ">
			<p><span style="line-height: 12px;">*</span> إختار الأماكن الذى ترغب فى بيع هذا المنتج فيه</p>
            
            <div style="vertical-align:middle">
            <input type="radio" id="pd_preferred_buyer_location_1" name="pd_preferred_buyer_location" value="abroad" <?php if($pdrow->pd_preferred_buyer_location=='abroad'){ ?> checked="checked"<?php } ?> /><label style="top:0px;"title=" Abroad Only">-  هذا المنتج للتصدير فقط    
  </label>
            </div>
            <div>
		         <input type="radio" id="pd_preferred_buyer_location_2" name="pd_preferred_buyer_location" value="any" <?php if($pdrow->pd_preferred_buyer_location=='any'){ ?> checked="checked"<?php } ?>/><label style="top:0px;"title="Abroad + Domestic">- هذا المنتج للتصدير أو للبيع الداخلى معـا  
     </label>
			</div>
            <div>
	             <input type="radio" id="pd_preferred_buyer_location_3" name="pd_preferred_buyer_location" value="domestic" <?php if($pdrow->pd_preferred_buyer_location=='domestic'){ ?> checked="checked"<?php } ?>/><label style="top:0px;"title="Domestic Only">-  هذا المنتج داخل 
    بلدك فقط   </label> 
    		</div>
            <div>
                 <input type="radio" id="pd_preferred_buyer_location_4" name="pd_preferred_buyer_location" value="my_city"<?php if($pdrow->pd_preferred_buyer_location=='my_city'){ ?> checked="checked"<?php } ?>/> <label style="top:0px;"title=" My City Only " >-    هذا المنتج للبيع داخل مدينتى فقط     </label>
            </div>
            
			</div>
			
		</div>
			<div class="edtbut">
				<span id="save_basic"><input name="btnUpdate" class="saps mt12"title=" إحفظ - التعديلات -  التى تمت على المنتج "  value="إحفظ التغييرات " type="submit"></span> 
                <span style="display:none;" id="basaving"> <img alt="" src="edit%20product_files/loading.gif" border="0" width="16" height="11"> </span>
			</div>
			<div id="editor_loading" style="display: none; padding: 230px; height: 0px;">
				<img src="images/indicator.gif">&nbsp;Loading editor...
			</div>
			<div class="c3 fs3" id="editor" style="">
				<p>وصف تفاصيل للمنتج</p>
				<textarea class="a_f" rows="15" id="pd_desc" name="pd_desc" onKeyUp="mecount();"><?php echo $pdrow->pd_desc;?></textarea>
				<div class="max"><font id="Charcount" color="#ff8000"><span id="cn" style="color:#ff8000">0</span> character (maximum of 4000) </font> character(s).</div>
			</div>
		</form>
	</div>
	<!--item basic info:ends-->
    <div id="addef" class="p-irt" style="display:none;">
	  <p class="bulb">التفاصيل الاضافية للمنتج تساعد المشتريين على فهم المنتج وسرعة التفاعل معه  </p>
	  <p class="b-img arp1 pdb1 f1" href="JavaScript:;"title=" Additional Details " >تفاصيل إضافية للمنتج </p>
	  <div class="c3"></div>
      <div id="abc"></div>
	  <!--additional details preview:start -->
	  <div id="adpre" class="pad f1 mr" style="display:none;">
	    <ul>
	      <li class="f1 tc"title="Product Status">  حالة المنتج </li>
	      <li class="f1 pct" id="view_hotnew"><?php if($pdrow->pd_hot=='1'){ echo "Hot";} else if($pdrow->pd_hot=='0'){ echo "-"; } ?></li>
	      <li class="f1 tc"title="Product Brand Name ">إسم ماركة المنتج </li>
	      <li class="f1 pct pbi"><?php if($pdrow->brand_name){ echo $pdrow->brand_name;}else{ echo "-"; }?></li>
	      <li class="f1 tc"title="Payment Terms">شروط الدفع</li>
	      <li class="f1 pct pbi" id="view_payment_terms"><?php if(get_payment_terms($pdrow->pd_id)!="") { echo get_payment_terms($pdrow->pd_id); } else { echo "-"; } ?></li>
	      <li class="f1 tc">FOB / Wholesale</li>
	      <li class="f1 pct" id="view_fob_price"><?php if($pdrow->pd_fob_price!="" && $pdrow->pd_currency!="") { echo $pdrow->pd_fob_price."&nbsp;".get_product_detail($pdrow->pd_id,'pd_currency'); } ?></li>
	      <li class="f1 tc">Minimum Order Quantity</li>
	      <li class="f1 pct" id="view_moq"><?php if($pdrow->pd_min_order_qty!="" && $pdrow->pd_unit!=""){ echo $pdrow->pd_min_order_qty."&nbsp;".get_product_detail($pdrow->pd_id,'pd_unit'); } else { echo "-"; } ?></li>
	      <li class="f1 tc"title="Port or Place of Dispatch">مكان أو ميناء التسليم </li>
	      <li class="f1 pct" id="view_port_of_dispatch"><?php if($pdrow->pd_pod!=""){ echo $pdrow->pd_pod; } else { echo "-"; } ?></li>
	      <li class="f1 tc"title="Production Capacity">مدى القدرة الإنتاجية</li>
	      <li class="f1 pct" id="view_prod_cap"><?php if($pdrow->pd_pn_capct!=""){ echo $pdrow->pd_pn_capct;} else { echo "-"; } ?></li>
	      <li class="f1 tc"title="Delivery Time">وقـت التسليم </li>
	      <li class="f1 pct" id="view_delivery_time"><?php if($pdrow->pd_dlv_time!=""){ echo $pdrow->pd_dlv_time; } else { echo "-"; } ?></li>
	      <li class="f1 tc"title="Packing Details">تفاصيل التعبئة والتغليف </li>
	      <li class="f1 pct pns7" id="view_packaging_det"><?php if($pdrow->pd_pck_dets!=""){ echo $pdrow->pd_pck_dets;} else { echo "-"; }?></li>
	      <li class="f1 c3 mt12"><a id="adedit" class="saps f1 f11 fw" onclick="showadditional();" style="cursor:pointer;">Edit</a></li>
	      <li class="f2 mt12"><a href="javascript:;" class="tab_p4 tab_ad" id="adnext">Promote on <?php echo getWebSiteName(); ?></a></li>
	      </ul>
	    </div>
	  <!--additional details preview:end-->
	  <!--add additional details form:start-->
	  <div style="display:block;" id="adfrm">
	    <form method="post" name="additional_details">
	      <div class="pad f1 mr">
	        <ul>
	          <li class="f1 tc adtp"title="  Product Display Status   " > حالة عرض المنتج </li>
	          <li class="f1 pct tc1">
	            <input class="rad" name="pd_hot" value="1" id="pd_hot" type="radio" <?php if($pdrow->pd_hot=='1'){?> checked="checked"<?php } ?>/>
	           العرض فى صفحة المنتجات الهامة   <input name="pd_hot" value="0" id="pd_hot" <?php if($pdrow->pd_hot=='0'){?> checked="checked"<?php } ?> onclick="return makelive(0)" type="radio" />
	           العرض فى صفحة المنتجات العادية </li>
                
	          <input name="hotnew" id="hotnew" value="" type="hidden" />
	          	<li class="f1 tc adtp"title=" Product Brand Name ">إسم ماركة المنتج </li>
                <li class="f1 pct pbi tc1">
                    <input name="pd_brand_name" id="pd_brand_name" maxlength="255" value="<?php echo $pdrow->brand_name; ?>" class="a_f adt form-control" type="text">
                </li>





	          <li class="f1 tc adtp"title=" Payment Terms ">طريقـة الدفـع</li>
	          <!-- <li class="f1 pct pbi tc1">

				<?php echo ucwords($paymentrow->pg_name);?>

              <?php
			  $pdpayment=explode(",",$pdrow->pd_payment);
              $paymentres=mysql_query("select * from payment_gateway" );
			  while($paymentrow=mysql_fetch_object($paymentres)){
			  if(in_array($paymentrow->id,$pdpayment))		
			  {




			  ?>
	            <input  class="cb1" name="pd_payment" id="pd_payment" value="<?php echo $paymentrow->id;?>" checked="checked" type="checkbox" />
	            
                <?php } else {  ?>
                <input  class="cb1" name="pd_payment" id="pd_payment" value="<?php echo $paymentrow->id;?>"  type="checkbox" />
	            <?php echo ucwords($paymentrow->pg_name);?>
                <?php } } ?>

                </li> -->
                <li class="f1 pct pbi tc1">
            	<?php
				$pdpayment=explode(",",$pdrow->pd_payment);
	            $paymentres=mysql_query("select * from payment_method ");
	            ?>
	            <!-- <select name="pd_payment" multiple id="pd_payment"> -->
	            	<span class="d-blcok" id="payOptionButton" data-toggle="collapse" data-target="#payOptions" style="cursor: pointer;">
	            		Select Options
	            		<span class="payOptionButtonArrowDown" style="width: max-content; transform: rotate(90deg); display: inline-block;">&#10151;</span>
	            	</span>

	            	<div class="collapse fade" id="payOptions" style="padding: 4px 14px;">
                        <?php
                        while($paymentrow=mysql_fetch_object($paymentres)){
                        
                            //echo "<option value='{$paymentrow->ph_id}'>{$paymentrow->ph_title}</option>";
                            echo '<input  class="cb1" name="pd_payment" value="'.$paymentrow->ph_id.'"  type="checkbox" />' . $paymentrow->ph_title . '<br>';
                    
                        } ?>
                    </div>
                    <!-- </select>   --> 
                </li>
	          <input name="in_pc_item_payment_terms" id="in_pc_item_payment_terms" value="L/C (Letter of Credit),D/A,T/T (Bank Transfer),Other" type="hidden" />
	          
	          
	          
	          <li class="f1 tc adtp"title="Port or Place of Dispatch">ميناء أو مكان التسليم </li>
	          <li class="f1 pct">
	            <input class="a_f adt form-control" name="pd_pod" id="pd_pod" maxlength="100" value="<?php echo $pdrow->pd_pod; ?>" type="text" />
	            </li>
	          <li class="f1 tc adtp"title="Production Capacity ">  معدل وقدرة الإنتاج </li>
	          <li class="f1 pct">
	            <input name="pd_pn_capct" id="pd_pn_capct" maxlength="100" value="<?php echo $pdrow->pd_pn_capct; ?>" class="a_f adt form-control" type="text" />
	            </li>
	          <li class="f1 tc adtp"title="Delivery Time ">موعد  التسليم</li>
	          <li class="f1 pct">
	            <input class="a_f adt form-control" name="pd_dlv_time" id="pd_dlv_time" maxlength="100" value="<?php echo $pdrow->pd_dlv_time; ?>" type="text" />
	            </li>
	          <li class="f1 tc adtp"title="Packing Details">تفاصيل التعبئة والتغليف  </li>
	          <li class="f1 pct">
	            <textarea rows="5" class="a_f form-control" name="pd_pck_dets" id="pd_pck_dets" onkeyup="packdetcount();">
				<?php echo $pdrow->pd_pck_dets; ?></textarea>
	            <font id="Charcount1" color="#ff8000"><span id="pckdet">20</span> character (maximum of 2000)</font> character(s).
                </li>
	          <li class="f1 tc adtp"title=" Attach PDF Brochure " >   حمل بروشور للمنتج  </li>
	          <li class="f1 pct1 mt5">
<iframe src="upload-prd-doc.php?pid=<?php echo $pdrow->pd_id;?>" border="0" framespacing="0" allowtransparency="true" scrolling="no" width="269" frameborder="0" height="30"> 
</iframe>
	            <span class="f2" id="indecator_gif0" style="left:37px;position:relative;top:-32px;"></span> (فقط    PDF    حمل بروشور صيغة) </li>
	          <li class="f1 tc adtp"> </li>
	          <li>
	            <input name="updateaddi" class="saps awt mt12 m5"title=" إحفظ التفاصيل الإضافية "

 value="إحفظ التغييرات " type="button" onclick="additionaldet_update(<?php echo $pdrow->pd_id;?>)"/>
	            <span style="display:none;" id="adsaving"> <img alt="" src="editproduct-step1_files/loading.gif" border="0" width="16" height="11" /></span>
	            <input name="cancleaddi" class="saps mt12 ml8" value="الغـــاء " id="adcls" type="button" onclick="showmedit();" />
	            </li>
	          </ul>
	        </div>
	      <!--<div class="pia f1 mt5">
	        <div class="fw mb5">Large Image</div>
	        <p id="old_img_form_1"></p>
	        <div id="add_large_image" class="db cssie3 mover">
	          <iframe framespacing="0" marginwidth="0" marginheight="0" style="margin-top:2px;" src="upload-prd-largeimg.php?pid=<?php /*echo $pdrow->pd_id;*/ ?>" class="mt5" scrolling="NO" width="123" frameborder="0" height="123"></iframe>
	          </div>
	        <div id="remove_large_image" class="dn mt5"> <a href="javascript:remove_large_image();"><img src="images/remove.gif" align="absmiddle" width="44" height="10" /></a> </div>
	        </div>-->	
	      </form>
	    <div class="c3"></div>
	    </div>
	  <!--add additional details form:end-->
	  <!--loading:start-->
	  <div style="display: none;" id="lo" class="f11 fw loi" align="center"><img alt="Please wait" src="images/indicator.gif" width="16" height="16" /> Please Wait...</div>
	  <!--loading:end-->
	  </div>
	<!--promote on iil:start-->
	<a name="pi"></a>
	<div id="pidef" class="p-irt" style="display:none">
	<p class="bulb">Well categorized product help you gain better visibility on <?php echo getWebSiteName(); ?> platform</p>
	<p class="b-img arp1 pim f1 p15">Promote on <?php echo getWebSiteName(); ?></p>
	<div class="c3"></div>
	
	<!--suggest category preview:start-->
	<div id="pipre" style="display:none;">
	<strong class="p15 pns7">Your Product is mapped &amp; promote in following <span id="pipre_count">0</span> categories on <?php echo get_page_settings(4);?> network:</strong>
	<ul class="pimt mt12">
	<div class="setcat1" id="selected_mcat_list"></div>
	<a href="javascript:;" class="saps f11 fw dbi mt12" id="piedit">Edit</a>
	</ul> 
	</div>
	<!--suggest category preview:end-->

	<!--suggest category form:start-->
	<div id="pifrm" style="display:none">
	<p class="p15 pns7"><strong>Select product categories where you want to promote your products on <?php echo get_page_settings(4);?> network.
	</strong><br><span align="left" id="selectedtags_span"></span></p>
	<ul class="tabs ml15 mr15">
	<li class="f1"><a href="javascript:suggcat();" class="curr" id="su_tab">Suggested Categories</a></li>
	<li class="f1"><a href="javascript:browsecat();" class="dis" id="b_tab">Browse Categories</a></li>
	<li class="f1"><a href="javascript:searchcat();" class="dis" id="s_tab">Search Categories</a></li>
	</ul>
	<div class="panes ml15 mr15">
		<div id="sugg_cat" class="sugc">
			<div id="sugg_mcat_header"></div>
			<div id="suggested_mcat_div"></div>
			<div id="suggested_mcat_process" align="center"><img src="images/indicator.gif"> Processing...</div>
		</div>

		<div id="browse_cat">
		<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
		<tbody><tr>
			<td width="50%"><strong>1:</strong><br>
			<select name="cat1" id="grp_list" style="width:100%;" size="7" class="mt5" onclick="subcat()"><option value="16">Agro, Marine &amp; Other Food Products and Beverages</option><option value="3">Apparel, Clothing &amp; Garments</option><option value="56">Architectural &amp; Designing Services</option><option value="83">Astrology, Vaastu &amp; Related Services</option><option value="5">Automobiles, Spare Parts and Accessories</option><option value="84">Ayurvedic &amp; Herbal Product</option><option value="6">Bicycles, Rickshaws, Spares and Accessories</option><option value="7">Building Construction Material, Equipment and Sanitaryware</option><option value="58">Building, Construction &amp; Real Estate Services</option><option value="55">Business &amp; Management Consultants</option><option value="96">Call Centers and Business Process Outsourcing</option><option value="8">Chemicals, Dyes &amp; Allied Products</option><option value="10">Computers, Software, IT Support &amp; Solutions</option><option value="11">Cosmetics, Toiletries &amp; Personal Care Products</option><option value="61">Educational &amp; Professional Training Institutes</option><option value="13">Electronics &amp; Electrical Goods &amp; Supplies</option><option value="66">Embassies, Consulates, Councils &amp; Trade Associations</option><option value="85">Facility Management &amp; House Keeping Services</option><option value="100">Fashion &amp; Garment Accessories for Men, Women &amp; Kids</option><option value="99">Financial &amp; Legal Advisory Services</option><option value="93">Furniture, Furniture Supplies &amp; Furniture Hardware</option><option value="18">Gems, Jewellery, Precious Stones &amp; Beads</option><option value="19">Gifts, Crafts, Antiques &amp; Handmade Decoratives</option><option value="68">HR Consultants &amp; Placement Agencies</option><option value="46">Home Furnishings and Home Textiles</option><option value="72">Hotels, Resorts &amp; Other Accommodation</option><option value="74">Housewares, Home Appliances &amp; Household Consumables</option><option value="98">Independent Contractors &amp; Freelance Workers</option><option value="14">Industrial &amp; Engineering Products, Spares and Supplies</option><option value="81">Industrial &amp; Engineering Services, Solutions &amp; Consultancy</option><option value="34">Industrial Plants, Machinery &amp; Equipment</option><option value="95">Information Technology and Telecommunication Services</option><option value="101">Interior &amp; Exterior Decoratives for Home &amp; Garden</option><option value="22">Leather and Leather Products &amp; Accessories</option><option value="102">Mechanical Components &amp; Parts</option><option value="49">Media, Advertising, Copywriting &amp; Publishing Services</option><option value="27">Medical, Pharma, Surgical &amp; Healthcare</option><option value="94">Medical, Pharmaceutical &amp; Health Care Services</option><option value="29">Metals, Minerals, Ores &amp; Alloys</option><option value="76">Musical Instruments</option><option value="9">Office, School &amp; Commercial Supplies and Consumables</option><option value="30">Packaging Material, Supplies &amp; Machines</option><option value="80">Packers &amp; Movers, Clearing Agents &amp; Logistic Services</option><option value="77">Paper and Paper Products</option><option value="42">Plastic &amp; Plastic Products</option><option value="32">Printing &amp; Publishing</option><option value="97">Products Rental, Leasing &amp; Maintenance Services</option><option value="45">Railway, Shipping &amp; Aviation Products, Spares &amp; Equipment</option><option value="53">Research, Development, Testing &amp; Laboratory Services</option><option value="41">Rubber &amp; Rubber Products</option><option value="40">Scientific, Measuring, Laboratory Instruments &amp; Supplies</option><option value="36">Sports Goods, Games, Toys &amp; Accessories</option><option value="26">Stones, Marble &amp; Granite Supplies</option><option value="37">Telecom Products, Equipment &amp; Supplies</option><option value="38">Textiles, Yarn, Fabrics &amp; Allied Industries</option><option value="25">Tools, Machine Tools, Power Tools &amp; Hand Tools</option><option value="63">Trade Event Organizers, Event Management &amp; Event Planners</option><option value="73">Travel Agents &amp; Tour Operators</option><option value="50">Travel, Tourism, Recreational &amp; Other Hospitality Services</option></select>
			</td>

			<td><img src="images/zero.gif" width="4" height="1"></td>

			<td width="50%">
			<div class="displayoff" id="subcat"> </div> <div id="subcat_process_img" style="display:none;" align="center"><img src="images/indicator.gif"> </div></td>
		</tr>
		<tr>
			<td width="50%"><img src="images/zero.gif" width="4" height="8"></td>
			<td></td>
			<td width="50%"></td>
		</tr>
		<tr>
			<td width="50%">
			<div id="mcat"></div> <div id="mcat_process_img" style="display:none;" align="center"> <br><br><img src="images/indicator.gif"> </div></td>
			<td></td>
			<td width="50%"></td>
		</tr>
		</tbody></table>
		</div>

		<div id="search_cat">
			<div style="font-size:13px; font-weight:bold; padding-bottom:5px; color:rgb(0, 74, 149);">Enter product keywords to find a category</div>

			<form name="form_search_mcat" onsubmit="return search_mcats();">
				<input class="myims" maxlength="60" size="33" name="search_mcat" id="search_mcat">
				<input name="button5" value="Search" onclick="return search_mcats();" type="button">
				<span style="color:gray">For example- "arm chair"</span>
			</form>

			<div id="search_process_bar" style="display:none" align="center"><br><img src="images/indicator.gif"> Processing...</div>

			<div id="s_result" style="display:none;">
			<div><br>
			<strong id="search_mcat_header"></strong>
			<div style="overflow: auto; height: 186px;" id="search_mcat_dropdown_div" class="mt5"> </div>
			</div>
			</div>
		</div>

	</div>

	<ul id="all_mcat_list" class="pimt mt12"> </ul>

	<input name="old_mcat_mapping" id="old_mcat_mapping" value="" type="hidden">
	<input name="new_mcat_mapping" id="new_mcat_mapping" value="" type="hidden">
	<span id="save_promote"><input class="saps mt12 ml15 sav" onclick="save_promote()" value="Save" type="button"></span> <span style="display:none;" id="pisaving"> <img alt="" src="edit%20product_files/loading.gif" border="0" width="16" height="11"> </span><input name="canclepi" class="saps mt12 ml8" value="Cancel" id="picls" type="button">
	</div>
	<!--suggest category form:end-->
	
	<!--loading:start-->
	<div class="f11 fw loi" id="lo2" style="display:none" align="center"><img src="images/indicator.gif" alt="Please wait" width="16" height="16"> Please Wait...</div>
	<!--loading:end-->
	
	</div>
	<!--promote on iil:end-->  <!--add additional details container:start-->
	<a name="ad"></a> 
	<div id="addefk" class="p-irt" style="display: none;">
	<p class="bulb">Additional product details help your product listing gain more visibility to interested buyers</p>
	<p class="b-img arp1 pdb1 f1" href="JavaScript:;">Additional Details</p>
	<div class="c3"></div>
	<!--additional details preview:start -->
	<div id="adpre" class="pad f1 mr" style="display: none;">
	<ul>
	<!--<li style="" class="f1 tc">Product Code</li><li class="f1 pct" id="view_ad_item_code">0332545454</li>-->
	<li class="f1 tc">Product Status</li><li class="f1 pct" id="view_hotnew">-</li>
	<li class="f1 tc">Payment Terms</li><li class="f1 pct pbi" id="view_payment_terms">L/C (Letter of Credit),D/A</li>
	<li class="f1 tc">FOB / Wholesale</li><li class="f1 pct" id="view_fob_price">12000 INR</li>
	<li class="f1 tc">Minimum Order Quantity</li><li class="f1 pct" id="view_moq">5 Ounce</li>
	<li class="f1 tc">Port of Dispatch</li> <li class="f1 pct" id="view_port_of_dispatch">-</li>
	<li class="f1 tc">Production Capacity</li><li class="f1 pct" id="view_prod_cap">-</li>
	<li class="f1 tc">Delivery Time</li><li class="f1 pct" id="view_delivery_time">-</li>
	<li class="f1 tc">Packing Details </li> <li class="f1 pct pns7" id="view_packaging_det">fgjkghkgukluk</li>
	<li class="f1 tc">Product PDF Brochure</li><li class="f1 pct mb12" id="view_file_doc">-</li>
	<li class="f1 c3 mt12"><a id="adedit" class="saps f1 f11 fw" href="javascript:;">Edit</a></li>
	<li class="f2 mt12"><a href="javascript:;" class="tab_p4 tab_ad" id="adnext">Promote on <?php echo getWebSiteName(); ?></a></li>
	</ul>
	</div>
	<!--additional details preview:end--> 

	<!--add additional details form:start-->
	<div id="addef" class="p-irt" style="">
	  <p class="bulb">Additional product details help your product listing gain more visibility to interested buyers</p>
	  <p class="b-img arp1 pdb1 f1" href="JavaScript:;">Additional Details</p>
	  <div class="c3"></div>
	  <!--additional details preview:start -->
	  <div id="adpre" class="pad f1 mr" style="display:none;">
	    <ul>
	      <!--<li style="" class="f1 tc">Product Code</li><li class="f1 pct" id="view_ad_item_code">0024568</li>-->
	      <li class="f1 tc">Product Status</li>
	      <li class="f1 pct" id="view_hotnew">-</li>
	      <li class="f1 tc">Payment Terms</li>
	      <li class="f1 pct pbi" id="view_payment_terms">L/C (Letter of Credit),D/A,T/T (Bank Transfer),Other</li>
	      <li class="f1 tc">FOB / Wholesale Price</li>
	      <li class="f1 pct" id="view_fob_price">12000 INR</li>
	      <li class="f1 tc">Minimum Order Quantity</li>
	      <li class="f1 pct" id="view_moq">5 Barrels</li>
	      <li class="f1 tc">Port of Dispatch</li>
	      <li class="f1 pct" id="view_port_of_dispatch">kkk</li>
	      <li class="f1 tc">Production Capacity</li>
	      <li class="f1 pct" id="view_prod_cap">ok</li>
	      <li class="f1 tc">Delivery Time</li>
	      <li class="f1 pct" id="view_delivery_time">5.04</li>
	      <li class="f1 tc">Packing Details </li>
	      <li class="f1 pct pns7" id="view_packaging_det">aerftgwertgewrtgergg</li>
	      <li class="f1 tc">Product PDF Brochure</li>
	      <li class="f1 pct mb12" id="view_file_doc">-</li>
	      <li class="f1 c3 mt12"><a id="adedit" class="saps f1 f11 fw" href="javascript:;">Edit</a></li>
	      <li class="f2 mt12"><a href="javascript:;" class="tab_p4 tab_ad" id="adnext">Promote on <?php echo getWebSiteName(); ?></a></li>
	      </ul>
	    </div>
	  <!--additional details preview:end-->
	  <!--add additional details form:start-->
	  <div style="display:none;" id="adfrmjj">
	    <form method="post" name="additional_details" onsubmit="return save_additional();">
	      <div class="pad f1 mr">
	        <ul>
	          <!--<li style="" class="f1 tc adtp">Product Code</li><li class="f1 pct"><input type="text" class="a_f adt" name="ad_pc_item_code" id="ad_pc_item_code" maxlength="100" value="0024568" onblur="check_duplicate_item_code('ad_pc_item_code')"/></li>-->
	          <li class="f1 tc adtp">Product Status</li>
	          <li class="f1 pct tc1">
	            <input class="rad" name="prd_type" value="H" id="hot" type="radio" />
	            Hot
	            <input name="prd_type" value="D" id="default" checked="checked" onclick="return makelive(0)" type="radio" />
	            Default</li>
	          <input name="hotnew" id="hotnew" value="" type="hidden" />
	          <li class="f1 tc adtp">Payment Terms</li>
	          <li class="f1 pct pbi tc1">
	            <input checked="checked" class="cb1" name="payment_terms" value="L/C (Letter of Credit)" type="checkbox" />
	            L/C (Letter of Credit)
	            <input checked="checked" class="cb1" name="payment_terms" value="D/A" type="checkbox" />
	            DA
	            <input class="cb1" name="payment_terms" value="D/P" type="checkbox" />
	            d/p <br />
	            <input checked="checked" class="cb1" name="payment_terms" value="T/T (Bank Transfer)" type="checkbox" />
	            T/T (Bank Transfer)
	            <input class="cb1" name="payment_terms" value="Western Union" type="checkbox" />
	            Western Union
	            <input checked="checked" class="cb1" name="payment_terms" value="Other" type="checkbox" />
	            Other </li>
	          <input name="in_pc_item_payment_terms" id="in_pc_item_payment_terms" value="L/C (Letter of Credit),D/A,T/T (Bank Transfer),Other" type="hidden" />
	          <li class="f1 tc adtp">FOB / Wholesale</li>
	          <li class="f1 pct">
	            <input class="a_f adt1" name="in_pc_item_fob_price" id="in_pc_item_fob_price" maxlength="20" value="12000" type="text" />
	            <select size="1" name="fob_price_currency" id="fob_price_currency" class="a_f s_u">
	              <option value="">-Select Currency-</option>
	              <option selected="selected" value="INR">INR</option>
	              <option value="USD">USD</option>
	              <option value="GBP">GBP</option>
	              <option value="RMB">RMB</option>
	              <option value="EUR">EUR</option>
	              <option value="AUD">AUD</option>
	              <option value="CAD">CAD</option>
	              <option value="CHF">CHF</option>
	              <option value="JPY">JPY</option>
	              <option value="HKD">HKD</option>
	              <option value="NZD">NZD</option>
	              <option value="SGD">SGD</option>
	              <option value="NTD">NTD</option>
	              <option value="Other">Other</option>
	              </select>
	            </li>
	          <input name="in_pc_item_fob_price_currency" id="in_pc_item_fob_price_currency" value="INR" type="hidden" />
	          <li class="f1 tc adtp">Minimum Order Quantity</li>
	          <li class="f1 pct">
	            <input class="a_f adt1" name="in_pc_item_min_order_quantity" id="in_pc_item_min_order_quantity" maxlength="20" value="5" type="text" />
	            <select size="1" name="moq_unit_type" id="moq_unit_type" class="a_f s_u">
	              <option value="">-Select Unit Type-</option>
	              <!--option value="Bag/Bags">Bag/Bags</option>
		<option value="Barrel/Barrels">Barrel/Barrels</option>
		<option value="Bushel/Bushels">Bushel/Bushels</option-->
	              <option value="Bag">Bag</option>
	              <option value="Bags">Bags</option>
	              <option value="Barrel">Barrel</option>
	              <option selected="selected" value="Barrels">Barrels</option>
	              <option value="Bushel">Bushel</option>
	              <option value="Bushels">Bushels</option>
	              <option value="Cubic Meter">Cubic Meter</option>
	              <option value="Dozen">Dozen</option>
	              <option value="Gallon">Gallon</option>
	              <option value="Gram">Gram</option>
	              <option value="Kilogram">Kilogram</option>
	              <option value="Kilometer">Kilometer</option>
	              <option value="Long Ton">Long Ton</option>
	              <option value="Litre">Litre</option>
	              <option value="Meter">Meter</option>
	              <option value="Metric Ton">Metric Ton</option>
	              <option value="Ounce">Ounce</option>
	              <option value="Pair">Pair</option>
	              <!--option value="Pack/Packs">Pack/Packs</option>
		<option value="Piece/Pieces">Piece/Pieces</option-->
	              <option value="Pack">Pack</option>
	              <option value="Packs">Packs</option>
	              <option value="Piece">Piece</option>
	              <option value="Pieces">Pieces</option>
	              <option value="Pound">Pound</option>
	              <!--option value="Set/Sets">Set/Sets</option-->
	              <option value="Set">Set</option>
	              <option value="Sets">Sets</option>
	              <option value="Short Ton">Short Ton</option>
	              <option value="Square Meter">Square Meter</option>
	              <option value="Ton">Ton</option>
	              </select>
	            </li>
	          <input name="in_pc_item_moq_unit_type" id="in_pc_item_moq_unit_type" value="Barrels" type="hidden" />
	          <li class="f1 tc adtp">Port of Dispatch</li>
	          <li class="f1 pct">
	            <input class="a_f adt" name="in_pc_item_port_of_dispatch" id="in_pc_item_port_of_dispatch" maxlength="100" value="kkk" type="text" />
	            </li>
	          <li class="f1 tc adtp">Production Capacity</li>
	          <li class="f1 pct">
	            <input name="in_pc_item_production_capacity" id="in_pc_item_production_capacity" maxlength="100" value="ok" class="a_f adt" type="text" />
	            </li>
	          <li class="f1 tc adtp">Delivery Time</li>
	          <li class="f1 pct">
	            <input class="a_f adt" name="in_pc_item_delivery_time" id="in_pc_item_delivery_time" maxlength="100" value="5.04" type="text" />
	            </li>
	          <li class="f1 tc adtp">Packing Details </li>
	          <li class="f1 pct">
	            <textarea rows="5" class="a_f" name="in_pc_item_packaging_details" id="in_pc_item_packaging_details" onkeyup="limiter('Charcount1','in_pc_item_packaging_details');">aerftgwertgewrtgergg</textarea>
	            <font id="Charcount1" color="#ff8000">20 character (maximum of 2000)</font> character(s).</li>
	          <li class="f1 tc adtp"title="Attach PDF Brochure"> فقط    PDF    حمل بروشور صيغة   </li>
	          <li class="f1 pct1 mt5">
	            <!--disabled by webxtor iframe src="images/upload-prd-doc.htm" border="0" framespacing="0" allowtransparency="true" scrolling="no" width="269" frameborder="0" height="30"> </iframe-->
	            <span class="f2" id="indecator_gif0" style="left:37px;position:relative;top:-32px;"></span> (.pdf type attachment only) </li>
	          <input name="item_doc" value="" id="myfile_doc_form0" type="hidden" />
	          <li class="f1 tc adtp"> </li>
	          <li>
	            <input name="updateaddi" class="saps awt mt12 m5"title=" إحفظ التفاصيل الإضافية " value="Save Details" type="submit" />
	            <span style="display:none;" id="adsaving"> <img alt="" src="images/loading.gif" border="0" width="16" height="11" /></span>
	            <input name="cancleaddi" class="saps mt12 ml8" value="الغـــاء " id="adcls" type="button" />
	            </li>
	          </ul>
	        </div>
	      <div class="pia f1 mt5">
	        <div class="fw mb5">Large Image</div>
	        <p id="old_img_form_1"></p>
	        <div id="add_large_image" class="db cssie3 mover">
	          <!--disabled by webxtor iframe framespacing="0" marginwidth="0" marginheight="0" style="margin-top:2px;" src="images/upload-image-prd-small_002.htm" class="mt5" scrolling="NO" width="123" frameborder="0" height="123"></iframe-->
	          </div>
	        <div id="remove_large_image" class="dn mt5"> <a href="javascript:remove_large_image();"><img src="images/remove.gif" align="absmiddle" width="44" height="10" /></a> </div>
	        </div>
	      <input name="in_pc_item_img_large_wh" id="in_pc_item_img_large_wh" value="" type="hidden" />
	      <input name="in_pc_item_img_original_wh2" id="in_pc_item_img_original_wh2" value="360,92" type="hidden" />
	      <input name="in_pc_img_small_500x500_wh2" id="in_pc_img_small_500x500_wh2" value="360,92" type="hidden" />
	      <input name="item_img_large" id="item_img_large" value="" type="hidden" />
	      <input name="item_img_large_500x500" id="item_img_large_500x500" value="" type="hidden" />
	      <input name="item_img_large_500x500_wh" id="item_img_large_500x500_wh" value="" type="hidden" />
	      <input name="item_img_large_125x125" id="item_img_large_125x125" value="" type="hidden" />
	      <input name="item_img_large_125x125_wh" id="item_img_large_125x125_wh" value="" type="hidden" />
	      <input name="hot_cnt" id="hot_cnt" value="0" type="hidden" />
	      </form>
	    <div class="c3"></div>
	    </div>
	  <!--add additional details form:end-->
	  <!--loading:start-->
	  <div style="display: none;" id="lo" class="f11 fw loi" align="center"><img alt="Please wait" src="images/indicator.gif" width="16" height="16" /> Please Wait...</div>
	  <!--loading:end-->
	  </div>
	<!--add additional details form:end-->
	
	<!--loading:start-->
	<div style="display: none;" id="lo" class="f11 fw loi" align="center"><img alt="Please wait" src="edit%20product_files/indicator.gif" width="16" height="16"> Please Wait...</div>
	<!--loading:end-->
	
	</div>  
	<!--add additional details container:ends-->
	</div>
		<div class="back-n fw"> <a href="product-list.php"title="Go Back to Manage Products">الرجوع لقائمة المنتجات المنشورة >> </a>
		<br><br>
		</div>
		<div class="c3"></div>
		</div>
		<!--manage product:end-->
		<input id="for" value="" type="hidden"> 
	</div></div>
		<div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div>
        <div class="c3">&nbsp;</div></div><br><br><br>
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>