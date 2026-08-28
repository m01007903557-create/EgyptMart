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
</style>


    <!---------------------------------------------------->



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



    ?>




    <?php


    if(isset($pc_id)&&$pc_id!="")
    {

    ?>

<style>
.rht_related_cat a {
	font-weight: 600;
}
</style>
<?php  ?>
    <div class="rht_related_cat" >
        <div class="">
            <header style="margin-top: 20px;"> <span class="h4" > Related Categories </span> </header><!--h3 style="font-size: 20px;">Related Categories</h3-->
            <section>
                <ul style="list-style: none;">
                    <?php

                    if($_COOKIE['loc_id'])
                    {
                        $sql_pd_ck=" and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";

                    }
                    else
                    {
                        $sql_pd_ck=" and (
	
	(pd_preferred_buyer_location='any')
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
                       
                    }
                   


                    $sqls="SELECT * FROM `product_category_arabyos` WHERE md5(pc_id)='".$pc_id."' and pc_status='1'  ORDER BY `pc_id` DESC";
                    $ress=mysql_query($sqls);
                    $RelatedCatCount = mysql_num_rows($ress);
                    $rows=mysql_fetch_object($ress);
                    $catId = $rows->pc_id;
                    $catParentId = $rows->pc_parent_id;

                 

                    $iMainParentId="SELECT * FROM `product_category_arabyos` WHERE pc_parent_id = '".$rows->pc_parent_id."' and pc_status='1' ORDER BY `pc_name` asc";
                    $iMainParentIdqueryResult = mysql_query($iMainParentId);

                    $rr = 0;
                    $count =0;
                    $counter = 0;
                    $iiiiNew = 0;
                    $iAlphaCount= 0;
                    while( $Results = mysql_fetch_object($iMainParentIdqueryResult) ){


                        


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

							$counter++;// webxtor 28 June 18
								 if($counter== 8)
						    {
						        echo '<div class="collapse" id="categoriesRight">';
						    }

                            echo ' <li class="first00"> <a href="products.php?c='.md5($Results->pc_id).'">'.$Results->pc_name.'	</a> </li>';
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
                        
                        $getCountsCat = mysql_num_rows($catsearch);
                        
                        $counter = 0;
                        while($getMOrecatsearch = mysql_fetch_object($catsearch)){
                            $counter++;
                            if($counter== 8)
                            {
                                echo '<div class="collapse" id="categories">';
                            }
                            echo '<li class="second00"> <a href="products.php?c='.md5($Results->pc_id).'">'.$Results->pc_name.'	</a> </li>';
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
                    <?php if($counter>7) { ?>
                        <div class="text-right">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse"  data-target="#categoriesRight" aria-expanded="false" aria-controls="collapseExample"> + More</button>
                        </div><?php }?>


                </ul>

            </section>

        </div>
    </div>
        <?php }   ?>

       


<!-- common for slider -->

<link rel="stylesheet" type="text/css" href="css/slick.css">
<link rel="stylesheet" type="text/css" href="css/slick-theme.css">
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<?php include "css/custom.php"; ?>
<style>
    .slick-product-image > img {
    min-height: auto!important;
    max-height: 180px!important;
    border: 1px solid #E9E9E9!important;
}
.slick-product-wrapper {
	max-width: none!important;
    width: 90%;
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
    background: rgb(0 0 0);
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
<!-- end common for slider -->

<!-- related slider -->
<?php                           
if(isset($pc_id)&&$pc_id!="")
{
    

    $sqls="SELECT * FROM `product_category_arabyos` WHERE md5(pc_id)='".$pc_id."' and pc_status='1'  ORDER BY `pc_id` DESC";
    $ress=mysql_query($sqls);
    $RelatedCatCount = mysql_num_rows($ress);
    $rows=mysql_fetch_object($ress);
    $catId = $rows->pc_id;
    $catParentId = $rows->pc_parent_id;

 

    $iMainParentId="SELECT * FROM `product_category_arabyos` WHERE pc_parent_id = '".$rows->pc_parent_id."' and pc_status='1' ORDER BY `pc_name` asc";
    $iMainParentIdqueryResult = mysql_query($iMainParentId);

    $rr = 0;
    $count =0;
    $counter = 0;
    $iiiiNew = 0;
    $iAlphaCount= 0;
    $rel_id = array();
    while( $Results = mysql_fetch_object($iMainParentIdqueryResult) ){
        $rel_id[] = $Results->pc_id;
    }
    $rel_id= implode(',',$rel_id); 
    $get_sub_cat = "SELECT distinct pc_id FROM product_category_arabyos WHERE pc_parent_id IN ($rel_id) GROUP BY pc_parent_id";
    $subcat_array = mysql_query($get_sub_cat);
    $rel_id = array();
    while( $Results = mysql_fetch_object($subcat_array) ){
        $rel_id[] = $Results->pc_id;
    }
    $rel_id= implode(',',$rel_id); 

                                if ($_COOKIE['loc_id'] != "") {
                                    $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_subcat_id IN ($rel_id) and pd_image!=''" . $sql_pd_ck . " order by rand()";
                                } else {
                                    $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_subcat_id IN ($rel_id) and pd_image!=''" . $sql_pd_ck . " order by rand()";
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
            <p style="font-size: 17px; text-align: center; margin-top: 20px;margin-bottom: 10px;"><b>Leader Suppliers</b></p>
               <div class="demobox" >
                  <div class="wrapper-container">
                     <div class="white_bg">
                        <div class="welcome_desc">
                           <div class="course_demo">
                              <ul id="EgyptMART-relatedCat">
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
<!-- end related slider -->
<!-- commonslider -->
<?php
                                /*        if ($_COOKIE['loc_id'] != "") {
                                            $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'  and pd_pck_dets = 1  and pd_image!=''" . $sql_pd_ck . " order by rand()";
                                        } else {
                                            $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'  and pd_pck_dets = 1  and pd_image!=''" . $sql_pd_ck . " order by rand()";
                                        }
										
                                 
                                 $rsleading = mysqli_query($con,$sqlleading);
                                 $totalbaneer = mysqli_num_rows($rsleading);
                                 $rembaner = $totalbaneer%2;
                                 if($totalbaneer> 0)
                                 {
                                  ?>
                
               <div class="demobox">
                  <div class="wrapper-container">
                     <div class="white_bg">
                        <div class="welcome_desc">
                           <div class="course_demo">
                              <ul id="EgyptMART-product">
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
                                      
                                  <a class="slick-product-wrapper" href="company/products.php?c=<?php echo rand(1000, 9999)  . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $rowleading->pd_subcat_id; ?>#<?php echo $rowleading->pd_id; ?>" target=_blank>
                                    <div class="demobox">
                                        <div class="slick-product-image">
                                       <img alt="" src="upload/myproduct/thumb/<?php echo $rowleading->pd_image; ?>" class="black" style="max-width:115px" title="<?php echo ucwords($rowleading->pd_title); ?>">
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
                                <?php } */ ?>
<!-- end common slider -->
                <script>
                $('#EgyptMART-product,#EgyptMART-relatedCat').slick({
                    nextArrow: '<div class="arrow_sli"><img src="/assets/img/botom.png" class="top-arrow" aria-label="Previous" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></div>',
                    prevArrow: '<div class="arrow_sli"><img src="/assets/img/top.png" class="bottom-arrow" aria-label="Next" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></button></div>',
                    centerMode: true,
                    centerPadding: '10px',
                    slidesToShow: 5,
                    autoplay: true,
                    vertical: true,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                centerMode: true,
                                centerPadding: '10px',
                                slidesToShow: 5
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                centerMode: true,
                                centerPadding: '10px',
                                slidesToShow: 5
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                centerMode: true,
                                centerPadding: '10px',
                                slidesToShow: 5
                            }
                        }
                    ]
                });
            </script>
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
