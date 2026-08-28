<?php
include 'common.php';

if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	$_SESSION['last_page']="company-banner.php";
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
						   
	showBannerList(<?php echo $row_bf->bnsprof_id; ?>,1);
});
function openUploadArea()
{
	if($('#mpId').val() < 3){
		alert("You have to subscribe to premium membership to add Company Images");
		window.location.href="membership_plans.php";
	}
	else {
	$("#upload_area").show();
	$("#add_new_banner").hide();
	$("#can_upload").show();
	}
}
function closeUploadArea()
{
	$("#upload_area").hide();
	$("#add_new_banner").show();
	$("#can_upload").hide();
}

function delBanner(id,cb_bnsprof_id)
{
	if(confirm("Are you sure to delete this Banner?"))
	{
		$.post("ajax-file/delCompanyBanner.php", {cb_id:id},	function(data){	//alert(data);
			showBannerList(cb_bnsprof_id,1);
		});	
	}
}
function showBannerList(cb_bnsprof_id,page)
{
	$.post("ajax-file/showCompanyBannerList.php", {cb_bnsprof_id:cb_bnsprof_id,page:page},	function(data){
		$("#banner_disp").html(data);
	});	
}

</script>

</head>

<body>
<div class="hm1 bbc" id="res-mob1">
	<?php include "includes/header_new.php"; ?>
	<br><br>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

<?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->
        <div class="w56 f1 p2b p14 blr" style="width:80%;hight:100%;"><div></div>
		<div class="c3"></div>
		<div>
		<div id="chg_name" class="f1 chng_a"><h1 class="f1" id="cpf_name">Company Images </h1></div><p id="pf_change" style="display:none;float:left;margin-top:0px"></p>
		<p class="f2 mt11 cnt_1" id="prof_cnt"></p>
		<div class="c3"></div>
		</div>
		
		<div class="clb px"></div> 
		<div class="" style="margin-top:4px;"><p class="aml"></p>
        <div id="re_link" class="utab">
        <span style="font-size: 12px;*float:left;">Add Company Images to your Company Profile</span>
        
        <a style="display: block;" class="f2 fw apr1" id="add_new_banner" onclick="openUploadArea();">Add Image </a>
		<input type="hidden" value="<?php echo getUserInfo($uid, 'usr_mp_id'); ?>" id="mpId" />
        <a style="display: none;" class="f2 fw apr1" id="can_upload" onclick="closeUploadArea();">Cancel</a>
        </div>
        
        <div id="upload_area" class="utab" style="background-color:#FAF4FF;display:none;height:auto">
        	<input type="hidden" id="cv_bnsprof_id" name="cb_bnsprof_id" value="<?php echo $row_bf->bnsprof_id; ?>" />
	        <span style="font-size: 12px;*float:left;margin-bottom:20px;">Preferred image height & width is 200px and 200px respectively.
            <b>
			<script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
			<script type="text/javascript">
				jQuery(function(){
					jQuery('#banner_upload').uploadifive({
						'auto'     : true,
						'formData' : {'id' : '<?php echo $row_bf->bnsprof_id; ?>'},
						'queueID'  : 'queue',
						'debug'    : true,
						'method'   : 'post',
						'uploadScript' : 'ajax-file/uploadCompanyBanner.php',
						'onAddQueueItem' : function(file) {
							//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
							//$("#drop").html('<img src="images/loading.gif" alt="Uploading...." />');
						},
						'onUploadComplete' : function(file,data) {
							showBannerList(<?php echo $row_bf->bnsprof_id; ?>,1);
						}
					});
				});
			</script>
            <div id="drop" style="padding-left:10px;">
            <input type="file" id="banner_upload" name="file_upload"/>
            </div>
            <div id="queue"></div>
                </b>
            </span>
            
        <div>
       
        </div>
        </div>
        <div class="c3"></div>
				<div class="c3"></div>
                
                 
                <div id="banner_disp" style="text-align:center;padding-top:10px;"></div>
            </div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>