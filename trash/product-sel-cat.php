<?php
ob_start();
session_start();
include 'common.php';

$_SESSION['last_page']="product-sel-cat.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	
	header("Location:sign-in.php");	
}	
if(get_membership_expired()!=true){ 

	 header("Location:membership_plans.php");
 }
$uid=$_SESSION['uid_indm'];
if(getUserInfo($uid,'user_type') <= 1) {
header("Location:sign-in.php");	
//header("Location:membership_plans.php");	
}

$sqlImg_del="select * from temp_product_image where tpi_usr_id='".$uid."'";
$resImg_del=mysqli_query($con,$sqlImg_del);
if(mysqli_num_rows($resImg_del)>0)
{
	$rowImg_del=mysqli_fetch_object($resImg_del);
	$path = "upload/myproduct/".$rowImg_del->tpi_image;
	if(is_file($path))
	{
		unlink($path);
	}
	mysqli_query($con,"delete from temp_product_image where tpi_usr_id='".$uid."'");
	
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- <link href="css/pro.css" type="text/css" rel="stylesheet"> -->
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

function showCategory(id)
{
	//var pc_id=document.getElementById('mcat_id').value;
//	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#cat_id').html(data);	showsubcat();	}); 

$.post("ajax-file/showSelectCategory.php", {id:id},	function(data){	

		newData=data.split('|');
		
		if(newData[0]){
			$("#cat_id").html(newData[0]);
			$("#cat_id").css({"display":"block"});
		}
		else{
			$("#cat_id").html('');
			$("#cat_id").css({"display":"none"});
		}
		$("#subcat_id").css({"display":"none"});
		$("#mc").html(newData[1]);
		$("#c").html('');
		$("#sc").html('');
		$("#categroyPath").css({"display":"block"});
		$("#catePathSucc").css({"display":"none"});
//		$("#categorySubmit").css({"display":"none"});
//		$("#categorySubmitDisable").css({"display":"block"});
	});
}
function showsubcat(id)
{ 
 	//$.post("ajax-file/chkSubcat.php",{cid:cid}, function(data){	$('#sbcat').html(data);	}); 
	
	//var pc_id = $('select#cat_id option:selected').val();
//	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#subcat_id').html(data);	}); 

	$.post("ajax-file/showSelectCategory.php", {id:id},	function(data){	
		newData=data.split('|');
		if(newData[0]){
		$("#subcat_id").html(newData[0]);
		$("#subcat_id").css({"display":"block"});
		}
		else{
			$("#subcat_id").html('');
			$("#subcat_id").css({"display":"none"});	
		}
		$("#c").html('&nbsp;&gt;&gt;&nbsp;'+newData[1]);
		$("#sc").html('');
		$("#catePathSucc").css({"display":"none"});
//		$("#categorySubmit").css({"display":"none"});
//		$("#categorySubmitDisable").css({"display":"block"});
		
	});
 }
 function showNextBtn(id)
{
	
	$("#pd_subcat_id").val(id);
	$.post("ajax-file/showSelectCategory.php", {id:id},	function(data){	
		newData=data.split('|');
		
		$("#sc").html('&nbsp;&gt;&gt;&nbsp;'+newData[1]);
		$("#catePathSucc").css({"display":"block"});
	//	$("#categorySubmitDisable").css({"display":"none"});
		$("#div_save").css({"display":"block"});

	});
}
function addAlertCategory()
{
	
	/*$.post("ajax-file/addTempBuyleadAlertCat.php",{id:$("#pd_subcat_id").val(),},    function(data){	});
	
	$.post("ajax-file/addBuyleadAlertCat.php",{},    function(data){  });	*/
}
 </script>
 
    
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

 
<!-- Header End Here::-->
<div class="inner_wrapper">
    <?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
		<?php include 'includes/left_menu.php';?>
<style>
.tabopen{
  background-color: #6500CA;
  color: #fff;
}
.tabclose{
  background-color: #fff;
  background: #fff;
  color: #000;
}
.top-bar ul li{vertical-align: top;}





</style>
  <script>
        function searchcat()
{
	$("#scs").removeClass("tabclose").addClass("tabopen");
	$("#bcs").removeClass("tabopen").addClass("tabclose");

	$("#searchContent").css("display","none");
	$("#searchContentcc").css("display","block");
}
function beowswcat()
{
	$("#bcs").removeClass("tabclose").addClass("tabopen");
	$("#scs").removeClass("tabopen").addClass("tabclose");

	$("#searchContentcc").css("display","none");
	$("#searchContent").css("display","block");
}
        </script>

		<!--left navigation:ends-->  
        <div class="w56b f1 p2b p14 blr"><div class="mt5"><h1 style="margin-top:-5px !important;padding-bottom:5px;">Select Product / Service Category </h1>
        <!--manage products/groups:start-->
		<div class="ap1">
	  	<a href="javascript:searchcat()" id="scs" class="f1 ap2  tabclose">Search Categories</a>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   	<a id="bcs" href="javascript:beowswcat()" style="margin-left: 26px;" class="f1 ap2 bc">Browse Categories</a>
		<p class="f2 mt12" id="page_str"></p>
		<div class="c3"></div>
		</div>

	<div class="apfc">



		
		<div class="fside f1" style="width:100%;">
			
	<div style="background-color:#ECECFF" id="searchContent" class="box">



			
			<div class="boxShadow" style="clear:both;margin-top:10px;">
			<div class="boxIn">
				<div id="browseCateContent" style="padding: 5px;">
					<div class="searchTips" style="display:block; padding-bottom:10px;">
						Select Categories from the list below
					</div>
					<div id="selectListCate" class="selectListCate">
    <div style="width:100%;" id="multiSelectList" class="clearfix multiSelectList">
    <?php
		$sql_mc="select * from product_category_arabyos where pc_status='1' and pc_parent_id='0' order by pc_name";
		$res_mc=mysqli_query($con,$sql_mc);
	?>
	<?php  $people=['JUNIOR','SENIOR','SPONSER']; if(in_array(strtok($icon['mst_name']," "), $people)){$selected_member='class="mybs"';}else{$selected_member='';} ?>
    <div style="width:32%;float:left;margin-right:0px;overflow-x:auto">
          <select name="mc_id" tabindex="1" class="column" size="10" id="mc_id" style="height:214px;width: 280px;" onChange="showCategory(this.value)">
            <?php while($row_mc=mysqli_fetch_object($res_mc)) {
					$selected = '';
					if(@$_GET['select'] == 'bs'){
						if(ucfirst($row_mc->pc_name)=='Business Services'){
							$selected = 'selected="selected"';
						}
					}
			?>
			
				<option value="<?php echo $row_mc->pc_id;  ?>" <?php echo $selected; ?><?php if(ucfirst($row_mc->pc_name)=='Business Services'){echo $selected_member;} ?>><?php echo ucfirst($row_mc->pc_name); ?></option>
			<?php } ?>
            </select>
            </div>
    <div style="width:33%;float:left;overflow-x:auto">
          <select name="cat_id" tabindex="2" class="column" size="10" id="cat_id" style="height: 214px; width: 280px;display:none;" onChange="showsubcat(this.value)">
            </select>
             </div>
    <div style="width:33%;float:left;overflow-x:auto">
          <select name="subcat_id" tabindex="3" class="column" size="10" id="subcat_id" style="height: 214px; width: 250px;display:none" onchange="showNextBtn(this.value);">
            </select>
    </div>
            
    </div>
</div>
				</div>
				
				
			</div>
			</div>
			
            <div id="categroyPathWrap" class="J-show" style="margin: 10px 0pt;">
		<div id="categroyPath" class="categroyPath" style="float:left;color:#99370B;font-weight:bold;width:auto;background: #F2F2FF;border:#7979FF solid 1px;padding: 7px; margin-top:10px;display:none;">
			<div style="color:#000;float:left;font-weight:bold;margin-right:10px;">Categories Selected:</div>			
				
                
                <span id="catePathText" style="float:left;">
                	<span id="mc"></span><span id="c"></span><span id="sc"></span>
                </span>
				
                <span style="margin-left: 5px; float: left;display:none;" id="catePathSucc"><img src="images/successB.gif" align="absmiddle"></span>
				<a onclick="return false;" id="changeCategoryBtn" style="display: none;" href="javascript:void(0);">Select New Category</a>
			<div style="clear:both;"></div>
		</div>
		<div style="clear:both;"></div>
			</div>
            
			
			
		</div>
			
            
		  	<div style="background-color:#ECECFF" id="searchContentcc" class="box">




			<div class="boxShadow" style="clear:both;margin-top:10px;">
			<div class="boxIn">
				<div id="browseCateContent" style="padding: 5px;">
					<div class="searchTips" style="display:block; padding-bottom:10px;">
						Select Categories from the list below
					</div>
					<div id="selectListCate" class="selectListCate">
    <div style="width:100%;" id="multiSelectList" class="clearfix multiSelectList">
      	<form action="product-add.php" method="POST" id="select_category" name="select_category" enctype="multipart/form-data">
    <div style="width:100%;float:left;margin-right:0px;">
            <input class="column" style="width: 550px;" type="text" name="search_product_cat" id="search_product_cat">
            <input type="submit" name="search" value="Next">
    </div>
    </form>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
	
			
            
			
	
			
            
				<div class="fs1">&nbsp;</div>
				<div style="margin:5px 0"></div>	
			</div>
			<form action="product-add.php" method="POST" id="select_category" name="select_category" enctype="multipart/form-data">
			<div id="div_save" style="display:none">
            <input type="hidden" id="pd_subcat_id" name="pd_subcat_id" value="" />
            <input name="btnSubmit" id="btnSubmit" class="c3 f2 saps mt12 mtt" style="margin-top:-35px;margin-right:95px;margin-top:-152px;" value=" Next >>" type="submit" onClick="addAlertCategory();">
            
            </div>
			</form>
		
		<div class="c3"></div>
	</div>
	<!--add product form:end-->
	</div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->


        <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" src="js/jquery.autocomplete2.js"></script>
<script type="text/javascript">
/*$( document ).ready(function() {
  showCategory('131');
 $('#mc_id option[value=131]').attr('selected','selected');
});*/
</script>

<script type="text/javascript">
$(document).ready(function(){
setTimeout(function(){
$(".mybs").attr("selected","true");
$( ".mybs" ).change();
	}, 1000);
	lostFocus();
	$('#search_product_cat').unbind().on('keyup',function() {
		var type11='Products';
		$("#search_product_cat").autocomplete("autocomplete.php", {
			selectFirst: true,
			extraParams: {type:type11},
			width: 550
		})
		.result(function(event, data, formatted) {
 			$("input#search_product_cat").val(data);
		});
	});
	<?php 
	if(@$_GET['select'] == 'bs'){ ?>
	showCategory(131);
	<?php } ?>
});
</script>
</div>

		<?php include 'includes/footer.php'; ?>