<?php
require_once __DIR__ . '/lib/connect.php';
$con = $connection ?? $con ?? null;
if (!$con) {
    die("Database connection failed");
}





function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo
|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
, $_SERVER["HTTP_USER_AGENT"]);
}
if(isMobileDevice()){
   $class= "stop_redirect";
   file_put_contents(__DIR__ . '/mobile_test.log', date('Y-m-d H:i:s') . " - تم اكتشاف موبايل\n", FILE_APPEND);
} else {
     $class= "";
}


include 'common.php';

require_once __DIR__ . '/lib/connect.php';
$connection = $con;

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

set_time_limit(100);


if (isset($_COOKIE['loc_id']) && $_COOKIE['loc_id'] != '') {
    // حالة: المستخدم اختار بلد معين من الهيدر
    $selected_country = $_COOKIE['loc_id'];
    
    
    
    
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM `user` WHERE country='$selected_country'))
        OR 
        (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM `user` WHERE country='$selected_country'))
        OR
        (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id WHERE c.ct_cn_id='$selected_country'))
        OR
        (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM `user` WHERE country='$selected_country'))
    )";
} else {
    // حالة: Global (لم يختار المستخدم بلداً)
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='any')
        OR
        (pd_preferred_buyer_location='abroad')
    )";
}


/*if (isset($_COOKIE['loc_id'])) {
    $sql_pd_ck = " and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from `user` where country='" . $_COOKIE['loc_id'] . "')) 
	or 
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from `user` where country='" . $_COOKIE['loc_id'] . "'))
	or
	(pd_preferred_buyer_location='my_city'  and pd_uid in(select distinct bnsprof_uid from business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id where c.ct_cn_id='" . $_COOKIE['loc_id'] . "')))";
} else {
    $sql_pd_ck = " and (
	(pd_preferred_buyer_location='any')
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from `user` where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
	)";
}*/


$SubCategoryArray = "";
$html = "";
$sql_cmt_cnt1 = "select DISTINCT pd_subcat_id  from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . "   ORDER BY FIELD(p_id,'5','4','3','15') ";
//echo "<!--$sql_cmt_cnt1-->";
$res_dd_mnu = mysqli_query($con,$sql_cmt_cnt1);
$pc_id_arr = array();
while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)) {
    $pc_id_arr[] = $row_dd_mnu->pd_subcat_id;
}

