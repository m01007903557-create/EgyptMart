<div id='cssmenu' style="float:left;width:220px;">
<!--adi css skype me "adityait121"-->
<style>
	div#saleoffer_slider {
    height: auto !Important;
}
div#product_slider {
    height: auto !Important;
}
</style>
<!--adi css skype me "adityait121"-->
<script type="text/javascript">
function scatAddDel(id)
{
	if($('#scat_'+id).attr('checked')) {
		$.post("ajax-file/addTempSellofferAlertCat.php",{id:id},    function(data){	showList()	});
	} else {
		$.post("ajax-file/delTempSellofferAlertCat.php",{id:id},    function(data){	showList()	});
	}
}
function addAlertCategory()
{
	$.post("ajax-file/addSellofferAlertCat.php",{},    function(data){	window.location.reload();   });
}
function delAlertCat(id)
{
	if(confirm("Are you sure to delete this Category?")){
		$.post("ajax-file/delSellofferAlertCat.php",{id:id},    function(data){	window.location.reload();   });
	}
}
</script>

   <!------------COUNTRIES & Catagories -------------------->

             <?php
				//print_r($_SESSION); 

          $view_product ="SELECT * FROM `business_type` WHERE 1";

//echo $view_product; die;

          $userArray = mysql_query($view_product);
               $userArrayRow_Type = array();
             while( $userArrayRow =mysql_fetch_array($userArray, MYSQL_ASSOC)){
				 
                $userArrayRow_Type[$userArrayRow['bsntyp_id']] = $userArrayRow['bsntyp_title'];
               // $userArrayRow_Result[$userArrayRow['usr_id']]['user_type'] = $userArrayRow['user_type'];
              }

          $view_product ="select `usr_id`,`user_type`, `usr_br_prefLocation`, `usr_pd_prefLocation`, `usr_so_prefLocation`, `usr_tnd_prefLocation`, `email`, `name_prefix`, `fname`, `lname`, `website`, `country`, `image` from `user` ";

          $view_product ="SELECT `user`.* , `business_profile`.* ,city.* FROM city, `business_profile`,user WHERE `business_profile`.`bnsprof_uid` = user.usr_id and city.ct_id = `business_profile`.bnsprof_city";


          $userArray = mysql_query($view_product);
               $userArrayRow_Result = array();
             while( $userArrayRow =mysql_fetch_array($userArray, MYSQL_ASSOC)){
                $userArrayRow_Result[$userArrayRow['usr_id']] = $userArrayRow;
               // $userArrayRow_Result[$userArrayRow['usr_id']]['user_type'] = $userArrayRow['user_type'];
              }



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
/*	$sql_pcat="select * from product_category where md5(pc_id)='".$pc_id."'";
	$res_pcat=mysql_query($sql_pcat);
	$row_pcat=mysql_fetch_object($res_pcat);*/

	if(isset($_COOKIE['loc_id']))
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
	
	
   $keywords_string  = "";
     if(isset($_GET['keywords']) && $_GET['keywords']!=''){
		 
       $keywords = mysql_real_escape_string($_GET['keywords']);
	   
	   
	 if($_GET['rctyp'] == 'buy_lead'){ 
	 $keywords_string=generateBuyleadSearchString($keywords);
	$keywords_string = " and(".$keywords_string.") " ;
	}
	else if($_GET['rctyp'] == 'tender'){ 
	 $keywords_string=generateTenderSearchString($keywords);
	$keywords_string = " and(".$keywords_string.") " ;
	}
	else if($_GET['rctyp'] == 'Suppliers'){ 
	 $keywords_string=generateSupplierSearchString($keywords);
	$keywords_string = $keywords_string ;
	}
	else if($_GET['rctyp'] == 'Products'){ 

    $keywords_string=generateProdSearchString($keywords);
	//echo $keywords_string;
		    $keywords_string = " and (pd_title LIKE ".$keywords_string.") " ;
			
			
	}
		
     }

?>

    <?php
    include_once 'index_ls_countries.php';
    ?>

