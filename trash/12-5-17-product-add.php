<?php
ob_start();
session_start();
include 'common.php';

$_SESSION['last_page']="product-add.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];

if(!isset($_POST['search_product_cat'])){
if(!isset($_POST['pd_subcat_id']) || $_POST['pd_subcat_id']=='0' || $_POST['pd_subcat_id']=='' )
{
	header("Location:product-sel-cat.php");
}
}else{
 $searchedproducts = $_SESSION['searchedproducts'];

if(!$searchedproducts && !array_key_exists($_POST['search_product_cat'],$searchedproducts))  {
   header("Location:product-sel-cat.php");
exit();
}


$_POST['search_product_cat'] = end(explode(">>",$_POST['search_product_cat']));
$id = $searchedproducts[$_POST['search_product_cat']];
if(!$id){
     header("Location:product-sel-cat.php");
exit();
}
$_POST['pd_subcat_id']= $id;
}

$sqlImg_del="select * from temp_product_image where tpi_usr_id='".$uid."'";
$resImg_del=mysql_query($sqlImg_del);
if(mysql_num_rows($resImg_del)>0)
{
	$rowImg_del=mysql_fetch_object($resImg_del);
	$path="upload/myproduct/".$rowImg_del->tpi_image;
	if(is_file($path))
	{
		unlink($path);
	}
	mysql_query("delete from temp_product_image where tpi_usr_id='".$uid."'");
	
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

<link href="css/pro.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    // Change this to the location of your server-side upload handler:
    var url ='http://arabyos.com/server/php/';
    jQuery('#file_upload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
				jQuery.post("companylogo-update.php", {'uid' :'<?php echo $uid; ?>', 'file' : file.name }, function(data) {
						list_photo();	
				});

            });
        }
       
    });
});
$(document).ready(function(){
	showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
});
var imageBasket = [];
var imageBasketlogo = [];
//console.log(imageBasket);

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
 
 
 function usePhotoToUploadlogo(id){
    if(jQuery.inArray(id,imageBasketlogo ) != -1){
   
  imageBasketlogo = $.grep(imageBasketlogo , function(value) {
  return value != id;
    });
  }else{
    imageBasketlogo .push(id);
    }
 
 //alert(imageBasketlogo);

 }
function usePhoto(id)
{

        var imgArr = imageBasket;
	var tbl='temp_product_image';
	var typ='product';
	var usr=document.getElementById('uid').value;
	//console.log(imgArr);
	$.post("ajax-file/addNewImgFrmGallery.php", {imgArr:imgArr,usr:usr,tbl:tbl,typ:typ}, function(data){
console.log(data);
		 jQuery('#cboxOverlay').remove();
    jQuery('#colorbox').remove();

		$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
		
		/*setTimeout(function (){*/

		showTempPhoto(usr);

        /* }, 500);*/
	});
}

