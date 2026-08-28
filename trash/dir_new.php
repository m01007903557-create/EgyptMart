<?php
ob_start();//test by webxtor 23 June 2019
session_start();
include "common.php";

if(isset($_COOKIE['loc_id']))
{
	$sql_pd_ck=" and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id where c.ct_cn_id='".$_COOKIE['loc_id']."')))";
	/*
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/


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
	$sql_pd_ck=" and (
	(pd_preferred_buyer_location='any')
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	
	/*(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/

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



$SubCategoryArray = "";
$html = "";
$sql_cmt_cnt1 = "select DISTINCT pd_subcat_id  from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . "   ORDER BY FIELD(p_id,'5','4','3','15') ";
$html .= "<!--$sql_cmt_cnt1-->";
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
    $sql_dd_cmnuParent = "select	DISTINCT pc_parent_id	from product_category where pc_id in ('$SubCategoryArray') and pc_status=1 ";

    $res_dd_cmnuParent = mysql_query($sql_dd_cmnuParent);
    while ($row_dd_cmnuParent = mysql_fetch_object($res_dd_cmnuParent)) {
        $pc_id_arrNew[] = $row_dd_cmnuParent->pc_parent_id;

    }

    $ParentCategoryArray = join("','", $pc_id_arrNew);

    $MasterCategoryArray = "";
    $sql_dd_cmnuMaster = "select	DISTINCT pc_parent_id	from product_category where pc_id in ('$ParentCategoryArray') and pc_status=1 ";
    $res_dd_cmnuMaster = mysql_query($sql_dd_cmnuMaster);
    while ($row_dd_cmnuMaster = mysql_fetch_object($res_dd_cmnuMaster)) {
        $pc_id_arrNewNew[] = $row_dd_cmnuMaster->pc_parent_id;

    }
    $MasterCategoryArray = join("','", $pc_id_arrNewNew);


     $first = isset($_POST['page'])?$_POST['page']:0;
     $sql_dd_cmnuAgainMaster_total = "select	DISTINCT pc_id	from product_category where pc_id in ('$MasterCategoryArray') ";
    $sql_dd_cmnuAgainMaster = "select	DISTINCT pc_id	, pc_image ,	pc_name 	from product_category where pc_id in ('$MasterCategoryArray') and pc_status=1 order by pc_name asc /*limit $first ,14*/";
     $res_dd_cmnuAgainMaster = mysql_query($sql_dd_cmnuAgainMaster);
    $res_dd_cmnuAgainMaster_total = mysql_query($sql_dd_cmnuAgainMaster_total);
    $total_count = mysql_num_rows($res_dd_cmnuAgainMaster_total);
   $c=1; while ($row_dd_cmnuAgainMaster = mysql_fetch_object($res_dd_cmnuAgainMaster)) {
        //$html.="<li class=\"ptag \"><a class=\"mobile-click stop_redirect\" data-id=\"".$c."\" href=\"category.php?token=".md5($row_dd_cmnuAgainMaster->pc_id)."\">";
        //$html .="<img  style=\"height:30px; margin-right: 10px; width:30px;\" src=\"http://arabyos.com/upload/category/". $row_dd_cmnuAgainMaster->pc_image."\">";
        //$html .=$row_dd_cmnuAgainMaster->pc_name."<span class=\"main_links_span\"></span> </a><div class=\"typography_3_colm r_".$c."\" ><div class=\"colm_3_container\">";
        //ob_start();
        $row_mcat = $row_dd_cmnuAgainMaster;
        ?>
<style>
	.lnk li { height: auto; }
</style>
<div class="bx1 mb1 lnk" id="main_cat_<?php echo $row_mcat->pc_id; ?>">
<p class="p4 lh1"><!--You can now find supplier of your choice listed under 
thousands of categories. Browse through the section and search for the 
most relevant supplier for your business needs.-->
<table width="98%"><tr><td width="12%"><img alt="" class="bg10a" src="upload/category/<?php echo $row_mcat->pc_image; ?>" align="left" height="75" width="75"></td><td style="padding-left:4%;background:#0600ff;" width="88%" ><h2 style="font-weight: 700;"><a href="category.php?token=<?php echo rand(1000,9999).md5($row_mcat->pc_id);?>" style="text-decoration:none;color: white;"><?php echo $row_mcat->pc_name; ?></a></h2></td></tr></table>
</p>

<ul class="sdu">
			<?php
			//$html .= ob_get_contents();
			//ob_end_clean();

        $sql_dd_cmnuAgainParent = "select	 pc_id	,	pc_name 	from product_category where pc_parent_id = '" . $row_dd_cmnuAgainMaster->pc_id . "'  and pc_id in ('$ParentCategoryArray') and pc_status=1 order by pc_name asc ";
        $res_dd_cmnuAgainParent = mysql_query($sql_dd_cmnuAgainParent);
        $rowcount = mysql_num_rows($res_dd_cmnuAgainParent);
        while ($row_dd_cmnuAgainParent = mysql_fetch_object($res_dd_cmnuAgainParent)) {
            /*unset($pc_id_arrNewNewNew);
            $iChildAgain = "";
            $sql_dd_cmnuAgainChild = "select	 pc_id	from product_category where pc_parent_id = '" . $row_dd_cmnuAgainParent->pc_id . "'  and pc_id in ('$SubCategoryArray') and pc_status=1";
            $res_dd_cmnuAgainChild = mysql_query($sql_dd_cmnuAgainChild);
            while ($row_dd_cmnuAgainChild = mysql_fetch_object($res_dd_cmnuAgainChild)) {
                $pc_id_arrNewNewNew[] = $row_dd_cmnuAgainChild->pc_id;
            }
            $iChildAgain = join("','", $pc_id_arrNewNewNew);
            /*$count = 0;
            $row['count'] = 0;
            $query_pag_num = "select count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
			where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . " and pd_subcat_id in('$iChildAgain') ORDER BY FIELD(p_id,'5','4','3','15')";
            $result_pag_num = mysql_query($query_pag_num);
            $row = mysql_fetch_array($result_pag_num);
            $count = $row['count'];*/
            //$html .="<div class=\"colmn_3_fullwidth\" style=\"    margin-bottom: -11px!important;  padding: 8px 0px!important;\"><ol class=\"ptaga some_links\">";
            //$html .="<li><a href=\"products.php?c=".md5($row_dd_cmnuAgainParent->pc_id)."\"  class=\"ptaga\">". $row_dd_cmnuAgainParent->pc_name."</a></li></ol></div>";
            $cat_row = $row_dd_cmnuAgainParent;
            ?>
<li ><a class="txt-blue" href="products.php?c=<?php echo md5($cat_row->pc_id);?>"><?php echo ucwords($cat_row->pc_name); ?></a><br><!--<img alt="" class="bg10a" src="upload/category/<?php /*echo $cat_row->pc_image;*/?>" align="left" height="75" width="75">-->
<span>
			<?php
        	$sql_dd_cmnuAgainParent2 = "select	 pc_id	,	pc_name 	from product_category where pc_parent_id = '" . $cat_row->pc_id . "'  and pc_id in ('$SubCategoryArray') and pc_status=1 order by pc_name asc ";
        	$res_dd_cmnuAgainParent2 = mysql_query($sql_dd_cmnuAgainParent2);
        	$rowcount2= mysql_num_rows($res_dd_cmnuAgainParent2);
        	$scat_i = 1;
        	while ($row_dd_cmnuAgainParent2 = mysql_fetch_object($res_dd_cmnuAgainParent2)) {
        		$scat_row = $row_dd_cmnuAgainParent2;
        		?>
    	<a href="products.php?sc=<?php echo md5($scat_row->pc_id); ?>"><?php echo ucwords($scat_row->pc_name); ?></a>
    	<?php if($scat_i==1 || $scat_i==2){?><br><?php }?>
        		<?php
        	}
			?>
</span>
</li>
			<?php
			//$html .= ob_get_contents();
        }
        //$html .="</div></div></li>";
        ?>
</ul>

<p class="c3"></p>
</div>
			<?php
			//$html .= ob_get_contents();
		$c++;

    }
    
    $html .= ob_get_contents();

    /* if($total_count/14>1){

         $html .="<li class=\"page_bar_item\"><a class=\"\" >";
        $page = floor($total_count/14);
        for($i=0;$i<=$page;$i++){
            $first = 14*$i;
           $html .="<span style='padding-left:15px;cursor:pointer; ";
            if($_POST['page']==$first)
                $html .= "color:red; ";
            $html .= "' onclick='get_load_leftdata(".$first.")'>".($i+1)."</span>";
        }
       $html.="</a></li>";
     }
     $html .="<li class=\"ptag\"><a class=\"\" href=\"dir.php\"><span></span>View All Categories >> <span class=\"main_links_span\"></span></a></li>";*/
}

