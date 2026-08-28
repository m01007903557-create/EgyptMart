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

?>

<!-- //////////////// -->
<?php
//print_r($location_geo_country);
//echo $sql_pd_ck; die;
          //  echo $view_country;
        if($_GET['rctyp'] == 'Suppliers'){ 
			if(isset($_COOKIE['loc_id'])){
				$view_country2 = "select business_profile.bnsprof_state from products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid  where (bnsprof_compname LIKE '%".$keywords."%') and ((pd_preferred_buyer_location =  'domestic' AND products.pd_currency =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'any' AND products.pd_currency =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'my_city' AND products.pd_currency =  '".$_COOKIE['loc_id']."')) group by products.pd_uid HAVING business_profile.bnsprof_state >0";
	//echo $view_country2; 	
			}else{
					$view_country2 = "select business_profile.bnsprof_state from products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid where (bnsprof_compname LIKE '%".$keywords."%') and ((pd_preferred_buyer_location =  'domestic' AND products.pd_currency =  '".$location_geo_country[0]."') OR (pd_preferred_buyer_location =  'any' AND products.pd_currency =  '".$location_geo_country[0]."') OR (pd_preferred_buyer_location =  'my_city' AND products.pd_currency =  '".$location_geo_country[0]."' )) group by products.pd_uid HAVING business_profile.bnsprof_state >0";
			}
		}
 //echo $_COOKIE['loc_id'];die;
 if(!isset($_COOKIE['loc_id'])) {
    //echo 'LINE 32';exit;
?>
       <?php /*?>   <header style="font-size: 16px;color: #00297c;font-weight: 700;"><span><?php if($_GET['rctyp'] == 'buy_lead'){ echo " Buy Leads"; } ?> Countries / States</span> </header><?php */?>
		<?php if($_GET['rctyp']){ $rctypval = $_GET['rctyp'];}
		 $keywordsval = '';	?>   
          <section class="ar-flags">
            <form action="search.php?rctyp=<?php echo $rctypval;?>&keywords=<?php echo $keywordsval;?>"  method="post"  >
                <?php
					 error_reporting(1);
					 
			
          if($_GET['rctyp'] == 'Suppliers'){ 
			if(isset($_COOKIE['loc_id'])){
			$view_country = "select * from products,product_category_arabyos,measurement_unit,country,business_profile,user,plan_member_id where usr_id = pd_uid AND products.pd_subcat_id = product_category_arabyos.pc_id AND bnsprof_uid = pd_uid AND mu_id=pd_unit AND pc_parent_id='".$row_scat->pc_id."' and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'any' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'my_city' AND user.country =  '".$_COOKIE['loc_id']."')) AND plan_member_id.expiry_date > " . time() . " and pd_status='1' and pd_image!='' group by pd_currency";
			}else{
				$view_country = "select * from products,product_category_arabyos,measurement_unit,country,business_profile,plan_member_id where mu_id=pd_unit and products.pd_subcat_id = product_category_arabyos.pc_id and pc_parent_id='".$row_scat->pc_id."' and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id  and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' AND plan_member_id.expiry_date > " . time() . " and pd_image!='' group by pd_id";
			}
	  }
			//echo $view_country;exit;
			//echo $data['country']; die;
		 $run_sql= mysql_query($view_country, $link);
		 $counter=0;
		 $country_id = array();
		 if(isset($_SESSION['country_id'])) {
			$country_id = $_SESSION['country_id'] ;
			 
		 }
		$count_country = count($run_sql);
		//var_dump($view_country);
		if($count_country!=''){  
		  ?>
    <?php /*?><header style="font-size: 16px;color: #00297c;font-weight: 700;"><span><?php if(($_GET['rctyp'] == 'buy_lead') || ($_GET['rctyp'] == 'tender') || ($_GET['rctyp'] == 'Suppliers') || ($_GET['rctyp'] == 'Products')){ echo " Supplier Countries"; }else{ echo "Countries / States"; } ?> </span> </header><?php */?>
    <header><span class="h4">Supplier Countries </span> </header>
   <?php }
 	$country_buy=array();
   while( $row11=mysql_fetch_array($run_sql, MYSQL_ASSOC))
		{
			//echo "<pre>" ; print_r($row11);  die;
			 if($_GET['rctyp'] == 'Suppliers'){ 
		$country_buy[] =  ucfirst(city_to_country(user_info($row11['pd_uid'],'bnsprof_city')));
			}
			
		}
	 if(count($country_buy)>0){
		 $country_buy=array_unique($country_buy);
		 foreach($country_buy as $cb){
		    if($_GET['rctyp'] == 'Suppliers'){ 
		//$country_buy =  ucfirst(city_to_country(user_info($row11['tnd_usr_id'],'bnsprof_city')));	
		//echo "<br>"."--------------".$country_buy;
             //  echo "SELECT * FROM `country` where cn_name ='$country_buy'"; exit;
				$Couflag =mysql_query("SELECT * FROM `country` where cn_name ='$cb'");
				 while($rowk=mysql_fetch_array($Couflag))
				  { 
				//echo $Couflag; die;
				//echo "<pre>" ; print_r($country_buy);  die;
			 $counter++;
                /*if($counter== 5)
                {
                   echo '<div class="collapse" id="countries">';
                }*/
  ?>
              <div class="checkbox" style="text-align:left;">
                <label class="sup">
                    <input type="checkbox" class="search_filter" <?php echo (in_array($rowk['cn_name'],$country_id))?"checked":'';?> name="country_id[]" value="<?php echo $rowk['cn_name']; ?>"> 
                  <?php  echo "<img src='images/country_flag/".$rowk['cn_flag']."' height='22' width='22'>"; ?> <span><?php echo $rowk['cn_name']; ?></span> </label>
              </div>
                            <?php
                             /*if($counter== 5)
                {
                   echo '</div>';
                }*/
	  }//while
							  }
							$data =  $userArrayRow_Result[$row11['pd_uid']];	
							$arra_country[]=$data['country'];	
		 }
 }
							//print_r($arra_country);
							function myfunction($val)
							{
								if($val!='')
								{
									return $val;
									}
								}
							 $country_string= implode(",",array_filter(array_unique($arra_country),'myfunction'));
				/*$rr = "SELECT * FROM `country` where cn_id in($country_string)"; 
					echo "-----------------------------".$rr;  die;	*/
							$CountryName =mysql_query("SELECT * FROM `country` where cn_id in($country_string)");
							$count_country = mysql_num_rows($CountryName);
							   while($getCountryName=mysql_fetch_array($CountryName, MYSQL_ASSOC))
                            {
								 $getCountryName;
								$counter++;
                /*if($counter== 5)
                {
                   echo '<div class="collapse" id="countries">';
                }*/

                $sql = mysql_query("select measurement_unit.*,country.*,business_profile.*,products.*,product_category_arabyos.*,user.usr_id,user.email,user.fname,user.website,user.country,user.image,user.country_ph_code,user.profileImage, MATCH (pd_title) AGAINST ('beans' IN BOOLEAN MODE) AS title_relevance from products,product_category_arabyos,measurement_unit,country,business_profile,user,plan_member_id where 
user.usr_id = bnsprof_uid
 and bnsprof_uid=pd_uid
 and products.pd_subcat_id =product_category_arabyos.pc_id
 and b_id = bnsprof_id 
and mu_id=pd_unit 
and pc_parent_id='".$row_scat->pc_id."'
and (user.country = '".$getCountryName['cn_id']."') 
and pd_currency=cn_id 
and pd_status='1' and 
pd_image!='' AND 
plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15'), pd_title asc ");
      $countrow = mysql_num_rows($sql);
      if($countrow){
 ?>
              <div class="checkbox" style="text-align:left;">
           <label>
          <input type="checkbox" class="search_filter" <?php echo (in_array($getCountryName['cn_name'],$country_id))?"checked":'';?> name="Phone From Checkout Form[]" value="<?php echo $getCountryName['cn_id']; ?>">
                  <?php  if($getCountryName['cn_flag'] !=''){
				  echo "<img src='images/country_flag/".$getCountryName['cn_flag']."' height='22' width='22'>"; ?> <span><?php echo $getCountryName['cn_name']; ?><!--(<?php echo  $countrow;?>)--></span> 
                  <?php } ?>
                  </label>
                  </div>
                            <?php
				}			//if($counter==$count_country)
               // if($counter>= 5)
			   if($count_country>=5)
			   {
			   /*if($counter==$count_country)
                {
                  echo '</div>';
                }*/
			   }
                }		 
                 if($count_country>= 5)
                {
                             ?>
              <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#countries" aria-expanded="false" aria-controls="collapseExample"> + View More </a> </div>
               <?php } ?>
               <?php 
               if($counter == 1){
               $web_cnfrm = true;	
               } 
               else {
               	$web_cnfrm = false;
               }
               ?>
			   <?php if($_GET['rctyp'] != 'tender' && $_GET['rctyp'] != 'buy_lead' && !$web_cnfrm){  ?>
              <div class="form-group text-center">
                <!--<button type="button" class="btn btn-sm btn-warning border-radius-0" style="padding:0 3px; margin-right:10px;">Confirm</button>-->
				<input id="btnSearch" value="Confirm" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit" style="float: left; margin-left: 6px; margin-top: 10px;">
                <!--<button type="reset" class="btn btn-sm btn-link border-radius-0 txt-bold txt-black"  style="padding:0 3px;">Cancel</button>-->
				<a href="javascript: window.history.go(-1)" class="cancelBtns" style="float: left; margin-bottom: 15px; margin-left: 4px; margin-top: 7px; width: 34px;font-weight:bold;">Cancel</a>
              </div>
			  <?php } ?>
            </form>
          </section>
<?php } if(isset($_COOKIE['loc_id'])) {
	//echo $view_country2;die;
	?>
          <section class="ar-flags">
    <?php /*?>  <header id="supplier_state_text" style="font-size: 16px;color: #00297c;font-weight: 700;"><?php if($_GET['rctyp'] == 'buy_lead'){ echo " Buy Leads"; }?> Countries / States </header><?php */?>
<?php // $sql = "select * from states where state_cn_id = ".$_COOKIE['loc_id']." and state_cn_id in( select distinct country from user where country='".$_COOKIE['loc_id']."' and usr_id in(".$view_country2.") )";
	//echo $view_country2;exit;
    $sql = "select * from states where state_id in (".$view_country2.") ";
		/*echo "----------------------";
		 */
  //echo $sql;
       $run_sql1= mysql_query($sql,$link) or die(mysql_error());
                         $state_id = array();
                if(isset($_SESSION['state_id'])) {
                  $state_id = $_SESSION['state_id'] ;
           }     
//if(!empty($state_id))
//if($state_id!='') {
	$count_states = mysql_num_rows($run_sql1);
	if($count_states!='') {
	?>
  <header><span class="h4">Supplier States</span></header>
   <?php }
                        $counter=0;
						//$count_states = mysql_num_rows($run_sql1);
                         while($rowz=mysql_fetch_array($run_sql1, MYSQL_ASSOC))
                            {
								$counter++;
							//	echo "<pre>"; print_r($rowz);
						$sql_country_flag_image="select * from country where cn_id='".$rowz['state_cn_id']."'";
						//echo $sql_country_flag_image;die;
						$result=mysql_query($sql_country_flag_image);
						$row_flag=mysql_fetch_array($result);
				if($counter== 5)
                {
                   echo '<div class="collapse" id="states">';
                }								
				if($_GET['rctyp']){ $rctypval = $_GET['rctyp'];}
				$keywordsval = "";
 ?>
			<form action="search.php?rctyp=<?php echo $rctypval;?>&keywords=<?php echo $keywordsval;?>"  method="post"  >
				<div id="loading_div_status">
                <label>
                    <input type="checkbox" class="search_filter" <?php echo (in_array($rowz['state_id'],$_POST['state_id']))?"checked":'';?> name="state_id[]" value="<?php echo $rowz['state_id']; ?>">
                        <?php  echo "<img src='images/country_flag/".$row_flag['cn_flag']."' height='22' width='22'>"; ?>
                 <span><?php echo $rowz['state_name']; ?></span>
                </label>
              </div>
              <?php
			  if($count_states>=5)
			  {
			  if($counter==$count_states)
			  {
			  echo '</div>';
			  }
			  }
			  ?>
          <?php }//while
		  		if($count_states>= 5)
                {
                             ?>
              <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#states" aria-expanded="false" aria-controls="collapseExample"> + View More </a> </div>
               <?php } //echo '</section>';} ?>
			   <div class="form-group text-center">
                <!--<button type="button" class="btn btn-sm btn-warning border-radius-0" style="padding:0 3px; margin-right:10px;">Confirm</button> -->
				<input id="btnSearch" value="Confirm" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit" style="float: left; margin-left: 6px; margin-top: 10px;">
                <!--<button type="reset" class="btn btn-sm btn-link border-radius-0 txt-bold txt-black"  style="padding:0 3px;">Cancel</button>-->
				<a href="javascript: window.history.go(-1)" class="cancelBtns" style="float: left; margin-bottom: 15px; margin-left: 4px; margin-top: 7px; width: 34px; font-weight:bold;">Cancel</a>
              </div>
			   
            </form>
			 <?php }  ?>
		</section>


</div>