<?php if( $_GET['keywords']!="" ){ ?>
<div class="col-lg-12 ar-box side-cat-menu">
			
          	  <?php 

	$keywordsss = mysql_real_escape_string($_POST['keywords']);
//echo "SELECT * FROM `product_category` WHERE `pc_name` LIKE '".$_GET['keywords']."' ORDER BY `pc_id` DESC";
	     $sqls="SELECT * FROM `product_category` WHERE `pc_name` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' ORDER BY `pc_id` DESC";
	$ress=mysql_query($sqls);
	$keyExits = mysql_num_rows($ress);
	$rows=mysql_fetch_object($ress);
	
	$catId = $rows->pc_id;
	$catParentId = $rows->pc_parent_id;
	
		 $query="SELECT * FROM `product_category` WHERE pc_parent_id='".$catParentId."' ORDER BY `pc_id` DESC";
	
	$queryResult = mysql_query($query);
		$catParentIdcount = mysql_num_rows($queryResult);

	while( $Results = mysql_fetch_object($queryResult) ){
		
	 
				if( $_GET['keywords'] ==  $Results->pc_name ){
					$getSubCatId = $Results->pc_id;
				}

	}
	
		 //$querys="SELECT * FROM `product_category` WHERE pc_parent_id='".$getSubCatId."' ORDER BY `pc_id` DESC";
	$querys="SELECT * FROM `product_category` WHERE `pc_name` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' GROUP BY `pc_parent_id` DESC";
	$queryResults = mysql_query($querys);
	$subCatc = mysql_num_rows($queryResults);
	//$subCatcfetch = mysql_fetch_object($queryResults);
	
	
if($subCatc > 0 && $getSubCatId !="" && $_GET['rctyp'] != 'Suppliers'){
	echo '             <header> <span class="h4" style="font-weight:bold">Sub Categories</span> </header>';
	
	?>
	 <section class="ar-flags">
            <form>
            <?php
			$counter = 0;
			while( $Resultss = mysql_fetch_object($queryResults) ){
				$counter++;
		if($_GET['rctyp'] == 'buy_lead'){ 
		
			$sql_prdyy ="select * from buy_requirement,measurement_unit,country,products where mu_id=br_estimate_qty_unit and br_pc_id = ".$Resultss->pc_id." and br_apprx_order_currency=cn_id ".$sql_br_ck." and br_display_status='1' and br_approval_status='1' order by br_id desc";
		} 
		
		else if($_GET['rctyp'] == "tender"){

		$sql_prdyy ="select * from tender,measurement_unit,country where mu_id=tnd_qty_mu_id and tnd_pc_id = ".$Resultss->pc_id." and 	tnd_currency=cn_id ".$sql_tnd_ck." and TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) and tnd_approval_status='1' order by tnd_id desc";
		
		//echo $sql_prdyy; die;
		} else {
			
			
		$sql_prdyy ="select * from products,measurement_unit,country,business_profile,plan_member_id where mu_id=pd_unit and  bnsprof_uid = pd_uid and b_id = bnsprof_id and pd_subcat_id = ".$Resultss->pc_id." and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and (pd_title LIKE ".$newkw.") and pd_image!='' AND plan_member_id.expiry_date > ". time() ." order by pd_id desc";
		
		// $sql_prd="select measurement_unit.*,country.*,".$prod_col.", MATCH (pd_title) AGAINST ('".$keywords."'  IN BOOLEAN MODE) AS title_relevance  from products,measurement_unit,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and pd_subcat_id = ".$_GET['idd']."  and (pd_title LIKE ".$newkw.") and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' AND plan_member_id.expiry_date > ". time() ."   GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15'), pd_title asc LIMIt 0, 15";
		 
		//echo $sql_prdyy; 
		}
				$getCounts = mysql_num_rows(mysql_query($sql_prdyy));
				
				
				if($getCounts==0){
					$getCounts = 0;
				}else{
					$getCounts = $getCounts;
				}
				if($counter== 5)
                {
                   echo '<div class="collapse" id="categories">';
                }	
				?>
              <div class="checkbox" style="text-align:left;">
                <label>
                  <input type="checkbox" value="">
                  <span><?php echo $Resultss->pc_name."(".$getCounts.")" ; ?></span> </label>
              </div>
    <?php 
	
	if($subCatc>=5)
			  {
			  if($counter==$subCatc)
			  {
			  echo '</div>';
			  }
			  }
	}//while ?>
    <?php if($subCatc>=5)
	{
		?>
              
              <div class="text-right"> <button class="btn btn-link" type="button" data-toggle="collapse"  data-target="#categories" aria-expanded="false" aria-controls="collapseExample"> + View More </button> </div>
        
              <?php
	}
	?>
            </form>
          </section>
		  <?php
}else{
if($catParentId!="" && $_GET['rctyp'] != 'Suppliers'){
	

	echo '    <header> <span class="h4">Sub Categories</span> </header>
    
        ';
		
		 //$query="SELECT * FROM `product_category` WHERE pc_parent_id='".$catParentId."' ORDER BY `pc_id` DESC";
	
	$queryResult = mysql_query($querys);
	//echo "  <ul>";
	echo " <section class='ar-flags'>
            <form>";
			$counter = 0;
			$getCounts  = 0;
	while($getMOress1 = mysql_fetch_object($queryResult)){
		$query1="SELECT * FROM `product_category` WHERE pc_parent_id='".$getMOress1->pc_parent_id."' ORDER BY `pc_id` DESC";
		//echo $catid =	$getMOress->pc_parent_id; 
		$queryResult1 = mysql_query($query1);
		$getCountsx =0;
		while($getMOress = mysql_fetch_object($queryResult1)){
			
			
			$ids1 = $getMOress->pc_id;
			//data Fetch
		 $newkw=generateProdSearchString(str_replace(' & ','',$getMOress->pc_name));
			//if($getMOress->pc_id == 1004){ 
	$sql_cmt_cnt1="select measurement_unit.*,country.*,products.*, MATCH (pd_title) AGAINST ('".$getMOress->pc_name."'  IN BOOLEAN MODE) AS title_relevance  from products,measurement_unit,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and pd_subcat_id = ".$ids1." and (pd_title LIKE ".$newkw.") and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' AND plan_member_id.expiry_date > ". time() ."   GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15'), pd_title";

$result_pag_num = mysql_query($sql_cmt_cnt1);
//$row = mysql_fetch_array($result_pag_num);
$getCountsx = mysql_num_rows($result_pag_num);
	// if($getCountsx){
	//	echo $sql_cmt_cnt1;
			$counter++;
				
						if($counter == 5)
                {
                   echo '<div class="collapse" id="categories">';
                }	
				if($counter != 0 && $counter != 5 ){ ?>
                  <div class="checkbox" style="text-align:left;">
                <label>
                 
                  <?php echo ' <span> <a href="search.php?keyword_type=&keywords='.$getMOress->pc_name.'&rctyp=Products&idd='.$getMOress->pc_id.'">'.$getMOress->pc_name.'('.$getCountsx.')</a> </span>';?> </label>
              </div>
              <?php
				

				}
				
				
			
		//	}
			} } //while

       if($counter>= 5)
                {
                 ?>  
                 </div>          
                <div class="text-right"> <button class="btn btn-link" type="button" data-toggle="collapse"  data-target="#categories" aria-expanded="false" aria-controls="collapseExample"> + View More </button> </div>
  
               <?php }
			   
	}
			    ?>
			   
			   
            </form>
          </section>
          <?php
}

	

	  ?>
	
        </div>
<?php } ?>
  <?  if($_GET['keywords'] != '') {
	  if(isset($_GET['rctyp']))
	  {
	  if($_GET['rctyp']!='Suppliers')
	  {
	$key =$_GET['keywords']; 
	  ?>
	  
<div class="col-lg-12 ar-box text-justify" style="padding-right:23px;" id="business-alert">
          <header> <span class="h5" style="font-size:18px;"><img src="images/bell.png" width="20"/> <b class="txt-orange">Business Alerts</b></span> </header>
          <b class="h5 txt-purple txt-bold" style="display:block;">  Get timely updates<br>in your inbox for</b>
          <p class="h4 txt-orange text-center margin-top-10">"<!--Rice--><? echo ucwords($_GET['keywords']); ?>"</p>
           <div class="text-center">
           <?php
		    if($_GET['rctyp']=='buy_lead')
			{
				
				
			  $sql_key="select * from buy_requirement join product_category on product_category.pc_id=tender.br_pc_id where br_pd_name = '".$_GET['keywords']."' and pc_status='1'";
				//echo $sql_key;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				//if(isset($_GET['keyword_typesss'])||isset($_GET['keywords'])||isset($_GET['rctyp'])){echo "?val=true";}
				?>
          <a href="manage-buylead-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->br_pc_id; ?>">
		  <?php
			}
		  else if($_GET['rctyp']=='tender')
		  {
			  $sql_key="select * from tender join product_category on product_category.pc_id=tender.tnd_pc_id where tnd_heading = '".$_GET['keywords']."' and pc_status='1'";
				//echo $sql_key;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				//echo '<pre>'; print_r($row_key);echo "</pre>";
			if(mysql_num_rows($query_key) > 0) {
			  ?>
				<a href="manage-tender-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->tnd_pc_id; ?>">
			<?php
			} else {
				$sql_key="select * from auction join product_category on product_category.pc_id=auction.auc_pc_id where auc_heading = '".$_GET['keywords']."' and pc_status='1'";
			//echo $sql_key;
				$query_key = mysql_query($sql_key);
				$row_key=mysql_fetch_object($query_key);
				//echo '<pre>'; print_r($row_key);echo "</pre>";
				if(mysql_num_rows($query_key) > 0) { ?>
				 <a href="manage-auction-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->auc_pc_id; ?>">
				<?php }
			}
		  }
			else
			{
				?>
          <a href="manage-selloffer-alert.php?search_keyword=<?php echo $key?><?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">
		  <?php
			}
			?><button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button></a>
          </div> 
		<?php 
		    $staticLeftBanner = staticAdsBanner('left');
			echo '<div class="row text-center advertise-divs" style="margin-top:20px; margin-bottom:20px;">';
			echo $staticLeftBanner.'</div>';

		   ?>
        </div>
  <?php }else{?>
  		<div class="col-lg-12 ar-box text-justify" style="padding-right:23px;" id="business-alert"> 
		<?php 
		    $staticLeftBanner = staticAdsBanner('left');
			echo '<div class="row text-center advertise-divs" style="margin-top:20px; margin-bottom:20px;">';
			echo $staticLeftBanner.'</div>';

		   ?>
        </div>
  	<?php }//else condition end for if search for suppliers  
	  }
	  else
	  {
	$key =$_GET['keywords']; 
	  ?>
	  
<div class="col-lg-12 ar-box text-justify" style="padding-right:23px;" id="business-alert">
          <header> <span class="h5" style="font-size:18px;"><img src="images/bell.png" width="20"/> <b class="txt-orange">Business Alerts</b></span> </header>
          <b class="h5 txt-purple txt-bold" style="display:block;"> Get timely updates<br>in your inbox for</b>
          <p class="h4 txt-orange text-center margin-top-10">"<!--Rice--><? echo ucwords($_GET['keywords']); ?>"</p>
           <div class="text-center">
           <?php
		    if($_GET['rctyp']=='buy_lead')
			{
				?>
          <a href="manage-buylead-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">
		  <?php
			}
		  else if($_GET['rctyp']=='tender')
		  {
			  ?>
          <a href="manage-tender-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">
		  <?php
			}
		  else if($_GET['rctyp']=='auction')
		  {
			  ?>
          <a href="manage-auction-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">
		  <?php
			}
			else
			{
				?>
          <a href="manage-selloffer-alert.php?search_keyword=<?=$key?><?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">
		  <?php
			}
			?><button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button></a>
          </div> 
		   <?php 
		    $staticLeftBanner = staticAdsBanner('left');
			echo '<div class="col-lg-12 text-right advertise-divs">';
			echo $staticLeftBanner.'</div>';
		   ?>
        </div>
  <? 
	  }
  }
?>
<div class="col-lg-12 side_compare_list" style="padding-right: 23px;">
<h4>Compare List</h4><br>
<div class="comp-list">

</div>
<a href="compare.php" class="text-center" ><button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Compare</button></a>
</div>
</div>