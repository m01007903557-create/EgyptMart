	       		<script type="text/javascript" src="https://arabyos.com/company/js/jquery-1.9.1.min.js"></script>
<link href="https://arabyos.com/company/css/colorbox.css" type="text/css" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>
<style>
.bt {
    background-position: 0 -240px;
    margin: 18px 0 30px 0;
    clear: left;
    height: 0px !Important;
    background-color: transparent;
    color: transparent;
	display: none;
}
.middle-control-part {
    padding: 10px 0;
    background: #fff;
    width: 100%;
}
button.btn.btn-default.border-radius-0.txt-bold.bold-xs.btn-white.text-capitalize {
    border-radius: 0px;
    background: #fff !important;
    color: #000;
    padding: 10px 23px;
}
.search-show-box-buyleads #final_result
{

}
</style>

<?php
ob_start();
session_start();
include "../common.php";

$minorder=$_POST['minorder'];



$page = 1;
$pc_id=$_POST['id'];

$cur_page = $page;
$page -= 1;
$per_page = 40; // Per page records
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
	
	$sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name from product_category m,product_category c where m.pc_id=c.pc_parent_id and md5(c.pc_id)='".$pc_id."'";
	$res_pcat=mysql_query($sql_pcat);
	$row_pcat=mysql_fetch_array($res_pcat);


?>

<?php

$sql_check1="select pc_id from product_category where  md5(pc_parent_id)='".$pc_id."'";
$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
     	while($data=mysql_fetch_array($res_check1)){
		        $pc_id_arr[]=$data['pc_id'];		
	}
	$ids = join("','",$pc_id_arr); 

  $sql_prd="select * from products,measurement_unit,country, business_profile, plan_member_id, smembership_plan
 where mu_id=pd_unit and pd_min_order_qty<=$minorder and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1'  ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > ". time() ." and pd_subcat_id in('$ids') ORDER BY FIELD(p_id,'5','4','3','15') LIMIT ".$start.", ".$per_page;
  //echo $sql_prd;

$recObj=mysql_query($sql_prd);

/* -----Total count--- */
 $query_pag_num = "SELECT count(*) AS count from products,measurement_unit,country, business_profile, plan_member_id where mu_id=pd_unit and pd_currency=cn_id ".$sql_pd_ck." and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1' and pd_image!='' and plan_member_id.expiry_date > ". time() ."  and pd_subcat_id in('$ids')"; // Total records

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
<div class="col-md-3 col-sm-3 col-xs-12 col-lg-1 prc-left-side" style="display:none;">
 
    <h3 style="font-size: 20px;cursor: pointer" onclick="toggle_menu();"><span class="fa fa-list"></span>&nbsp; MY MARKETS</h3>
    <div style="padding-left:10px;padding-right:10px;" class="left-side-bar-sale-offer">

       <!-- <div style="border-bottom:1px solid #999;margin-bottom:2px;" onclick="toggle_menu();"><img src="../css/img/my-market.png" style="width: 100%;max-width: 200px"></div>-->
   
		<link rel="stylesheet" href="../css/menu_styles.css" type="text/css" />
        <div id='cssmenu' class="" style="float:left;width:200px;display: none">
            <ul>

                <?php
                $sql_dd_mnu="select pc_id,pc_name,pc_image from product_category where pc_parent_id = '0' and pc_status = '1' ".$sql_order;
                $res_dd_mnu=mysqli_query($con, $sql_dd_mnu);
                while($row_dd_mnu=mysqli_fetch_object($res_dd_mnu))
                {
                    ?>
                    <li class='has-sub'><a href="category.php?token=<?php echo rand(10,9999).md5($row_dd_mnu->pc_id); ?>"><span><?php echo $row_dd_mnu->pc_name;?></span></a>
                            <?php
                            $sql_dd_cmnu="select pc_id,pc_sort_name from product_category where pc_parent_id = '".$row_dd_mnu->pc_id."' and pc_status = '1' ".$sql_order;
                            $res_dd_cmnu=mysqli_query($con, $sql_dd_cmnu);
                            while($row_dd_cmnu=mysqli_fetch_object($res_dd_cmnu))
                            {
                                $row_scnt=mysqli_fetch_object(mysqli_query($con, "select count(*) as cnt from product_category where pc_parent_id = '".$row_dd_cmnu->pc_id."' and pc_status = '1'"));
                                ?>
                                <li><a href="products.php?c=<?php echo md5($row_dd_cmnu->pc_id); ?>"><span><?php echo ucwords($row_dd_cmnu->pc_sort_name); ?></span></a>
                                </li>
                            <?php	}
                            ?>
                        </ul>
                    </li>
                <?php } ?>

                <li><a href="dir.php"> View All Categories </a></li>
            </ul>
        </div>

        <?php
        $sql_subcat="select p.pc_id,p.pc_sort_name,(select count(*) from products,measurement_unit,country, business_profile, plan_member_id where mu_id=pd_unit and  business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' and pd_subcat_id=p.pc_id and plan_member_id.expiry_date > ". time() .") as tot_prod from product_category p where md5(p.pc_parent_id)='".$pc_id."' ";


        $res_subcat=mysql_query($sql_subcat);
        while($row_subcat=mysql_fetch_object($res_subcat))
        {
            if($row_subcat->tot_prod){
                ?>
                <div class="item-list">
                    <a style="cursor:pointer;" onClick="refineProductBySubCategory(1,'<?php echo md5($row_subcat->pc_id); ?>');"><?php echo ucwords($row_subcat->pc_sort_name)." (".($row_subcat->tot_prod).")"; ?></a>
                </div>
            <?php	} }
        ?>
    </div>
