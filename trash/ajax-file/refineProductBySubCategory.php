<style>
.bt {
    background-position: 0 -240px;
    margin: 18px 0 30px 0;
    clear: left;
    height: 0px !Important;
    background-color: transparent;
    color: transparent;
}
</style>
<?php
ob_start();
session_start();
include "../common.php";


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

?>

<?php
/*	$sql_pcat="select * from product_category_arabyos where md5(pc_id)='".$pc_id."'";
	$res_pcat=mysql_query($sql_pcat);
	$row_pcat=mysql_fetch_object($res_pcat);*/
	
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

	$sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name,s.pc_sort_name from product_category_arabyos m,product_category_arabyos c,product_category_arabyos s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and md5(s.pc_id)='".$pc_id."'";
	$res_pcat=mysql_query($sql_pcat);
	$row_pcat=mysql_fetch_array($res_pcat);
?>

<?php
 $sql_prd="select * from products,measurement_unit,country, business_profile, plan_member_id where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and plan_member_id.expiry_date > ". time() ."  and md5(pd_subcat_id)='".$pc_id."'  ORDER BY FIELD(p_id,'5','4','3','15')  LIMIT ".$start.", ".$per_page;

$recObj=mysql_query($sql_prd);

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!=''  and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and plan_member_id.expiry_date > ". time() ."   and md5(pd_subcat_id)='".$pc_id."'"; // Total records

$result_pag_num = mysql_query($query_pag_num);
$row = mysql_fetch_array($result_pag_num);
$count = $row['count'];
$no_of_paginations = ceil($count / $per_page);
$pagi_string="Page ".($page+1)." of ".$no_of_paginations;
/* ---------------Calculating the starting and endign values for the loop----------------------------------- */
if($cur_page >= 7)
{
    $start_loop = $cur_page - 3;
    if($no_of_paginations > $cur_page + 3)
        $end_loop = $cur_page + 3;
    else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6)
    {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    }
    else
    {
        $end_loop = $no_of_paginations;
    }
}
else
{
    $start_loop = 1;
    if($no_of_paginations > 7)
        $end_loop = 7;
    else
        $end_loop = $no_of_paginations;
}
?>

<div style="border:1px solid #F5ECFF;border-radius:5px;padding-left:10px;">
<h2 style="color:#36006C"><?php echo ucwords($row_pcat[3]); ?></h2>
</div>

<div class="als-container" id="product_slider" style="border:1px solid #F5ECFF;border-radius:5px;">
  <!--<span class="als-prev"><img src="images/thin_left_arrow_333.png" alt="prev" title="previous" /></span>-->
  <div class="als-viewport" align="center">
    <ul class="als-wrapper">
    <?php
	
	if(mysql_num_rows($recObj)>0)
	{
	while($row_prd=mysql_fetch_object($recObj))
	{
		$newtext = substr($row_prd->pd_title, 0, "12");
	?>
      <li class="als-item" style="border:1px solid #ccc;margin-top:1%;margin-left:1%;margin-bottom:1%;border-radius:4px; float:left; height:190px; background-color:rgba(251, 251, 251, 0.96);">
       <?php
		//$row_bprof=mysql_fetch_object(mysql_query("select bnsprof_id,bnsprof_city,bnsprof_state from business_profile where bnsprof_uid='".$row_prd->pd_uid."' limit 1"));
		
		 if(isset($_COOKIE['loc_id']))
	{
		$row_cityname=mysql_fetch_object(mysql_query("select ct_name from city where ct_id='".$row_prd->bnsprof_city."' limit 1"));
		 $cn_city=$row_cityname->ct_name;
		 
		 $row_statename=mysql_fetch_object(mysql_query("select state_name from states where state_id='".$row_prd->bnsprof_state."' limit 1"));
		 $cn_state=$row_statename->state_name;
		 
		 $cn_name=$cn_city."&nbsp;"."-&nbsp;".$cn_state;
		 
	}
	else {
		
		$row_cityname=mysql_fetch_object(mysql_query("select ct_cn_id from city where ct_id='".$row_prd->bnsprof_city."' limit 1"));
		
		$row_countryname=mysql_fetch_object(mysql_query("select cn_name from country where cn_id='".$row_cityname->ct_cn_id."' limit 1"));
		
		    $cn_name = $row_countryname->cn_name;
	     }
	  ?>
      <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_prd->bnsprof_id); ?>&sc=<?php echo rand(10000,99999).$row_prd->pd_subcat_id; ?>#<?php echo $row_prd->pd_id; ?>" style="text-decoration:none;color:#000" target="_blank">
      <img src="upload/myproduct/thumb/<?php echo $row_prd->pd_image; ?>" alt="<?php echo ucwords(substr($row_prd->pd_title,0,28)); ?>" title="<?php echo ucwords($row_prd->pd_title); ?>" />
      <div style="height:0%;margin-top:3%;padding-top:5%;"><span style="color:blue;"><b><?php echo ucwords($newtext); ?>....</b></span><br />
      
      <span style="color:red;"><?php echo $cn_name; ?></span></div>
<div style="height:10%;margin-top:21%; font-size:11px;">MOQ: <span style="color:red; font-weight: 600; font-size:15px;"><?php echo $row_prd->pd_min_order_qty; ?>&nbsp;</span><?php echo $row_prd->mu_name; ?></div>
<div style="height:10%;margin-top:1%; font-size:11px;"><?php echo $row_prd->cn_currency; ?>&nbsp;<span style="color:red; font-weight: 600; font-size:15px;"><?php echo $row_prd->pd_fob_price ?></span>/<?php echo $row_prd->mu_name; ?></div>
	  </a>
      </li>
	<?php }
	}
	else
	{	?>
		<li class="als-item" style="border:1px solid #484891;margin-top:1%;margin-left:1.5%;border-radius:4px;width:97%;height:20px;color:#F00">No products listed for this category.</li>
<?php	}
	?>

    </ul>
  </div>
   <?php if($count>0){ ?>
    <p class="cl"><br></p>
	<p align="center" style="margin-bottom:10px;">
        <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:refineProductBySubCategory('1','<?php echo $pc_id; ?>')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:refineProductBySubCategory('<?php echo $pre; ?>','<?php echo $pc_id; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:refineProductBySubCategory('<?php echo $nex; ?>','<?php echo $pc_id; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:refineProductBySubCategory('<?php echo $no_of_paginations; ?>','<?php echo $pc_id; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
	</p>
	<?php } ?>
  <!--<span class="als-next"><img src="images/thin_right_arrow_333.png" alt="next" title="next" /></span>-->
</div>
<?php } ?>
