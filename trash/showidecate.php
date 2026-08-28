<?php
error_reporting(0);
session_start();
/*$server 	= "localhost";
$user  		= "arabyos4_ab2016u";	
$db_name 	= "arabyos4_ab2016u";
$pass 		= "!Gv][-[{rZ2Q"; 	*/
include 'lib/connect.php';
$con = mysqli_connect($server, $user, $pass, $db_name);
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$link = mysql_connect($server, $user, $pass);
$db_selected = mysql_select_db($db_name, $link);



if(isset($_COOKIE['loc_id']))
{
	$sql_pd_ck=" and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(pd_preferred_buyer_location='my_city'  and pd_uid in(select distinct bnsprof_uid from business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id where c.ct_cn_id='".$_COOKIE['loc_id']."')))";
}
else
{
	$sql_pd_ck=" and (
	(pd_preferred_buyer_location='any')
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
}
?>


<?php
$SubCategoryArray ="";
$sql_cmt_cnt1 = "select DISTINCT pd_subcat_id  from products INNER JOIN product_category ON (pd_subcat_id=pc_id),measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ."   ORDER BY pc_name ";// FIELD(/*p_id,'5','4','3','15'*/)
$res_dd_mnu=mysql_query($sql_cmt_cnt1);
while($row_dd_mnu=mysql_fetch_object($res_dd_mnu))
	{
	  $pc_id_arr[]=$row_dd_mnu->pd_subcat_id;		
	}
	$SubCategoryArray = join("','",$pc_id_arr); 

	
	 $ParentCategoryArray ="";
	 $sql_dd_cmnuParent="select	DISTINCT pc_parent_id	from product_category where pc_id in ('$SubCategoryArray')  ORDER BY pc_name";
	$res_dd_cmnuParent=mysql_query($sql_dd_cmnuParent);
	while($row_dd_cmnuParent=mysql_fetch_object($res_dd_cmnuParent))
		{	
		 $pc_id_arrNew[]=$row_dd_cmnuParent->pc_parent_id;	
	
		}
	$ParentCategoryArray = join("','",$pc_id_arrNew); 
	
	 $MasterCategoryArray ="";
	 $sql_dd_cmnuMaster="select	DISTINCT pc_parent_id	from product_category where pc_id in ('$ParentCategoryArray')  ORDER BY pc_name";
	$res_dd_cmnuMaster=mysql_query($sql_dd_cmnuMaster);
	while($row_dd_cmnuMaster=mysql_fetch_object($res_dd_cmnuMaster))
		{	
		 $pc_id_arrNewNew[]=$row_dd_cmnuMaster->pc_parent_id;	
	
		}
	$MasterCategoryArray = join("','",$pc_id_arrNewNew); 
	?>
     <ul>
     <?php
	
	 $sql_dd_cmnuAgainMaster="select	 pc_id	,	pc_name 	from product_category where pc_id in ('$MasterCategoryArray')  ORDER BY pc_name";
	$res_dd_cmnuAgainMaster=mysql_query($sql_dd_cmnuAgainMaster);
	while($row_dd_cmnuAgainMaster=mysql_fetch_object($res_dd_cmnuAgainMaster))
		{	
		 
		 ?>
<li class='has-sub'><a href="category.php?token=<?php echo rand(10,9999).md5($row_dd_cmnuAgainMaster->pc_id); ?>"><span><?php echo $row_dd_cmnuAgainMaster->pc_name;?></span></a><!--category.php?token=-->
    <ul>
     <?php
	 $sql_dd_cmnuAgainParent="select	 pc_id	,	pc_name 	from product_category where pc_parent_id = '".$row_dd_cmnuAgainMaster->pc_id."'  and pc_id in ('$ParentCategoryArray')  ORDER BY pc_name";
	$res_dd_cmnuAgainParent=mysql_query($sql_dd_cmnuAgainParent);
	while($row_dd_cmnuAgainParent=mysql_fetch_object($res_dd_cmnuAgainParent))
		{	
		
		
		unset($pc_id_arrNewNewNew);
	$iChildAgain ="";
	$sql_dd_cmnuAgainChild="select	 pc_id	from product_category where pc_parent_id = '".$row_dd_cmnuAgainParent->pc_id."'  and pc_id in ('$SubCategoryArray')  ORDER BY pc_name";
	$res_dd_cmnuAgainChild=mysql_query($sql_dd_cmnuAgainChild);
	while($row_dd_cmnuAgainChild=mysql_fetch_object($res_dd_cmnuAgainChild))
		{	
		 
		 $pc_id_arrNewNewNew[]=$row_dd_cmnuAgainChild->pc_id;	
 		}
$iChildAgain = join("','",$pc_id_arrNewNewNew); 
$count=0;
$row['count']=0;
 $query_pag_num = "select count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$iChildAgain') ORDER BY FIELD(p_id,'5','4','3','15')";
$result_pag_num = mysql_query($query_pag_num);
$row = mysql_fetch_array($result_pag_num);
$count = $row['count'];		 
		 ?>
<li class='has-sub'><a href="products.php?c=<?php echo md5($row_dd_cmnuAgainParent->pc_id); ?>"><span><?php echo $row_dd_cmnuAgainParent->pc_name;?> (<?php echo $count ?>)</span></a></li>
<?php } ?>
</ul>
    
    
    <?php 
	
	
		}
	?>
    </li>
		 <li><a href="dir.php"> View All Categories </a></li>
    <?php
	
	exit;
 ?>