if (count($pc_id_arr) == 0) {
    $html .= '<li class="ptag text-danger" style="padding: 5px;">Currently, this country has not any business to display !</li>';
}
 else {
    $SubCategoryArray = join("','", $pc_id_arr);


    $ParentCategoryArray = "";
    $sql_dd_cmnuParent = "select	DISTINCT pc_parent_id	from product_category where pc_id in ('$SubCategoryArray') and pc_status=1 ";

    $res_dd_cmnuParent = mysqli_query($con,$sql_dd_cmnuParent);
    while ($row_dd_cmnuParent = mysqli_fetch_object($res_dd_cmnuParent)) {
        $pc_id_arrNew[] = $row_dd_cmnuParent->pc_parent_id;

    }

    $ParentCategoryArray = join("','", $pc_id_arrNew);

    $MasterCategoryArray = "";
    $sql_dd_cmnuMaster = "select	DISTINCT pc_parent_id	from product_category where pc_id in ('$ParentCategoryArray') and pc_status=1 ";
    $res_dd_cmnuMaster = mysqli_query($con,$sql_dd_cmnuMaster);
    while ($row_dd_cmnuMaster = mysqli_fetch_object($res_dd_cmnuMaster)) {
        $pc_id_arrNewNew[] = $row_dd_cmnuMaster->pc_parent_id;

    }
    $MasterCategoryArray = join("','", $pc_id_arrNewNew);


     $first = isset($_POST['page'])?$_POST['page']:0;
     $sql_dd_cmnuAgainMaster_total = "select	DISTINCT pc_id	from product_category where pc_id in ('$MasterCategoryArray') ";
    $sql_dd_cmnuAgainMaster = "select	DISTINCT pc_id	, pc_image ,	pc_name 	from product_category where pc_id in ('$MasterCategoryArray') and pc_status=1 order by pc_order, pc_name asc limit $first ,14";
     $res_dd_cmnuAgainMaster = mysqli_query($con,$sql_dd_cmnuAgainMaster);
    $res_dd_cmnuAgainMaster_total = mysqli_query($con,$sql_dd_cmnuAgainMaster_total);
    $total_count = mysqli_num_rows($res_dd_cmnuAgainMaster_total);
   $c=1; while ($row_dd_cmnuAgainMaster = mysqli_fetch_object($res_dd_cmnuAgainMaster)) {
        $html.="<li class=\"ptag \"><a class=\"mobile-click ".$class."\" data-id=\"".$c."\" href=\"category.php?token=".rand(1000,9999).md5($row_dd_cmnuAgainMaster->pc_id)."\">";
        $html .="<img  style=\"height:30px; margin-right: 10px; width:30px;\" src=\"http://egyptmart.shop/upload/category/". $row_dd_cmnuAgainMaster->pc_image."\">";
        $html .=$row_dd_cmnuAgainMaster->pc_name."<span class=\"main_links_span\"></span> </a><div class=\"typography_3_colm r_".$c."\" ><div class=\"colm_3_container\">";

        $sql_dd_cmnuAgainParent = "select	 pc_id	,	pc_name 	from product_category where pc_parent_id = '" . $row_dd_cmnuAgainMaster->pc_id . "'  and pc_id in ('$ParentCategoryArray') and pc_status=1 order by pc_order, pc_name asc ";
        $res_dd_cmnuAgainParent = mysqli_query($con,$sql_dd_cmnuAgainParent);
        $rowcount = mysqli_num_rows($res_dd_cmnuAgainParent);
        while ($row_dd_cmnuAgainParent = mysqli_fetch_object($res_dd_cmnuAgainParent)) {
            unset($pc_id_arrNewNewNew);
            $iChildAgain = "";
            $sql_dd_cmnuAgainChild = "select	 pc_id	from product_category where pc_parent_id = '" . $row_dd_cmnuAgainParent->pc_id . "'  and pc_id in ('$SubCategoryArray') and pc_status=1";
            $res_dd_cmnuAgainChild = mysqli_query($con,$sql_dd_cmnuAgainChild);
            while ($row_dd_cmnuAgainChild = mysqli_fetch_object($res_dd_cmnuAgainChild)) {
                $pc_id_arrNewNewNew[] = $row_dd_cmnuAgainChild->pc_id;
            }
            $iChildAgain = join("','", $pc_id_arrNewNewNew);
            $count = 0;
            $row['count'] = 0;
            $query_pag_num = "select count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
			where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . " and pd_subcat_id in('$iChildAgain') ORDER BY FIELD(p_id,'5','4','3','15')";
            $result_pag_num = mysqli_query($con,$query_pag_num);
            $row = mysqli_fetch_array($result_pag_num);
            $count = $row['count'];
            $html .="<div class=\"colmn_3_fullwidth\" style=\"    margin-bottom: -11px!important;  padding: 8px 0px!important;\"><ol class=\"ptaga some_links\">";
            $html .="<li><a href=\"products.php?c=".md5($row_dd_cmnuAgainParent->pc_id)."\"  class=\"ptaga\">". $row_dd_cmnuAgainParent->pc_name."</a></li></ol></div>";
        }
        $html .="</div></div></li>";
		$c++;

    }

     if($total_count/14>1){

         $html .="<li class=\"page_bar_item\"><a class=\"\" >";
        $page = floor($total_count/14);
        for($i=0;$i<=$page;$i++){
            $first = 14*$i;
           $html .="<span style='padding-left:40px;cursor:pointer; ";
            if(($_POST['page'] ?? 0) == $first)
                $html .= "color:red; ";
            $html .= "' onclick='get_load_leftdata(".$first.")'>".($i+1)."</span>";
        }
       $html.="</a></li>";
     }
     $html .="<li class=\"ptag\"><a class=\"\" href=\"dir.php\"><span></span> << شاهد كل التصنيفات <span class=\"main_links_span\"></span></a></li>";
}


echo $html;


