<?php
ob_start();
session_start();

//print_r($_SESSION);
//$uid=$_SESSION['uid_indm'];

include 'common.php';

if(isset($_GET["popup"])&&$_GET["popup"]=='close')
{
$_SESSION["popup"]=2;
}


$_SESSION['last_page']="my-dashboard.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
    header("Location:sign-in.php"); 
}
//$uid=$_SESSION['uid_indm'];


?>
 <!-------------------------------popup--------------------------------->
<?php /*?><?php 
if(!isset($_SESSION["popup"])){
?><?php */?>
<body>
<?php 
$myvar;
if(isset($_SESSION["popup"])){
    $myvar=$_SESSION["popup"];
    }
if($myvar == 1 || $myvar==""){
?>
<div class="loader_img" style="display: none">
   <div class="popup-box">
   <div id="nlogin">
<?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){    
        $uid=$_SESSION['uid_indm'];
    ?>
    <?php /*?>
<p class="bp13 m12 j1">Welcome: <span class="cr6"><?php echo user_info($uid,'name_prefix')."&nbsp;".user_info($uid,'fname');?></span><br><span><b class="bo1"title=" لوحة مفاتيح - أعمال المنصة  ">Go to</b><a href="my-dashboard.php" style="text-decoration:none"> My Dashboard</a></span></p>
<?php }else{ ?>
<p class="bp13 m12 j1">Welcome to <?php echo getWebSiteName(); ?><br><span><b class="bo1">New User? &nbsp; </b><a href="create_account.php">Join Now!</a></span></p>
<?php } ?><?php */?>
</div>
<div class="backg">
<a href="http://arabyos.com/my-dashboard.php?popup=close"><img class="close" src="close.png"/></a>
<?php /*if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){  
        $uid=$_SESSION['uid_indm'];
    */ ?>
<p class="bp13 m12 j1">Welcome: <span style=" background:none; padding-left:0;"><?php echo user_info($uid,'name_prefix')."&nbsp;".user_info($uid,'fname');?></span><br><span><b class="bo1"title=" لوحة مفاتيح - أعمال المنصة  ">Go to</b><a href="http://arabyos.com/my-dashboard.php?popup=close" style="text-decoration:none"> My Dashboard</a></span></p>
<?php }else{ ?>
<p class="bp13 m12 j1">Welcome to <?php echo getWebSiteName(); ?><br><span><b class="bo1">New User? &nbsp; </b><a href="create_account.php">Join Now!</a></span></p>
<?php } ?>

<div class="tab-main">
                    <div class="wrapper">
                    <ul class="top_btn_list">
                    <li>
                    <input id="radio1" class="radio_btn" name="tab" type="radio" checked="checked">
                    <label for="radio1" class="label_btn"title=" لفرص الشراء – للمشتريين  ">For Buyers</label>
                    <div id="cont1" class="content_tab"title=" مستورد –  أو شركة شراء –  أو  تاجر  -  أو بيت شراء متخصص ">
                    <img class="heading" src="head.png"/>
                    <div class="text-box">
                        <div class="text-box-left">
                            <img src="1.png"/>
                        </div>
                        <div class="text-box-right">
                            <h2><a href="post-buy-req.php"title=" إرسل لنا - طلبات شراء -  وتلقى تسعيرات وأفضل عروض البيع -  من شركات بيع مؤهلة ولها وجود حفيفى  ">Send Us Your Requirements</a></h2>
                            <p>Receive responses & quotes from pre-verified and qualified suppliers.</p>
                        </div>
                    </div>
                    <div class="text-box">
                        <div class="text-box-left">
                            <img src="2.png"/>
                        </div>
                        <div class="text-box-right">
                            <h2><a href="#" style="cursor:text"title=" إبحث وإكتشف –  ألاف من فرص البيع -  لمنتجات وخدمات تجارية لأعمالك  وتجارتك ">Find Anything For Your Business Instantly.</a></h2>
                            <p>Send inquires directly to suppliers 
