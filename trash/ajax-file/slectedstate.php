<div>
	<?php 
	include "../common.php";
	$id=(int)$_REQUEST['id'];//POST no longer working?? 
	$pc_id=$cid = $_REQUEST['cid'];
	?>
	<p>
<?php
	$country="SELECT * FROM `country` where cn_id=$id";
	$c=mysql_query($country);
	while($data=mysql_fetch_array($c)){
		?>
		<span class="outer_c"><input type="checkbox" name="country_sel" value="<?php echo $data['cn_id'];?>"><span style="font-weight:bold;">
		<?php
		echo $data['cn_name'];?></span><i class="fa fa-angle-down close_state" id="<?php echo $data['cn_id'];?>" style="font-size: 15px;margin-left: 5px;cursor: pointer;"></i></span>
		<?php
		
	}
	
	?>	
	</p>
	<?php
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

	$parent = 'parent_';
	if ($_REQUEST['is_sub']) {
		$parent = '';
	}
    $sql_check1="select pc_id from product_category where  md5(pc_{$parent}id)='".$pc_id."'";
    $res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
    while($data=mysql_fetch_array($res_check1)){
        $pc_id_arr[]=$data['pc_id'];
    }
    $ids = join("','",$pc_id_arr);

//from index_ls_countries
//$view_country = "select * from products,measurement_unit,country,business_profile,plan_member_id where mu_id=pd_unit and (pd_title LIKE '%".$_GET['keywords']."%') and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id  and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' AND plan_member_id.expiry_date > " . time() . " and pd_image!='' group by pd_id";
$country = "select DISTINCT user.country, country.cn_name from products,measurement_unit,country INNER JOIN `user` ON user.country = country.cn_id,business_profile,plan_member_id where mu_id=pd_unit and (/*pd_title LIKE '%".$_GET['keywords']."%'*/ pd_subcat_id in('$ids') ) and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id  and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' AND plan_member_id.expiry_date > " . time() . " and pd_image!='' group by pd_id";
//echo $view_country;

$country = "select DISTINCT /*user.country*/ country.cn_id as country, country.cn_name from products,measurement_unit,country /*INNER JOIN `user` ON user.country = country.cn_id*/, business_profile, plan_member_id, smembership_plan
where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY FIELD(p_id,'5','4','3','15') /*LIMIT ".$start.", ".$per_page."*/";

$country = "select DISTINCT states.state_id, states.state_name from products,measurement_unit,country,states, business_profile, plan_member_id, smembership_plan
where mu_id=pd_unit and pd_currency=cn_id and state_cn_id=$id and states.state_id=business_profile.bnsprof_state ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY state_id/*FIELD(p_id,'5','4','3','15') */";

			$view_country2 = "select DISTINCT  business_profile.bnsprof_state state_id, states.state_name from products,business_profile,plan_member_id, states  where pd_image!=''  and (products.pd_subcat_id in('$ids')) and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id AND plan_member_id.expiry_date > " . time() . " and pd_status='1' ".$sql_pd_ck." AND state_cn_id=bnsprof_state group by products.pd_uid";

	$state="SELECT * FROM `states` where state_cn_id=$id";
	$cc=mysql_query($country);
	while($data1=mysql_fetch_array($cc)){//print_r($data1);
		?>
		<span class="outer_c"><input type="checkbox" name="state_sel" value="<?php echo $data1['state_id']; ?>"><span>
		<?php
		echo $data1['state_name'];?></span></span>
		<?php
		
	}
	
	?>
	</div>
