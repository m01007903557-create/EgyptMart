<?php
include "common.php";

if(isset($_COOKIE['loc_id']))
{
	$sql_br_ck=" and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	/*
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/

	$sql_so_ck=" and (
	(so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	/*
	(so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and so_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')
	or
	(so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	
	*/
}
else
{

	$sql_br_ck=" and (
	
	(br_preferred_supplier_location='any')
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	/*(br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/

	$sql_so_ck=" and (
	
	(so_preferred_buyer_location='any')
	or
	(so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	/*(so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/eto-index-2.css" rel="STYLESHEET">

<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript" src="js/leftdd-1.js"></script>

<script type="text/javascript">
$('document').ready( function(){
	showSaleoffers(1);
	showBuyleads(1);
        
        $('.mobile-menu').click(function(){
            
            $('.search-show-box-buyleads .left-side-bar-sale-offer #cssmenu').toggleClass('menu-active');
            
        });
        
        
});
function showSaleoffers(page)
{
	$.post("ajax-file/showLatestSelloffers.php",{page:page},    function(data){    $('#so').html(data); });
}
function showBuyleads(page)
{
	$.post("ajax-file/showLatestBuyleads.php",{page:page},    function(data){    $('#bl').html(data); });
}




</script>
</head>
<body class="search-show-box-buyleads sale-offers search-page-now">
<div class="q_hm1">
<!-- Header start Here::-->
<?php include "includes/header_new.php"; ?>

<div class="inner_wrapper">
    <div class="bt" style="display: none;"><img src="images/z.gif" alt="" height="1" width="1"></div>


<!--<link href="css/eto-index-2.css" rel="STYLESHEET">-->
<p class="q_c3"></p>

<!--New Header_sellHome End -->

	<!--New Header End --><!--Left Section Start -->

<?php
if(get_page_settings('25')=='manual')
{
	$sql_order=" order by pc_order,pc_name";
}
else
{
	$sql_order=" order by pc_name";
}
?>

<div class="left-side-bar-sale-offer" style="float:left">
    
    <span class="mobile-menu desktop-mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i></span>
    
<link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
<div id='cssmenu' style="float:left;width:200px;">
<ul>

<?php

//from index //ajax_get_leftmenu_again.php


$SubCategoryArray = "";
$html = "";
$sql_cmt_cnt1 = "select DISTINCT pd_subcat_id  from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . "   ORDER BY FIELD(p_id,'5','4','3','15') ";

$res_dd_mnu = mysql_query($sql_cmt_cnt1);
$pc_id_arr = array();
while ($row_dd_mnu = mysql_fetch_object($res_dd_mnu)) {
    $pc_id_arr[] = $row_dd_mnu->pd_subcat_id;
}

if (count($pc_id_arr) == 0) {
    $html .= '<li class="ptag text-danger" style="padding: 5px;">Currently, this country has not any business to display !</li>';
}
 else {
    $SubCategoryArray = join("','", $pc_id_arr);


    $ParentCategoryArray = "";
    $sql_dd_cmnuParent = "select	DISTINCT pc_parent_id	from product_category_arabyos where pc_id in ('$SubCategoryArray')  and pc_status = '1'";

    $res_dd_cmnuParent = mysql_query($sql_dd_cmnuParent);
    while ($row_dd_cmnuParent = mysql_fetch_object($res_dd_cmnuParent)) {
        $pc_id_arrNew[] = $row_dd_cmnuParent->pc_parent_id;

    }

    $ParentCategoryArray = join("','", $pc_id_arrNew);

    $MasterCategoryArray = "";
    $sql_dd_cmnuMaster = "select	DISTINCT pc_parent_id	from product_category_arabyos where pc_id in ('$ParentCategoryArray') and pc_status = '1' ";
    $res_dd_cmnuMaster = mysql_query($sql_dd_cmnuMaster);
    while ($row_dd_cmnuMaster = mysql_fetch_object($res_dd_cmnuMaster)) {
        $pc_id_arrNewNew[] = $row_dd_cmnuMaster->pc_parent_id;

    }
    $MasterCategoryArray = join("','", $pc_id_arrNewNew);


     $first = isset($_POST['page'])?$_POST['page']:0;
     $sql_dd_cmnuAgainMaster_total = "select	DISTINCT pc_id	from product_category_arabyos where pc_id in ('$MasterCategoryArray') and pc_status = '1' ";
    $sql_dd_cmnuAgainMaster = "select	DISTINCT pc_id	, pc_image ,	pc_name 	from product_category_arabyos where pc_id in ('$MasterCategoryArray') and pc_status = '1'  order by pc_name asc /*limit $first ,14*/";
     $res_dd_cmnuAgainMaster = mysql_query($sql_dd_cmnuAgainMaster);
    $res_dd_cmnuAgainMaster_total = mysql_query($sql_dd_cmnuAgainMaster_total);
    $total_count = mysql_num_rows($res_dd_cmnuAgainMaster);
}

//$sql_dd_mnu="select pc_id,pc_name,pc_image from product_category_arabyos where pc_parent_id = '0' and pc_status = '1' ".$sql_order;
//$res_dd_mnu=mysqli_query($con, $sql_dd_mnu);
while($row_dd_mnu=mysql_fetch_object($res_dd_cmnuAgainMaster))//mnu
{
?>
	<li class='has-sub'><a href="category.php?token=<?php echo rand(10,9999).md5($row_dd_mnu->pc_id); ?>"><span><?php echo $row_dd_mnu->pc_name;?></span></a><!--category.php?token=-->
    <ul>
    <?php
	$sql_dd_cmnu="select pc_id,pc_sort_name from product_category_arabyos where pc_parent_id = '".$row_dd_mnu->pc_id."' and pc_status = '1' and pc_id in ('$ParentCategoryArray') ".$sql_order;
	$res_dd_cmnu=mysqli_query($con, $sql_dd_cmnu);
	while($row_dd_cmnu=mysqli_fetch_object($res_dd_cmnu))//cmnu
	{	
		$row_scnt=mysqli_fetch_object(mysqli_query($con, "select count(*) as cnt from product_category_arabyos where pc_parent_id = '".$row_dd_cmnu->pc_id."' and pc_status = '1'"));
	?>
		<li><a href="products.php?c=<?php echo md5($row_dd_cmnu->pc_id); ?>"><span><?php echo ucwords($row_dd_cmnu->pc_sort_name); ?></span></a><!--catcompany.php?token=-->
        <?php /* if($row_scnt->cnt>0){ ?>
            <ul>
		    <?php
				$sql_dd_smnu="select pc_id,pc_sort_name from product_category_arabyos where pc_parent_id = '".$row_dd_cmnu->pc_id."' and pc_status = '1' ".$sql_order;
				$res_dd_smnu=mysqli_query($con, $sql_dd_smnu);
				while($row_dd_smnu=mysqli_fetch_object($res_dd_smnu))
				{	?>
					<li><a href="products.php?sc=<?php echo md5($row_dd_smnu->pc_id); ?>"><span><?php echo ucwords($row_dd_smnu->pc_sort_name); ?></span></a></li>		
		<?php	}
	?>
		    </ul>
        <?php }*/ ?>
        </li>
<?php	}
	?>
    </ul>
    </li>
<?php } ?>
	
	<li><a href="dir.php"> View All Categories </a></li>
</ul>
</div>
</div>
<!--Left Section Ends -->
<!--Center Section Start -->
<div class="right-side-bar-sale-offer" style="float:left">
    <div class="so_q_bt11 so_w3">
<a class="cb2" href="sale-offers.php">Trade Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;
<a class="cb2" href="post-buy-req.php" rel="nofollow">Post Buy Requirement</a>&nbsp;&nbsp;|&nbsp;&nbsp;
<a class="cb2" href="tenders.php" rel="nofollow">Tender</a>&nbsp;&nbsp;|&nbsp;&nbsp;
<a class="cb2" href="manage-sell-offer.php" rel="nofollow">My Trade Offers</a>&nbsp;&nbsp;|&nbsp;&nbsp;
<a class="cb2" href="manage-buy-requirement.php" rel="nofollow">My Buy Leads</a>&nbsp;&nbsp;|&nbsp;&nbsp;
<a class="cb2" href="manage-selloffer-alert.php" rel="nofollow">Mange Sell Offer Alerts</a>
</div>
    
<div class="bxc fd">
   

    
<div class="m1 bxt  fd min-container-saleoffer" style="text-align:right;margin-top:1px">
<div id="m4t">

<?php
	$sql_adv="select * from advertisement where adv_imagewidth='468' and adv_imageheight='60' and adv_status='1' order by rand() limit 1";
	$res_adv=mysqli_query($con, $sql_adv);
	if(mysqli_num_rows($res_adv)>0)
	{
		$row_adv=mysqli_fetch_object($res_adv);	
		?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/
	}
	else
	{
?>
		<img alt="" src="upload/advertisement/468-60-advertisement.png" border="0" height="60" hspace="0" vspace="0" width="468">
<?php	}	?>

</div>
</div>



<p class="c3"><br></p>

<div id="so"></div>

<div id="bl"></div>

<p class="c3 p1"></p>


</div>










    </div>
    



</div>





</div>
</div>

<p class="c3"><br></p></div>

</div>
<!--Footer starts here-->
<?php include 'includes/footer.php';?>