of  your choice.</p>
                            <form method="GET" name="searchForm2" action="search.php" onSubmit="return validsearch_r();">
                                <input size="24" class="m1 bl6" id="keywords_r" name="keywords" type="text"title="- إبحث وإكتشف –  ألاف من فرص البيع ">
                                <input type="hidden" name="rctyp" value="Products" />
                               <input value="Search" class="m1 fz1 ff1 m5" type="submit"title="- إبحث وإكتشف –  ألاف من فرص البيع ">
                            </form>
                        </div>
                    </div>
                    <div class="text-box">
                        <div class="text-box-left">
                            <img src="3.png"/>
                        </div>
                        <div class="text-box-right">
                            <h2><a href="manage-selloffer-alert.php"title=" سجل - أهم الأصناف - التى تود شراؤها  -  لتتلقى إشعارات –  عروض بيع خاصه  لها –  فى بريدك وعلى الموبايل ">Manage Sell Offer Alerts</a></h2>
                            <p>Get updates on relevant products and sell offer alerts</p>
                        </div>
                    </div>
                   
                    <div>
                    </div>
                    </div>
                    </li>
                    <li>
                    <input id="radio2" class="radio_btn" name="tab" type="radio">
                    <label for="radio2" class="label_btn"title=" للموردين - شركات -  ومصانع - وخدمات تجارية ">For Suppliers</label>
                    <div id="cont2" class="content_tab"title=" للمصنعين  –  والمصدرين  –  وتجار الجملة  –  وتجار التجزئة  –  ومقدمو الخدمات التجارية  ">
                    <img class="heading" src="head2.png"/>
                    <div class="text-box">
                        <div class="text-box-left">
                            <img src="1.png"/>
                        </div>
                        <div class="text-box-right">
                            <h2><a href="create-free-website.php"title="إذا كنت -  من رواد الصناعة أو التجارة -  فى مجالك  -  فم بإدخال -  حتى 30 منتج / خدمة –  فى أكبر منصة أعمال - للبيع والشراء - بين الشركات -  فى المنطفة العربية   /   إنضم –  لأهم 10,0000 - بائع –  ومشترى -  عربى وعالمى ">Join Top 10,000 Arabian Suppliers & Buyers!</a></h2>
<p>List up to 30 products / services FREE as a Leader supplier @ most powerful B2B platform in Arab world !</p>
                        </div>
                    </div>
                    <div class="text-box">
                        <div class="text-box-left">
                            <img src="2.png"/>
                        </div>
                        <div class="text-box-right">
                            <h2><a href="my-selloffer-locationpref.php"title=" إنشىء صفحات - أعمالك التجارية –  موقعك المصغر / متجرك  – الذى يحتوى على أهم منتجاتك وخدماتك التجارية – فى سوق مصر على الانترنت بين أهم تجار ومصانع مصر والمنطقة  ">Create Your B2B2C Business WebSite !</a></h2>
                            <p>Display your business in Dynamic Mini- B2B WebSite. Start domestic / abroad sales growth !</p>
                        </div>
                    </div>
                    <div class="text-box">
                        <div class="text-box-left">
                            <img src="3.png"/>
                        </div>
                        <div class="text-box-right"title=" سجل - أهم الأصناف - التى تقوم ببيعها -  لتتلقى إشعارات –  طلبات شراء جاهزة –  فى بريدك - وعلى الموبايل ">
                            <h2><a href="manage-buylead-alert.php">Manage Buy Lead Alerts !</a></h2>
                            <p>Get relevant alerts of buyers requirements directly via your mobile & mailbox.</p>
                        </div>
                    </div>
                    <div>
                    </div>
                    </div>
                    </li>
 
                    </ul>
                    </div>
                    </div>
</div>
</div>
   
   
   </div><?php }  ?>
   <script type="text/javascript">
    $(document).ready(function(){
        $('.loader_img').fadeIn('slow')
    })
      $(window).load(function() {
      $(".loader_img").fadeOut("slow");
      $.post("/UpdateTheSession.php", {"id": "2"});
      });
   </script>
 
   <!-------------------------------popup--------------------------------->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo getSiteTitle(); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo getSiteTitle(); ?>">
    <meta name="keywords" content="<?php echo get_page_settings(2); ?>">
    <meta name="description" content="<?php echo get_page_settings(3); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/style.css" type="text/css" rel="stylesheet">
<link href="css/style123.css" type="text/css" rel="stylesheet">
 <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/pdash-v-1.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        
    <?php include "includes/header_new.php"; ?>

<!--        <div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>-->
        <br><br>

        

        <!--feedback widget:ends-->
        
<div class="container my_acc_wrapper">
    <div class="row" style="margin:0!important;">
        <div class="col-md-12">
           
           <?php include 'includes/header_menu.php';?>
            <?php
$sql="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$uid."' and status = '1'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
?>
            <!--myzone drop elements:starts-->
        <!--company profile-->    

