<style>
.text-right button.btn.btn-default.btn-xs {
    height: auto !Important;
    width: auto !Important;
}
button.btn.btn-sm.btn-default.border-radius-0.txt-bold.bold-xs.btn-white.text-capitalize {
    height: auto !Important;
    width: auto !Important;
}
.right-section-search-buylead {
    position: absolute;
    right: 0;
}
#right-image{max-width: 238px !important;}
@media (max-width: 480px){
	.ryt.fl.ser-right.right-section-search-buylead {
	     display: block ; 
	     clear: both;
	}
}
</style>
<?php 
if($_GET["rctyp"] == "tender"){
?>
</div>
<?php } ?>
<div class="ryt fl ser-right right-section-search-buylead" >
<!---------------------------------------------------->



<?php	if(isset($_COOKIE['loc_id']))
	{
	
		$sql_pd_ck=" and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".mysql_real_escape_string($_COOKIE['loc_id'])."'))
	or
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".mysql_real_escape_string($_COOKIE['loc_id'])."'))
	or
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".mysql_real_escape_string($_COOKIE['loc_id'])."'))))";
	
		
	$sql_br_ck=" and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	
	$sql_tnd_ck=" and ((tnd_preferred_location='domestic' and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(tnd_preferred_location='any' and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(tnd_preferred_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	
	
	}
	
	else
	{
		
		$sql_pd_ck=" and (

	(pd_preferred_buyer_location='any')
	or

	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".mysql_real_escape_string($location_geo_country[0])."')))
	)";
		
	
	$sql_br_ck=" and (
	
	(br_preferred_supplier_location='any')
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country[0]."')))
	)";
	$sql_tnd_ck=" and (
	
	(tnd_preferred_location='any')
	or
	(tnd_preferred_location='abroad' and tnd_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country[0]."')))
	)";
	
		}
	


?>