ob_end_clean();

//echo $html;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<link type="text/css" rel="stylesheet" href="css/home-page-2.css">
<link type="text/css" rel="stylesheet" href="css/main-v2.css">
<link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">

        <!--JSCSS-->
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>

</head>
<body data-twttr-rendered="true" class="search-show-box directory-template">
	<div class="q_hm1">
        
    <?php include "includes/header_new.php"; ?>
        
    <!-- <div class="bt">
<img src="images/z.gif" alt="" height="1" width="1">
</div> -->

<!--New Header End -->

<!--Left Start::-->
<div class="inner_wrapper">
    <div class="lft fl">

<div class="bc">
<p class="cor f2"></p></div>

<?php 
if(get_page_settings('25')=='manual')
{
	$sql_order=" order by pc_order,pc_name";
}
else
{
	$sql_order=" order by pc_name";
}
/*
$sql_mcat="select pc_id,pc_name,pc_image from product_category where pc_parent_id = '0' and pc_status = '1' ".$sql_order;
$res_mcat=mysqli_query($con, $sql_mcat);
while($row_mcat=mysqli_fetch_object($res_mcat))
{
?>
<div class="bx1 mb1 lnk" id="main_cat_<?php echo $row_mcat->pc_id; ?>">
<p class="p4 lh1"><!--You can now find supplier of your choice listed under 
thousands of categories. Browse through the section and search for the 
most relevant supplier for your business needs.-->
<table width="98%"><tr><td width="12%"><img alt="" class="bg10a" src="upload/category/<?php echo $row_mcat->pc_image; ?>" align="left" height="75" width="75"></td><td style="padding-left:4%;background:#0600ff;" width="88%" ><h2 style="font-weight: 700;"><a href="category.php?token=<?php echo rand(1000,9999).md5($row_mcat->pc_id);?>" style="text-decoration:none;color: white;"><?php echo $row_mcat->pc_name; ?></a></h2></td></tr></table>
</p>

<ul class="sdu">
<?php

	
$cat_res = mysqli_query($con, "select pc_id,pc_name,pc_sort_name from product_category where pc_parent_id = '".$row_mcat->pc_id."' and pc_status = '1' ".$sql_order);
if(mysqli_num_rows($cat_res) > 0){
	$cat_i = 1;
while($cat_row = mysqli_fetch_object($cat_res)){
?>

<li ><a class="txt-blue" href="products.php?c=<?php echo md5($cat_row->pc_id);?>"><?php echo ucwords($cat_row->pc_sort_name); ?></a><br><!--<img alt="" class="bg10a" src="upload/category/<?php /*echo $cat_row->pc_image;* /?>" align="left" height="75" width="75">-->
<span>
<?php 		
	$scat_res = mysqli_query($con, "select pc_id,pc_sort_name from product_category where pc_parent_id = '".$cat_row->pc_id."' and pc_status = '1' order by pc_sort_name asc limit 0,3");
	$scat_i = 1;
	while($scat_row = mysqli_fetch_object($scat_res))
	{
	?>
    	<a href="products.php?sc=<?php echo md5($scat_row->pc_id); ?>"><?php echo ucwords($scat_row->pc_sort_name); ?></a>
    	<?php if($scat_i==1 || $scat_i==2){?><br><?php }?>		
    <?php }	?>
</span>
</li>
<?php	
	$cat_i++;	
	}
} ?>

</ul>

<p class="c3"></p>
</div>
<?php	}	
*/