<?php

  //print_r($row);
  $company_name = $row->usr_id;
  $billing_sql = "SELECT * from billing_history WHERE bh_usr_id = '".$company_name."' ORDER BY bh_updated_date DESC LIMIT 1";
  $billing_query = mysql_query($billing_sql) or die(mysql_error()); 
  $billing_detail = mysql_fetch_object($billing_query);
   
  //print_r($billing_detail);
  //$sql = "SELECT * from plan_member_id pm, smembership_plan mp WHERE pm.b_id = '".$row->usr_id."' AND pm.p_id=mp.mp_id ORDER BY pm.expiry_date DESC LIMIT 1";
  //$sql = "SELECT * from user u, smembership_plan mp WHERE u.usr_id = '".$row->usr_id."' AND u.usr_mp_id=mp.mp_id LIMIT 1";
  $sql = "SELECT * from plan_member_id pm, business_profile b, smembership_plan mp WHERE b.bnsprof_uid = '".$row->usr_id."'AND pm.b_id = b.bnsprof_id AND pm.p_id=mp.mp_id ORDER BY pm.expiry_date DESC LIMIT 1";
  $query = mysql_query($sql) or die(mysql_error()); 
  $plan_detail = mysql_fetch_object($query);
  //print_r($plan_detail);
?>
<div class="f1 nd2 cnfl" style="width:37.4%">
    <h2 style="border-bottom:1px solid #D2D2D2;bold;">Your Membership Plan Status</h2>
    <!--address:start-->
    <div class="p5 lh cfc">

        <div class="oi oinpr" style=" padding-bottom:80px;">
        <style>
        .oinpr li { padding-right:0px; }
        </style>

        <div class="fl " style="width:62%">
        <ul>
            <li class="fw f1">Date</li><!-- lem-->
            <li class="fw">:</li>
            <li><?php echo date('d-M-Y H:i T');?></li>
        </ul>
        <ul>
            <li class="fw f1">Membership Plan</li>
            <li class="fw">:</li>
            <li><?php echo ($plan_detail->expiry_date != '' && date('d F Y',$plan_detail->expiry_date) == '09 September 9999')?'PROMO Plan':$plan_detail->mst_name; ?></li>
        </ul>
        <ul>
            <li class="fw f1">Starting Period Time</li>
            <li class="fw">:</li>
            <li><?php echo $plan_detail->start_date ? date('Y-m-d', $plan_detail->start_date) : 'N/A';?></li>
        </ul>
        <ul>
            <li class="fw f1">Subscription End</li>
            <li class="fw">:</li>
            <li><?php echo ($plan_detail->expiry_date != '' && date('d F Y',$plan_detail->expiry_date) == '09 September 9999')?'PROMO Plan':($plan_detail->expiry_date ? date('Y-m-d', $plan_detail->expiry_date) : 'N/A');?></li>
        </ul>
        </div>
        <div class="fl " style="text-align:right; width:38%">
        <ul>
            <li class="fw f1">Annual Amount</li>
            <li class="fw">:</li>
            <li><?php echo number_format($plan_detail->mp_amount, 2);?></li>
        </ul>
        <ul>
            <li class="fw f1">Paid Amount</li>
            <li class="fw">:</li>
            <li><?php echo number_format($billing_detail->bh_amount, 2);?><br /><br /><a href="membership_plans.php" class="prur fw mt3" style="color:white;"title="شاهد الخطط التسويقية والحلول التى تقدمها المنصة لأعضائها">Upgrade</a></li>
        </ul>
        
        </div>
    </div>
                            
    
    </div><!--address:end--></div>


    
        <!--Enquiries & Contacts-->
<div class="f1 nd2 ebh3"><!-- col-md-8 col-sm-12 col-xs-12 cuscol -->





        <div class="f1 nd1"><!--dw-->
            <!--personalized URL condition 1:start--> 
            <div class="dap p8 dem mt12 demw c3 ml7"> 
            <?php
                $cid=rand(1000,9999).md5($row->bnsprof_id);
            ?>
            <p class="lh f1 fw yf" title="  شاهد - موقع شركتك المصغر - لأعمالك ومنتجاتك وخدماتك التجارية"><?php
            if($row->user_type > 1){
            if($row->bnsprof_compname !=''){
            ?>
<li><a href="company/index.php?c=<?php echo $cid; ?>" class="txt-yellow" style=" color:#03bf00;font-weight:700;"title="موقع شركتى - المصغر">My B2B Website</a></li>

           Your <span class="fw">Business Page @ <?php echo getWebSiteName(); ?></span> is here to PREVIEW at:

 
<br> <a href="company/index.php?c=<?php echo $cid; ?>" class="fwl txt-blue" style="line-height: 20px;" target="_blank"><?php echo $_SERVER['HTTP_HOST']; ?>/company/index.php?c=<?php echo $cid; ?></a>
            <?php }else{    ?>
            <a href="create-your-website.php">Click here</a> to Create your <span class="fw">Business Page @ <?php echo getWebSiteName(); ?></span>.
            <?php } }?>
            </p> <br><br>
             <div class="c3"></div> 
             </div>
             
             <div class="c3 mt5"></div> <!--personalized URL condition 1:ends-->
            
        </div>
