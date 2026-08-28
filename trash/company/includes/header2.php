<?php
error_reporting(0);
include "../common.php";
$c=$_GET['c'];
$id=substr($_GET['c'],4);
$sql="select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and md5(bnsprof_id)='".$id."'";
$res=mysql_query($sql);
$row=mysql_fetch_object($res);
$path=$_SERVER['SCRIPT_NAME'];
$pos=strrpos($path,'/');
$file=substr($path,($pos+1));
//$file = strstr($file, '.', true);
$dotpos=strrpos($file,'.');
$file=substr($file,0,($dotpos));
if($file=="enquiry"){
$_SESSION['last_page']="company/enquiry.php?c=".$_GET['c'];

if(!isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']=='')
{
	header("Location:../sign-in.php");
}
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
<script src="js/analytics.js" async=""></script>
<script src="ls/html5.js"></script>
<script src="js/jquery.js"></script>
<script language="javascript" src="js/tabbing.js" type="text/javascript"></script>
<script type="text/javascript" src="js/mojozoom.js"></script>  
<link type="text/css" href="css/mojozoom.css" rel="stylesheet" />  
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
<section id="ei">
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

	<div class="seaReg">
<form name="searchForm" action="http://<?php echo $_SERVER['HTTP_HOST']; ?>/arabyos/" onSubmit="return validsearch()" method="GET" id="hdr_frm" target="_blank">
<input value="<?php echo $_GET['keywords'];?>" class="input" name="keywords" id="keywords" onfocus="gotFocus();" onblur="lostFocus()" value=""/> 
<input type="hidden" id="keyword_type" name="keyword_type" value="" />
<input type="hidden" name="rctyp" id="rctyp" value="<?php if($_GET['rctyp']!=""){ echo $_GET['rctyp']; } else { ?>Products<?php }?>"/>
<input name="submit" id="btnSearch" value="Search" class="search" type="submit">
	</form>
	</div>
	</nav>
</section>
</head>
	<body>	
<header>
<div id="logo">
<section>
<ul class="cb">
<?php if($row->bnsprof_complogo!='' && is_file("../upload/companylogo/".$row->bnsprof_complogo)){ ?><li><img src="../upload/companylogo/<?php echo $row->bnsprof_complogo; ?>" style="max-height:100px;margin-right:10px;" /></li><?php } ?>
<li>
<h1><?php echo $row->bnsprof_compname; ?></h1>
<p style="padding-left:10px; ">Member Since : <?php echo date("Y", strtotime($row->date));?> | Country : <?php echo get_country_name($row->country); ?></p>
</li>
</ul>
</section>
</div>

	<nav class="cb" id="tml">
	<ul>
<li><a href="index.php?c=<?php echo $c; ?>" <?php if($file=="index"){?> class="on" <?php } else {?> class="" <?php } ?>>Home</a></li>
<li><a href="products.php?c=<?php echo $c; ?>" <?php if($file=="products"){?> class="on" <?php } else {?> class="" <?php } ?>>Products</a></li>
<li><a href="profile.php?c=<?php echo $c; ?>" <?php if($file=="profile"){?> class="on" <?php } else {?> class="" <?php } ?>>Profile</a></li>
<li><a href="enquiry.php?c=<?php echo $c; ?>" <?php if($file=="enquiry"){?> class="on" <?php } else {?> class="" <?php } ?>>Contact us</a></li>
			</ul>
	</nav>
				<style>
			#header {background: url(images/bg_1003.jpg) repeat center center;}
			</style>
					<section id="header">
		<figure>
		<ul style="left: -482px;" class="cb" id="products">
<?php
$sql_pd_slid="select * from products where pd_uid='".$row->usr_id."' and pd_status='1' and pd_image!=''";
$res_pd_slid=mysql_query($sql_pd_slid);
if(mysql_num_rows($res_pd_slid)>0)
{	
while($row_pd_slid=mysql_fetch_object($res_pd_slid)){
?>
<li><img src="../upload/myproduct/<?php echo $row_pd_slid->pd_image; ?>" alt="<?php echo $row_pd_slid->pd_title; ?>" height="100" width="100"></li>
<?php }} ?>							</ul>
		</figure>
					<div class="subTit">
					<?php
$sql_pc="select * from product_category, products where pc_id=pd_subcat_id and pd_uid='".$row->usr_id."' and pd_status='1' limit 4";
$res_pc=mysql_query($sql_pc);
$p_cate = array();
$i=0;
while($row_pc=mysql_fetch_object($res_pc)){
$p_cate[$i]=$row_pc->pc_name;
$i++;
}
$p_cates = array_unique($p_cate);
echo $p_catess = implode(', ',$p_cates);
?>
					</div>
					</section>
		</header>
<br>