</div>
    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 prc-right-side" >

        <div class="col-md-9 col-sm-9 col-xs-12 col-lg-9" id="final_result">
            <div class="middle-part">
                <button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize " style="border-top:2px solid #ff7519 !important;border:0px;">Products</button>
                <button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize " disabled><a href="https://arabyos.com/catcompany.php"style="color:#000;">Suppliers</a></button>
            </div>
            <div class="middle-control-part row" style="margin-left:0px;">
                <div class="col-md-2 col-xs-12">
                    <div class="dropdown sup-country-list">
                        <span class=" dropdown-toggle"  id="menu1" data-toggle="dropdown">Supplier Countries</span>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                            <?php
                            if(isset($_COOKIE['loc_id'])){
                                $view_country = "select pd_uid from products,measurement_unit,country,business_profile,user,plan_member_id where usr_id = pd_uid AND bnsprof_uid = pd_uid AND mu_id=pd_unit AND (bnsprof_compname LIKE '%".$keywords."%') and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'any' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'my_city' AND user.country =  '".$_COOKIE['loc_id']."')) AND plan_member_id.expiry_date > " . time() . " and pd_status='1' and pd_image!='' group by pd_currency";
                            }else{
                                $view_country = "select pd_uid from products,measurement_unit,country,business_profile,user,plan_member_id where usr_id = pd_uid AND bnsprof_uid = pd_uid AND mu_id=pd_unit AND (bnsprof_compname LIKE '%".$keywords."%') and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic') OR (pd_preferred_buyer_location =  'any') OR (pd_preferred_buyer_location =  'my_city')) AND plan_member_id.expiry_date > " . time() . " and pd_status='1' and pd_image!='' group by pd_currency";
                            }
                            $run_sql= mysql_query($view_country, $link);
                            $country_buy=array();

                            while( $row11=mysql_fetch_array($run_sql, MYSQL_ASSOC)) {

                                $country_buy[] = ucfirst(city_to_country(user_info($row11['pd_uid'], 'bnsprof_city')));
                            }
                            $country_buy = array_unique($country_buy,SORT_STRING);
                            if (count($country_buy) > 0) {

                                    foreach ($country_buy as $cb) {
                                        $Couflag = mysql_query("SELECT * FROM `country` where cn_name ='$cb'");
                                        while ($rowk = mysql_fetch_array($Couflag)) { ?>
                                            <li><?php echo "<img src='images/country_flag/" . $rowk['cn_flag'] . "' height='22' width='22'>"; ?>
                                                <span><?php echo $rowk['cn_name']; ?></span></li>

                                        <?php }
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-10 col-xs-12">
				
                    <?php
                    $membership_type = array();
                    if(isset($_SESSION['membership_type'])) {
                        $membership_type = $_SESSION['membership_type'] ;
                    }

                    $mtqury = "select sp.mp_id, sip.mst_icon, sp.mst_name from smembership_plan sp join smembership_icon_plan sip ON sp.mp_id = sip.mp_id where sp.mp_status != '0'";
                    $mtresult = mysql_query($mtqury);
                    $plan_array_icons = array();

                    while($mtrow = mysql_fetch_array($mtresult)){
                        $plan_array_icons[$mtrow['mp_id']] =    $mtrow['mst_icon'];
                        ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="search_filter" <?php echo (in_array($mtrow['mp_id'],$_POST['mst_type']))?"checked":'';?> name="mst_type[]" value="<?php echo $mtrow['mp_id']; ?>"> <img src="admin/images/<?php echo $mtrow['mst_icon']; ?>" width="20px" height="20px;" style="margin-right:5px;"/> <span class="txt-gray" class="text-uppercase"><?php echo $mtrow['mst_name']; ?></span>
                        </label>
                        <?php }?>
                    <button class="btn btn-xs btn-default border-radius-0 " style="padding:0 5px 0 5px">OK</button>
                    <label for="min_order" class="checkbox-inline">Min Order : <input id="min_order" type="text" value="<?php echo $minorder;?>" style="width: 50px"/></label>
                    <button class="btn btn-xs btn-default border-radius-0 " id="minorder" style="padding:0 5px 0 5px">OK</button>
                </div>
				
				<div class="col-sm-12"style="margin-left: -10px;margin-top:8px;">
				<label class="checkbox-inline">Supplier Location:</label>
				<select id="showcnt">
				<option>All countries & Regions</option>
				
				<select>
				<option>All countries & Regions</option>
				</select>
				</div>
            </div>
            <!--<div style="border-radius:5px;padding-left:10px;">
                <h2 style="color:#36006C"><?php /*echo ucwords($row_pcat[3]); */?></h2>
            </div>-->
	
            <div class="als-container" id="product_slider" style="border-radius:5px;">
			<div class="countries">
			<div class="countries_inner">
	<?php 
	
	$country="SELECT DISTINCT user.country, country.cn_name FROM `country` INNER JOIN `user` ON user.country = country.cn_id";
	$cc=mysql_query($country);
	while($data1=mysql_fetch_array($cc)){
		?>
		<span class="outer_c"><input type="checkbox" value="<?php echo $data1['country'];?>"><span>
		<?php
		echo $data1['cn_name'];?></span><i class="fa fa-angle-down cnt_state"style="font-size: 15px;margin-left: 5px;cursor: pointer;"></i></span>
		<?php
	}
	
	?>
	</div>
	<div class="state_section"></div>
	</div>
                <!--<span class="als-prev"><img src="images/thin_left_arrow_333.png" alt="prev" title="previous" /></span>-->
				
                <div class="als-viewport" align="center">

                    <ul class="als-wrapper">
                        <?php
                        if(mysql_num_rows($recObj)>0)
                        {
                            ?>

                            <?php
						
                            while($row_prd=mysql_fetch_object($recObj))
                            {
								?>

<?php
                                $newtext = substr($row_prd->pd_title, 0, "12");

                                //echo "select bnsprof_id,bnsprof_city,bnsprof_state from business_profile where bnsprof_uid='".$row_prd->pd_uid."' limit 1";

                                ?>
                                <li class="als-item" style="border:1px solid #ccc;margin-top:1%;margin-left:1%;padding:4px !important;margin-bottom:1%;border-radius:4px; float:left; height:auto; background-color:rgba(251, 251, 251, 0.96);">
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
									<?php $row_contact=mysql_fetch_object(mysql_query("select mobile1,country_ph_code,usr_id from user where usr_id='".$row_prd->pd_uid."' limit 1"));?>
                                    <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_prd->bnsprof_id); ?>&sc=<?php echo rand(10000,99999).$row_prd->pd_subcat_id; ?>#<?php echo $row_prd->pd_id; ?>" style="text-decoration:none;color:#000" target="_blank">
                                        <img src="upload/myproduct/thumb/<?php echo $row_prd->pd_image; ?>" alt="<?php echo ucwords(substr($row_prd->pd_title,0,28)); ?>" title="<?php echo ucwords($row_prd->pd_title); ?>" />
                                        <div style="height:0%;margin-top:3%;padding-top:5%;"><span class="utext" style="color:#005ce6;font-size:16px !important;"><b><?php echo
                                                    $newtext; ?></span></b><br />
                                            <span style="color:red;font-size:13px !important;font-weight:bold;"><?php echo $cn_name; ?></span></div>
                                        <hr />
                                        <hr />
                                        <div style="height:10%;margin-top:5%; font-size:13px;">MOQ : <span style="color:red; font-weight: 600; font-size:15px !important;"><?php echo $row_prd->pd_min_order_qty; ?>&nbsp;</span><?php echo $row_prd->mu_name; ?></div>
                                        <div style="height:10%;margin-top:1%; font-size:13px;"><?php echo $row_prd->cn_currency; ?>&nbsp;<span style="color:red; font-weight: 600; font-size:15px !important;"><?php echo $row_prd->pd_fob_price ?></span>/<?php echo $row_prd->mu_name; ?></div>
                                        <div style="font-weight:bold;font-size: 13px;padding: 4px 0;"><?php echo $row_prd->mst_name; ?> </div>
                                    </a>
						<p class="cnt-phone"><span class="cnt-phone-inner"><img src="images/mobile.png" width="25px">&nbsp;&nbsp;<a href="tel:+<?php echo $row_contact->country_ph_code;?>-<?php echo $row_contact->mobile1;?>"><?php echo $row_contact->country_ph_code;?>-<?php echo $row_contact->mobile1;?></a></p>

									<p class="cnt_supplier"><span class="cnt_supplier_inner"><i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;&nbsp;<a href="ajax-file/quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row_prd->bnsprof_id); ?>&pid=<?php echo $row_prd->pd_id; ?>&c=<?php echo $row_contact->usr_id; ?>&vform=1" id="btn_ajax<?php echo $row_prd->pd_id; ?>" rel="product-send-inquiry" class="inquiry_but" style="color:#fff;"`>Contact Supplier</a></span></p>
 
<div class="link pt10px">	
    									                                                        <script>
                                                        $(document).ready(function() {
                                                          var uid_ind='<?php echo $_SESSION['uid_indm']; ?>';
                                           $("#btn_ajax"+<?php echo $row_prd->pd_id; ?>).click(function(event){              

if(uid_ind==''){
window.location.href="https://www.arabyos.com/sign-in.php";
}else{
	event.preventDefault();
                                                            $("#btn_ajax" +<?php echo $row_prd->pd_id; ?>).colorbox({width: "62%", height: "89%"}); } });
                                                        });
	 
                                                  
                                                        </script>

								</div>
								</li>

                            <?php }	?>
			

                                                       
                                                    

                            <?php
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
                            <a href="javascript:loadProductByCategory('1','<?php echo $pc_id; ?>')"><img id="firstmail" src="images/firsten.gif"></a>
                        <?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
                        ?>&nbsp;<?php

                        // FOR ENABLING THE PREVIOUS BUTTON
                        if ($previous_btn && $cur_page > 1){
                            $pre = $cur_page - 1;
                            ?><a href="javascript:loadProductByCategory('<?php echo $pre; ?>','<?php echo $pc_id; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php
                        }else if($previous_btn){
                            ?><img id="prevmail" src="images/prevmail.gif"><?php	}
                        ?>&nbsp;<?php

                        // TO ENABLE THE NEXT BUTTON
                        if($next_btn && $cur_page < $no_of_paginations){
                            $nex = $cur_page + 1;
                            ?><a href="javascript:loadProductByCategory('<?php echo $nex; ?>','<?php echo $pc_id; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                        }else if ($next_btn){
                            ?><img id="nextmail" src="images/nextmail.gif"><?php } ?>&nbsp;<?php
                        // TO ENABLE THE END BUTTON

                        if ($last_btn && $cur_page < $no_of_paginations) {
                            ?><a href="javascript:loadProductByCategory('<?php echo $no_of_paginations; ?>','<?php echo $pc_id; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
                        <?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
                        ?>
                    </p>
                <?php } ?>
                <!--<span class="als-next"><img src="images/thin_right_arrow_333.png" alt="next" title="next" /></span>-->
            </div>
        </div>
        <div class="col-md-3 col-sm-12 col-xs-12 col-lg-3" style="background:#fff;">

            <?php    include_once '../index_rightsidebar_back.php';       ?>
        </div>
    </div>
	
<!--1px solid #484891;-->


