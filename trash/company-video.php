<?php
include 'common.php';

if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	$_SESSION['last_page']="company-video.php";
	header("Location:sign-in.php");
}

$uid=$_SESSION['uid_indm'];

$sql_bf="select * from business_profile where bnsprof_uid='".$uid."'";
$res_bf=mysqli_query($con, $sql_bf);
$row_bf=mysqli_fetch_object($res_bf);

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

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/about-us.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script>
$(document).ready(function(){
						   
	showVideoList(<?php echo $row_bf->bnsprof_id; ?>,1);
});
function openUploadArea()
{
	$("#upload_area").show();
	$("#add_new_video").hide();
}
function closeUploadArea()
{
	$("#upload_area").hide();
	$("#add_new_video").show();
}
function saveVideo()
{
	var cv_bnsprof_id=$("#cv_bnsprof_id").val();
	var vlink=$("#cv_video_link").val();
	
	if(vlink!='')
	{
		$.post("ajax-file/addCompanyVideo.php", {cv_bnsprof_id:cv_bnsprof_id,vlink:vlink},	function(data){
			$("#cv_video_link").val('');
			closeUploadArea();
			$("#video_disp").html('<img src="images/animated_loading.gif" />');
			alert('Video added successfully.');
			showVideoList(cv_bnsprof_id,1);
		});	
	
	}
	else
	{
	    alert('Please a valid embeded Youtube video Link.');
	}
}
function delVideo(id,cv_bnsprof_id)
{
	if(confirm("Are you sure to delete this Video?"))
	{
		$.post("ajax-file/delCompanyVideo.php", {cv_id:id},	function(data){	//alert(data);
			showVideoList(cv_bnsprof_id,1);
		});	
	}
}
function showVideoList(cv_bnsprof_id,page)
{
	$.post("ajax-file/showCompanyVideoList.php", {cv_bnsprof_id:cv_bnsprof_id,page:page},	function(data){
		$("#video_disp").html(data);
	});	
}

</script>

</head>

<body>
<div class="hm1 bbc" id="res-mob1">
	<?php include "includes/header_new.php"; ?>
	<br><br>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

<div class="inner_wrapper">
    <?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->
        <div class="w56 f1 p2b p14 blr" style="width:80%;hight:100%;"><div></div>
		<div class="c3"></div>
		<div>
		<div id="chg_name" class="f1 chng_a"><h1 class="f1" id="cpf_name">Company Video</h1></div><p id="pf_change" style="display:none;float:left;margin-top:0px"></p>
		<p class="f2 mt11 cnt_1" id="prof_cnt"></p>
		<div class="c3"></div>
		</div>
		
		<div class="clb px"></div> 
		<div class="" style="margin-top:4px;"><p class="aml"></p>
        <div id="re_link" class="utab">
        <span style="font-size: 12px;*float:left;">Add Video to your Company Profile</span>
        
        <a style="display: block;" class="f2 fw apr1" id="add_new_video" onclick="openUploadArea();">Add New Video</a>
        </div>
        
        <div id="upload_area" class="utab" style="background-color:#F5ECFF;display:none;height:auto">
        	<input type="hidden" id="cv_bnsprof_id" name="cv_bnsprof_id" value="<?php echo $row_bf->bnsprof_id; ?>" />
	        <span style="font-size: 12px;*float:left;margin-bottom:20px;">Youtube embeded video Link : (Maximum video size 640x360) 
            <textarea id="cv_video_link" name="cv_video_link" style="width:100%"></textarea></span>
        <div>
        <a style="display: block;" class="f2 fw apr1" id="can_upload" onclick="closeUploadArea();">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a style="display: block;" class="f2 fw apr1" id="edit_add" onclick="saveVideo();">Add Video</a>
        </div>
        </div>
		
        <div class="c3"></div>
				<div class="c3"></div>
                
                 
                <div id="video_disp" style="text-align:center;padding-top:10px;"></div>
            </div></div>
		<div class="c3">&nbsp;</div></div>
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>