</div>
    </div>
    <!--latest buy leads:start-->
    <div class="clearfix"></div>
   
   
    
    
  <div class="col-md-10 my_acc_main">
        <div class="f1 nd2 cnfl">
    <h2 style="border-bottom:1px solid #D2D2D2;">My Contact Details</h2>
    <!--address:start-->
    <div class="p5 lh cfc">
    <div>
        <a href="my-contactdetails.php" class="f2 f11 ded" style="margin-left:5px"><span class="dbg bnr">Edit</span></a>
        <!--<div class="f11 pbar tr f2"><div class="f2 cb"><div class="f1 cbin" style="width: 50%;"></div></div>Completeness 50% </div>-->
        <div class="c3"></div>
    </div>
    
    <strong class="txt-blue"><?php echo $row->bnsprof_compname; ?></strong>
    <br><?php echo $row->name_prefix; ?>&nbsp;<?php echo $row->fname; ?>&nbsp;<?php echo $row->lname; ?><br>
    <?php if($row->address != ''){  echo $row->address.", ";    }   ?>
    <?php if($row->bnsprof_city != '' && $row->bnsprof_city!='0'){  echo get_city_name($row->bnsprof_city).", ";    }   ?>
    <?php if($row->bnsprof_state !='' && $row->bnsprof_state !='0'){    echo get_state_name($row->bnsprof_state).", ";  }   ?>
    <?php echo get_country_name($row->country);?>
    <div class="mt12"></div><div class="oi">
    <ul>
        
                
        <?php 
        if($row->usr_oauth_reg == '0')
        {
            echo '<li class="fw f1 lem">Email</li><li class="fw">:</li><li class="txt-blue">'.$row->email; 
        }
        elseif($row->usr_oauth_reg == '1')
        {
            ?><li><strong>Logged In With</strong></li>
        <li><strong>:</strong></li>
        <li><img src="social_media_images/facebook_logo.jpg" /><?php
        } 
        elseif($row->usr_oauth_reg == '2')
        {
        ?><li><strong>Logged In With</strong></li>
        <li><strong>:</strong></li>
        <li><img src="social_media_images/gmail_.png" /><?php
        }
        elseif($row->usr_oauth_reg == '3')
        {
        ?><li><strong>Logged In With</strong></li>
        <li><strong>:</strong></li>
        <li><img src="social_media_images/twtBrd.jpg" /><?php
        }
        elseif($row->usr_oauth_reg == '4')
        {
        ?><li><strong>Logged In With</strong></li>
        <li><strong>:</strong></li>
        <li><img src="social_media_images/linkedinLog.png" /><?php
        }   
        ?>
        <?php if(getEmailVerificationStatus()==1){  ?>
         <?php if($row->usr_emailVerify == '1'){?><span class="ml8 f11 bnr dbg mo_ver">Verified</span><?php }else{?>&nbsp;<a href="sendVerifyLink.php" style="color: #F00">Verify Now</a><?php }?>
         <?php  }   ?>
         </li>
    </ul>
    </div>
    <?php if($row->mobile1 != ''){?>
    <div class="oi">
        <ul>
            <li class="fw f1 lem">Mobile</li>
            <li class="fw">:</li>
            <li><?php echo $row->country_ph_code;?>-<?php echo $row->mobile1;?></li>
        </ul>
    </div>
    <?php }?>
        <?php if(isset($_GET['verifySucces']) && $_GET['verifySucces'] == '1'){ ?>
                <div id="conf" class="emcr mt12 dbg bnr f11" style="display: block;">
                    Your primary e-Mail ID has been verified successfully.
                </div>
                <?php } ?>
               <?php if(isset($_GET['verifylinksend']) && $_GET['verifylinksend'] == '1'){?>
                <div style="display:block" id="alem">
                    <div style="display:block" id="conf2" class="emcr mt12 dbg bnr f11">We have sent an e-Mail to your alternate email ID. Kindly check your mail box &amp; verify your email ID.</div>                    
                </div>
           <?php }?> 
    
    </div><!--address:end--></div>
    
    
    
    
    <!--latest buy leads:ends--><!--my contact details:start-->
    <div class="f1 nd2 ebh3">
    <?php 
    $prd_num = mysqli_num_rows(mysqli_query($con, "select * from products,measurement_unit,country where mu_id=pd_unit and pd_currency=cn_id and pd_status = '1' and pd_uid = '".$uid."'"));  
    ?>
        <h2 class="pro_tle"><a href="product-list.php">My Products / Services (<?php echo $prd_num;?>)-<span style="color:rgb(0, 0, 255);font-weight:normal;font-size:12px;"> View All</span></a></h2>
    
        
        <a href="product-add.php" class="f2" style="margin:-21px 8px 0px 0px;text-decoration:none; color:#19528E; font-weight:bold"title="إعرض منتجات أو خدمات  - وتلقى إستفسارات شراء من داخل وخارج مصر">Add Product</a>
                
      <?php 
    $prd_res = mysqli_query($con, "select * from products,measurement_unit,country where mu_id=pd_unit and pd_currency=cn_id and pd_uid='".$uid."' order by rand() limit 0,3");  
    if(mysqli_num_rows($prd_res)>0)
    {
        while($prd_row = mysqli_fetch_object($prd_res)){
    ?>  
    
      <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
        <p class="colr-n" style="margin-top:-5px;"><strong><?php echo ucwords(stripslashes($prd_row->pd_title)); ?></strong></p>        
        <div class="rej_pro">
                <span>
                    <p style="display:inline-block; padding-left:24px;float:left;*min-width:150px !important;" <?php if($prd_row->pd_status == '2'){?>class="dbg rej_pro1" <?php }?>>                                           
                    <?php if($prd_row->pd_status == '0'){?>
                    <img src="images/pend_clock.png" width="11" height="11"/>   
                    <?php }?>
                    
                    <strong><?php if($prd_row->pd_status == '0'){echo '<font color="#496703">Pending:</font>';}elseif($prd_row->pd_status == '2'){echo '<font color="red">Rejected:</font>';}?></strong>
                    </p>
                </span>
                <?php if($prd_row->pd_status == '0'){?>
                <span class="f2">
                    <a href="product-edit.php?token=<?php echo rand(1000,9999).md5($prd_row->pd_id);?>">Edit Product</a>
                </span>
                <?php }?>
                <div style="clear:both"></div>
                <?php if($prd_row->pd_status == '1'){?>
                <p class="colr-n mtlf">
                    <span>This product <label style="color:green">is visible</label> in <?php echo getWebSiteName(); ?> Search.</span>
                </p>
                <p class="dbg" style="background-position:7px -340px;background-repeat:no-repeat;margin-left:20px">
                    <a href="product-edit.php?token=<?php echo rand(1000,9999).md5($prd_row->pd_id);?>" style="margin-left:20px">Edit Product</a>
                </p>
                 
                <?php }else{?>
                 <p class="colr-n mtlf">
                    <span>This product <label style="color:red">will not be visible</label> in <?php echo getWebSiteName(); ?> Search.</span>
                </p>
                 <?php }?>
         </div>
       </div>
      
        
      <?php }}else{?>  <br />

      <div class="c3 h5 bb1">&nbsp;</div>
        <div align="center"><a href="product-add.php" class="prur fw mt3" onMouseOver="amp();"title="إعرض منتجات أو خدمات  - وتلقى إستفسارات شراء من داخل وخارج مصر">Add Products</a>&nbsp;&nbsp;&nbsp;</div>
        <div class="c3 mt12">&nbsp;</div>
        <div class="in2 p5">
            <span class="inic dbg bnr f1"></span>
            <span style="display: block;" id="wap"><strong>Why Add more Products?</strong><p>Add more products with photo and description will help you to get more inquiries.</p></span>
            <span id="wgp" style="display: none;"><strong>Why Group your Products?</strong><p>Group your products and improve the quality of  your free website.</p></span>
            <div class="c3"></div>
        </div>
      <?php }?>  
        
        
        
    </div>  
    <!--my contact details:ends-->
 

 
 
    <!--MFSForm:start-->
        <link href="css/mfs.css" type="text/css" rel="stylesheet">
        <!--<div style="display:none;width:90px;right:683px!important;top:702px;position:absolute;" id="mf_spi2" class="spiral mf_pns"></div>-->
        <!--MFSForm:ends--> 
    
    
    
    
    
    <!--my products:start-->
