<?php
error_reporting(0);

include "../common.php";
$c = $_GET['c'];
$id = substr($_GET['c'], 4);

$sql = "select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and md5(bnsprof_id)='" . $id . "'";
$res = mysql_query($sql);
$row = mysql_fetch_object($res);

$sql_usr = "select * from user, business_profile where usr_id= '".$_SESSION['uid_indm']."' and bnsprof_uid='".$_SESSION['uid_indm']."'";
$res_usr = mysql_query($sql_usr);
$row_usr = mysql_fetch_object($res_usr);


//echo "<pre>";
//print_r($row_usr);
//die;

$banner = array();


$bsql="select * from company_banner where cb_bnsprof_id=$row->bnsprof_id";
$bres = mysql_query($bsql);

while ($row_t = mysql_fetch_object($bres)) {
    //echo "<pre>";
    //print_r($row_t);
    $banner[] = $row_t->cb_image;
}
//echo "<pre>";
//print_r($banner);
//die;

$coun = "select * from country";
$count = mysql_query($coun);
while ($countr = mysql_fetch_object($count)) {
    $country[] = $countr->cn_name;
}




$ct = "select * from city where ct_id=$row->bnsprof_city";
$cit = mysql_query($ct);
$citys = mysql_fetch_object($cit);


$st = "select * from states where state_id=$row->bnsprof_state";
$sta = mysql_query($st);
$states = mysql_fetch_object($sta);

$produc = array();


$bsql = "select * from product_category where pc_parent_id=$row->bnsprof_uid";
$bres = mysql_query($bsql);
while ($row_t = mysql_fetch_object($bres)) {
    $produc[] = $row_t->pc_name;
}







$path = $_SERVER['SCRIPT_NAME'];
$pos = strrpos($path, '/');
$file = substr($path, ($pos + 1));
//$file = strstr($file, '.', true);
$dotpos = strrpos($file, '.');
$file = substr($file, 0, ($dotpos));
if ($file == "enquiry") {
    $_SESSION['last_page'] = "company/enquiry.php?c=" . $_GET['c'];

    if (!isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] == '') {
        header("Location:../sign-in.php");
    }
}

if($row->bnsprof_comp_url==''){
    $company=company;
}else{
    $company=$row->bnsprof_comp_url;
}


