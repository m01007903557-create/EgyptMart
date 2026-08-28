<?php
ob_start();
session_start();
include "../common.php";


//from refineProductBySubCategory_new.php
if($_POST['page'])
{
$page = $_POST['page'];
$pc_id=$_POST['id'];

$cur_page = $page;
$page -= 1;
$per_page = 20; // Per page records
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;
    if(isset($_COOKIE['loc_id']))
    {
        $sql_pd_ck=" and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
        /*
        (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
        or
        (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
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
    }
    $sql_sub_cat = "";

    if(isset($_POST['is_sub'])&&$_POST['is_sub']=="true") {
        $sql_sub_cat = " AND md5(pd_subcat_id)='".$pc_id."'";
    }
    else {
        $sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name from product_category m,product_category c where m.pc_id=c.pc_parent_id and md5(c.pc_id)='".$pc_id."'";
        $res_pcat=mysql_query($sql_pcat);
        $row_pcat=mysql_fetch_array($res_pcat);
        $sql_check1="select pc_id from product_category where  md5(pc_parent_id)='".$pc_id."'";
        $res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
        while($data=mysql_fetch_array($res_check1)){
            $pc_id_arr[]=$data['pc_id'];
        }
        $ids = join("','",$pc_id_arr);
        $sql_sub_cat = " AND  pd_subcat_id IN ('".$ids."')";
    }


    $member = "";
    if(isset($_POST['mst_type'])) {
        $member = $_POST['mst_type'];
        if($member!=""){
            $member = " and p_id IN (".$member.")";
        }
    }
    $minorder = "";
    if(isset($_POST['min_order'])) {
        $minorder = $_POST['min_order'];
        if($minorder!=""&&$minorder!="0"){
            $minorder = " and pd_min_order_qty =".$minorder;
        }
    }
    $country = "";
    if(isset($_POST['country'])) {
        $country = mysql_real_escape_string($_POST['country']);
        if($country!=""){
            $country = " and cn_id IN (".$country.")";
        }
    }
/* from md_search,  TODO loc_id* /
                        if (isset($_COOKIE['loc_id'])) {
                            $checkCountry = " AND cn_id = '" . $_COOKIE['loc_id'] . "'";
                            if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) {
                                $scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%" . $_POST['scity'] . "%' and ct_cn_id=" . $_COOKIE['loc_id'] . " )) and";
                            }
                            if (strlen($scity) > 0)
                                $sql_pd_country = " and  ( " . $scity . "
					( pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "')))";
                        }
                        else {
                            $checkCountry = "";
                            if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) {
                                $scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%" . $_POST['scity'] . "%' ) ) ";
                            }
                            if (strlen($scity) > 0)
                                $sql_pd_country = " and  (  " . $scity . ")";
                        }
                        $newkw = generateProdSearchString($keywords);
                        $sql_prd = "select measurement_unit.*,country.*," . $bus_col . "," . $prod_col . ", MATCH (pd_title) AGAINST ('" . $keywords . "'  IN BOOLEAN MODE) AS title_relevance  from products,measurement_unit,country,business_profile,plan_member_id where bnsprof_uid=pd_uid and b_id = bnsprof_id  and mu_id=pd_unit   and (pd_title LIKE " . $newkw . ") and pd_currency=cn_id " . $sql_pd_country . " AND pd_status='1'" . $checkCountry . " AND pd_image!=''  " . $keyword . " AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15'), pd_title asc limit 0,20";
/**/

	$sql_pd_city = '';
	if (isset($_POST['city']) && strlen($_POST['city']) > 0) {
		$scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%" . mysql_real_escape_string($_POST['city']) . "%' ) ) ";
		$sql_pd_city = " and  (  " . $scity . ")";
	}

	$sql_pd_state = '';
	if (isset($_POST['state']) && strlen($_POST['state']) > 0) {
		$sql_pd_state = " and bnsprof_state IN(" . mysql_real_escape_string($_POST['state']) . ")";
		if ($country) {
			$sql_pd_state = str_replace(' and bnsprof_state', ' or bnsprof_state', $sql_pd_state );
			$country = str_replace(' and cn_id', ' and (cn_id', $country ) . $sql_pd_state . ')';
			$sql_pd_state = '';
		}
	}

    $sql_prd="select * from products,measurement_unit,country c, business_profile, plan_member_id, smembership_plan, city, country c2
 where c2.cn_id=pd_currency AND bnsprof_city=ct_id AND mu_id=pd_unit and /*pd_currency=cn_id*/ cn_id=ct_cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." ".$sql_sub_cat." ". $member ." ".$minorder." $country $sql_pd_city $sql_pd_state ORDER BY FIELD(p_id,'5','4','3','15') ";

    $sql_prd="select pc_id, pc_sort_name, count(pd_id) as tot_prod from products JOIN product_category ON (pd_subcat_id=pc_id),measurement_unit,country c, business_profile, plan_member_id, smembership_plan, city, country c2
 where c2.cn_id=pd_currency AND bnsprof_city=ct_id AND mu_id=pd_unit and /*pd_currency=cn_id*/ cn_id=ct_cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." ".$sql_sub_cat." ". $member ." ".$minorder." $country $sql_pd_city $sql_pd_state GROUP BY pd_subcat_id ORDER BY FIELD(p_id,'5','4','3','15') ";
	$sql_prd = preg_replace('#([^.a-z_-])cn_id#msi', '$1c.cn_id', $sql_prd);
    //echo $sql_prd;exit;


            $res_subcat=mysql_query($sql_prd);
            while($row_subcat=mysql_fetch_object($res_subcat))
            {
                if($row_subcat->tot_prod){
                    ?>
                    <div class="item-list">
                        <a style="cursor:pointer;" onClick="/*refineProductBySubCategory*/loadProductByCategory(1,'<?php echo md5($row_subcat->pc_id); ?>', true);"><?php echo ucwords($row_subcat->pc_sort_name)." (".($row_subcat->tot_prod).")"; ?></a><?php if ($_POST['is_sub'] && $_POST['is_sub'] != 'false') { ?><button class="btn btn-xs btn-default border-radius-0 " onclick="/*refineProductBySubCategory*/loadProductByCategory(1,main_cat/*,-1*/);" style="padding:0 5px 0 5px">Cancel</button><?php } ?>
                    </div>
                <?php	} }
}