<?php if($_GET['keywords']!=""){ 
	if(isset($_GET['rctyp']) || isset($_GET['grid']) )
	{
	  if($_GET['rctyp']!='Suppliers' || $_GET['grid'] == 'active')
	  {
?>
    <div class="rht_related_cat" >
    
<?php  if($_GET['rctyp']!='tender' && $_GET['rctyp']!='buy_lead'){ ?> 
      <div class="">
        <section>
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
//echo 	"SELECT * FROM `product_category_arabyos` WHERE `pc_name` LIKE '".$_GET['keywords']."' ORDER BY `pc_id` DESC";
	
	    $sqls="SELECT * FROM `product_category_arabyos` WHERE `pc_name`  LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' and pc_status='1'  ORDER BY `pc_id` DESC";
		$ress=mysql_query($sqls);
			/**/
		$RelatedCatCount = mysql_num_rows($ress);
			/**/
		$rows=mysql_fetch_object($ress);
		$catId = $rows->pc_id;
		$catParentId = $rows->pc_parent_id;
	 
	 $iParentId = '';
	$iProductCategoryId=mysql_query("SELECT  pd_subcat_id FROM `products` JOIN `business_profile` ON business_profile.bnsprof_uid = products.pd_uid WHERE (`pd_title` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' OR `bnsprof_compname` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%') and pd_status='1' ");
	$iCategoryDetail = mysql_fetch_object($iProductCategoryId);

	$queryNewAlpha="SELECT  * FROM product_category_arabyos WHERE pc_id='".$iCategoryDetail->pd_subcat_id."' and pc_status='1' ORDER BY `pc_id` DESC";
	$queryResultNewAlpha = mysql_query($queryNewAlpha);
	$ResultsNewAlpha = mysql_fetch_object($queryResultNewAlpha) ;

   	$query="SELECT * FROM `product_category_arabyos` WHERE pc_id = '".$ResultsNewAlpha->pc_parent_id."' and pc_status='1' ORDER BY `pc_id` DESC";
	$queryResult = mysql_query($query);
	$Results = mysql_fetch_object($queryResult);
	
	$iMainParentId="SELECT * FROM `product_category_arabyos` WHERE pc_parent_id = '".$Results->pc_parent_id."' and pc_status='1' ORDER BY `pc_name` asc LIMIT 5";


	$iMainParentIdqueryResult = mysql_query($iMainParentId);
	
	$rr = 0; $counter = 0;
	
	while( $Results = mysql_fetch_object($iMainParentIdqueryResult) ){
			if( $rr == 0){
			echo ' <header> <span class="h4">Related Categories</span> </header>
			<ul>';
			}
	//echo "<br>";
			// $getCounts = mysql_num_rows(mysql_query("SELECT distinct pc_parent_id FROM `product_category_arabyos` WHERE pc_id IN (".$iParentId.") and pc_status='1' ORDER BY `pc_id` DESC"));
		

	
	    $iThisValu = 0;
		unset($pc_id_arr);
		$sql_check1="select pc_id from product_category_arabyos where pc_parent_id='".$Results->pc_id."'";
		$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
     	while($data=mysql_fetch_array($res_check1)){
		        $pc_id_arr[]=$data['pc_id'];		
	}
	$ids = join("','",$pc_id_arr); 

  $sql_prd="select * from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY FIELD(p_id,'5','4','3','15') ";
  //echo $sql_prd;
       
		$recObj=mysql_query($sql_prd);
		$row_prd=mysql_fetch_object($recObj);
		 $iThisValu = mysql_num_rows($recObj);
		if($iThisValu>0)
		{	
		
					$counter++;
					 if($counter== 8)
                {
                    echo '<div class="collapse" id="categoriesRight">';
                }		
		
		echo ' <li> <a href="products.php?c='.md5($Results->pc_id).'&rctyp=Products">'.$Results->pc_name.'	</a> </li>'; 
				if( $_GET['keywords'] ==  $Results->pc_name ){
					$getSubCatId = $Results->pc_id;
				}
	}
	
		$rr++;
	$reddID  = 	$Results->pc_parent_id;
		}
	 
	
	
	
	
	if( $getCounts == 011 ){
			$queryResulsst = mysql_query("SELECT * 
			FROM  `product_category_arabyos` 
			WHERE  `pc_id` ='".$reddID."' and pc_status='1'");
			$getMOre = mysql_fetch_object($queryResulsst);
			$catsearch = mysql_query("SELECT * 
			FROM  `product_category_arabyos` 
			WHERE  `pc_parent_id` ='".$getMOre->pc_parent_id."' and pc_status='1'");
			/**/
			echo '---->'.$getCountsCat = mysql_num_rows($catsearch);
			/**/
			$counter = 0;
				while($getMOrecatsearch = mysql_fetch_object($catsearch)){
					$counter++;
					 if($counter== 8)
                {
                    echo '<div class="collapse" id="categoriesRight">';
                }	
				echo '<li> <a href="products.php?c='.md5($Results->pc_id).'&rctyp=Products">'.$Results->pc_name.'	</a> </li>'; 
				if( $_GET['keywords'] ==  $Results->pc_name ){
					$getSubCatId = $Results->pc_id;
				}
		}
	}
	if($counter > 7)
	{
		 echo '</div >';
	}
	//while ?>
    <?php if($counter>4) { ?>
    <div class="text-right">
      <button class="btn btn-link collapsed" type="button" data-toggle="collapse"  data-target="#categoriesRight" aria-expanded="false" aria-controls="collapseExample"> More +</button>
    </div><?php }?>

	  </ul>
        </section>
      </div>
      
      
      
      
 <?php  }  ?>
 
<?php  if( $_GET['rctyp']=='buy_lead'){?> 
      <div class="">
        <section>
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
//echo 	"SELECT * FROM `product_category_arabyos` WHERE `pc_name` LIKE '".$_GET['keywords']."' ORDER BY `pc_id` DESC";
	
	    $sqls="SELECT * FROM `product_category_arabyos` WHERE `pc_name` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' and pc_status='1'  ORDER BY `pc_id` DESC";
		$ress=mysql_query($sqls);
			/**/
		$RelatedCatCount = mysql_num_rows($ress);
			/**/
		$rows=mysql_fetch_object($ress);
		$catId = $rows->pc_id;
		$catParentId = $rows->pc_parent_id;
		
		
		$keywordsssSS = $_GET['keywords'];
		$SubCategoryArray ="";
		$keywords=trim($_GET['keywords']);
		$keywords_string=generateBuyleadSearchString($keywords);
	 $iParentId = '';	
  $iProductCategoryId = "select * , MATCH (br_pd_name) AGAINST ('".$keywordsssSS."'  IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id   and  (".$keywords_string.") and br_approval_status = '1' and br_display_status = '1' and br_status = '1' ".$sql_br_ck." ".$sql_extra." order by title_relevance asc  " ;
  //$iProductCategoryId=mysql_query("SELECT  pd_subcat_id FROM `products` WHERE `pd_title` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' and pd_status='1' ");
	$nEWAAiProductCategoryId=mysql_query($iProductCategoryId);
	
	
	$iCategoryDetail = mysql_fetch_object($nEWAAiProductCategoryId);
	
	

	$queryNewAlpha="SELECT  * FROM product_category_arabyos WHERE pc_id='".$iCategoryDetail->br_pc_id."' and pc_status='1' ORDER BY `pc_id` DESC";
	$queryResultNewAlpha = mysql_query($queryNewAlpha);
	$ResultsNewAlpha = mysql_fetch_object($queryResultNewAlpha) ;




   	$query="SELECT * FROM `product_category_arabyos` WHERE pc_id = '".$ResultsNewAlpha->pc_parent_id."' and pc_status='1' ORDER BY `pc_id` DESC";
	$queryResult = mysql_query($query);
	$Results = mysql_fetch_object($queryResult);
	
	
	

	
	 $iMainParentId="SELECT * FROM `product_category_arabyos` WHERE pc_parent_id = '".$Results->pc_parent_id."' and pc_status='1' ORDER BY `pc_name` asc";
	$iMainParentIdqueryResult = mysql_query($iMainParentId);
	
	$rr = 0;
	
	while( $Results = mysql_fetch_object($iMainParentIdqueryResult) ){
			if( $rr == 0){
			echo ' <header> <span class="h4">Related Categories</span> </header>
			<ul>';
			}
	//echo "<br>";
			// $getCounts = mysql_num_rows(mysql_query("SELECT distinct pc_parent_id FROM `product_category_arabyos` WHERE pc_id IN (".$iParentId.") and pc_status='1' ORDER BY `pc_id` DESC"));
		

	
	    $iThisValu = 0;
		unset($pc_id_arr);
		$sql_check1="select pc_id from product_category_arabyos where pc_parent_id='".$Results->pc_id."'";
		$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
     	while($data=mysql_fetch_array($res_check1)){
		        $pc_id_arr[]=$data['pc_id'];		
	}
	$ids = join("','",$pc_id_arr); 



$sql_prd = "select * , MATCH (br_pd_name) AGAINST ('".$keywordsssSS."'  IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id   and  (".$keywords_string.") and br_approval_status = '1' and br_display_status = '1' and br_pc_id in('$ids') and br_status = '1' ".$sql_br_ck." ".$sql_extra." order by title_relevance desc  " ;

 // $sql_prd="select * from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
// where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY FIELD(p_id,'5','4','3','15') ";


		$recObj=mysql_query($sql_prd);
		$row_prd=mysql_fetch_object($recObj);
		 $iThisValu = mysql_num_rows($recObj);
		if($iThisValu>0)
		{	
		
		
		
		echo ' <li> <a href="https://arabyos.com/buyleads.php">'.$Results->pc_name.'	</a> </li>'; 
				if( $_GET['keywords'] ==  $Results->pc_name ){
					$getSubCatId = $Results->pc_id;
				}
	}
	
		$rr++;
	$reddID  = 	$Results->pc_parent_id;
		}
	 
	
	
	
	
	if( $getCounts == 011 ){
			$queryResulsst = mysql_query("SELECT * 
			FROM  `product_category_arabyos` 
			WHERE  `pc_id` ='".$reddID."' and pc_status='1'");
			$getMOre = mysql_fetch_object($queryResulsst);
			$catsearch = mysql_query("SELECT * 
			FROM  `product_category_arabyos` 
			WHERE  `pc_parent_id` ='".$getMOre->pc_parent_id."' and pc_status='1'");
			/**/
			$getCountsCat = mysql_num_rows($catsearch);
			/**/
			$counter = 0;
				while($getMOrecatsearch = mysql_fetch_object($catsearch)){
					$counter++;
					 if($counter== 5)
                {
                   echo '<div class="collapse" id="fruit">';
                }
					echo ' <li> <a href="products.php?c='.md5($getMOrecatsearch->pc_id).'&rctyp=Products">'.$getMOrecatsearch->pc_name.'</a> </li>'; 
			     /* if($counter== 5)
                {
                   echo '</div>';
                }*/
				/**/
				if($getCountsCat>=5)
			  {
			  if($counter==$getCountsCat)
			  {
			  echo '</div>';
			  }
			  }
				/**/
				}//while
				  if($getCountsCat>= 5)
                {
                             ?>
             <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#fruit" aria-expanded="false" aria-controls="collapseExample"> + View More </a> </div>
               <?php } 
	}
	  ?>
	  </ul>
        </section>
      </div>
      
      
      
      
 <?php  }  ?> 
 
 
 <?php if($_GET['rctyp']=='tender'     ||  $_GET['rctyp']=='buy_lead'){?>
<?php
$tdate=date("Y-m-d");
$iNewkeywords = $_GET['keywords'];
 $sql_t="select * from tender,product_category_arabyos,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and tnd_heading like '%$iNewkeywords%' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) ".$sql_tnd_ck." and tnd_status='1' order by tnd_id LIMIT 0,  5";
//echo $sql_t;
//$sql_testi="select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by testi_updated_date desc";
$res_t=mysqli_query($con, $sql_t);
if(mysqli_num_rows($res_t)>0){
?>
<div class="mb1 c3" style="
    border: 1px solid #ccc;
    border-radius: 5px;background: #fff;
">
<p class="bg bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
<div class=" bbx cln">
<p class=" bxh f2 color_coding" style="color: #c30000;">Related Tenders</p>



<div style="display: block;" id="d2">
<?php
$n=1;
while($row_t=mysqli_fetch_object($res_t)){
	$len=strlen($row_t->tnd_details);
?>
<?php if($n>1){ ?><br class="c3"><?php } ?>
<p class="lnh1">
<b class="cor lnh1"><a href="tender-details.php?id=<?php echo rand(1000,9999).md5($row_t->tnd_id); ?>" style="text-decoration:none;color:E84000"><?php echo $row_t->tnd_heading; ?></a><br>
<span class="cb2"><?php echo get_country_name($row_t->country); ?></span></b><br><?php echo substr($row_t->tnd_details,0,120); ?>
</p>
<?php if($len>120){	?><p class="c3 pa1 rm tr"><a href="tender-details.php?id=<?php echo rand(1000,9999).md5($row_t->tnd_id); ?>" target="_blank"> Read More...</a></p><?php } ?>
<?php 
$n++;
} ?>

</div>



<p class="c3"></p>
</div>
<p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
</div>
<?php } ?>


<?php
$sql_t="select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_approval_status='1' and TO_DAYS(auc_due_date)>=TO_DAYS(now()) ".$sql_auc_ck." and auc_heading like '%$iNewkeywords%' and auc_status='1' order by auc_id LIMIT 0,  5";
//echo $sql_t;
//$sql_testi="select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by testi_updated_date desc";
$res_t=mysqli_query($con, $sql_t);
if(mysqli_num_rows($res_t)>0){
?>
<div class="mb1 c3" style="
    border: 1px solid #ccc;
    border-radius: 5px;
">
<p class="bg bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
<div class=" bbx cln">
<p class=" bxh f2  color_coding">Related Auctions</p>



<div style="display: block;" id="d2">
<?php
$n=1;
while($row_t=mysqli_fetch_object($res_t)){
	$len=strlen($row_t->auc_details);
?>
<?php if($n>1){ ?><br class="c3"><?php } ?>
<p class="lnh1">
<b class="cor lnh1"><a href="auction-details.php?id=<?php echo rand(1000,9999).md5($row_t->auc_id); ?>" style="text-decoration:none;color:E84000"><?php echo $row_t->auc_heading; ?></a><br>
<span class="cb2"><?php echo get_country_name($row_t->country); ?></span></b><br><?php echo substr($row_t->auc_details,0,120); ?>
</p>
<?php if($len>120){	?><p class="c3 pa1 rm tr"><a href="auction-details.php?id=<?php echo rand(1000,9999).md5($row_t->auc_id); ?>" target="_blank"> Read More...</a></p><?php } ?>
<?php 
$n++;
} ?>

</div>



<p class="c3"></p>
</div>
<p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
</div>
<?php } ?>





<?php  }	?>     
      
      
      
      
      
      
      
<?php }else if($_GET['rctyp']=='Suppliers'){?>
		<div style="" >
      <div class=""></div></div>
	 <?php }
}
} ?>
<? //print_r($_SESSION);  
if($_GET['keywords'] != '') { 
if(isset($_GET['rctyp']) || isset($_GET['grid']) )
	  {
	  if($_GET['rctyp']!='Suppliers' || $_GET['grid'] == 'active')
	  {
?>
    <style>
	#sideAdTable_event{
		position: absolute;
		top: 0px;
		z-index: 4;
	}
	</style>
      <div class="col-lg-12" style="padding:3px;" id="right-image">
        <div class="col-lg-12 popup-sub-box">
          <header>
            <h3 style="color:#fff;">Submit Buy Requirement For</h3>
			  
			  <!-- test --->
			  	<?  if($_GET['keywords'] != '') {
	  if(isset($_GET['rctyp']))
	  {
	  if($_GET['rctyp']!='Suppliers')
	  {
	$key =$_GET['keywords']; 
	  ?>
<div class="col-lg-12 ar-box text-justify hidden-xs" style="padding-right:23px;" id="business-alert">
    <div class="text-center">
    <?php
    		if ($neeraj_right_sidebar_content_comming_vairable ) { //neeraj_right_sidebar_content_comming_vairable)  {
    			$busss_alert_cat_name = $neeraj_right_sidebar_content_comming_vairable;//  = neeraj_right_sidebar_content_comming_vairable;
				?>
			<h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($busss_alert_cat_name); ?>"</h3>
				<?php 
    		} else 
		    if($_GET['rctyp']=='buy_lead')
			{
				$busss_alert_cat_name='';
				
			//  $sql_key="select * from product_category_arabyos where pc_name like '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1' and pc_parent_id!='0'";
				 $sql_key="select * from buy_requirement join product_category_arabyos on product_category_arabyos.pc_id=buy_requirement.br_pc_id where (br_pd_name like '%".str_replace("+"," ",$_GET['keywords'])."%' OR pc_name like '%".str_replace("+"," ",$_GET['keywords'])."%') and pc_status='1'  and pc_parent_id!='0'";
				//echo $sql_key;
				
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				if(mysql_num_rows($query_key)>0){
				$busss_alert_cat_name=$row_key->pc_name;
				?>
	  <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($busss_alert_cat_name); ?>"</h3>
			<?php } else{ 
			$sql_second_query=mysql_query("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%".str_replace(array("+","%20"),array(" "," "),$_GET['keywords'])."%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");
			$fetch_records=mysql_fetch_object($sql_second_query);
			if(mysql_num_rows($sql_second_query) > 0){
				$sub_cat_id_get=$fetch_records->pc_id;
				$sql_second_query1=mysql_query("SELECT * FROM product_category_arabyos WHERE pc_parent_id='".$sub_cat_id_get."' and pc_status='1'");
				$fetch_records1=mysql_fetch_object($sql_second_query1);
				if(mysql_num_rows($sql_second_query1) > 0){
					$busss_alert_cat_name=$fetch_records1->pc_name;
				}
				else{
					$busss_alert_cat_name=$fetch_records->pc_name;
				}

			}
			?>
			 <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($busss_alert_cat_name); ?>"</h3>
	  <?php
				}//if(isset($_GET['keyword_typesss'])||isset($_GET['keywords'])||isset($_GET['rctyp'])){echo "?val=true";}
			
			}
		 else if($_GET['rctyp']=='Products')
			{
			?>	
			 
			<?php	
				$busss_alert_cat_name='';
			    $sql_key="select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id where pd_title like '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1'";
				// Changed by webxtor @ 18 June 2018, not too efficient..
				/*select * from products join product_category_arabyos on product_category_arbyos.pc_id=products.pd_subcat_id
 where (pd_title rlike '[[:<:]]table[[:>:]]' OR pd_title rlike '[[:<:]]table' OR pd_title like '%table%'/) and pc_status='1'
Order by  pd_title rlike '[[:<:]]table[[:>:]]' desc,  pd_title rlike '[[:<:]]table' desc, pd_title like '%table%'desc* /
				$skeyw = str_replace("+"," ",$_GET['keywords']);
				$sql_key = "select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id
 					where (pd_title rlike '[[:<:]]{$skeyw}[[:>:]]' OR pd_title rlike '[[:<:]]{$skeyw}' OR pd_title like '%$skeyw%') and pc_status='1'
					Order by  pd_title rlike '[[:<:]]{$skeyw}[[:>:]]' desc,  pd_title rlike '[[:<:]]{$skeyw}' desc, pd_title like '%$skeyw%'desc";*/
				//echo $sql_key;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				if(mysql_num_rows($query_key)>0){
				$busss_alert_cat_name=$row_key->pc_name;
				?>
	  <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($busss_alert_cat_name); ?>"</h3>
			<?php } else{ 
			$sql_second_query=mysql_query("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%".str_replace(array("+","%20"),array(" "," "),$_GET['keywords'])."%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");
			$fetch_records=mysql_fetch_object($sql_second_query);
			if(mysql_num_rows($sql_second_query) > 0){
				$sub_cat_id_get=$fetch_records->pc_id;
				$sql_second_query1=mysql_query("SELECT * FROM product_category_arabyos WHERE pc_parent_id='".$sub_cat_id_get."' and pc_status='1'");
				$fetch_records1=mysql_fetch_object($sql_second_query1);
				if(mysql_num_rows($sql_second_query1) > 0){
					$busss_alert_cat_name=$fetch_records1->pc_name;
				}
				else{
					$busss_alert_cat_name=$fetch_records->pc_name;
				}

			}
			?>
			 <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($busss_alert_cat_name); ?>"</h3>
	  <?php
				}
			
			}
		  else if($_GET['rctyp']=='tender')
		  {
			  $sql_key="select * from tender join product_category_arabyos on product_category_arabyos.pc_id=tender.tnd_pc_id where tnd_heading like  '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1'";
				//echo $sql_key;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				//echo '<pre>'; print_r($row_key);echo "</pre>";
			if(mysql_num_rows($query_key) > 0) {
			  ?>
	  <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($row_key->pc_name); ?>"</h3>
		
    <?php
			} else {
				$busss_alert_cat_name='';
				$sql_key="select * from auction join product_category_arabyos on product_category_arabyos.pc_id=auction.auc_pc_id where auc_heading like  '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1'";
			//echo $sql_key;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				//echo '<pre>'; print_r($row_key);echo "</pre>";
				if(mysql_num_rows($query_key) > 0) { ?>
	   <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($row_key->pc_name); ?>"</h3>
		
    <?php }
		else{
				$sql_second_query=mysql_query("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%".str_replace(array("+","%20"),array(" "," "),$_GET['keywords'])."%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");
			$fetch_records=mysql_fetch_object($sql_second_query);
			if(mysql_num_rows($sql_second_query) > 0){
				$sub_cat_id_get=$fetch_records->pc_id;
				$sql_second_query1=mysql_query("SELECT * FROM product_category_arabyos WHERE pc_parent_id='".$sub_cat_id_get."' and pc_status='1'");
				$fetch_records1=mysql_fetch_object($sql_second_query1);
				if(mysql_num_rows($sql_second_query1) > 0){
					$busss_alert_cat_name=$fetch_records1->pc_name;
				}
				else{
					$busss_alert_cat_name=$fetch_records->pc_name;
				}

			}
		?>
			<h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($busss_alert_cat_name); ?>"</h3>
		<?php }
			}
		  }
			else
			{
				?>
	  	 <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($_GET['keywords']); ?>"</h3>
	  <?php
				
			}
			
		   ?>
<?php }} }?>
			  
			  <!-- test --->
			  
			  
            <?php /* <h3 style="color:#f58238;">"<!--Rice--><?php echo ucwords($_GET['keywords']); ?>"</h3>*/ ?>
          </header>
          <section class="col-lg-12">
            <div class="col-lg-12" style="border:1px solid #a094c7; padding:2px; position:relative;">
            <form action="post-buy-req.php" method="post" id="abcccccc">
            <input type="hidden" name="keywords" value="<?php echo $_GET['keywords']; ?>" />
              <textarea style="width:100%; max-width:100%; min-height:150px; max-height:150px; border:none; background-color:transparent; position:relative; z-index:5;"  id="table-input" name="specs"></textarea>
              <table style="width:100%;" id="sideAdTable_event" class="sideBakwastable">
                <tr>
                  <td><i class="fa fa-exclamation-triangle" style="color:#ba2025; font-size:18px;"></i></td>
                  <td class="h4 " style="font-size:14px;"> Enter Product/Service Specifications </td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Application of Product</td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Product Features</td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Material - Product Packaging</td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Any Special Requirement</td>
                </tr>
              </table>
            </div>
            <div class="col-lg-12 margin-top-10 margin-bottom-10 text-center">
              <button class="btn btn-default btn-warning" style="padding:3px 5px;" type="submit"> <b class="txt-bold">Get Instant Quote Now</b><br>
              <small>For many verified Suppliers </small> </button>
            </div>
            </form>
            <script>
			$(document).ready(function(){
									 $("#table-input").focusout(function(){ 
												if($("#table-input").val()=='')
												{
												$("#sideAdTable_event").show();
												}else
												{
												$("#sideAdTable_event").hide();	
												}
												});
									   $("#table-input").focusin(function(){
										$("#sideAdTable_event").hide();
								  });
						});
			</script>
             <? if($_SESSION['uid_indm']!='') { ?>
            <div>
              <ul>
                <li>Your Contact Information</li>
                  <? $user_id = $_SESSION['uid_indm'];
			//	echo $user_id;
				$query="SELECT * FROM `user` WHERE usr_id='".$user_id."' ";
				 // echo $query;
				$queryResult = mysql_query($query);
					// echo $queryResult;				
					  while( $row = mysql_fetch_assoc( $queryResult ) ){
         // echo $row['usr_id'];
               ?>
                <li><!--Tame Sami--><?=ucwords($row['fname'])?> <?=ucwords($row['lname'])?></li>
                <?  $sql ="SELECT * FROM `country` WHERE cn_id='".$row['country']."' ";
					$result = mysql_query($sql);
					  $row1 = mysql_fetch_assoc( $result ) ;
						 $sql2 ="SELECT * FROM `business_profile` WHERE bnsprof_uid='".$user_id."' ";
						  $result2 = mysql_query($sql2);
					  $row2 = mysql_fetch_assoc( $result2 );
					 // echo $row2['bnsprof_city']; die;
					   $sql_city ="SELECT * FROM `city` WHERE ct_id='".$row2['bnsprof_city']."' ";
					    $result_city = mysql_query($sql_city);
					  $row_city = mysql_fetch_assoc( $result_city );
					?> 
                <li><!--Egypt - Cairo--><?=ucwords($row1['cn_name'])?> - <?=ucwords($row_city['ct_name'])?></li>
                <li><!--+20--><?=$row1['cn_ph']?> <!--8620005556--><?=$row['mobile1']?></li>
                <li><?=$row['email']?> </li>
                <? } ?>
              </ul>
            </div>
            <? } ?>
          </section>
        </div>
        <div class="clearfix"></div>
      </div>
<? } 
}
}?> 







   
<!--      <div class="col-lg-12 text-right">
        <div class="side-ad"> 
        </div>
      </div>
    -->
<!-- <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active"> 
      <img class="d-block w-100" src="..." alt="First slide">
    </div>
    <div class="carousel-item">
      <img class="d-block w-100" src="..." alt="Second slide">
    </div>
    <div class="carousel-item">
      <img class="d-block w-100" src="..." alt="Third slide">
    </div>
  </div>
  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div> -->
		<?php 
		    $staticLeftBanner = staticAdsBanner('right');
			echo '<div class="col-lg-12 advertise-divs">';
			echo $staticLeftBanner.'</div>';
		   ?>
		   <div class="clearfix"></div>
      <!--<div class="col-lg-12"> <img src="images/ad.png"  style="width:100%;"/> 
	  </div>-->
	  <!-- slick slider -->
	  <link rel="stylesheet" type="text/css" href="css/slick.css">
<link rel="stylesheet" type="text/css" href="css/slick-theme.css">
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<?php include "css/custom.php"; ?>
<style>
.slick-product-image > img {
    min-height: auto!important;
    max-height: 170px!important;
    border: 1px solid #E9E9E9!important;
}
.slick-product-wrapper {
	max-width: none!important;
    width: 70%;
    display: inline-block;
    padding-top: 10px;
    padding-bottom: 10px;
}
.matterbox p {
	text-align: center;

}
.ihoves{
	text-align: center!important;
}
.top-arrow::before, .bottom-arrow::before {
    font-family: slick;
    font-size: 20px;
    line-height: 1;
    opacity: .75;
    color: #fff;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
.bottom-arrow::before {
    content: '←';
}
.top-arrow::before {
    content: '→';
}
.arrow_sli {
    height: 100%;
    width: 100%;
    right: 0;
    background: rgb(34,122,191);
    z-index: 9;
}

</style>
    <?php
if (isset($_COOKIE['loc_id'])) {
                $sql_pd_ck = " and (
        (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                /*
                    (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                    or
                    (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                    */
                $sql_so_ck = " and (
        (so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                /*
                    (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and so_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                    or
                    (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                    */
                $sql_br_ck = " and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                /*
                    (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                    or
                    (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                    */
            } else {
                $sql_pd_ck = " and (
        (pd_preferred_buyer_location='any')
        or
        (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
        )";
                $sql_so_ck = " and (
        (so_preferred_buyer_location='any')
        or
        (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
        )";
                $sql_br_ck = " and (
        (br_preferred_supplier_location='any')
        or
        (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
        )";
            }
?> 
	  <?php 
if(isset($related_id)&&!empty($related_id))
{	
	$rel_sup_id = array();
	$i = 1;
	foreach($related_id as $related_id){
		$rel_sup_id[] = $related_id;
	}
    $rel_id= implode(',',$rel_sup_id);
	
	$sqls="SELECT `pd_subcat_id` FROM `products` WHERE pd_uid IN ($rel_id) and pd_status='1'";
    $ress=mysql_query($sqls);
	$RelatedCatCount = mysql_num_rows($ress);
	$rel_pc_id = array();
	while( $Results = mysql_fetch_object($ress) ){
        $rel_pc_id[] = $Results->pd_subcat_id;
	}
	// echo '<pre>';print_r($rel_pc_id);exit;
    $rel_id= implode(',',$rel_pc_id); 

                                if ($_COOKIE['loc_id'] != "") {
                                    $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_subcat_id IN ($rel_id) and pd_image!=''" . $sql_pd_ck . " order by rand() Limit 0,50";
                                } else {
                                    $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_subcat_id IN ($rel_id) and pd_image!=''" . $sql_pd_ck . " order by rand() Limit 0,50";
                                }
                                // echo  $sqlleading;exit;
                                 
                                 $rsleading = mysqli_query($con,$sqlleading);
                                 $totalbaneer = mysqli_num_rows($rsleading);
								// echo $totalbaneer ;exit;
								 $rembaner = $totalbaneer%2;
								 if($totalbaneer > 2){
                                 if($totalbaneer> 0)
                                 {
                                  ?>
                <p style="font-size: 17px;margin-top: 15px;margin-bottom: 10px; display: inline-block;"><b>Related Leader Suppliers</b></p>
               <div class="demobox" >
                  <div class="wrapper-container">
                     <div class="white_bg">
                        <div class="welcome_desc">
                           <div class="course_demo">
                              <ul id="ARABYOS-relatedCat">
                                 <?php
                                    
                                   while($rowleading = mysqli_fetch_object($rsleading))
                                     {
                                       $pd_id = $rowleading->pd_id;
                                       $pd_image = $rowleading->pd_image;
                                       $pd_title = $rowleading->pd_title;
                                       $adv_icon = '';
                                        echo '<div class="main-slick-wrapper-item">';
									   $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $rowleading->pd_uid . "' limit 1"));
                                                    $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_bprof->bnsprof_id;
                                    ?>
                                      
                                  <a class="slick-product-wrapper" href="company/products.php?c=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $rowleading->pd_subcat_id; ?>#<?php echo $rowleading->pd_id; ?>" target=_blank>
                                    <div class="demobox">
                                        <div class="slick-product-image">
                                       <img alt="" src="upload/myproduct/<?php echo $rowleading->pd_image; ?>" class="black" style="margin: auto;border: 1px solid #E9E9E9!important;" title="<?php echo ucwords($rowleading->pd_title); ?>">
                                        </div>
                                       <div class="matterbox">
                                           <div class="icon-pic-with-heading">
                                           <div class="icon_pic"><img alt=""src="images/<?php echo $adv_icon;?>"class="img-responsive" width="18"></div>
                                           <div class="ihover-wrapper"> 
                                           <h3 class="ihoves">
                                                <?php echo ucwords(substr($pd_title, 0,15)); ?><?php if (strlen($pd_title) > 15) { ?>...<?php } ?>
                                             </h3>
                                           <div class="auction_hover">
                                                   <p><?php echo ucwords($pd_title); ?></p>
                                                </div>
                                           </div>
                                           </div>
                                          <div class="rightmatter">
                                                                    <p>
                                                                        <span class="nam"><?php echo get_country_name($rowleading->country); ?></span><br>
                                                                    <p>MOQ: <span
                                                                                class="nam"><?php echo $rowleading->pd_min_order_qty; ?><?php echo $rowleading->mu_name; ?></span><br>
                                                                    <p><?php echo $rowleading->cn_currency; ?><span
                                                                                style="font-size:11px!important"
                                                                                class="nam"><?php echo $rowleading->pd_fob_price ?>
                                                                            /</span><?php echo $rowleading->mu_name; ?>
                                                                    <div class="clear"></div>
                                                                </div>
                                          <div class="clear"></div>
                                       </div>
                                    </div>
                                 </a>
                                 <?php
                                     echo '</div>';
                                    }
                                    if($rembaner==1){ echo '</div>';}
                                    ?>
                              </ul>
                              <script>$(window).load(function(){$("#flexiselDemo4").flexisel({visibleItems:4,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                           </div>
                        </div>
                        <div class="clear" style="height:1px"></div>
                     </div>
                  </div>
               </div>
                                <?php }} ?>
 <?php } ?>
 <script>
	$('#ARABYOS-relatedCat').slick({
		nextArrow: '<div class="arrow_sli"><img src="/assets/img/botom.png" class="top-arrow" aria-label="Previous" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></div>',
		prevArrow: '<div class="arrow_sli"><img src="/assets/img/top.png" class="bottom-arrow" aria-label="Next" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></button></div>',
		centerMode: true,
		centerPadding: '10px',
		slidesToShow: 7,
		autoplay: true,
		vertical: true,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					centerMode: true,
					centerPadding: '10px',
					slidesToShow: 7
				}
			},
			{
				breakpoint: 768,
				settings: {
					centerMode: true,
					centerPadding: '10px',
					slidesToShow: 7
				}
			},
			{
				breakpoint: 480,
				settings: {
					centerMode: true,
					centerPadding: '10px',
					slidesToShow: 7
				}
			}
		]
	});
	</script>


	  <!-- slick slider end-->




    </div>
    <div class="clearfix"></div>
<!--------------------------------------------------------->
<!-- Trade Offers Start Thu Dec 26 11:30:01 2013-->
<script type="text/javascript">
function buy_show()
{
	$("#bs1").removeClass("cp c4");
	$("#ss1").addClass("cp c4");
	$("#bs2").removeClass("off").addClass("on mt2");
	$("#ss2").removeClass("on mt2").addClass("off");
}
function sell_show()
{
	$("#bs1").addClass("cp c4");
	$("#ss1").removeClass("cp c4");
	$("#bs2").removeClass("on mt2").addClass("off");
	$("#ss2").removeClass("off").addClass("on mt2");
}
</script>
</div>