if ($file == "index") {
    $_SESSION['last_page'] = $company."/index.php?c=" . $_GET['c'];   
}
else if ($file == "products") {
    $_SESSION['last_page'] = $company."/products.php?c=" . $_GET['c'];
}
else if ($file == "profile") {
    $_SESSION['last_page'] = $company."/profile.php?c=" . $_GET['c'];
}
else if($file == "video"){
    $_SESSION['last_page'] = $company."/video.php?c=" . $_GET['c'];
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="en" lang="en" ><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php echo $row->bnsprof_compname; ?></title>
<base href="../company/" />

<meta name="title" content="<?php echo $row->bnsprof_compname; ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<link href="css/company.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
<link href="css/jquery.bxslider.css" rel="stylesheet" type="text/css">
<script src="js/jquery.js"></script>
<script src="js/analytics.js" async=""></script>
<script src="ls/html5.js"></script>
<script language="javascript" src="js/tabbing.js" type="text/javascript"></script>
<script type="text/javascript" src="js/mojozoom.js"></script>  
<link type="text/css" href="css/mojozoom.css" rel="stylesheet" />
<script src="js/functions.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="/resources/demos/style.css">
      
 
<link rel="stylesheet" type="text/css" href="css/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="css/slick/slick-theme.css"/>




 <style>
    
    label, input { display:block; }
    input.text { margin-bottom:12px; width:95%; padding: .4em; }
    fieldset { padding:0; border:0; margin-top:25px; }
    h1 { font-size: 1.2em; margin: .6em 0; }
    div#users-contain { width: 350px; margin: 20px 0; }
    div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
    .ui-dialog .ui-state-error { padding: .3em; overflow: visible;}
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    #ui-id-1 {text-align: center; background-color: green; height: 23px; width:100%; color:white; padding-top:20px; font-size: 20px;}
    .ui-resizable{overflow: visible !important;}
    .ui-dialog-buttonset{float:left !important; margin-top: 10px !important; margin-left:131px;}
    .ui-dialog-buttonpane{margin-top:-63px !important;}
    #dialog-form{height:420px; display:none;}
    .ui-draggable .ui-dialog-titlebar{padding: 0 0 !important;}
   .ui-corner-all{overflow: visible !important;}
   .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {border-radius: 9px !important;}
    .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {top: -3%; right: -2px;}
  </style>
  <script>
                                                $(function () {
                                                    dialog = $("#dialog-form").dialog({
                                                        autoOpen: false,
                                                        height: 450,
                                                        width: 400,
                                                        resizable: false,
                                                        modal: true,
                                                    });
                                                    $("#create-user").button().on("click", function () {
                                                        dialog.dialog("open");
                                                    });
                                                });
                                            </script>
<script type="text/javascript">
(function($){
	
	$(document).ready(function(){
		var speed=1;
		w=$('#products li').length*$('#products li').width();
		appendHTML=$('#products').html();
		$($('#products')).append(appendHTML)
		
		function scroll2left(){
			moveLeft=parseInt($('#products').css('left'))-speed;
			$('#products').css({'left':moveLeft},500);
			if($('#products').css('left')==(-w+'px')){
				$('#products').css('left',0)
			}
		}
		moveLeft_t=setInterval(scroll2left,5);
		
		$('#products').mouseenter(function(){speed=0})
		$('#products').mouseleave(function(){speed=1})
		
				 
	})
})(jQuery)
</script>
<style type="text/css">
</style>
<section id="ei" style="border-bottom: 6px solid #dadada;">
<nav class="cb">
<ul>
<li><figure><a href="../index.php"><img src="../sitelogo/<?php echo get_page_settings(5);?>" alt="<?php echo getWebSiteName(); ?>" style="margin-top:-5px; height:26px"></a></figure></li>
<li>
		<a href="../create_account.php" class="b">List Your Company - Free</a> | <a href="../sign-in.php">SignIn</a>
		| <a href="../manage-selloffer-alert.php">Subscribe Trade Alerts</a> | <a href="../search_adv.php">Search Products</a> | <a href="../buyleads.php">Latest Buy Leads</a></li>
</ul>
<script>
function showsrchm()
{
	$("#smnu").show();	
}
function hidesrchm()
{
	$("#smnu").hide();	
}
function OutboundLink(type)
{
	$("#keyword_type").val('');
	if(type == 'buy_lead')
	{
		$("#a1").html("Buy Leads");
		$("#keyword_type").val("Buy Leads");
		
	}
	else
	{
		$("#a1").html(type);
		$("#keyword_type").val(type);
	}
	$("#rctyp").val(type);
	$("#smnu").hide();
	lostFocus();
	//alert($("#keyword_type").val());
}
function validsearch()
{
	var keywords=document.getElementById('keywords');
	if(keywords.value=='' || keywords.value == null)
	{
		alert("Please enter a valid text to search.");
		return false;
	}
}
function gotFocus()
{
	var keywords=$("input#keywords").val();
	if(keywords=='Enter product / service to search' || keywords=='Enter Buy Lead to search' || keywords=='Enter Supplier to search')
	{
		$("input#keywords").val('')
	}
}
function lostFocus()
{
	var type=$("#keyword_type").val();
	var keywords=$("input#keywords").val();
	if(type=='Products' && (keywords=='' || keywords=='Enter Buy Lead to search' || keywords=='Enter Supplier to search'))
	{
		$("input#keywords").val('Search Product');
	}
	else if(type=='Buy Leads' && (keywords=='' || keywords=='Enter product / service to search' || keywords=='Enter Supplier to search'))
	{
		$("input#keywords").val('Enter Buy Lead to search');
	}
	else if(type=='Suppliers' && (keywords=='' || keywords=='Enter product / service to search' || keywords=='Enter Buy Lead to search'))
	{
		$("input#keywords").val('Enter Supplier to search');
	}
}
</script>
<link rel="stylesheet" href="../css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" src="../js/jquery.autocomplete2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	lostFocus();
	
	$('#keywords').keydown(function() {	

		var type=$("#keyword_type").val();
		$("#keywords").autocomplete("../autocomplete.php", {
			selectFirst: true,
			extraParams: {type:type},
			width: 407
		})
		.result(function(event, data, formatted) {
			$("input#keywords").val(data);
		});
	});
});
</script>
<style type="text/css">
    .ui-dialog-buttonpane .ui-dialog-buttonset .ui-button{
            background: rgba(0, 149, 255, 0.85);
            color: #fff;
            margin-top: 10px;
    }