<ul>
<?php

$sql_dd_mnu="select pc_id,pc_name from product_category where pc_parent_id = '0' and pc_status = '1' order by pc_id ".$sql_order;
$res_dd_mnu=mysql_query($sql_dd_mnu);
while($row_dd_mnu=mysql_fetch_object($res_dd_mnu))
{
	$sql_dd_cmnu="select pc_id from product_category where pc_parent_id = '".$row_dd_mnu->pc_id."' and  pc_status = '1' ".$sql_order;
	$res_dd_cmnu=mysql_query($sql_dd_cmnu);
	$final =0;
	$pc_id_arrx ="";
	$ids1="";
	while($row_dd_cmnu1=mysql_fetch_object($res_dd_cmnu))
	{
	$sql_check2="select pc_id from product_category where  pc_parent_id ='".$row_dd_cmnu1->pc_id."'";
	$res_check2=mysql_query($sql_check2)or die('MySql Error' . mysql_error());
     	while($data1=mysql_fetch_object($res_check2)){
		        $pc_id_arrx[]=$data1->pc_id;		
	}
	$ids1 = join("','",$pc_id_arrx); 
$sql_cmt_cnt1 = "select count(*) AS count2 from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids1') ORDER BY FIELD(p_id,'5','4','3','15') limit 0 , 1";
$result_pag_num = mysql_query($sql_cmt_cnt1);
$row = mysql_fetch_object($result_pag_num);
$final += $row->count2;
	}
if($final>0){
?>
	<li class='has-sub'><a href="category.php?token=<?php echo rand(10,9999).md5($row_dd_mnu->pc_id); ?>"><span><?php echo $row_dd_mnu->pc_name;?></span></a><!--category.php?token=-->
    <ul>
    <?php
	$sql_dd_cmnu="select pc_id,pc_sort_name from product_category where pc_parent_id = '".$row_dd_mnu->pc_id."' and pc_status = '1' ".$sql_order;
	$res_dd_cmnu=mysql_query($sql_dd_cmnu);
	$ids ="";
	while($row_dd_cmnu=mysql_fetch_object($res_dd_cmnu))
	{
$sql_check1="select pc_id from product_category where pc_parent_id='".$row_dd_cmnu->pc_id."'";
$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
     	while($data=mysql_fetch_object($res_check1)){
		        $pc_id_arr[]=$data->pc_id;		
	}
	$ids = join("','",$pc_id_arr); 
$query_pag_num = "select count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY FIELD(p_id,'5','4','3','15')";
$result_pag_num = mysql_query($query_pag_num);
$row = mysql_fetch_object($result_pag_num);
$count = $row->count;
		if($count >0 ){ ?>
<li><a href="products.php?c=<?php echo md5($row_dd_cmnu->pc_id); ?>"><span><?php echo ucwords($row_dd_cmnu->pc_sort_name); ?> ( <?php echo $count;?> ) </span></a><!--catcompany.php?token=-->
        <?php 	}    ?>
</li>
<?php 	
$pc_id_arr ="";
}
	?>
    </ul>
    </li>
<?php 
	 }
		}
	
			 ?>
	
</ul>
