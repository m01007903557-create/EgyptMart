<?php
ob_start();
session_start();

// عرض جميع الأخطاء للمساعدة في التصحيح (يمكنك تعطيلها لاحقاً)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'common.php';

if(isset($_GET["popup"]) && $_GET["popup"] == 'close') {
    $_SESSION["popup"] = 2;
}

$_SESSION['last_page'] = "my-dashboard.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    header("Location: sign-in.php");
    exit();
}
$uid = $_SESSION['uid_indm'];
$current_uid = $uid;
?>

<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="css/style.css" type="text/css" rel="stylesheet">
    <link href="css/style123.css" type="text/css" rel="stylesheet">
    <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/pdash-v-1.css" type="text/css" rel="stylesheet">

    <script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
</head>

<body>
    <div class="hm1 bbc" id="res-mob1">

        <?php include "includes/header_new.php"; ?>

        <div class="container my_acc_wrapper">

            <div class="row" style="margin:0!important;">
                <div class="col-md-12">
                    <br>
                    <?php include 'includes/header_menu.php'; ?>

                    <?php
                    $sql = "select * from user, business_profile where usr_id = bnsprof_uid and usr_id = '" . mysqli_real_escape_string($con, $uid) . "' and status = '1'";
                    $res = mysqli_query($con, $sql);
                    $row = mysqli_fetch_object($res);
                    ?>

                    <?php
                    $company_name = $row->usr_id;
                    $billing_sql = "SELECT * from billing_history WHERE bh_usr_id = '" . mysqli_real_escape_string($con, $company_name) . "' ORDER BY bh_updated_date DESC LIMIT 1";
                    $billing_query = mysqli_query($con, $billing_sql);
                    $billing_detail = mysqli_fetch_object($billing_query);

                    $sql = "SELECT * from user u, smembership_plan mp WHERE u.usr_id = '" . mysqli_real_escape_string($con, $row->usr_id) . "' AND u.usr_mp_id = mp.mp_id LIMIT 1";
                    $query_01 = mysqli_query($con, $sql);
                    $plan_detail = mysqli_fetch_object($query_01);
                    $plan_key = isset($plan_detail->s_key) ? $plan_detail->s_key : '';
                    
                    $mswp = 'SELECT * FROM smembership_plan mp, user u, membership_plan mps where u.usr_id = ' . (int)$row->usr_id . ' AND u.usr_mp_id = mp.mp_id AND mps.mp_id = mp.mp_id';
                    $q = mysqli_query($con, $mswp);
                    $mp = mysqli_fetch_object($q);
                    
                    $qq = 'SELECT * FROM membership_plan WHERE s_key = "' . mysqli_real_escape_string($con, $plan_key) . '"';
                    $ww = mysqli_query($con, $qq);
                    $membership_plan = mysqli_fetch_object($ww);

                    $sql_plan = "select * from plan_member_id, business_profile where b_id = bnsprof_id and bnsprof_uid = '" . mysqli_real_escape_string($con, $current_uid) . "'";
                    $query_plan = mysqli_query($con, $sql_plan);
                    $plan_dates = mysqli_fetch_object($query_plan);
                    
                    $start_date = isset($plan_dates->start_date) ? date("Y-m-d", $plan_dates->start_date) : 'N/A';
                    $expiry_date = isset($plan_dates->expiry_date) ? date("Y-m-d", $plan_dates->expiry_date) : 'N/A';

                    $count = mysqli_num_rows($query_01);
                    ?>
                    
                    <?php if($count == 0 || (isset($plan_detail->mst_name) && $plan_detail->mst_name == 'JUNIOR')){
                        $link = 'membership_plans.php';
                    } else {
                        $link = 'product-add.php';
                    } ?>

                    <div class="f1 nd2 cnfl" style="width:37.4%">
                        <h2 style="border-bottom:1px solid #D2D2D2;" title="My Current Plan">خطط إشتراكى بالمنصة</h2>
                        <div class="p5 lh cfc">
                            <div class="oi oinpr" style=" padding-bottom:80px;">
                                <style>
                                .oinpr li {
                                    padding-right: 0px;
                                }
                                </style>

                                <div class="fl " style="width:62%">
                                    <ul>
                                        <li class="fw f1">Date</li>
                                        <li class="fw">:</li>
                                        <li><?php echo date('d-M-Y H:i T'); ?></li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">خطة عضويتى</li>
                                        <li class="fw">:</li>
                                        <li><?php 
                                        if(isset($plan_detail->mst_name)){
                                            echo ((isset($plan_detail->expiry_date) && $plan_detail->expiry_date != '' && date('d F Y', $plan_detail->expiry_date) == '09 September 9999')) ? 'N/A' : htmlspecialchars($plan_detail->mst_name); 
                                        } else {
                                            echo "N/A";
                                        }
                                        ?>
                                        </li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">بداية الخطة</li>
                                        <li class="fw">:</li>
                                        <li><?php echo htmlspecialchars($start_date); ?></li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">نهاية الخطة</li>
                                        <li class="fw">:</li>
                                        <li><?php echo htmlspecialchars($expiry_date); ?></li>
                                    </ul>
                                </div>
                                <div class="fl " style="text-align:right; width:38%">
                                    <ul>
                                        <li class="fw f1">القيمة السنوية</li>
                                        <li class="fw">:</li>
                                        <li><?php echo isset($membership_plan->mp_amount) ? number_format($membership_plan->mp_amount, 2) : '0.00'; ?></li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">القيمة المدفوعة</li>
                                        <li class="fw">:</li>
                                        <li><?php 
                                        if(isset($membership_plan->s_key) && ($membership_plan->s_key == 1 || $membership_plan->s_key == 0)){
                                            echo '0.00';
                                        } else {
                                            echo isset($membership_plan->mp_amount) ? number_format($membership_plan->mp_amount, 2) : '0.00';
                                        ?>
                                        <br /><br />
                                        <a href="membership_plans.php" target="_blank" class="prur fw mt3" style="color:#ff0000;">شاهد جميع الخطط</a>
                                        <?php } ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                   <div class="f1 nd2 ebh3" id="miniSiteBox">

    <?php
        $cid = rand(1000, 9999) . md5($row->bnsprof_id);
        $miniSiteUrl = 'company/index.php?c=' . htmlspecialchars($cid);
    ?>

    <div class="f1" style="width:100%;">

        <div class="demw c3">

            <h2 class="pro_tle">
                <a href="membership_plans.php"
                   target="_blank"
                   title="My Company Mini-Site">
                    الموقغ المصغر لشركتى
                    <span style="color:rgb(0,0,255);font-weight:normal;font-size:9px;">
                        شاهد خطط الاشتراك
                    </span>
                </a>
            </h2>

            <?php if($count == 0 || (isset($plan_detail->mst_name) && $plan_detail->mst_name == 'JUNIOR')) { ?>

                <div class="in2 p5">
                    <span class="inic dbg bnr f1"></span>

                    <span style="display:block;" id="wap">
                        <p>!لايوجد موقع مصغر للشركة تم إنشاؤه عن طريقك</p>
                    </span>

                    <div align="center" class="mt-5" style="margin-top:20px;">
                        <a href="membership_plans.php" class="prur fw">
                            إنشىء موقعك المصغر للتجارة
                        </a>
                    </div>

                    <div class="c3"></div>
                </div>

            <?php } else { ?>

                <?php if($row->user_type > 1 && $row->bnsprof_compname != '') { ?>

                    <!-- Mini-Site Icon -->
                    <div style="text-align:center; padding:20px 0;">

                        <a href="javascript:void(0);"
                           id="openMiniSite"
                           title="Open My Mini-Site"
                           style="
                               display:inline-flex;
                               align-items:center;
                               justify-content:center;
                               width:70px;
                               height:70px;
                               border-radius:50%;
                               background:#03bf00;
                               color:#fff;
                               text-decoration:none;
                               font-size:30px;
                               font-weight:bold;
                               box-shadow:0 2px 8px rgba(0,0,0,0.25);
                           ">
                            <i class="fa fa-globe"></i>
                        </a>

                        <div style="margin-top:8px;">
                            <strong style="color:#03bf00;">
                                هنا موقع شركتك المصغر
                            </strong>
                        </div>

                        <div style="font-size:11px; color:#777; margin-top:3px;">
                            Click the icon to open Mini-Site
                        </div>

                    </div>

                    <!-- Mini-Site Window -->
                    <div id="miniSiteModal"
                         style="
                             display:none;
                             position:fixed;
                             z-index:99999;
                             top:0;
                             left:0;
                             width:100%;
                             height:100%;
                             background:rgba(0,0,0,0.65);
                         ">

                        <div style="
                             position:relative;
                             width:90%;
                             height:90%;
                             margin:3% auto;
                             background:#fff;
                             border-radius:5px;
                             box-shadow:0 5px 25px rgba(0,0,0,0.4);
                             overflow:hidden;
                        ">

                            <!-- Close -->
                            <button type="button"
                                    id="closeMiniSite"
                                    style="
                                        position:absolute;
                                        top:8px;
                                        right:10px;
                                        z-index:100000;
                                        width:32px;
                                        height:32px;
                                        border:0;
                                        border-radius:50%;
                                        background:#ed0001;
                                        color:#fff;
                                        font-size:20px;
                                        line-height:30px;
                                        cursor:pointer;
                                    ">
                                &times;
                            </button>

                            <!-- Mini-Site -->
                            <iframe
                                id="miniSiteFrame"
                                src=""
                                data-src="<?php echo $miniSiteUrl; ?>"
                                style="
                                    width:100%;
                                    height:100%;
                                    border:0;
                                "
                                loading="lazy">
                            </iframe>

                        </div>
                    </div>

                    <br>

                    <div style="text-align:center;">
                        <a href="company/products.php?c=<?php echo htmlspecialchars($cid); ?>"
                           target="_blank"
                           class="txt-yellow"
                           style="color:#03bf00;font-weight:800;"
                           title="My Mini-Site">
                            هنا موقع شركتك المصغر للمعاينة
                        </a>
                    </div>

                <?php } else { ?>

                    <div style="text-align:center; padding:20px;">
                        <a href="create-your-website.php">
                            Click here
                        </a>
                        to Create your
                        <span class="fw">
                            Business Page @ <?php echo htmlspecialchars(getWebSiteName()); ?>
                        </span>.
                    </div>

                <?php } ?>

            <?php } ?>

            <div class="c3"></div>

        </div>

        <div class="c3 mt5"></div>

    </div>