</style>

	<div class="seaReg" style="width:205px">
<form name="searchForm" action="../search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm" target="_blank">
<input value="<?php echo $_GET['keywords'];?>" class="input" name="keywords" id="keywords" onfocus="gotFocus();" onblur="lostFocus()" value="Search Products" style="width:120px; float:left !important"/> 
<input type="hidden" id="keyword_type" name="keyword_type" value="Products" />
<input type="hidden" name="rctyp" id="rctyp" value="<?php if($_GET['rctyp']!=""){ echo $_GET['rctyp']; } else { ?>Products<?php }?>"/>
<input name="submit" id="btnSearch" value="Search" class="search" type="submit">
	</form>
	</div>
	</nav>
</section>
</head>
	<body>	
<header>
<div id="logo" style="margin: 0px -2px 0px;">
   
<section>
	<div class="company_profile_top_first"> 	
		<ul class="cb">
<?php if($row->bnsprof_complogo!='' && is_file("../upload/companylogo/".$row->bnsprof_complogo)){ ?><li><img src="../upload/companylogo/<?php echo $row->bnsprof_complogo; ?>" style="max-height:76px;margin-right:10px; padding-top:10px;" /></li><?php } ?>

			<li><h1 style="color: #fff; text-shadow: 1px 1px #060; font: 30px/.7em Arial, Helvetica, sans-serif; text-transform: capitalize;"><?php echo $row->bnsprof_compname; ?></h1>
			
                        <p style="padding-left:10px; color: color: #eeff1d !important; text-shadow: 1px 1px #060; margin-top: -6px; font-size: 16px;"> <span style="padding-right: 7px; margin-top: -6px;"><img src="<?php echo BASE_URL ?>/images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="<?php echo get_country_name($row->country); ?>" class="w4" align="top" height="30" width="35"/></span><span style="line-height: 27px;"> <?php echo get_country_name($row->country); ?> - <?php echo $states->state_name; ?> - <?php echo $citys->ct_name; ?></span></p>
			</li>
		</ul>
</div>
<div class="company_profile_top_sec"> 	
	<div class="company_info">
		<div class="header_top_div" style="box-shadow: -4px 6px 7px #4C4646;">
			<div class="top-text1"><span><img src="<?php echo BASE_URL ?>/company/images/membericon.png" /></span><span>SENIOR Member</span></div>
			<div class="top-text2">Since : <?php echo date("Y", strtotime($row->date));?></div>
		</div>
	</div>