echo $html;

?>


<p class="c3 mb1"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>

<br class="c3">
<!--Buy Lead Form Code Start-->
         <div align="left"><br>
              <span id="form_redirect_path"></span>
              
              <span id="q_lead_enrichment"></span><span id="q_lead_conversion"></span><span id="q_lead_impressionload"></span>
                                </div>
<!--Buy Lead Form Code Ends-->
</div>
<!--Left End::-->
<!--ryt Start::-->
<div class="ryt fr">

<div class="tbx">
<div class="wlc" id="wl">
<?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){	
		$uid=$_SESSION['uid_indm'];
	?>
    <div id="nlogin"><p class="bp13 m12">Welcome: <span class="cr6"><?php echo user_info($uid,'name_prefix')."&nbsp;".user_info($uid,'fname');?></span><br><span><b class="bo1">Go to &nbsp;</b><a href="my-dashboard.php">My Dashboard</a></span></p></div>
    <?php }else{ ?>
	<p class="bp13 m12">Welcome to <?php echo getWebSiteName(); ?><br><span><b class="bo1">New User? &nbsp; </b><a href="create_account.php">Join Now!</a></span></p>
<?php } ?>
<script type="text/javascript">
function buy()
{
	$("#fb1").addClass("bb").css("top","2px");
	$("#fs1").removeClass("bb").css("top","1px");

	$("#fb2").removeClass("off").addClass("ct1");
	$("#fs2").removeClass("ct1").addClass("off");
}
function sell()
{
	$("#fb1").removeClass("bb").css("top","1px");
	$("#fs1").addClass("bb").css("top","2px");

	$("#fb2").removeClass("ct1").addClass("off");
	$("#fs2").removeClass("off").addClass("ct1");
}
</script>