</div>
                </div>

                <div class="clearfix"></div>

                <div class="col-md-10 my_acc_main">
                    <div class="f1 nd2 cnfl">
                        <h2 style="border-bottom:1px solid #D2D2D2;">تفاصيل الإتصال بشركتى</h2>
                        <div class="p5 lh cfc">
                            <div>
                                <a href="my-contactdetails.php" class="f2 f11 ded" style="margin-left:5px">
                                    <span class="dbg bnr">عـدل</span>
                                </a>
                                <div class="c3"></div>
                            </div>
                            <strong class="txt-blue"><?php echo htmlspecialchars($row->bnsprof_compname); ?></strong>
                            <br><?php echo htmlspecialchars($row->name_prefix); ?>&nbsp;<?php echo htmlspecialchars($row->fname); ?>&nbsp;<?php echo htmlspecialchars($row->lname); ?><br>
                            <?php if(isset($row->address) && $row->address != ''){ echo htmlspecialchars($row->address) . ", "; } ?>
                            <?php if(isset($row->bnsprof_city) && $row->bnsprof_city != '' && $row->bnsprof_city != '0'){ echo htmlspecialchars(get_city_name($row->bnsprof_city)) . ", "; } ?>
                            <?php if(isset($row->bnsprof_state) && $row->bnsprof_state != '' && $row->bnsprof_state != '0'){ echo htmlspecialchars(get_state_name($row->bnsprof_state)) . ", "; } ?>
                            <?php echo isset($row->country) ? htmlspecialchars(get_country_name($row->country)) : ''; ?>
                            <div class="mt12"></div>
                            <div class="oi">
                                <ul>
                                    <?php 
                                    if($row->usr_oauth_reg == '0') {
                                        echo '<li class="fw f1 lem">Email</li><li class="fw">:</li><li class="txt-blue">' . htmlspecialchars($row->email);
                                    } elseif($row->usr_oauth_reg == '1') { ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/facebook_logo.jpg" alt="Facebook" /><?php
                                    } elseif($row->usr_oauth_reg == '2') { ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/gmail_.png" alt="Gmail" /><?php
                                    } elseif($row->usr_oauth_reg == '3') { ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/twtBrd.jpg" alt="Twitter" /><?php
                                    } elseif($row->usr_oauth_reg == '4') { ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/linkedinLog.png" alt="LinkedIn" /><?php
                                    } ?>
                                    
                                    <?php if(getEmailVerificationStatus() == 1){ ?>
                                        <?php if($row->usr_emailVerify == '1'){ ?>
                                            <span class="ml8 f11 bnr dbg mo_ver">Verified</span>
                                        <?php } else { ?>
                                            &nbsp;<a href="sendVerifyLink.php" style="color: #F00">حقق بريدك الآن</a>
                                        <?php } ?>
                                    <?php } ?>
                                    </li>
                                </ul>
                            </div>
                            
                            <?php if($row->mobile1 != ''){ ?>
                            <div class="oi">
                                <ul>
                                    <li class="fw f1 lem">Mobile</li>
                                    <li class="fw">:</li>
                                    <li><?php echo htmlspecialchars($row->country_ph_code); ?>-<?php echo htmlspecialchars($row->mobile1); ?></li>
                                </ul>
                            </div>
                            <?php } ?>
                            
                            <?php if(isset($_GET['verifySucces']) && $_GET['verifySucces'] == '1'){ ?>
                            <div id="conf" class="emcr mt12 dbg bnr f11" style="display: block;">
                                Your primary e-Mail ID has been verified successfully.
                            </div>
                            <?php } ?>
                            
                            <?php if(isset($_GET['verifylinksend']) && $_GET['verifylinksend'] == '1'){ ?>
                            <div style="display:block" id="alem">
                                <div style="display:block" id="conf2" class="emcr mt12 dbg bnr f11">
                                    We have sent an e-Mail to your alternate email ID. Kindly check your mail box &amp; verify your email ID.
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="f1 nd2 ebh3">
                        <?php 
                        $prd_num = mysqli_num_rows(mysqli_query($con, "select * from products, measurement_unit, country where mu_id = pd_unit and pd_currency = cn_id and pd_status = '1' and pd_uid = '" . mysqli_real_escape_string($con, $uid) . "'"));
                        ?>
                        <h2 class="pro_tle">
                            <a href="product-list.php">منتجاتى وخدماتى المنشورة (<?php echo (int)$prd_num; ?>)
                                <span style="color:rgb(0, 0, 255);font-weight:normal;font-size:12px;">شاهد الجميع</span>
                            </a>
                        </h2>
                        <a href="<?php echo htmlspecialchars($link); ?>" class="f2" style="margin:-21px 8px 0px 0px;text-decoration:none; color:#ed0001; font-weight:bold">أضف منتج</a>

                        <?php 
                        $prd_res = mysqli_query($con, "select * from products, measurement_unit, country where mu_id = pd_unit and pd_currency = cn_id and pd_uid = '" . mysqli_real_escape_string($con, $uid) . "' order by rand() limit 0,3");
                        if(mysqli_num_rows($prd_res) > 0) {
                            while($prd_row = mysqli_fetch_object($prd_res)) {
                        ?>
                        <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
                            <p class="colr-n" style="margin-top:-5px;">
                                <strong><?php echo htmlspecialchars(ucwords(stripslashes($prd_row->pd_title))); ?></strong>
                            </p>
                            <div class="rej_pro">
                                <span>
                                    <p style="display:inline-block; padding-left:24px;float:left;*min-width:150px !important;">
                                        <?php if($prd_row->pd_status == '0'){ ?>
                                        <img src="images/pend_clock.png" width="11" height="11" />
                                        <?php } ?>
                                        <strong><?php if($prd_row->pd_status == '0'){ echo '<font color="#496703">Pending:</font>'; } elseif($prd_row->pd_status == '2'){ echo '<font color="red">Rejected:</font>'; } ?></strong>
                                    </p>
                                </span>
                                <?php if($prd_row->pd_status == '0'){ ?>
                                <span class="f2">
                                    <a href="product-edit.php?token=<?php echo rand(1000,9999) . md5($prd_row->pd_id); ?>">Edit Product</a>
                                </span>
                                <?php } ?>
                                <div style="clear:both"></div>
                                <?php if($prd_row->pd_status == '1'){ ?>
                                <p class="colr-n mtlf">
                                    <span>This product <label style="color:green">is visible</label> in <?php echo htmlspecialchars(getWebSiteName()); ?> Search.</span>
                                </p>
                                <p class="dbg" style="background-position:7px -340px;background-repeat:no-repeat;margin-left:20px">
                                    <a href="product-edit.php?token=<?php echo rand(1000,9999) . md5($prd_row->pd_id); ?>" style="margin-left:20px">Edit Product</a>
                                </p>
                                <?php } else { ?>
                                <p class="colr-n mtlf">
                                    <span>This product <label style="color:red">will not be visible</label> in <?php echo htmlspecialchars(getWebSiteName()); ?> Search.</span>
                                </p>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } 
                        } else { ?>
                        <br />
                        <div class="c3 h5 bb1">&nbsp;</div>
                        <div align="center">
                            <a href='<?php echo htmlspecialchars($link); ?>' class="prur fw mt3">أضف منتج جديد</a>&nbsp;&nbsp;&nbsp;
                        </div>
                        <div class="c3 mt12">&nbsp;</div>
                        <div class="in2 p5">
                            <span class="inic dbg bnr f1"></span>
                            <span style="display: block;" id="wap">
                                <strong>لماذا تضيف المزيد من المنتجات ؟</strong>
                                <p>! إضافة مزيد من المنتجات تزيد من فرص الحصول على مزيد من الاستفسارات وطلبات الشراء</p>
                            </span>
                            <span id="wgp" style="display: none;">
                                <strong>Why Group your Products?</strong>
                                <p>Group your products and improve the quality of your free website.</p>
                            </span>
                            <div class="c3"></div>
                        </div>
                        <?php } ?>
                    </div>

                    <link href="css/mfs.css" type="text/css" rel="stylesheet">

                    <div class="f1 nd2 ebh3 nd5 " id="buylead_top_1">
                        <h2>آخر طلبات الشراء</h2>
                        <div id="buylead_1" class="">
                            <a href="post-buy-req.php" class="f2" style="margin:-21px 8px 0px 0px;text-decoration:none; color:#ed0001; font-weight:bold"></a>

                            <?php 
                            $bl_res = mysqli_query($con, "select * from buy_requirement, measurement_unit where br_estimate_qty_unit = mu_id and br_approval_status = '1' and br_display_status = '1' and br_u_id = '" . mysqli_real_escape_string($con, $_SESSION['uid_indm']) . "' order by br_updated_date desc limit 0,3");
                            
                            if(mysqli_num_rows($bl_res) > 0){
                                while($bl_row = mysqli_fetch_object($bl_res)) {
                            ?>
                            <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
                                <table width="100%">
                                    <tr>
                                        <td width="80%">
                                            <p class="colr-n" style="margin-top:-5px;">
                                                <strong><a href="buyleads-details.php?id=<?php echo rand(1000,9999) . md5($bl_row->br_id); ?>"><?php echo htmlspecialchars(ucwords(stripslashes($bl_row->br_pd_name))); ?></a></strong>
                                            </p>
                                        </td>
                                        <td style="text-align:right; color:#999; font-size:10px">
                                            <?php echo date('d M, Y', strtotime($bl_row->br_updated_date)); ?>
                                        </td>
                                    </tr>
                                </table>
                                <div class="rej_pro">
                                    <p class="colr-n mtlf">
                                        <span><?php echo htmlspecialchars(substr($bl_row->br_requirement, 0, 100)); ?>
                                        <?php if(strlen($bl_row->br_requirement) > 100){ ?>
                                            <a href="buyleads-details.php?id=<?php echo rand(1000,9999) . md5($bl_row->br_id); ?>">more...</a>
                                        <?php } ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <?php } 
                            } else { ?>
                            <div class="p5 bb1 txc mt12">لايوجد طلبات شراء</div>
                            <div class="in2 p5 mt12">
                                <span class="inic dbg bnr f1"></span>
                                <span><strong>!لايوجد طلبات شراء جديدة تم نشرها <span></span> </strong></span>
                                <ul>
                                    <li class="ltm">&nbsp;</li>
                                    <li class="ltm">&nbsp;</li>
                                    <li class="ltm">
                                        <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span>
                                        <a style="color:#19528E; font-weight:bold; text-decoration:" href="subscription.php">شراء كريديت</a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span>
                                        <a style="color:#ed0001; font-weight:bold; text-decoration:" href="post-buy-req.php">أنشر طلب شراء</a>
                                    </li>
                                    <li class="ltm">
                                        <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span>
                                        <a style="color:#19528E; font-weight:bold; text-decoration:" href="buyleads.php">طلبات شراء</a>
                                    </li>
                                </ul>
                                <div class="c3 h5"></div>
                            </div>
                            <?php } ?>
                        </div>

                        <script language="javascript" type="text/javascript">
                        setTimeout("LatestBuyLeads_free('')", 100);
                        </script>
                    </div>

                    <div class="f1 nd2 ebh3">
                        <h2>عروض بيعى الخاصة</h2>
                        <form action="" name="form1">
                            <?php 
                            $so_res = mysqli_query($con, "select * from sale_offer where so_usr_id = '" . mysqli_real_escape_string($con, $_SESSION['uid_indm']) . "' order by so_updated_date desc limit 0,3");
                            
                            if(mysqli_num_rows($so_res) > 0){
                                while($so_row = mysqli_fetch_object($so_res)) {
                            ?>
                            <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
                                <table width="100%">
                                    <tr>
                                        <td width="80%">
                                            <p class="colr-n" style="margin-top:-5px;">
                                                <strong><a href="saleoffer-details.php?id=<?php echo rand(1000,9999) . md5($so_row->so_id); ?>"><?php echo htmlspecialchars(ucwords(stripslashes($so_row->so_service))); ?></a></strong>
                                            </p>
                                        </td>
                                        <td style="text-align:right; color:#999; font-size:10px">
                                            <?php echo date('d M, Y', strtotime($so_row->so_updated_date)); ?>
                                        </td>
                                    </tr>
                                </table>
                                <div class="rej_pro">
                                    <span>
                                        <p style="display:inline-block; padding-left:24px;float:left;*min-width:150px !important;">
                                            <?php if($so_row->so_approval_status == '0'){ ?>
                                            <img src="images/pend_clock.png" width="11" height="11" />
                                            <?php } ?>
                                            <strong><?php if($so_row->so_approval_status == '0'){ echo '<font color="#496703">Pending:</font>'; } elseif($so_row->so_approval_status == '2'){ echo '<font color="red">Rejected:</font>'; } ?></strong>
                                        </p>
                                    </span>
                                    <div style="clear:both"></div>
                                    <p class="colr-n mtlf">
                                        <span><?php echo htmlspecialchars(substr($so_row->so_description, 0, 100)); ?>
                                        <?php if(strlen($so_row->so_description) > 100){ ?>
                                            <a href="saleoffer-details.php?id=<?php echo rand(1000,9999) . md5($so_row->so_id); ?>">more...</a>
                                        <?php } ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <?php } 
                            } else { ?>
                            <div class="c3 h5 bb1">&nbsp;</div>
                            <div class="sell mt12 brdt">
                                <h3>كيف تعمل ؟</h3>
                                <p class="mt12 dbg bnr f1">
                                    <strong>أنشر عروض بيع خاصة مجانا</strong><br>
                                    <span>أنشر إعلانات وعروض بيع مجانا عن تجارتك</span>
                                </p>
                                <p style="margin-left: 10px;" class="mt12 dbg bnr f1">
                                    <strong>أحصل على أفضل مشتريين لتجارتك</strong><br>
                                    <span>إنها سهلة وفعالة</span>
                                </p>
                                <p class="mt12 dbg bnr f1 clw">
                                    <strong>إستجابات من المشتريين</strong><br>
                                    <span>طبقا لعروض تجارتك المختلفة</span>
                                </p>
                                <div class="c3"></div>
                                <a href="post-sell-offer.php" class="prur f1 mt12 fw mt4">أنشر عروض بيع خاصة</a>
                                <div class="c3"></div>
                            </div>
                            <?php } ?>
                        </form>
                    </div>

                    <div class="mt5"></div>
                    <div class="f1 nd2 ebh3">
                        <a name="enquiries"></a>
                        <?php
                        $sql_enq = "select * from message, user where msg_to = '" . mysqli_real_escape_string($con, $_SESSION['uid_indm']) . "' and msg_from = usr_id and msg_to_status = '1' order by msg_date desc limit 0,4";
                        $res_enq = mysqli_query($con, $sql_enq);
                        $num_enq = mysqli_num_rows($res_enq);
                        ?>
                        <h2>رسائل بريدى (<?php echo (int)$num_enq; ?>)</h2>
                        <?php while($row_enq = mysqli_fetch_object($res_enq)){ ?>
                        <div class="sms bnr">
                            <a href="my-enquiries.php?ii=<?php echo (int)$row_enq->msg_id; ?>&tp=in">
                                <strong><?php echo htmlspecialchars($row_enq->name_prefix . " " . ucfirst($row_enq->fname) . " " . ucfirst($row_enq->lname)); ?></strong><br>
                                <?php if($row_enq->msg_subject != ''){ 
                                    echo htmlspecialchars(stripslashes($row_enq->msg_subject));
                                } else { ?>
                                    No Subject
                                <?php } ?>
                            </a>
                        </div>
                        <?php } ?>
                        <a href="my-enquiries.php" class="sall f1 mt5">إظهار كل الرسائل</a>
                    </div>

                    <div class="f1 nd2 cnfl" style="padding-bottom:5px">
                        <a name="alerts"></a>
                        <h2 style="border-bottom:1px solid #D2D2D2;">
                            قائمة الدفوعات
                            <a href="transaction_history.php">
                                <span style="color:rgb(0, 0, 255);font-weight:normal;font-size:12px;"> مشاهدة الجميع</span>
                            </a>
                        </h2>
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
                        $bh_res = mysqli_query($con, "select * from billing_history where bh_status = '1' and bh_usr_id = '" . mysqli_real_escape_string($con, $_SESSION['uid_indm']) . "' order by bh_updated_date desc limit 0,5");
                        if(mysqli_num_rows($bh_res) > 0){
                            while($bh_row = mysqli_fetch_object($bh_res)) {
                        ?>
                        <div class="mst2 mst3">
                            <table border="0" cellpadding="2" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td width="25%" style="vertical-align:middle">
                                            <?php echo date('d M, y', strtotime($bh_row->bh_updated_date)); ?>
                                        </td>
                                        <td width="50%" style="vertical-align:middle">
                                            <?php if($bh_row->bh_type == '1'){ echo 'Credit Purchased'; }
                                            elseif($bh_row->bh_type == '2'){ echo 'Credit Used For buy Leads'; }
                                            elseif($bh_row->bh_type == '3'){ echo 'Credit Used For Tender'; }
                                            elseif($bh_row->bh_type == '4'){ echo 'Credit Used For Auction'; } ?>
                                        </td>
                                        <td width="25%" style="vertical-align:middle; text-align:right">
                                            <?php if($bh_row->bh_type == '1'){ echo $bh_row->bh_credit_purchased; }
                                            elseif($bh_row->bh_type == '2' || $bh_row->bh_type == '3' || $bh_row->bh_type == '4'){ echo $bh_row->bh_credit_used; } ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php } 
                        } else { ?>
                        <div class="mst2 mst3">
                            <table border="0" cellpadding="2" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td width="100%" style="vertical-align:middle; text-align:center">
                                            <strong>!لايوجد مدفوعات</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                        <div class="c3"></div>
                    </div>

                    <div>
                        <div class="c3"></div>
                        <br>
                        <div style="margin-left:53px;" id="buy_lead_gen_form"></div>
                        <div id="bl_overlay_layer" class="layer1" style="display:none">
                            <div class="bl_overlay"></div>
                        </div>
                    </div>
                    <div class="m2"></div>
                </div>

                <div class="col-md-2 my_acc_sb">
                    <div class="f2 leftnv">
                        <div class="qickp1 mt12">
                            <p class="qickp fw fz4">روابط هامة</p>
                            <div class="qickp2">
                                <ul>
                                    <li><a href="post-buy-req.php">أنشر طلب شراء</a></li>
                                    <li><a href="post-sell-offer.php">أنشر عرض بيع خاص</a></li>
                                    <li><a href="my-contactdetails.php">عدل معلومات اتصال تجارتك</a></li>
                                </ul>
                            </div>
                        </div>
                        <br>
                        <style>
                        #rnav {
                            border-left: none
                        }
                        </style>

                        <div class="vem2 vem mt12 c3">
                            <h2>معلومات متحقق منها</h2>

                            <?php if(isset($_GET['verifySucces']) && $_GET['verifySucces'] == '1'){ ?>
                            <div id="conf" class="emcr mt12 dbg bnr f11" style="display: block;">
                                تم التحقق من بريدك الالكترونى بنجاح
                            </div>
                            <?php } ?>

                            <div id="veri_prim" style="display:block" class="mt12 f11">
                                <?php if($row->usr_oauth_reg == '0') {
                                    echo "<strong>Email:</strong><br>" . htmlspecialchars($row->email);
                                } elseif($row->usr_oauth_reg == '1') { ?>
                                    <strong>Logged In With:</strong><br><img src="social_media_images/facebook_logo.jpg" alt="Facebook" />
                                <?php } elseif($row->usr_oauth_reg == '2') { ?>
                                    <strong>Logged In With:</strong><br><img src="social_media_images/gmail_.png" alt="Gmail" />
                                <?php } elseif($row->usr_oauth_reg == '3') { ?>
                                    <strong>Logged In With:</strong><br><img src="social_media_images/twtBrd.jpg" alt="Twitter" />
                                <?php } elseif($row->usr_oauth_reg == '4') { ?>
                                    <strong>Logged In With:</strong><br><img src="social_media_images/linkedinLog.png" alt="LinkedIn" />
                                <?php } ?>
                                <br>
                                <?php if(getEmailVerificationStatus() == 1){ ?>
                                    <?php if($row->usr_emailVerify == '1'){ ?>
                                        <div class="f11 bnr dbg mo_ver mt5">Verified</div>
                                    <?php } else { ?>
                                        <a href="sendVerifyLink.php">تحقق الآن</a>
                                    <?php } ?>
                                <?php } ?>
                                <div class="c3"></div>
                            </div>
                            
                            <?php if($row->mobile1 != ''){ ?>
                            <div class="mt12 f11">
                                <strong>Mobile:</strong><br><?php echo htmlspecialchars($row->country_ph_code); ?>-<?php echo htmlspecialchars($row->mobile1); ?><br>
                                <div class="c3"></div>
                            </div>
                            <?php } ?>

                            <div id="alh" class="pout mt12"></div>
                            
                            <?php if(isset($_GET['verifylinksend']) && $_GET['verifylinksend'] == '1'){ ?>
                            <div style="display:block" id="alem">
                                <div style="display:block" id="conf2" class="emcr mt12 dbg bnr f11">
                                    قمنا بإرسال رسالة تحقق الى بريدك .. رجاء الذهاب الى بريدك وفتح الرسالة للتحقق
                                </div>
                            </div>
                            <?php } ?>
                            <div class="c3"></div>
                        </div>

                        <div class="c3">&nbsp;</div>
                        <div>&nbsp;</div>
                        <a href="post-buy-req.php">
                            <img src="images/buy_req_bnr_1.gif" alt="View Latest Buy Req" border="0">
                        </a>
                    </div>
                </div>
                <br><br><br><br><br>
                <div class="clearfix"></div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    var openBtn = document.getElementById('openMiniSite');
    var closeBtn = document.getElementById('closeMiniSite');
    var modal = document.getElementById('miniSiteModal');
    var frame = document.getElementById('miniSiteFrame');

    if (openBtn && modal && frame) {

        openBtn.addEventListener('click', function () {
            frame.src = frame.getAttribute('data-src');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });

    }

    if (closeBtn && modal) {

        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
            document.body.style.overflow = '';

            if (frame) {
                frame.src = '';
            }
        });

    }

    if (modal) {

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';

                if (frame) {
                    frame.src = '';
                }
            }
        });

    }

});
</script>
</body>
</html>