</div>
</section>
</div>

	<nav class="cb company_nav_head" id="tml">
	  <ul class="company_menu" style="width:100%;">
        <li><a href="../<?php echo $row->bnsprof_comp_url;?>/index.php?c=<?php echo $c; ?>" <?php if($file=="index"){?> class="on" <?php } else {?> class="" <?php } ?>>Home</a></li>
        <li><a href="../<?php echo $row->bnsprof_comp_url;?>/products.php?c=<?php echo $c; ?>" <?php if($file=="products"){?> class="on" <?php } else {?> class="" <?php } ?>>Products</a></li>
        <li><a href="../<?php echo $row->bnsprof_comp_url;?>/profile.php?c=<?php echo $c; ?>" <?php if($file=="profile"){?> class="on" <?php } else {?> class="" <?php } ?>>Profile</a></li>
        <li><a href="../<?php echo $row->bnsprof_comp_url;?>/enquiry.php?c=<?php echo $c; ?>" <?php if($file=="enquiry"){?> class="on" <?php } else {?> class="" <?php } ?>>Contact us</a></li>
	<li><span><a style="background:none; color:#fff; text-shadow:none;" href="../<?php echo $row->bnsprof_comp_url;?>/video.php?c=<?php echo $c; ?>">Company Video</a></span><img style="width: 35px; float: right; padding-top: 8px;" src="<?php echo BASE_URL ?>/company/images/video.png"></li>
	  </ul>
	  
	  <?php /*?><ul class="company_video_link"><li><span><a href="../<?php echo $row->bnsprof_comp_url;?>/video.php?c=<?php echo $c; ?>">Company Video</a></span><img src="<?php echo BASE_URL ?>/company/images/video.png"></li></ul><?php */?>
	  
	 <ul class="user_profile_link" style="margin-top: -100px; margin-left: -217px;">
		<li>
			<a href="javaScript:void(0)" style="padding: 4px 8px 4px 5px !important; color: #eeff1d !important;">Communicate with Supplier</a>
	        <div id="profile_sub_menu">
				<div class="profile_list_value">
				   <div class="user_profile_image"> 
					  <img src="<?php echo BASE_URL ?>/server/php/files/thumbnail/<?php echo $row->image ?>" width="70" height="60">
				   </div>
				   <div class="user_name_value">
					   <div class="menu_text1">What can I do for you?</div>
					   <div class="user_name"><?php echo $row->bnsprof_ceofname.' '.$row->bnsprof_ceolname; ?></div>
				   </div>
				</div>
				<div class="profile_list_value">
					<div onclick="checklogin()" class="contact_div" id="create-user" style="font-size:13px; color: darkblue;"><span style="padding-right: 5px; float:left;"><img src="<?php echo BASE_URL ?>/company/images/mail_box.png" width="25"></span><span style="padding-right: 0px; font-size: 14px;"><b>Today Latest Business contact</b></span></div>
				
                                </div>
				<div class="profile_list_value">
					<div class="chat"><span><img src="<?php echo BASE_URL ?>/company/images/chatnow.png" width="55"></span><span><?php echo $row->bnsprof_ceofname.' '.$row->bnsprof_ceolname; ?></span></div>
					</div>
				</div>	
                   <div class="abcdefgh">
                            
                         <div id="dialog-form" title="Today Latest Business contact" style="background-color: beige; overflow: visible;" >
                                                                                <img style="position: absolute; left:-125px; top:-33px;"src="images/girls_PNG6471.png"/>
                                                                                <img style="position: absolute; left: 44px; top: -49px; width: 60px; height: 50px;"src="images/popar.png"/>
                                                                                
                                                                                <form method="post" action="smsMail.php">
                                                                                    <fieldset>
                                                                                        <input type="hidden" value="<?php echo $row->bnsprof_uid;?>" name="msg_to">
                                                                                        <label for="country">Select Country</label>
                                                                                        <select name="country" id="country" class="text ui-widget-content ui-corner-all" style="width:98%">
                                                                                            <option value=""><?php echo get_country_name($row_usr->country); ?></option>
                                                                                            <?php foreach ($country as $key => $eachCountry) { ?>
                                                                                                <option value="<?php echo $key; ?>"><?php echo $eachCountry; ?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                        <label for="name">Your Company Name</label>
                                                                                        <input type="text" name="name" id="name" value="<?php echo $row_usr->bnsprof_compname; ?>" placeholder="Your Company Name" class="text ui-widget-content ui-corner-all">
                                                                                            <label for="email" style="margin-top: 20px;">Email</label>
                                                                                            <input type="hidden" value="<?php echo $c; ?>" name="c">
                                                                                            <input type="hidden" value="<?php echo $row_usr->usr_id; ?>" name="company">
                                                                                            <input type="text" name="email" id="email" placeholder="email" value="<?php echo $row_usr->email; ?>" class="text ui-widget-content ui-corner-all">
                                                                                                <label for="mobile">Mobile</label>
                                                                                                <div class="flag-div" style="position:relative;">
                                                                                                    <input type="text" name="country_code" id="country_code" class="text ui-widget-content ui-corner-all" value="<?php echo $row_usr->country_ph_code; ?>" style="float:left; width: 15%; padding-left: 36px;">
                                                                                                        <img style="position:absolute; left: 4px; top:5px;" src="<?php echo BASE_URL ?>/images/country_flag/<?php echo get_country_flag($row_usr->country); ?>"
                                                                                                             </div>
                                                                                                            <input type="text" name="mobile" id="mobile" placeholder="Enter Your Mobile Number" value="<?php echo $row_usr->mobile1; ?>" class="text ui-widget-content ui-corner-all" style="float:left; width: 67%; margin-left: 5px;">
                                                                                                                <label for="description">Description</label>
                                                                                                                <textarea placeholder="" name="description" rows="4" cols="43" style="background-color:white;resize: none;"></textarea>
                                                                                                                <!-- Allow form submission with keyboard without duplicating the dialog button -->
                                                                                                                <input type="submit" id="popbutton" tabindex="-1" style="position:absolute; top:-1100px;background: #000">
                                                                                                                    </fieldset>
                                                                                                                    <input type="submit" value="send SMS" style="padding: .4em 1em; line-height: normal; background: rgba(0, 149, 255, 0.85); color: #fff; margin-left: 130px;">
                                                                                                                    </form>
                                                                                                                    </div>
                        </div>
			</div>
		</li>
	  </ul>
	
	
	</nav>
    
    <style>
        .custom-slider-container{
            width:700px;
            margin: 10px auto;
            overflow: hidden;
            height: 165px;
        }
        .custom-slider-container .center{
            margin-top: 20px;
        }
        .custom-slider-container .slick-list{
            overflow: visible;
            padding: 0px !important;
        }
        .custom-slider-container .slick-current{
            transform: scale(1.3);
        }
        .custom-slider-container .slick-current img{
            width: 120px;
        }
        .custom-slider-container .slick-current img{
            width: 120px;
        }
        
        
    </style>
    
    <style>
        .img-current-border{
            border:1px solid black;
        }
    </style>
    <section id="header" style="padding-top:0px;">
        <div class="custom-slider-container">
             <div class="center">
             <?php
		foreach($banner as $aBan){
		?>
<div>
   <img width="100" alt="Fresh Plum" class="img-current-border" src="<?php echo BASE_URL ?>/upload/company_banner/thumb/<?php echo $aBan ?>">
</div>    
	<?php } ?>
                                                                
        </div>
        </div>
       <p style="padding-left:60px; padding-bottom:2px; font-size:18px; line-height: 1.5em; color: #595959; text-shadow: 1px 1px #ecf6fd;">
        					 <?php
        					  $size = sizeof($business_type);
							foreach($business_type as $index=>$btp)
							 {
							?>
							    	<?php if($index<$size-1){?>
					<span><?php echo $btp?>-</span>
					<?php }else{?><?php echo $btp?><?php }?>  
							<?php
								
							 }
                                                         
						 ?>
						 </p>    

    </section>
		</header>
<br>
    
    
    
    
    
    <script type="text/javascript" src="js/slick/slick.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
      $('.center').slick({
            centerMode: true,
            centerPadding: '60px',
            autoplay: true,
            slidesToShow: 3,
            responsive: [
              {
                breakpoint: 768,
                settings: {
                  arrows: false,
                  centerMode: true,
                  centerPadding: '40px',
                  slidesToShow: 3
                }
              },
              {
                breakpoint: 480,
                settings: {
                  arrows: false,
                  centerMode: true,
                  centerPadding: '40px',
                  slidesToShow: 1
                }
              }
            ]
          });
    });
  </script>
<script>
                                                                                                                            function checklogin() {
                                                                                                                                        var user="<?php echo $_SESSION['uid_indm'] ?>";
                                                                                                                                        if(user==''){
                                                                                                                                            window.location.href = "../sign-in.php";
                                                                                                                                        }
                                                                                                                                      }
                                                                                                                        </script>