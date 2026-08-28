<div id='cssmenu' style="float:left;width:220px !important;">

<!--adi css skype me "adityait121"-->

<style>

	div#saleoffer_slider {

    height: auto !Important;

}

div#product_slider {

    height: auto !Important;

}





.lnh1{     padding-left: 10px;

    border-bottom: #0000001f 1px solid;

    margin-top: 10px;}

	

	

.lnh1 .cor 	a {

		    text-decoration: none;

    color: E84000;

    margin-left: -13px;

    text-transform: capitalize;

		}

	

.beellling {     margin-left: -36px;}

.beellling-get { margin-top: -5px;}	

.beellling-key {    text-align: left;;}

	

	

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



<!------------COUNTRIES & Catagories neeraj-------------------->



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

/*	$sql_pcat="select * from product_category_arabyos where md5(pc_id)='".$pc_id."'";

	$res_pcat=mysql_query($sql_pcat);

	$row_pcat=mysql_fetch_object($res_pcat);*/

	//echo $_COOKIE['loc_id'].">>>>";

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

<?php if($_GET['rctyp']!='Suppliers'  ) { ?>

<?php if( $_GET['keywords']!="" ){ ?>





<?php if( $_GET['rctyp']=="Products" ){ ?>

<div class="col-lg-12 ar-box side-cat-menu">

<?php 

	$keywordsss = mysql_real_escape_string($_POST['keywords']);

    $sqls="SELECT * FROM `product_category_arabyos` WHERE `pc_name` LIKE '%".mysql_real_escape_string($_GET['keywords'])."%' and pc_status='1' ORDER BY `pc_id` DESC";

	$ress=mysql_query($sqls);

	$keyExits = mysql_num_rows($ress);

	$rows=mysql_fetch_object($ress);

	

	$catId = $rows->pc_id;

	$catParentId = $rows->pc_parent_id;

	$querys="select p.pc_id,p.pc_name,(select count(*) from products,measurement_unit,country, business_profile, plan_member_id where mu_id=pd_unit and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' and pd_subcat_id=p.pc_id and plan_member_id.expiry_date > ". time() .") as tot_prod from product_category_arabyos p where p.pc_parent_id='".$catParentId."' and pc_status='1' ";


	$tkeyword= trim($_GET['keywords']);
	$iProductCategoryId=mysql_query("SELECT  pd_subcat_id FROM `products` JOIN `business_profile` ON business_profile.bnsprof_uid = products.pd_uid WHERE (`pd_title` LIKE '%".mysql_real_escape_string($tkeyword)."%' OR `bnsprof_compname` LIKE '%".mysql_real_escape_string($tkeyword)."%') and pd_status='1' ");

	$iCategoryDetail = mysql_fetch_object($iProductCategoryId);

	

	$queryNewAlpha="SELECT  * FROM product_category_arabyos WHERE pc_id='".$iCategoryDetail->pd_subcat_id."' and pc_status='1' ORDER BY `pc_id` DESC";

	$queryResultNewAlpha = mysql_query($queryNewAlpha);

	$ResultsNewAlpha = mysql_fetch_object($queryResultNewAlpha) ;

 	?>

<section class="ar-flags">

  <form>

    <?php

	    $sql_check1="select * from product_category_arabyos where pc_parent_id='".$ResultsNewAlpha->pc_parent_id."'";

		$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());

		$counter = 0;

		$iiii = 0;

     	while($data=mysql_fetch_array($res_check1)){

			$iiii = $iiii+1;

 $sql_prd="select * from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan

 where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id = '".$data['pc_id']."' ORDER BY FIELD(p_id,'5','4','3','15') ";

  //echo $sql_prd;

		$iThisValu =	0 ;

		$recObj=mysql_query($sql_prd);

		$row_prd=mysql_fetch_object($recObj);

		$iThisValu = mysql_num_rows($recObj);

		$safasfasf = mysql_fetch_array($recObj);

		if($iiii=='1') { echo '<header> <span class="h4" style="font-weight:bold"> Sub Categories</span> </header>'; }

			

			$counter++;

				if($counter== 6)

                {

                   echo '<div class="collapse" id="categories">';

                }	



		if($iThisValu>0)

		{// TODO: hide more button when <=5 ? otherwise broken layout - webxtor @ 18 June 2018

				?>

                    <div class="checkbox" style="text-align:left;">

                     <!--  <label> <?php echo ' <span> <a href="search.php?keyword_type=&keywords='.$_GET['keywords'].'&rctyp=Products&idd='.$data['pc_id'].'">'.$data['pc_name'].'('.$iThisValu.')</a> </span>';?> </label> -->
                     <?php echo ' <span> <a href="search.php?keyword_type=&keywords='.$_GET['keywords'].'&rctyp=Products&idd='.$data['pc_id'].'">'.$data['pc_name'].'</a> </span>';?>

                    </div>

    <?php 

		}

	

	}

	

	if($counter >= 5)

	{    //21 august gaurav  //

			// echo '</div >';

		 //21 august gaurav //

	}

	//while ?>

    <?php if($counter>=5) { ?>

   

    <div class="text-right">

      <button class="btn btn-link collapsed" style="color: #1736e4;" type="button" data-toggle="collapse"  data-target="#categories" aria-expanded="false" aria-controls="collapseExample"> + View More </button>

    </div>

   

    <?php 	} ?>

  </form>

</section>

<?php

$queryResults = mysql_query($querys);

$subCatc = mysql_num_rows($queryResults);

?>

<!-- Div close for all country common  -->

<!-- NOTE : if you revert old so remove below comment and remove below div -->

</div>

<?php //if($_COOKIE['loc_id']!='187') { echo '</div>'; } ?> 



<!-- Div close for all country common  -->



<?php } 



if( $_GET['rctyp']=="tender" ){	



if( $_GET['ccidd']=="" ){

?>



<div class="col-lg-12 ar-box side-cat-menu">

<?php  $keywordsssSS = $_GET['keywords'];

		$SubCategoryArray ="";

		$iProductCategoryId="select * from tender,product_category_arabyos,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and tnd_heading like '%$keywordsssSS%' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) ".$sql_tnd_ck." and tnd_status='1' order by tnd_id";

		$nEWiProductCategoryId=mysql_query($iProductCategoryId);

	

	while($iCategoryDetail = mysql_fetch_object($nEWiProductCategoryId))

			{

				$pc_id_arr[]=$iCategoryDetail->tnd_pc_id;	

			}

	$SubCategoryArray = join("','",$pc_id_arr); 

	

 	$MasterCategoryArray ="";

	$queryNewAlpha="SELECT  * FROM product_category_arabyos WHERE pc_id  in ('$SubCategoryArray') and pc_status='1' ORDER BY `pc_id` DESC";

	$queryResultNewAlpha = mysql_query($queryNewAlpha);

	while($ResultsNewAlpha = mysql_fetch_object($queryResultNewAlpha))

	{	

		 $pc_id_arrNewNew[]=$ResultsNewAlpha->pc_parent_id;	

	

	}

	$MasterCategoryArray = join("','",$pc_id_arrNewNew); 

 	?>

<section class="ar-flags">

  <form>

    <?php	

	    $sql_check1="select * from product_category_arabyos where pc_parent_id in ('$MasterCategoryArray')";

		$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());

		$counter = 0;

		$iiiiNew = 0;

		$iAlphaCount= 0;

     	while($data=mysql_fetch_array($res_check1)){

			$iiiiNew = $iiiiNew+1;

			

 $sql_prd="select * from tender,product_category_arabyos,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and tnd_heading like '%$keywordsssSS%' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) ".$sql_tnd_ck." and tnd_status='1'  and tnd_pc_id = '".$data['pc_id']."' order by tnd_id LIMIT 0,  5";

 

		$iThisValu =	0 ;

		$recObj=mysql_query($sql_prd);

		$row_prd=mysql_fetch_object($recObj);

		$iThisValu = mysql_num_rows($recObj);

		$safasfasf = mysql_fetch_array($recObj);

		

		if($iAlphaCount=='0' && $iThisValu>0) {

			

			$iAlphaCount= $iAlphaCount+1;

			 echo '<header> <span class="h4" style="font-weight:bold">Sub Tenders </span> </header>'; 

			 

			 }

		if($iThisValu>0)

		{	

			

			$counter++;

				if($counter== 6)

                {

                   echo '<div class="collapse" id="categories">';

                }	

				?>

<div class="checkbox" style="text-align:left;">

<label> <?php echo ' <span> <a href="search.php?keyword_type=&keywords='.$_GET['keywords'].'&rctyp=tender&ttidd='.$data['pc_id'].'">'.$data['pc_name'].'('.$iThisValu.')</a> </span>';?> </label>

</div>

    <?php 

		}

	}

	if($counter > 5)

	{

		 echo '</div >';

	}

	//while ?>

    <?php if($counter>5) { ?>

    <div class="text-right">

      <button class="btn btn-link collapsed" type="button" data-toggle="collapse"  data-target="#categories" aria-expanded="false" aria-controls="collapseExample"> + View More </button>

    </div>

   

    <?php 	} ?>

  </form>

</section>





<?php



 } ?>





<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------Action Category NOw---------------------------------------------------------------->





<?php  



if( $_GET['ttidd']=="" ){	

		$keywordsssSS = $_GET['keywords'];

		$SubCategoryArray ="";

		

		

		$iProductCategoryId="select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_approval_status='1' and TO_DAYS(auc_due_date)>=TO_DAYS(now()) ".$sql_auc_ck." and auc_heading like '%$keywordsssSS%' and auc_status='1' order by auc_id";

		$nEWiProductCategoryId=mysql_query($iProductCategoryId);

	while($iCategoryDetail = mysql_fetch_object($nEWiProductCategoryId))

			{

				$pc_id_arr[]=$iCategoryDetail->auc_pc_id;	

			}

	$SubCategoryArray = join("','",$pc_id_arr); 

	

 	$MasterCategoryArray ="";

	$queryNewAlpha="SELECT  * FROM product_category_arabyos WHERE pc_id  in ('$SubCategoryArray') and pc_status='1' ORDER BY `pc_id` DESC";

	$queryResultNewAlpha = mysql_query($queryNewAlpha);

	while($ResultsNewAlpha = mysql_fetch_object($queryResultNewAlpha))

	{	

		 $pc_id_arrNewNew[]=$ResultsNewAlpha->pc_parent_id;	

	

	}

	$MasterCategoryArray = join("','",$pc_id_arrNewNew); 

 	?>

<section class="ar-flags">

  <form>

    <?php	

	    $sql_check1="select * from product_category_arabyos where pc_parent_id in ('$MasterCategoryArray')";

		$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());

		$counter = 0;

		$iiiiNew = 0;

		$iALphaActNew = 0;

     	while($data=mysql_fetch_array($res_check1)){

			$iiiiNew = $iiiiNew+1;







$sql_prd="select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_approval_status='1' and TO_DAYS(auc_due_date)>=TO_DAYS(now()) ".$sql_auc_ck." and auc_pc_id = '".$data['pc_id']."' and auc_status='1' order by auc_id";

 

		$iThisValu =	0 ;

		$recObj=mysql_query($sql_prd);

		$row_prd=mysql_fetch_object($recObj);

		$iThisValu = mysql_num_rows($recObj);

		$safasfasf = mysql_fetch_array($recObj);

		

		if($iALphaActNew=='0'  && $row_prd>0) { 

		

		$iALphaActNew = $iALphaActNew+1;

		

		echo '<header> <span class="h4" style="font-weight:bold">Sub Auctions </span> </header>'; }

		if($iThisValu>0)

		{	

			

			$counter++;

				if($counter== 6)

                {

                   echo '<div class="collapse" id="categories1">';

                }	

				?>

<div class="checkbox" style="text-align:left;">

<label> <?php echo ' <span> <a href="search.php?keyword_type=&keywords='.$_GET['keywords'].'&rctyp=tender&ccidd='.$data['pc_id'].'">'.$data['pc_name'].'('.$iThisValu.')</a> </span>';?> </label>

</div>

    <?php 

		}

	}

	if($counter > 5)

	{

		 echo '</div >';

	}

	

	//while ?>

    <?php if($counter>5) { ?>

    <div class="text-right">

      <button class="btn btn-link" type="button" data-toggle="collapse"  data-target="#categories1" aria-expanded="false" aria-controls="collapseExample"> + View More </button>

    </div>

   

    <?php 	} ?>

  </form>

</section>



<?php 

 }   if($_COOKIE['loc_id']!='187' && $_GET['ccidd']=='') { echo '</div>'; }   ?>