function usePhotoForLogo(id)
{         
        var imgArr = imageBasketlogo;
	var tbl='temp_product_image';
	var typ1='logo';
	var usr=document.getElementById('uid').value;
	$.post("ajax-file/addNewImgFrmGallery.php", {imgArr:imgArr,usr:usr,tbl:tbl,typ:typ1}, function(data){
console.log(data);
		 jQuery('#cboxOverlay').remove();
    jQuery('#colorbox').remove();
		$("#imglogo_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width: 43px;    height: 46px;"/>');
		
		/*setTimeout(function (){*/

		
		
		showTempPhotoLogo(usr);

        /* }, 500);*/
	});
}
function showTempPhoto(usr)
{
	$.get("ajax-file/showTempProductImage.php", {usr:usr},	function(data){
		$("#img_disp").html('');												 
		$("#img_disp").html('<img src="'+data+'" alt="" style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
	});
}

function showTempPhotoLogo(usr)
{
	$.get("ajax-file/showTempProductLogo.php", {usr:usr},	function(data){
		$("#imglogo_disp").html('');												 
		$("#imglogo_disp").html('<img src="'+data+'" alt="" style="width: 43px;    height: 46px;"/>');
	});
}
function mecount()
{
	if($.trim($("#pd_desc").val()) != ''){
	var cnt=$("#pd_desc").val().length;	
	$("#cn").html(cnt);
	}
}
mecount();
function showCategory()
{
	var pc_id=document.getElementById('mcat_id').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#cat_id').html(data);	showsubcat();	}); 
}
function showsubcat()
{ 
 	//$.post("ajax-file/chkSubcat.php",{cid:cid}, function(data){	$('#sbcat').html(data);	}); 
	
	var pc_id = $('select#cat_id option:selected').val();
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pd_subcat_id').html(data);	}); 
 }
 </script>
 
 <script type="text/javascript">
function additem2()
{
	var pd_subcat_id=document.getElementById('pd_subcat_id');
	var pd_title=document.getElementById('pd_title');
	var pd_code=document.getElementById('pd_code');
	var pd_desc=document.getElementById('pd_desc');
	var uid=document.getElementById('uid');
	
	var pd_min_order_qty=document.getElementById('pd_min_order_qty');
	var pd_unit=document.getElementById('pd_unit');
	var pd_fob_price=document.getElementById('pd_fob_price');
	var pd_fob_price2=document.getElementById('pd_fob_price2');
	var pd_currency=document.getElementById('pd_currency');
	
	var pd_preferred_buyer_location = $('input:radio[name=pd_preferred_buyer_location]:checked').val();
	var pd_status = $('input:radio[name=pd_hot]:checked').val();
    var message="";
    var valid=true;
	
   	if(pd_title.value=='')
	{
		message="Please enter the Product Name.";
		pd_title.focus();
		valid=false;
	}
	else if(pd_min_order_qty.value=='' || pd_min_order_qty.value==null || pd_min_order_qty.value=='0' || pd_min_order_qty.value=='00')
	{
		message="Please enter Minimum Order Quantity.";
		pd_min_order_qty.value='';
		pd_min_order_qty.focus();
		valid=false;
	}
	else if(isNaN(pd_min_order_qty.value))
	{
		message="Please enter valid Minimum Order Quantity.";
		pd_min_order_qty.value='';
		pd_min_order_qty.focus();
		valid=false;
	}
	else if(pd_unit.value=='')
	{
		message="Please choose Measurement Unit for Minimum Order Quantity.";
		pd_unit.focus();
		valid=false;
	}
	else if(pd_fob_price.value=='' || pd_fob_price.value==null || pd_fob_price.value=='0' || pd_fob_price.value=='00')
	{
		message="Please enter FOB Price.";
		pd_fob_price.value='';
		pd_fob_price.focus();
		valid=false;
	}
	else if(isNaN(pd_fob_price.value))
	{
		message="Please enter valid FOB Price.";
		pd_fob_price.value='';
		pd_fob_price.focus();
		valid=false;
	}
	else if(pd_fob_price2.value=='' || pd_fob_price2.value==null || pd_fob_price2.value=='0' || pd_fob_price2.value=='00')
	{
		message="Please enter FOB Price.";
		pd_fob_price2.value='';
		pd_fob_price2.focus();
		valid=false;
	}
	else if(isNaN(pd_fob_price2.value))
	{
		message="Please enter valid FOB Price.";
		pd_fob_price2.value='';
		pd_fob_price2.focus();
		valid=false;
	}
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
	else
	{
			
		$.post("ajax-file/productAdd.php", {pd_subcat_id:pd_subcat_id.value,pd_title:pd_title.value,pd_status:pd_status.value,pd_code:pd_code.value,pd_desc:pd_desc.value,uid:uid.value,pd_min_order_qty:pd_min_order_qty.value,pd_unit:pd_unit.value,pd_fob_price:pd_fob_price.value,pd_fob_price2:pd_fob_price2.value,pd_currency:pd_currency.value,pd_preferred_buyer_location:pd_preferred_buyer_location},function(data){	

			data=data.trim();
			dt=data.split("|")
			if(dt[0]=='0')
			{
				document.getElementById('updatemessage').style.display="block";
				document.getElementById('updatemessage').style.color = "red";
				document.getElementById('updatemessage').innerHTML = dt[1];
			}
			else
			{
				alert(dt[1]);
				showTempPhoto(uid.value);
				window.location.reload();
			}																  
		});
	}
	if(!valid)
	{
		document.getElementById('updatemessage').style.color = "red";
		document.getElementById('updatemessage').innerHTML = message;	
	}
	return valid;
}
</script>
    
</head>

<body>
<input type="hidden" value='' id='tempPhoto'/>
<input type="hidden" value='' id='tempLogo'/>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

 
<!-- Header End Here::-->
<?php include 'includes/header_menu.php';?> 
		<!--left navigation:start-->
		<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->  
        <div class="w56b f1 p2b p14 blr"><div class="mt5"><h1 style="margin-top:-5px !important;padding-bottom:5px;">Manage Products</h1>
        <!--manage products/groups:start-->
		<div class="ap1">
		<a href="product-list.php" class="f1 ap2">Products</a>
         <p class="f1 loading" style="display:none;" id="loading"><img src="images/my2-loading.gif" class="loading_m2">&nbsp;Loading...&nbsp;</p>
		<p class="f2 mt12" id="page_str"></p>
		<div class="c3"></div>
		</div>
		
	<!--you are here:start-->
			<p class="urh"><span class="f1" style="font-size:14px;"></span>&nbsp;&nbsp;<strong style="color:#444444"></strong></p><!--add product form:start-->
	<div class="apfc">

		<div class="pia f1">
		<div style="position:absolute" id="tempwarning_add"></div>
		<p id="img_form" style="margin-top: 2px;"></p>
		<div id="img_disp" class="cssie1 mover">
		<!--<iframe framespacing="0" marginwidth="0" marginheight="0" style="margin-top:2px;" src="upload-product-image.php" class="mt5" scrolling="NO" width="123" frameborder="0" height="123"></iframe>-->
        <img src="upload/myproduct/add-image.gif" title="ADD Product Image" style="width:125px;height:125px;margin-top:1px;margin-left:1px;">
		</div>
        
        <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
				<script type="text/javascript">
					jQuery(function(){
						jQuery('#product_upload').uploadifive({
							'auto'     : true,
							'formData' : {'usr' : '<?php echo $_SESSION['uid_indm']; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'ajax-file/addTempProductImg.php',
							'onAddQueueItem' : function(file) {
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
								$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
							},
							'onUploadComplete' : function(file,data) {
								showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
							}
						});
					});
				</script>
                <div>
                <div id="drop" style="font-width:200">
		            <input type="file" id="product_upload" name="product_upload" style="font-width:200" title="ADD Product Image"/>
	            </div>
	            <div id="queue"></div>
                </div>
                <div>
                <link rel="stylesheet" href="css/colorbox.css" />
					<script src="js/jquery.colorbox.js"></script>
                    <script>
						$(document).ready(function(){
							//Examples of how to assign the ColorBox event to elements
							$('.ajax').on('click', function() {
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
            <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;" title="ADD Product Image">Select from Image Gallery</a>
                </div>
                
		<div id="remove_image" class="dn mt5">
			<a href="javascript:remove_small_image('add');"><img src="images/remove.gif" align="absmiddle" width="44" height="10"></a>
		</div>

                <!-- new file-->
                
                		<div style="position:absolute" id="tempwarning_add"></div>
		<p id="img_form" style="margin-top: 50px;"></p>
		<div id="imglogo_disps" class="cssie1 mover" style="position: relative;">
		<!--<iframe framespacing="0" marginwidth="0" marginheight="0" style="margin-top:2px;" src="upload-product-image.php" class="mt5" scrolling="NO" width="123" frameborder="0" height="123"></iframe>-->
        <img src="upload/myproduct/logo_upload.jpg" title="ADD Logo,Brand,Discount,Sign" style="width:125px;height:125px;margin-top:1px;margin-left:1px;">
<div id="imglogo_disp" class="cssie1 mover" style="width: 43px;    height: 46px;left: 5px;bottom: 4px;position: absolute;">
<img src="upload/myproduct/logo_uploadmini.jpg" title="ADD Logo,Brand,Discount,Sign" style="width: 43px;    height: 46px;">
</div>
		</div>

        <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
				<script type="text/javascript">
					jQuery(function(){
						jQuery('#productlogo_upload').uploadifive({
							'auto'     : true,
							'formData' : {'usr' : '<?php echo $_SESSION['uid_indm']; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'ajax-file/addTempProductImglogo.php',
							'onAddQueueItem' : function(file) {
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
								$("#imglogo_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width: 43px;    height: 46px;"/>');
							},
							'onUploadComplete' : function(file,data) {
								showTempPhotoLogo(<?php echo $_SESSION['uid_indm']; ?>);
							}
						});
					});
				</script>
                <div>
                <div id="drop" style="font-width:200">
		            <input type="file" id="productlogo_upload" name="productlogo_upload" style="font-width:200" title="ADD Logo,Brand,Discount,Sign"/>
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
            <a class="ajaxa" href="popup-imagegallery-logo.php" style="text-decoration:none;" title="ADD Logo,Brand,Discount,Sign ">Select from Image Gallery</a>


                </div>
            
		<div id="remove_image" class="dn mt5">
			<a href="javascript:remove_small_image('add');"><img src="images/remove.gif" align="absmiddle" width="44" height="10"></a>
		</div>
                </div>

		<form action="" method="POST" id="add_new" name="add_new" enctype="multipart/form-data">
		<div class="fside f1">
        <div id="updatemessage" style="margin-bottom:5px;"><?php echo $msg; ?></div>
            
            
            <!--<?php /*if(isset($cat_id) && $cat_id != ''){*/ ?><script> showsubcat('<?php /*echo $cat_id;*/ ?>');</script><?php /*}*/ ?>
            <div id="sbcat"></div>-->
            <input type="hidden" id="pd_subcat_id" name="pd_subcat_id" value="<?php echo $_POST['pd_subcat_id']; ?>" />
            
			<div class="fs1 f1">
			<p><span style="line-height: 12px;">*</span> Product Name</p>
			<input name="pd_title" id="pd_title" maxlength="60" class="a_f pf1" type="text" value="<?php echo $pd_title;?>">
			<span>Please enter the Product Name here.</span>
			</div>
	
			<div class="fs2 f1">
			<p>Product Item Code</p>
			<input name="pd_code" id="pd_code" maxlength="60" class="a_f pf1" onblur="check_duplicate_item_code()" type="text" value="<?php echo $pd_code?>">
			<span>Please enter the Product / Item code here.</span>
            <input type="hidden" name="uid" id="uid" value="<?php echo $uid; ?>" />
			</div>
            
			<div class="fs1 f1">
			<p><span style="line-height: 12px;">*</span> Minimum Order Quantity</p>
			<input name="pd_min_order_qty" id="pd_min_order_qty" maxlength="60" class="a_f pf1" type="text" value="<?php echo $pd_min_order_qty;?>">
			<span>Please enter the Minimum Order Quantity here.</span>
			</div>
	
			<div class="fs2 f1">
			<p>&nbsp;</p>
			<select size="1" name="pd_unit" id="pd_unit" class="a_f s_u">
              <option value="">-Select Unit Type-</option>
              <?php                
				$unitsql=mysql_query("select * from measurement_unit where mu_status='1'");
				while($unitrow=mysql_fetch_object($unitsql)){
				?>
              <option value="<?php echo $unitrow->mu_id;?>" <?php if($pd_unit==$unitrow->mu_id){ ?> selected="selected" <?php } ?> ><?php echo $unitrow->mu_name;?></option>
				<?php } ?>
	        </select>
			</div>
            
			<div class="fs1 f1">
			<p><span style="line-height: 12px;">*</span> FOB Price</p>
			From : <input name="pd_fob_price" id="pd_fob_price" maxlength="60" class="a_f pf1" type="text" value="<?php echo $pd_fob_price;?>">
            To : <input name="pd_fob_price2" id="pd_fob_price2" maxlength="60" class="a_f pf1" type="text" value="<?php echo $pd_fob_price2;?>">
            
			<span>Please enter the FOB Price here.</span>
			</div>
	
			<div class="fs2 f1">
			<p>&nbsp;</p>
			<select size="1" name="pd_currency" id="pd_currency" class="a_f s_u">
                <option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysql_query("select * from country where cn_status='1'");
				while($currencyrow=mysql_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if(user_info($uid,'country')== $currencyrow->cn_id){ ?> selected="selected" <?php } else if($pd_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
			</div>
            
            <div class="fs1 f1" style="padding-top:4px; width: 50% !important;float: left;">
			<p><span style="line-height: 12px;">*</span> Location Prefrences</p>
            <div style="vertical-align:middle">
            <input type="radio" id="pd_preferred_buyer_location_1" name="pd_preferred_buyer_location" value="abroad" /><label style="top:0px;">Abroad Only</label>
            </div>
            <div>
		         <input type="radio" id="pd_preferred_buyer_location_2" name="pd_preferred_buyer_location" value="any" checked="checked"/><label style="top:0px;">Abroad + Domestic</label>
			</div>
            <div>
	             <input type="radio" id="pd_preferred_buyer_location_3" name="pd_preferred_buyer_location" value="domestic"/><label style="top:0px;">Domestic Only</label>
    		</div>
            <div>
                 <input type="radio" id="pd_preferred_buyer_location_4" name="pd_preferred_buyer_location" value="my_city"/><label style="top:0px;">My City Only</label>
            </div>
			</div>
			 <div class="fs1 f1" style="padding-top:4px; width: 50% !important;float: left;">
			<div>
			<p><span style="line-height: 12px;">*</span>Product Status</p>
			
	            <input class="rad" name="pd_hot" value="1"  type="radio" >
	           <label>Mark as HOT</label>
	            <input name="pd_hot" value="0" id="pd_hot"  type="radio" checked="checked">
	            <label>Default</label>
			</div>
			</div>
			
            <div style="clear:both;line-height: 21px;*line-height: 17px;">&nbsp;</div>
				<div class="fs1">&nbsp;</div>
				<div style="margin:5px 0"></div>	
			</div>
			<div id="editor_loading" style="display: none; padding: 230px; height: 0px;">
				<img src="images/indicator.gif">&nbsp;Loading editor...
			</div>
			<div id="div_save">
            <input name="btnSubmit" id="btnSubmit" class="c3 f2 saps mt12 mtt" style="margin-top:-35px;margin-right:-1px; -webkit-margin-before:-33px;*margin-top:-35px;margin-top:-33px\9;" value="Add Product" type="button" onclick="additem2();">
            </div>
			<div class="c3 fs3" id="editor" style="">
				<p>Product Description</p>
				<textarea class="a_f" rows="15" id="pd_desc" name="pd_desc" onKeyUp="mecount();"><?php echo $pd_desc;?></textarea>
				<div class="max"><span id="cn" style="color:#ff8000">0</span><font id="Charcount" color="#ff8000"> character (maximum of 4000) </font> character(s).</div>
				<br>
			</div>
		</form>
		<div class="c3"></div>
	</div>
	<!--add product form:end-->
	</div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>