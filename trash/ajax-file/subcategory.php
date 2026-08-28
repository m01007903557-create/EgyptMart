<ul>
<?php
$sql_dd_mnu="select pc_id,pc_name,pc_image from product_category_arabyos where pc_parent_id = '0' and pc_status = '1' ".$sql_order;
$res_dd_mnu=mysql_query($sql_dd_mnu);
while($row_dd_mnu=mysql_fetch_object($res_dd_mnu))
{
//################################

	$sql_dd_cmnu="select pc_id,pc_sort_name from product_category_arabyos where pc_parent_id = '".$row_dd_mnu->pc_id."' and  pc_status = '1' ".$sql_order;
	$res_dd_cmnu=mysql_query($sql_dd_cmnu);
	$final =0;
	$pc_id_arrx ="";
	$ids1="";
	while($row_dd_cmnu1=mysql_fetch_object($res_dd_cmnu))
	{

	$sql_check2="select pc_id from product_category_arabyos where md5(pc_parent_id)='".md5($row_dd_cmnu1->pc_id)."'";
	$res_check2=mysql_query($sql_check2)or die('MySql Error' . mysql_error());
     	while($data1=mysql_fetch_array($res_check2)){
		        $pc_id_arrx[]=$data1['pc_id'];		
	}
	$ids1 = join("','",$pc_id_arrx); 
		
$sql_cmt_cnt1 = "select count(*) AS count2 from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids1') ORDER BY FIELD(p_id,'5','4','3','15')";


$result_pag_num = mysql_query($sql_cmt_cnt1);
$row = mysql_fetch_array($result_pag_num);
$final += $row['count2'];
	}

 if($final>0){

//################################	
?>
	<li class='has-sub'><a href="category.php?token=<?php echo rand(10,9999).md5($row_dd_mnu->pc_id); ?>"><span><?php echo $row_dd_mnu->pc_name;?></span></a><!--category.php?token=-->
    <ul>
    <?php
	$sql_dd_cmnu="select pc_id,pc_sort_name from product_category_arabyos where pc_parent_id = '".$row_dd_mnu->pc_id."' and pc_status = '1' ".$sql_order;
	$res_dd_cmnu=mysql_query($sql_dd_cmnu);
	$ids ="";
	while($row_dd_cmnu=mysql_fetch_object($res_dd_cmnu))
	{
			
$sql_check1="select pc_id from product_category_arabyos where md5(pc_parent_id)='".md5($row_dd_cmnu->pc_id)."'";
$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
     	while($data=mysql_fetch_array($res_check1)){
		        $pc_id_arr[]=$data['pc_id'];		
	}
	$ids = join("','",$pc_id_arr); 
		
$query_pag_num = "select count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY FIELD(p_id,'5','4','3','15')";

$result_pag_num = mysql_query($query_pag_num);
$row = mysql_fetch_array($result_pag_num);
$count = $row['count'];
		
		
		///
		if($count >0 ){
		
	?>
  
		<li><a href="products.php?c=<?php echo md5($row_dd_cmnu->pc_id); ?>"><span><?php echo ucwords($row_dd_cmnu->pc_sort_name); ?> ( <?php echo $count;?> ) </span></a><!--catcompany.php?token=-->
        <?php
		}  
		  ?>
        </li>
<?php 	
$pc_id_arr ="";
}
	?>
    </ul>
    </li>
<?php  }
}mysql_close(); ?>
</ul>