<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->

<!--------------------------------------------------------Action Category END---------------------------------------------------------------->



<?php 



} 





if( $_GET['rctyp']=="buy_lead" ){	

?>



<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category NOw---------------------------------------------------------------->

<div class="col-lg-12 ar-box side-cat-menu">

<?php  $keywordsssSS = $_GET['keywords'];

		$SubCategoryArray ="";

		$keywords=trim($_GET['keywords']);

		$keywords_string=generateBuyleadSearchString($keywords);

		

  $iProductCategoryId = "select * , MATCH (br_pd_name) AGAINST ('".$keywordsssSS."'  IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit,user where br_estimate_qty_unit=mu_id and br_u_id=usr_id  and  (".$keywords_string.") and br_approval_status = '1' and br_display_status = '1' and br_status = '1' ".$sql_br_ck." ".$sql_extra." order by title_relevance desc  " ;

	

 		$nEWiProductCategoryId=mysql_query($iProductCategoryId);

	

	while($iCategoryDetail = mysql_fetch_object($nEWiProductCategoryId))

			{

				$pc_id_arr[]=$iCategoryDetail->br_pc_id;	

			}

	$SubCategoryArray = join("','",$pc_id_arr); 

	

 	$MasterCategoryArray ="";

	 $queryNewAlpha="SELECT  * FROM product_category_arabyos WHERE pc_id  in ('$SubCategoryArray') and pc_status='1' ORDER BY `pc_id` DESC";

	$queryResultNewAlpha = mysql_query($queryNewAlpha);

	while($ResultsNewAlpha = mysql_fetch_object($queryResultNewAlpha))

	{	

		 $pc_id_arrNewNew[]=$ResultsNewAlpha->pc_parent_id;	

	

	}

	$MasterCategoryArray = join("','",$pc_id_arrNewNew); 

 	?>

<section class="ar-flags">

  <form>

    <?php	

	    $sql_check1="select * from product_category_arabyos where pc_parent_id in ('$MasterCategoryArray')";

		$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());

		$counter = 0;

		$iiiiNew = 0;

     	while($data=mysql_fetch_array($res_check1)){

			$iiiiNew = $iiiiNew+1;



$sql_prd = "select * , MATCH (br_pd_name) AGAINST ('".$keywordsssSS."'  IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id   and  (".$keywords_string.") and br_approval_status = '1' and br_display_status = '1'  and br_pc_id = '".$data['pc_id']."' and br_status = '1' ".$sql_br_ck." ".$sql_extra." order by title_relevance asc  " ;

//$sql_prd = "select * , MATCH (br_pd_name) AGAINST ('".$keywordsssSS."'  IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id    ".$keywords_string."  and br_approval_status = '1' and br_display_status = '1' and br_status = '1' ".$sql_br_ck." ".$sql_extra." and br_pc_id = '".$data['pc_id']."' order by title_relevance desc  " ;

			

//$sql_prd="select * from tender,product_category_arabyos,user,business_profile where br_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and tnd_heading like '%$keywordsssSS%' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) ".$sql_tnd_ck." and tnd_status='1'  and br_pc_id = '".$data['pc_id']."' order by tnd_id LIMIT 0,  5";

 

		$iThisValu =	0 ;

		$recObj=mysql_query($sql_prd);

		$row_prd=mysql_fetch_object($recObj);

		$iThisValu = mysql_num_rows($recObj);

		$safasfasf = mysql_fetch_array($recObj);

		if($iiiiNew=='1') { echo '<header> <span class="h4" style="font-weight:bold"> Sub Categories</span> </header>'; }

		if($iThisValu>0)

		{	

			

			$counter++;

				if($counter== 6)

                {

                   echo '<div class="collapse" id="categories">';

                }	

				?>

		<div class="checkbox" style="text-align:left;">

		<label> <?php echo ' <span> <a href="search.php?keyword_type=&keywords='.$_GET['keywords'].'&rctyp=buy_lead&bbidd='.$data['pc_id'].'">'.$data['pc_name'].'('.$iThisValu.')</a> </span>';?> </label>

		</div>

    <?php 

		}

	}

	if($counter > 5)

	{

		// echo '</div >';

	}

	//while ?>

    <?php if($counter>5) { ?>

    <div class="text-right">

      <button class="btn btn-link" type="button" data-toggle="collapse"  data-target="#categories" aria-expanded="false" aria-controls="collapseExample"> + View More </button>

    </div>

   

    <?php 	} ?>

  </form>

