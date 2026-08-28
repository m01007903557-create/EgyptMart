<?php
//print_r($location_geo_country);
//echo $sql_pd_ck; die;
          //  echo $view_country;
       if($_GET['rctyp'] == 'buy_lead'){ 
		$view_country2 = "select business_profile.bnsprof_state from buy_requirement,business_profile  where business_profile.bnsprof_uid = buy_requirement.br_u_id ".$keywords_string."  and br_display_status='1' and br_approval_status='1' ".$sql_br_ck." group by buy_requirement.br_u_id";
		//echo $view_country2; 	
		} 
		else if($_GET['rctyp'] == 'tender'){
			$tender_keywords_string = generateTenderSearchString($keywords);
			if(isset($_COOKIE['loc_id'])){
				$tenderCondition = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (tnd_preferred_location='any' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (tnd_preferred_location='my_city' AND tnd_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='".$_COOKIE['loc_id']."'))))";
			}else{
				$tenderCondition = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
			}
			$view_country2 = "select business_profile.bnsprof_state from tender,business_profile where business_profile.bnsprof_uid = tender.tnd_usr_id AND (".$tender_keywords_string.") and tnd_approval_status='1' ".$tenderCondition." group by tender.tnd_usr_id";//and TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) 
	//echo $view_country2; 	
		} 
		else if($_GET['rctyp'] == 'Suppliers'){ 
			if(isset($_COOKIE['loc_id'])){
				$view_country2 = "select business_profile.bnsprof_state from products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid  where (bnsprof_compname LIKE '%".$keywords."%') and ((pd_preferred_buyer_location =  'domestic' AND products.pd_currency =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'any' AND products.pd_currency =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'my_city' AND products.pd_currency =  '".$_COOKIE['loc_id']."')) group by products.pd_uid HAVING business_profile.bnsprof_state >0";
	//echo $view_country2; 	
			}else{
					$view_country2 = "select business_profile.bnsprof_state from products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid  where (bnsprof_compname LIKE '%".$keywords."%') and ((pd_preferred_buyer_location =  'domestic' AND products.pd_currency =  '".$location_geo_country[0]."') OR (pd_preferred_buyer_location =  'any' AND products.pd_currency =  '".$location_geo_country[0]."') OR (pd_preferred_buyer_location =  'my_city' AND products.pd_currency =  '".$location_geo_country[0]."' )) group by products.pd_uid HAVING business_profile.bnsprof_state >0";
			}
		} 
		 //else if($_GET['rctyp'] == 'Products') {
		else {
			$view_country2 = "select business_profile.bnsprof_state from products,business_profile  where pd_image!=''  and (products.pd_title LIKE '%".$_GET['keywords']."%') and business_profile.bnsprof_uid = products.pd_uid and pd_status='1' ".$sql_pd_ck." group by products.pd_uid";			 
			//echo $view_country2;
		}
 //echo $_COOKIE['loc_id'];die;
 if(!isset($_COOKIE['loc_id'])) {
    //echo 'LINE 32';exit;
?>
       <?php /*?>   <header style="font-size: 16px;color: #00297c;font-weight: 700;"><span><?php if($_GET['rctyp'] == 'buy_lead'){ echo " Buy Leads"; } ?> Countries / States</span> </header><?php */?>
		<?php if($_GET['rctyp']){ $rctypval = $_GET['rctyp'];}
		if($_GET['keywords']){ $keywordsval = $_GET['keywords'];}	?>   
          <section class="ar-flags">
            <form action="search.php?rctyp=<?php echo $rctypval;?>&keywords=<?php echo $keywordsval;?>"  method="post"  >
                <?php
                     error_reporting(1);
               //  echo $view_country;
       if($_GET['rctyp'] == 'buy_lead'){ 
			 $view_country = "select * from buy_requirement,measurement_unit,country where br_estimate_qty_unit=mu_id ".$keywords_string." and br_display_status='1' and br_approval_status='1' ".$sql_br_ck."  group by br_u_id";
	//echo $view_country; 	
		} 
		else if($_GET['rctyp'] == 'tender'){
			$tender_keywords_string = generateTenderSearchString($keywords);
			if(isset($_COOKIE['loc_id'])){
				$tenderCondition = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (tnd_preferred_location='any' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (tnd_preferred_location='my_city' AND tnd_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='".$_COOKIE['loc_id']."'))))";
			}else{
				$tenderCondition = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
			}
			$view_country = "select * from tender,product_category,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid AND (".$tender_keywords_string.") and tnd_approval_status = '1' ".$tenderCondition." group by tender.tnd_currency ORDER BY tnd_id desc ";//and TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) 
		}else if($_GET['rctyp'] == 'Suppliers'){ 
			if(isset($_COOKIE['loc_id'])){
				$view_country = "select * from products,measurement_unit,country,business_profile,user where usr_id = pd_uid AND bnsprof_uid = pd_uid AND mu_id=pd_unit AND (bnsprof_compname LIKE '%".$keywords."%') and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'any' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'my_city' AND user.country =  '".$_COOKIE['loc_id']."')) and pd_status='1' and pd_image!='' group by pd_currency";
			}else{
				$view_country = "select * from products,measurement_unit,country,business_profile,user where usr_id = pd_uid AND bnsprof_uid = pd_uid AND mu_id=pd_unit AND (bnsprof_compname LIKE '%".$keywords."%') and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic') OR (pd_preferred_buyer_location =  'any') OR (pd_preferred_buyer_location =  'my_city')) and pd_status='1' and pd_image!='' group by pd_currency";
			}
	  }else { 
		  if($_GET['idd']!=""){
			  $view_country="select * from products,measurement_unit,country where mu_id=pd_unit and pd_subcat_id = ".$_GET['idd']." and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' group by pd_currency ";
		 }else{
			 $view_country = "select * from products,measurement_unit,country where mu_id=pd_unit and (pd_title LIKE '%".$_GET['keywords']."%') and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' group by pd_id";
			//echo $view_country;
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
		if($count_country!=''){  
		  ?>
    <?php /*?><header style="font-size: 16px;color: #00297c;font-weight: 700;"><span><?php if(($_GET['rctyp'] == 'buy_lead') || ($_GET['rctyp'] == 'tender') || ($_GET['rctyp'] == 'Suppliers') || ($_GET['rctyp'] == 'Products')){ echo " Supplier Countries"; }else{ echo "Countries / States"; } ?> </span> </header><?php */?>
    <header><span class="h4">Supplier Countries </span> </header>
   <?php }
   while( $row11=mysql_fetch_array($run_sql, MYSQL_ASSOC))
		{
			//echo "<pre>" ; print_r($row11);  die;
		  if($_GET['rctyp'] == 'buy_lead'){ 
		$country_buy =  ucfirst(city_to_country(user_info($row11['br_u_id'],'bnsprof_city')));	
				$Couflag =mysql_query("SELECT * FROM `country` where cn_name ='$country_buy'");
				 while($rowk=mysql_fetch_array($Couflag))
				 { 
				//echo $Couflag; die;
				//echo "<pre>" ; print_r($country_buy);  die;
			 $counter++;
		if($counter== 5)
		{
			echo '<div class="collapse" id="countries">';
		}
		?>
		<div class="checkbox" style="text-align:left;">
		<label>
		<input type="checkbox" class="search_filter" <?php echo (in_array($rowk['cn_name'],$country_id))?"checked":'';?> name="country_id[]" value="<?php echo $rowk['cn_name']; ?>"> 
		<?php  echo "<img src='images/country_flag/".$rowk['cn_flag']."' height='22' width='22'>"; ?> <span><?php echo $rowk['cn_name']; ?></span> </label>
		</div>
				<?php
				 if($counter== 5)
		{
		echo '</div>';
		}
		}//while
		  }
		 if($_GET['rctyp'] == 'tender'){ 
		$country_buy =  ucfirst(city_to_country(user_info($row11['tnd_usr_id'],'bnsprof_city')));	
		//echo "<br>"."--------------".$country_buy;
				$Couflag =mysql_query("SELECT * FROM `country` where cn_name ='$country_buy'");
				 while($rowk=mysql_fetch_array($Couflag))
				  { 
				//echo $Couflag; die;
				//echo "<pre>" ; print_r($country_buy);  die;
			 $counter++;
                if($counter== 5)
                {
                   echo '<div class="collapse" id="countries">';
                }
  ?>
              <div class="checkbox" style="text-align:left;">
                <label>
                    <input type="checkbox" class="search_filter" <?php echo (in_array($rowk['cn_name'],$country_id))?"checked":'';?> name="country_id[]" value="<?php echo $rowk['cn_name']; ?>"> 
                  <?php  echo "<img src='images/country_flag/".$rowk['cn_flag']."' height='22' width='22'>"; ?> <span><?php echo $rowk['cn_name']; ?></span> </label>
              </div>
                            <?php
                             if($counter== 5)
                {
                   echo '</div>';
                }
	  }//while
							  }//temder
							$data =  $userArrayRow_Result[$row11['pd_uid']];	
							$arra_country[]=$data['country'];	
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
                if($counter== 5)
                {
                   echo '<div class="collapse" id="countries">';
                }
				//echo $count_country;
					  ?>
              <div class="checkbox" style="text-align:left;">
           <label>
          <input type="checkbox" class="search_filter" <?php echo (in_array($getCountryName['cn_name'],$country_id))?"checked":'';?> name="country_id[]" value="<?php echo $getCountryName['cn_id']; ?>">
                  <?php  if($getCountryName['cn_flag'] !=''){
				  echo "<img src='images/country_flag/".$getCountryName['cn_flag']."' height='22' width='22'>"; ?> <span><?php echo $getCountryName['cn_name']; ?></span> 
                  <?php } ?>
                  </label>
                  </div>
                            <?php
							//if($counter==$count_country)
               // if($counter>= 5)
			   if($count_country>=5)
			   {
			   if($counter==$count_country)
                {
                  echo '</div>';
                }
			   }
                }		 
                 if($count_country>= 5)
                {
                             ?>
              <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#countries" aria-expanded="false" aria-controls="collapseExample"> + View More </a> </div>
               <?php } ?>
			   <?php if($_GET['rctyp'] != 'tender' && $_GET['rctyp'] != 'buy_lead'){  ?>
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
				if($_GET['keywords']){ $keywordsval = $_GET['keywords'];}
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
              <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#states" aria-expanded="false" aria-controls="collapseExample"> + sView More </a> </div>
               <?php } //echo '</section>';} ?>
			   <?php if($_GET['rctyp'] != 'tender' && $_GET['rctyp'] != 'buy_lead'){  ?>
              <div class="form-group text-center">
                <!--<button type="button" class="btn btn-sm btn-warning border-radius-0" style="padding:0 3px; margin-right:10px;">Confirm</button> -->
				<input id="btnSearch" value="Confirm" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit" style="float: left; margin-left: 6px; margin-top: 10px;">
                <!--<button type="reset" class="btn btn-sm btn-link border-radius-0 txt-bold txt-black"  style="padding:0 3px;">Cancel</button>-->
				<a href="javascript: window.history.go(-1)" class="cancelBtns" style="float: left; margin-bottom: 15px; margin-left: 4px; margin-top: 7px; width: 34px; font-weight:bold;">Cancel</a>
              </div>
			  <?php } ?>
            </form>
			 <?php }  ?>
		</section>   