<p class="spb"><img src="images/n_zero.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></p>
<div class="p1 p2">
	<p class="fb1 bb" id="fb1" onMouseOver="buy()" style="position:relative;top:2px;z-index:1000 ">For Buyers</p>
	<p class="fs1" id="fs1" onMouseOver="sell()" style="position:relative;top:1px;z-index:1000;left:-18px;">For Suppliers</p>
</div>
<p class="c3"></p>
<div class="bc7 mr1 bl6" id="mdiv">

<div id="fb2" class="ct1">
<p class="one"><a href="post-buy-req.php">Post your Buy Requirement</a></p>
<p class="p12">Receive responses from<br>pre-verified and qualified suppliers.</p>
<p class="two">Search for a product</p>
<div class="p12">Send enquiries directly to the suppliers of your choice.
<script>
function validsearch_r()
{
	var keywords=document.getElementById('keywords_r');
	if(keywords.value=='' || keywords.value == null)
	{
		alert("Please enter a valid text to search.");
		return false;
	}
}
</script>
<form method="GET" name="searchForm2" action="search.php" onSubmit="return validsearch_r();">
<input type="hidden" name="rctyp" id="rctyp" value="Products"/>
<table cellpadding="0" cellspacing="0">
<tbody><tr>
<td><input size="24" class="m1 bl6" style="width:130px;" id="keywords_r" name="keywords" type="text"></td>
<td>
&nbsp; 
<input value="Search" class="m1 fz1 ff1 m5 r-block-search" style="width:55px;    margin-left: -25px;" type="submit"></td>
</tr>
</tbody></table></form>
</div>
<p class="thre" style="margin-top:-3px!important;"><a href="manage-selloffer-alert.php">Manage Sell Offer Alerts</a></p>
<p class="p12">Get Relevant and Updated Buy Leads and Sell Offers directly in your email.</p>
</div>

<div class="off" id="fs2">
<p class="one"><a href="create-free-website.php">Create your e-catalog for FREE !</a></p>
<p class="p12 m31">Get listed in our catalogs, advertise your products to thousands of buyers worldwide, and much more! </p>
<p class="two"><a href="post-sell-offer.php">Advertise FREE</a></p>
<p class="p12">Promote your offers and products<br>to the buyers worldwide.</p>
<p class="thre"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></p>
<p class="p12">Get updates on relevant buyer requirements directly in your email.</p></div></div>
<p class="c1 p33"></p></div></div>
<p id="flx" class="on"></p>


<?php
$sql_l_so="select * from sale_offer,user,business_profile where so_usr_id=usr_id and usr_id=bnsprof_uid and so_approval_status='1' ".$sql_so_ck." and so_status='1' and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)>=now() order by so_approval_date desc LIMIT 5";
$res_l_so=mysqli_query($con, $sql_l_so);
if(mysqli_num_rows($res_l_so)>0){
?>
<div class="mb1 c3">
<p class="bxt"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
<div class="bbx led" style="border:1px solid #DDD;border-radius:5px;width:248px;"><p class="bg bxh">Latest Sell Offers</p>
<!-- Trade Offers Start Thu Feb 20 16:40:01 2014-->
<ul id="sell_offers" class="ovr">
<?php	while($row_l_so=mysqli_fetch_object($res_l_so)){	?>
<li style="background-color:#FAF4FF">
<a style="background-position: 0px -1545px;" class="c_flg" rel="f_in" href="saleoffer-details.php?id=<?php echo rand(1000,9999).md5($row_l_so->so_id); ?>">
<img src="images/country_flag/<?php echo get_country_flag($row_l_so->country); ?>" style="margin-left:-15px;width:18px;" alt="<?php echo get_country_name($row_l_so->country); ?>" title="<?php echo get_country_name($row_l_so->country); ?>">&nbsp;&nbsp;
<?php echo ucwords(stripslashes($row_l_so->so_service)); ?></a>
<span>
Trade Scope:<?php
		if($row_l_so->so_preferred_buyer_location=='any')
		{
			echo "(Foreign & Domestic)";
		}
    	else if($row_l_so->so_preferred_buyer_location=='abroad')
		{
			echo "(Foreign Only)";
		}
		else if($row_l_so->so_preferred_buyer_location=='domestic')
		{
			echo "(Domestic Only)";
			?>
               <!--&nbsp;&nbsp;<img src="images/country_flag/<?php /*echo get_country_flag($row_l_so->country);*/ ?>" alt="" class="w4" align="top" height="15" width="23">-->
               <?php
		}
		else if($row_l_so->so_preferred_buyer_location=='my_city' && $row_l_so->bnsprof_city!='0')
		{
			echo get_city_name($row_l_so->bnsprof_city)."(250 KM)";
		}
?>
       </span> 
</li>
<?php } ?>
</ul><p class="c3 rm tr"><a href="sale-offers.php"> View all Sell Offers</a></p><!-- Trade Offers End -->
</div>
<p class="bxb"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
</div>
<?php	}	?>