</section>





 











<?php  



   if($_COOKIE['loc_id']!='187' && $_GET['ccidd']=='') { echo '</div>'; }   ?>





<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->

<!--------------------------------------------------------BUY LEADAS Category END---------------------------------------------------------------->



<?php 



} 

 }  

 }	

  if($_GET['keywords'] != '') {

	  if(isset($_GET['rctyp']))

	  {

	  if($_GET['rctyp']!='Suppliers')

	  {

	$key =$_GET['keywords']; 

		  

		

		  

	  ?>

<div class="col-lg-12 ar-box text-justify webcast-alert-fix hidden-xs" style="padding-right:23px;" id="business-alert">

  <header> <span class="h5 beellling" style="font-size:18px;"><img src="images/bell.png" width="20"/> <b class="txt-orange">Business Alerts</b></span> </header>

  <b class="h5 txt-purple txt-bold beellling-get" style="display:block;"> Get timely updates<br>

  in your inbox for :</b>

  

    <?php

		    if($_GET['rctyp']=='buy_lead')

			{

				

				

			 // $sql_key="select * from product_category_arabyos where pc_name like '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1' and pc_parent_id!='0'";

				

				 $sql_key="select * from buy_requirement join product_category_arabyos on product_category_arabyos.pc_id=buy_requirement.br_pc_id where (br_pd_name like '%".str_replace("+"," ",$_GET['keywords'])."%' OR pc_name like '%".str_replace("+"," ",$_GET['keywords'])."%') and pc_status='1'  and pc_parent_id!='0'";

				//echo $sql_key;

				$query_key = mysql_query($sql_key);

				$row_key=mysql_fetch_object($query_key);

				//if(isset($_GET['keyword_typesss'])||isset($_GET['keywords'])||isset($_GET['rctyp'])){echo "?val=true";}

				if(mysql_num_rows($query_key) > 0){

				

				?>



	<p class="h4 txt-orange text-center margin-top-10 beellling-key max7">"<!--Rice--><? echo ucwords($row_key->pc_name); ?>"</p>

  <div class="text-center">

    <a href="manage-buylead-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->br_pc_id; ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

    <?php }else { 

			 $busss_alert_cat_name='';

		$sql_second_query=mysql_query("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%".str_replace(array("+","%20"),array(" "," "),$_GET['keywords'])."%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");

			$fetch_records=mysql_fetch_object($sql_second_query);

			if(mysql_num_rows($sql_second_query) > 0){

			$sub_cat_id_get=$fetch_records->pc_id;

				$sql_second_query1=mysql_query("SELECT * FROM product_category_arabyos WHERE pc_parent_id='".$sub_cat_id_get."' and pc_status='1'");

				$fetch_records1=mysql_fetch_object($sql_second_query1);

				if(mysql_num_rows($sql_second_query1) > 0){

					$busss_alert_cat_name=$fetch_records1->pc_name;

					$sub_cat_id_get=$fetch_records1->pc_id;

				}

				else{

					$busss_alert_cat_name=$fetch_records->pc_name;

				}



			}

		?> 

		

			<p class="h4 txt-orange text-center margin-top-10 beellling-key max6">"<!--Rice--><?php echo $busss_alert_cat_name; ?>"</p>

  <div class="text-center">

    <a href="manage-buylead-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $sub_cat_id_get; ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

		<?php }

			}

		  else if($_GET['rctyp']=='tender')

		  {

			  $sql_key="select * from tender join product_category_arabyos on product_category_arabyos.pc_id=tender.tnd_pc_id where tnd_heading like '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1'";

				//echo $sql_key;

				$query_key = mysql_query($sql_key);

				$row_key=mysql_fetch_object($query_key);

				//echo '<pre>'; print_r($row_key);echo "</pre>";

			if(mysql_num_rows($query_key) > 0) {

			  ?>

		<p class="h4 txt-orange text-center margin-top-10 beellling-key max5">"<!--Rice--><? echo ucwords($row_key->pc_name); ?>"</p>

  <div class="text-center">

    <a href="manage-tender-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->tnd_pc_id; ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

    <?php

			} else {

				$sql_key="select * from auction join product_category_arabyos on product_category_arabyos.pc_id=auction.auc_pc_id where auc_heading like '%".str_replace("+"," ",$_GET['keywords'])."%' and pc_status='1'";

			//echo $sql_key;

				$query_key = mysql_query($sql_key);

				$row_key=mysql_fetch_object($query_key);

				//echo '<pre>'; print_r($row_key);echo "</pre>";

				if(mysql_num_rows($query_key) > 0) { ?>

		<p class="h4 txt-orange text-center margin-top-10 beellling-key max4">"<!--Rice--><? echo ucwords($row_key->pc_name); ?>"</p>

  <div class="text-center">

    <a href="manage-auction-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->auc_pc_id; ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

    <?php }

			else {

				 $busss_alert_cat_name='';

		$sql_second_query=mysql_query("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%".str_replace(array("+","%20"),array(" "," "),$_GET['keywords'])."%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");

			$fetch_records=mysql_fetch_object($sql_second_query);

			if(mysql_num_rows($sql_second_query) > 0){

			$sub_cat_id_get=$fetch_records->pc_id;

				$sql_second_query1=mysql_query("SELECT * FROM product_category_arabyos WHERE pc_parent_id='".$sub_cat_id_get."' and pc_status='1'");

				$fetch_records1=mysql_fetch_object($sql_second_query1);

				if(mysql_num_rows($sql_second_query1) > 0){

					$busss_alert_cat_name=$fetch_records1->pc_name;

					$sub_cat_id_get=$fetch_records1->pc_id;

				}

				else{

					$busss_alert_cat_name=$fetch_records->pc_name;

				}



			}

		?> 

			<p class="h4 txt-orange text-center margin-top-10 beellling-key max3">"<?php echo $busss_alert_cat_name; ?>"</p>

  <div class="text-center">

     <a href="manage-auction-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $sub_cat_id_get; ?>">

	 <button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

		<?php }

			}

		  }

			else

			{

				

				$sql_key="select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id where pd_title like '".$_GET['keywords']."' and pc_status='1'";

			//echo $sql_key;

				$query_key = mysql_query($sql_key);

				$row_key=mysql_fetch_object($query_key);

				//echo '<pre>'; print_r($row_key);echo "</pre>";

				if(mysql_num_rows($query_key) > 0) { 

				$neeraj_right_sidebar_content_comming_vairable = $row_key->pc_name;

				?>

		<p class="h4 txt-orange text-center margin-top-10 beellling-key max2">"<!--Rice--><? echo ucwords($row_key->pc_name); ?>"</p>

  <div class="text-center">

   <a href="manage-selloffer-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $row_key->pd_subcat_id; ?>">

   <button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

    <?php }else {

			$busss_alert_cat_name='';

			$sql_key11="select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id join `business_profile` on business_profile.bnsprof_uid = products.pd_uid where (pd_title like '%".$_GET['keywords']."%' or bnsprof_compname LIKE '%".$_GET['keywords']."%') and pc_status='1'";

			//echo $sql_key;

				$query_key11 = mysql_query($sql_key11);

				$row_key11=mysql_fetch_object($query_key11);

				//echo '<pre>'; print_r($row_key);echo "</pre>";

				if(mysql_num_rows($query_key11) > 0) { 		

					$busss_alert_cat_name=$row_key11->pc_name;

					$sub_cat_id_get=$row_key11->pd_subcat_id;

				}

				else{

			

			$sql_second_query=mysql_query("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%".str_replace(array("+","%20"),array(" "," "),$_GET['keywords'])."%' AND pc.pc_parent_id!='0' and pc_status='1'");

			$fetch_records=mysql_fetch_object($sql_second_query);

			if(mysql_num_rows($sql_second_query) > 0){

			$sub_cat_id_get=$fetch_records->pc_id;

				$sql_second_query1=mysql_query("SELECT * FROM product_category_arabyos WHERE pc_parent_id='".$sub_cat_id_get."' and pc_status='1'");

				$fetch_records1=mysql_fetch_object($sql_second_query1);

				if(mysql_num_rows($sql_second_query1) > 0){

					$busss_alert_cat_name=$fetch_records1->pc_name;

					$sub_cat_id_get=$fetch_records1->pc_id;

				}

				else{

					$busss_alert_cat_name=$fetch_records->pc_name;

				}



			}

				}

				

				define('neeraj_right_sidebar_content_comming_vairable',$busss_alert_cat_name);

	   ?> 

	   			

			<p class="h4 txt-orange text-center margin-top-10 beellling-key max">"<?php echo $busss_alert_cat_name; ?>"</p>

  <div class="text-center">

    <a href="manage-selloffer-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>&sub_cat_id=<?php echo $sub_cat_id_get; ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a> </div>

		<?php }

			}

			?>

    

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

  <header> <span class="h5 beellling" style="font-size:18px;"><img src="images/bell.png" width="20"/> <b class="txt-orange">Business Alerts</b></span> </header>

  <b class="h5 txt-purple txt-bold beellling-get" style="display:block;"> Get timely updates<br>

  in your inbox for :</b>

  <p class="h4 txt-orange text-center margin-top-10 beellling-key">"<!--Rice--><? echo ucwords($_GET['keywords']); ?>"</p>

  <div class="text-center">

    <?php

		    if($_GET['rctyp']=='buy_lead')

			{

				?>

    <a href="manage-buylead-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a>

    <?php

			}

		  else if($_GET['rctyp']=='tender')

		  {

			  ?>

    <a href="manage-tender-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a>

    <?php

			}

		  else if($_GET['rctyp']=='auction')

		  {

			  ?>

    <a href="manage-auction-alert.php?val=true<?php  if(isset($_GET['keyword_typesss'])){echo "&keyword_typesss=".$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo "&keywords=".$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){echo "&rctyp=".$_GET['rctyp'];} ?>">

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a>

    <?php

			}

			else

			{

				?>

    <a href="manage-selloffer-alert.php?search_keyword=<?=$key?><?php  if(isset($_GET['keyword_typesss'])){echo '&keyword_typesss='.$_GET['keyword_typesss'];} ?><?php if(isset($_GET['keywords'])){echo '&keywords='.$_GET['keywords'];} ?><?php if(isset($_GET['rctyp'])){ echo '&rctyp='.$_GET['rctyp'];} ?>" >

	<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Confirm</button>

    </a>

    <?php

			}

			?>

    

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

  <?php if($_GET['rctyp']!='Suppliers'  && $_GET['rctyp']!='tender' && $_GET['rctyp']!='buy_lead') { ?>

  	<div class="col-lg-12 side_compare_list webcast-alert-fix1" style="padding-right: 0; padding-left: 0; display: none;">

		<h4>Compare List</h4>

		<!-- <br> -->

		<div class="comp-list">
		</div>

		<a href="compare.php" class="text-center" >

		<button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-top-10" style="padding:0 3px;">Compare</button>

		</a> 

	</div>

  <?php } ?>

</div>

<?php //if($_GET['grid']=='active' || $_GET['list']=='active' || $_GET['rctyp']=='') {  echo '</div>'; } // breaks design! webxtor 1 Aug 2018 ?>





