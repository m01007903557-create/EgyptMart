<?php
include 'common.php';
$uid=$_SESSION['uid_indm'];


$sql_wc="select * from website_content where wc_usr_id='".$uid."'";
$res_wc=mysql_query($sql_wc);
$row_wc=mysql_fetch_object($res_wc);

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
<link href="css/p.css" type="text/css" rel="stylesheet">
<link href="css/ap_p.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

    
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
 <div class="w56 f1 p2b p14 blr" style="width:80%">
 <script type="text/javascript" src="js/jquery.js"></script>
 <script type="text/javascript" src="js/jquery-ui.js"></script>
	<style>
	*{margin:0; padding:0;}
	#test-list{list-style:none;}
	#test-list li{display:block}
	#test-list li, .fid{padding:8px;background-color:#ffffff; margin-bottom:8px; width:97%;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;border:1px solid #e6e6e6;background-color:#ffffff;/*filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f6f6f6');background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f6f6f6));background:-webkit-linear-gradient(top, #ffffff, #f6f6f6);background:-moz-linear-gradient(top, #ffffff, #f6f6f6);background:-ms-linear-gradient(top, #ffffff, #f6f6f6);background:-o-linear-gradient(top, #ffffff, #f6f6f6)*/}
	#test-list li:hover{background:#fef8ea}
	#test-list li div.handle{cursor:move}
	#test-list li h4, .fid h4{font-size:12px; color:#0C5097}
	.mt2{margin-top:2px}
	.saps{border:1px solid #216ce3;color:#fff;text-decoration:none;font-size:12px; font-family:Arial, Helvetica, sans-serif; padding:5px;text-align:center;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;background-color:#4787ed;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#67a0ff), to(#4787ed));background:-webkit-linear-gradient(top, #67a0ff, #4787ed);background:-moz-linear-gradient(top, #67a0ff, #4787ed);background:-ms-linear-gradient(top, #67a0ff, #4787ed);background:-o-linear-gradient(top, #67a0ff, #4787ed)}
	.saps:hover{border:1px solid #216ce3;text-decoration:none;-webkit-box-shadow:0 5px 5px rgba(0,0,0,0.2);-moz-box-shadow:0 5px 5px rgba(0,0,0,0.2);box-shadow:0 5px 5px rgba(0,0,0,0.2)}
</style>

<script type="text/javascript">
$(document).ready(function(){ 	
	  function slideout(){
  setTimeout(function(){
  $("#response").slideUp("slow", function () {
      });  
}, 2000);}
	
    $("#response").hide();
	$(function() {
	$("#list ul").sortable({ opacity: 0.8, cursor: 'move', update: function() {
			
			var order = $(this).sortable("serialize") + '&update=update'; 
			$.post("updateList.php", order, function(theResponse){
				$("#response").html(theResponse);
				$("#response").slideDown('slow');
				slideout();
			}); 															 
		}								  
		});
	});
});	
</script>

<div>
	<h1 class="f1"> About Us - Rearrange</h1>
	</div>
	<div class="mt5">
	<div style="float:right; width:300px;"></div>
	<div class="c3"></div>
	<div class="aml"><div class="c3"></div></div>	
	<div class="utab"><p class="f1">Drag and Drop the Titles to change Display Sequence</p>
    <a class="f2 fw prf_s" href="myprofile.php">&nbsp;</a>
    <a href="myprofile.php#form_tst1" style="display: block;" class="f2 fw apr1" id="edit_add">Add About Us</a>
    </div></div>
			<div class="mt5">
				
				<div class="c3">&nbsp;</div>
                <div id="list">
                <div id="response"> </div>
			<ul class="ui-sortable" id="test-list">
            <?php
				$abtresult = mysql_query("SELECT * FROM about_us,profile_heading where abtus_ph_id = ph_id and abtus_wc_id='".$row_wc->abtus_wc_id."' ORDER BY abtus_order");
				while($abtrow = mysql_fetch_object($abtresult))
				{     
				?>
	<li id="arrayorder_<?php echo $abtrow->abtus_id; ?>">
    <div class="handle">
    <img src="images/arrow1.png" alt="move" class="mt2" align="right" width="10" height="10"><h4><?php echo ucwords($abtrow->ph_title); ?> </h4></div>
    </li>
        <?php } ?>        
			</ul>
            </div>
            
			</div>
            
            </div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>