<?php
$sql_l_bl="select * from buy_requirement,user,business_profile where br_u_id=usr_id and usr_id=bnsprof_uid and br_approval_status='1' and br_display_status='1' ".$sql_br_ck." and br_status='1' order by br_updated_date desc LIMIT 5";
$res_l_bl=mysqli_query($con, $sql_l_bl);
if(mysqli_num_rows($res_l_bl)>0){
?>

<div class="mb1 c3">
<p class="bxt"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
<div class="bbx led" style="border:1px solid #DDD;border-radius:5px;width:248px;"><p class="bg bxh">Latest Buy Leads</p>
<ul id="buy_leads" class="ovr">
<?php	while($row_l_bl=mysqli_fetch_object($res_l_bl)){	?>
<li style="background-color:#FAF4FF"><a style="background-position: 0px -1545px;" class="c_flg" rel="f_in" href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($row_l_bl->br_id); ?>"><img src="images/country_flag/<?php echo get_country_flag($row_l_bl->country); ?>" style="margin-left:-15px;width:18px;" alt="<?php echo get_country_name($row_l_bl->country); ?>" title="<?php echo get_country_name($row_l_bl->country); ?>">&nbsp;&nbsp;<?php echo $row_l_bl->br_pd_name; ?></a>
<span>
<?php if($row_l_bl->br_preferred_supplier_location != ''){
	?>
    Trade Scope:
    <?php
	if($row_l_bl->br_preferred_supplier_location=='any')
	{
				echo "(Foreign & Domestic)";
				
			}
	    	else if($row_l_bl->br_preferred_supplier_location=='abroad')
			{
				echo "(Foreign Only)";
			}
			else if($row_l_bl->br_preferred_supplier_location=='domestic')
			{	
				echo "(Domestic Only)";
			?>
			<!--<img src="images/country_flag/<?php /*echo get_country_flag($row_l_bl->country);*/ ?>" alt="" class="w4" align="top" height="12" width="20"/>-->
		<?php	}
			else if($row_l_bl->br_preferred_supplier_location=='my_city' && $row_l_bl->bnsprof_city!='0')
			{
				echo get_city_name($row_l_bl->bnsprof_city)."(250 KM)";
			}
	
    } ?>
</span></li>
<?php } ?>

</ul>
<p class="c3 rm tr"><a id="vbl" href="buyleads.php"> View all Buy Leads</a></p><!-- Trade Offers End -->
</div>

<p class="bxb"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
</div>
<?php	}	?>

<div id="m4t4">

 <?php
	$sql_adv="select * from advertisement where adv_imagewidth='250' and adv_imageheight='250' and adv_status='1' order by rand() limit 1";
	$res_adv=mysqli_query($con, $sql_adv);
	if(mysqli_num_rows($res_adv)>0)
	{
		$row_adv=mysqli_fetch_object($res_adv);	
		?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="250" height="250"/></a><?php
	}
	else
	{
?>
        <img alt="" src="upload/advertisement/250-250-advertisement.png" border="0" height="250" hspace="0" vspace="0" width="250">
<?php	}	?>

</div><br>



<p class="c3"></p>
<div style="margin:10px auto 0px;width:160px;">
<div style="float:left;margin-left;15px;">

</div>

</div>
</div>
</div>
<br>
<!--ryt End::-->
<br class="c3">
<br class="c3">
<!--Footer Start::-->
<br class="c3">
</div>

<!--new f6 footer starts-->
<!-- Footer Start Here::-->
<?php include 'includes/footer.php';?>