<div class="f1 nd2 ebh3 nd5 " id="buylead_top_1">
        <h2> Latest Buy Leads</h2><div id="buylead_1" class="">
            
        <?php 
        $bl_res = mysqli_query($con, "select * from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id and br_approval_status = '1' and br_display_status = '1' and br_u_id = '".$_SESSION['uid_indm']."' order by br_updated_date desc limit 0,3");
        
        if(mysqli_num_rows($bl_res) > 0){
            while($bl_row = mysqli_fetch_object($bl_res))
            {
        ?>
        <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
        <table width="100%">
        <tr>
        <td width="80%">
        <p class="colr-n" style="margin-top:-5px;"><strong><a href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($bl_row->br_id);?>"><?php echo ucwords(stripslashes($bl_row->br_pd_name)); ?></a></strong></p> 
            </td>
            <td style="text-align:right; color:#999; font-size:10px"><?php echo date('d M, Y',strtotime($bl_row->br_updated_date));?></td>
            </tr>
            </table>
        <div class="rej_pro">
                
              
                 <p class="colr-n mtlf">
                    <span><?php echo substr($bl_row->br_requirement,0,100);?><?php if(strlen($bl_row->br_requirement)>100){ ?><a href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($bl_row->br_id);?>">more...</a>  <?php } ?></span>
                </p>
                
         </div>
       </div>
        
        <?php }}else{?>
        
            <div class="p5 bb1 txc mt12">No new leads found.</div>
        <div class="in2 p5 mt12">
        <span class="inic dbg bnr f1"></span>
        <span><strong>There is no new <span>Buy Leads</span> Posted By You.</strong></span>
        <ul>
        <li class="ltm">&nbsp;</li>
        <li class="ltm">&nbsp;</li>
        <li class="ltm">
        <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span><a style="color:#19528E; font-weight:bold; text-decoration:" href="subscription.php">Buy Credits</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span><a style="color:#19528E; font-weight:bold; text-decoration:" href="post-buy-req.php">Post Buy Request</a>
        
        </li>
        <li class="ltm"><span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span><a style="color:#19528E; font-weight:bold; text-decoration:" href="buyleads.php">Buy Leads</a></li>
        </ul>
        
        <div class="c3 h5"></div>
        </div>
        
        <?php }?>
        
        
        </div>
        
        
        <script language="javascript" type="text/javascript">setTimeout("LatestBuyLeads_free('')",100);</script>
        
        </div>  
    <!--my products:ends--><!--SellOffers:start-->
    
    
    
    
    
    <div class="f1 nd2 ebh3">
    <h2>My Sale Offers</h2>
    <form action="" name="form1">
    
     <?php 
        $so_res = mysqli_query($con, "select * from sale_offer where so_usr_id = '".$_SESSION['uid_indm']."' order by so_updated_date desc limit 0,3");
        
        if(mysqli_num_rows($so_res) > 0){
            
            while($so_row = mysqli_fetch_object($so_res))
            {
        ?>
        
        
        <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
        <table width="100%">
        <tr>
        <td width="80%">
        <p class="colr-n" style="margin-top:-5px;"><strong><a href="saleoffer-details.php?id=<?php echo rand(1000,9999).md5($so_row->so_id);?>"><?php echo ucwords(stripslashes($so_row->so_service)); ?></a></strong></p> 
            </td>
            <td style="text-align:right; color:#999; font-size:10px"><?php echo date('d M, Y',strtotime($so_row->so_updated_date));?></td>
            </tr>
            </table>
      
        <div class="rej_pro">
                <span>
                    <p style="display:inline-block; padding-left:24px;float:left;*min-width:150px !important;" <?php if($so_row->so_approval_status == '2'){?>class="dbg rej_pro1" <?php }?>>                                           
                    <?php if($so_row->so_approval_status == '0'){?>
                    <img src="images/pend_clock.png" width="11" height="11"/>   
                    <?php }?>
                    
                    <strong><?php if($so_row->so_approval_status == '0'){echo '<font color="#496703">Pending:</font>';}elseif($so_row->so_approval_status == '2'){echo '<font color="red">Rejected:</font>';}?></strong>
                    </p>
                </span>
              
                <div style="clear:both"></div>
               
                 <p class="colr-n mtlf">
                    <span><?php echo substr($so_row->so_description,0,100);?><?php if(strlen($so_row->so_description)>100){ ?><a href="saleoffer-details.php?id=<?php echo rand(1000,9999).md5($so_row->so_id);?>">more...</a>  <?php } ?></span>
                </p>
                 
         </div>
       </div>
    
    <?php }}else{?>
    
    <div class="c3 h5 bb1">&nbsp;</div>
    
    
        <div class="sell mt12 brdt">
        <h3>How it works?</h3>
        <p class="mt12 dbg bnr f1"><strong>Post Business Ads FREE</strong><br><span>List your business Ads FREE.</span></p>
        <p style="margin-left: 10px;" class="mt12 dbg bnr f1"><strong>Get Best Buyers</strong><br><span>Its Easy &amp; Effective.</span></p>
        <p class="mt12 dbg bnr f1 clw"><strong>Qualified Buyers Respond</strong><br><span>As per your business offerings.</span></p>
        <div class="c3"></div>
        <a href="post-sell-offer.php"  class="prur f1 mt12 fw mt4"title="سجل عروض بيع خاصة - وتلقى إستفسارات شراء">Post a New Sell Offer</a>
        <div class="c3"></div>
        </div>
        
          <?php }?>
        
        </form>
        
        
        </div>
    <!--SellOffers:ends-->
    
    
    
    
    
    <!--my enquiry:start-->
    <div class="mt5"></div>
    <div class="f1 nd2 ebh3">
        <a name="enquiries"></a>
        <?php
            $sql_enq="select * from message,user where msg_to='".$_SESSION['uid_indm']."' and msg_from=usr_id and msg_to_status='1' order by msg_date desc limit 0,4";
            $res_enq=mysqli_query($con, $sql_enq);
            $num_enq=mysqli_num_rows($res_enq);
        ?>
        <h2>My Inquiries (<?php echo $num_enq; ?>)</h2>
        <?php
            while($row_enq=mysqli_fetch_object($res_enq)){
        ?>
        <div class="sms bnr"><a href="my-enquiries.php?ii=<?php echo $row_enq->msg_id; ?>&tp=in" onClick="return track(this,'MyDashboard','EnqView','');"><strong><?php echo $row_enq->name_prefix." ".ucfirst($row_enq->fname)." ".ucfirst($row_enq->lname); ?></strong><br>
        <?php if($row->msg_subject!=''){ 
                echo stripslashes($row->msg_subject);
            }else{ ?>
            No Subject
            <?php } ?>
        </a></div>
        
        <?php   }   ?>
        <a href="my-enquiries.php" class="sall f1 mt5">Show all Inquiries</a>
    
    </div>
    
    
    <!--my enquiry:ends-->
    
    
    <!--AlertsNewsletters:start-->
    <div class="f1 nd2 cnfl" style="padding-bottom:5px">
    
    <a name="alerts"></a>
    
    <h2 style="border-bottom:1px solid #D2D2D2;">Transaction History-<a href="transaction_history.php"><span style="color:rgb(0, 0, 255);font-weight:normal;font-size:12px;"> View All</span></a></h2>
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td width="25%"><h2>Date</h2></td>
                <td width="50%"><h2>Details</h2></td>
                <td width="25%"><h2>Purchase/Use</h2></td>
            </tr>        
            
        </tbody>
    </table>
   <?php 
   $bh_res = mysqli_query($con, "select * from billing_history where bh_status = '1' and bh_usr_id = '".$_SESSION['uid_indm']."' order by bh_updated_date desc limit 0,5");
   if(mysqli_num_rows($bh_res) > 0){
        
    while($bh_row = mysqli_fetch_object($bh_res))
    {
   ?> 
    <div class="mst2  mst3">    
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
        <tbody>                 
            <tr>
                <td width="25%" style="vertical-align:middle"><?php echo date('d M, y',strtotime($bh_row->bh_updated_date));?></td>
                <td width="50%" style="vertical-align:middle"><?php if($bh_row->bh_type == '1'){echo 'Credit Purchased';}elseif($bh_row->bh_type == '2'){echo 'Credit Used For buy Leads';}elseif($bh_row->bh_type == '3'){echo 'Credit Used For Tender';}elseif($bh_row->bh_type == '4'){echo 'Credit Used For Auction';} ?></td>
                <td width="25%" style="vertical-align:middle; text-align:right"><?php if($bh_row->bh_type == '1'){echo $bh_row->bh_credit_purchased;}elseif($bh_row->bh_type == '2' || $bh_row->bh_type == '3' || $bh_row->bh_type == '4'){echo $bh_row->bh_credit_used;}?></td>
            </tr>
        </tbody>
    </table>
  </div>
    <?php }}else{?>
    <div class="mst2  mst3">    
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
        <tbody>                 
            <tr>
                <td width="100%" style="vertical-align:middle; text-align:center"><strong>No Transactions</strong></td>               
            </tr>
        </tbody>
    </table>
  </div>
    <?php }?>
    <div class="c3"></div>
    </div>

    <!--AlertsNewsletters:ends-->
        <div>
        <div class="c3"></div>
        <br>
        <div style="margin-left:53px;" id="buy_lead_gen_form"></div>
        <div id="bl_overlay_layer" class="layer1" style="display:none">
        <div class="bl_overlay"></div>
        </div>
        
        <!--Buy Lead Form Code Ends--><!--bottom banner ends--></div><div class="m2"></div><!--Footer code Starts--><!-- Footer Start Here::-->
  </div>
        

    
<div class="col-md-2 my_acc_sb">
            <div class="f2 leftnv"><div class="qickp1 mt12"> <p class="qickp fw fz4">Quick Links</p> 
        <div class="qickp2">
            <ul>
                <li><a href="post-buy-req.php">Post Buy Requirement</a></li>
                <li><a href="post-sell-offer.php">Post Sell Offers</a></li>
                <!--<li><a href="my-settings.php">Update Privacy Settings</a></li>-->
                <li><a href="my-contactdetails.php">Edit Contact Details</a></li>
            </ul>
        </div>
        
        
        
        </div>
        
        <br><style> #rnav{border-left:none} </style>
        
        
        <div class="vem2 vem mt12 c3">
        
        
                <h2>Verified contact information</h2>
                
                <?php if(isset($_GET['verifySucces']) && $_GET['verifySucces'] == '1'){?>
                <div id="conf" class="emcr mt12 dbg bnr f11" style="display: block;">
                    Your primary e-Mail ID has been verified successfully.
                </div>
                <?php }?>
                
                
                <div id="veri_prim" style="display:block" class="mt12 f11">
       <?php if($row->usr_oauth_reg == '0')
       {echo "<strong>Email:</strong><br>".$row->email;}
        
        elseif($row->usr_oauth_reg == '1')
        {
            ?><strong>Logged In With:</strong><br><img src="social_media_images/facebook_logo.jpg" /><?php
        } 
        elseif($row->usr_oauth_reg == '2')
        {
        ?><strong>Logged In With:</strong><br><img src="social_media_images/gmail_.png" /><?php
        }
        elseif($row->usr_oauth_reg == '3')
        {
        ?><strong>Logged In With:</strong><br><img src="social_media_images/twtBrd.jpg" /><?php
        }
        elseif($row->usr_oauth_reg == '4')
        {
        ?><strong>Logged In With:</strong><br><img src="social_media_images/linkedinLog.png" /><?php
        }   
        ?>
                    
                    
                    
                    <br>
                    
                    
                    
                <?php if(getEmailVerificationStatus()==1){  ?>
                    
                <?php if($row->usr_emailVerify == '1'){?><div class="f11 bnr dbg mo_ver mt5">Verified</div><?php }else{?><a href="sendVerifyLink.php">Verify Now</a><?php }?>

                <?php } ?>
                
                <div class="c3"></div></div>
                 <?php if($row->mobile1 != ''){?>
                <div class="mt12 f11"><strong>Mobile:</strong><br><?php echo $row->country_ph_code ;?>-<?php echo $row->mobile1;?><br>
                <div class="c3"></div></div>
                <?php }?>

                <div id="alh" class="pout mt12">

</div>
<?php if(isset($_GET['verifylinksend']) && $_GET['verifylinksend'] == '1'){?>
                <div style="display:block" id="alem">
                    <div style="display:block" id="conf2" class="emcr mt12 dbg bnr f11">We have sent an e-Mail to your alternate email ID. Kindly check your mail box &amp; verify your email ID.</div>                    
                </div>
           <?php }?>     
                <div class="c3"></div>
                </div>
                
                
                
                
                <div class="c3">&nbsp;</div>
                <div>&nbsp;</div>
                <a href="post-buy-req.php" onClick="return track(this,'MyDashboard','BuyReqBannerRight','');">
                    <img src="images/buy_req_bnr_1.gif" alt="View Latest Buy Req" border="0">
                </a> 
                
                <div class="mt12">&nbsp;</div>
                
                    <?php
    $sql_adv="select * from advertisement where adv_imagewidth='180' and adv_imageheight='240' and adv_status='1' order by rand() limit 1";
    $res_adv=mysqli_query($con, $sql_adv);
    if(mysqli_num_rows($res_adv)>0)
    {
        $row_adv=mysqli_fetch_object($res_adv); 
        ?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="180" height="240"/></a><?php
    }
    else
    {
?>
</>
<?php   }   ?>
                            
                    
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
        </div><!--right navigation:ends-->
        <div class="c3">&nbsp;</div>
</div>
    </div>
    <br><br><br><br><br>
        <div class="clearfix"></div>
</div>
    </div>
        <!--footer:start-->
        <?php include 'includes/footer.php'; ?>
