<?php
include 'common.php';
set_time_limit(600);
if (isset($_COOKIE['loc_id'])) {
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
}


$SubCategoryArray = "";
$html = "";
$sql_cmt_cnt1 = "select DISTINCT pd_subcat_id  from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . "   ORDER BY FIELD(p_id,'5','4','3','15') ";
//echo "<!--$sql_cmt_cnt1-->";
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
    $sql_dd_cmnuParent = "select	DISTINCT pc_parent_id	from product_category_arabyos where pc_id in ('$SubCategoryArray') and pc_status=1 ";

    $res_dd_cmnuParent = mysql_query($sql_dd_cmnuParent);
    while ($row_dd_cmnuParent = mysql_fetch_object($res_dd_cmnuParent)) {
        $pc_id_arrNew[] = $row_dd_cmnuParent->pc_parent_id;

    }

    $ParentCategoryArray = join("','", $pc_id_arrNew);

    $MasterCategoryArray = "";
    $sql_dd_cmnuMaster = "select	DISTINCT pc_parent_id	from product_category_arabyos where pc_id in ('$ParentCategoryArray') and pc_status=1 ";
    $res_dd_cmnuMaster = mysql_query($sql_dd_cmnuMaster);
    while ($row_dd_cmnuMaster = mysql_fetch_object($res_dd_cmnuMaster)) {
        $pc_id_arrNewNew[] = $row_dd_cmnuMaster->pc_parent_id;

    }
    $MasterCategoryArray = join("','", $pc_id_arrNewNew);


     $first = isset($_POST['page'])?$_POST['page']:0;
     $sql_dd_cmnuAgainMaster_total = "select	DISTINCT pc_id	from product_category_arabyos where pc_id in ('$MasterCategoryArray') ";
    $sql_dd_cmnuAgainMaster = "select	DISTINCT pc_id	, pc_image ,	pc_name 	from product_category_arabyos where pc_id in ('$MasterCategoryArray') and pc_status=1 order by pc_name asc limit $first ,14";
     $res_dd_cmnuAgainMaster = mysql_query($sql_dd_cmnuAgainMaster);
    $res_dd_cmnuAgainMaster_total = mysql_query($sql_dd_cmnuAgainMaster_total);
    $total_count = mysql_num_rows($res_dd_cmnuAgainMaster_total);
   $c=1; while ($row_dd_cmnuAgainMaster = mysql_fetch_object($res_dd_cmnuAgainMaster)) {
        $html.="<li class=\"ptag \"><a class=\"mobile-click stop_redirect\" data-id=\"".$c."\" href=\"category.php?token=".md5($row_dd_cmnuAgainMaster->pc_id)."\">";
        $html .="<img  style=\"height:30px; margin-right: 10px; width:30px;\" src=\"http://arabyos.com/upload/category/". $row_dd_cmnuAgainMaster->pc_image."\">";
        $html .=$row_dd_cmnuAgainMaster->pc_name."<span class=\"main_links_span\"></span> </a><div class=\"typography_3_colm r_".$c."\" ><div class=\"colm_3_container\">";

        $sql_dd_cmnuAgainParent = "select	 pc_id	,	pc_name 	from product_category_arabyos where pc_parent_id = '" . $row_dd_cmnuAgainMaster->pc_id . "'  and pc_id in ('$ParentCategoryArray') and pc_status=1 order by pc_name asc ";
        $res_dd_cmnuAgainParent = mysql_query($sql_dd_cmnuAgainParent);
        $rowcount = mysql_num_rows($res_dd_cmnuAgainParent);
        while ($row_dd_cmnuAgainParent = mysql_fetch_object($res_dd_cmnuAgainParent)) {
            unset($pc_id_arrNewNewNew);
            $iChildAgain = "";
            $sql_dd_cmnuAgainChild = "select	 pc_id	from product_category_arabyos where pc_parent_id = '" . $row_dd_cmnuAgainParent->pc_id . "'  and pc_id in ('$SubCategoryArray') and pc_status=1";
            $res_dd_cmnuAgainChild = mysql_query($sql_dd_cmnuAgainChild);
            while ($row_dd_cmnuAgainChild = mysql_fetch_object($res_dd_cmnuAgainChild)) {
                $pc_id_arrNewNewNew[] = $row_dd_cmnuAgainChild->pc_id;
            }
            $iChildAgain = join("','", $pc_id_arrNewNewNew);
            $count = 0;
            $row['count'] = 0;
            $query_pag_num = "select count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
			where mu_id=pd_unit and pd_currency=cn_id " . $sql_pd_ck . " and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . " and pd_subcat_id in('$iChildAgain') ORDER BY FIELD(p_id,'5','4','3','15')";
            $result_pag_num = mysql_query($query_pag_num);
            $row = mysql_fetch_array($result_pag_num);
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
           $html .="<span style='padding-left:15px;cursor:pointer; ";
            if($_POST['page']==$first)
                $html .= "color:red; ";
            $html .= "' onclick='get_load_leftdata(".$first.")'>".($i+1)."</span>";
        }
       $html.="</a></li>";
     }
     $html .="<li class=\"ptag\"><a class=\"\" href=\"dir.php\"><span></span>View All Categories >> <span class=\"main_links_span\"></span></a></li>";
}

echo $html;


