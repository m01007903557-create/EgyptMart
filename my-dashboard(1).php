<?php
// my-dashboard.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
session_start();
include 'common.php';

// معالجة إغلاق البوب أب
if (isset($_GET["popup"]) && $_GET["popup"] == 'close') {
    $_SESSION["popup"] = 2;
}

$_SESSION['last_page'] = "my-dashboard.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link href="css/style.css" type="text/css" rel="stylesheet">
    <link href="css/style123.css" type="text/css" rel="stylesheet">
    <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/pdash-v-1.css" type="text/css" rel="stylesheet">
    
    <script src="js/jquery-1.2.1.min.js"></script>
</head>

<body>
    <div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>

        <div class="container my_acc_wrapper">
            <div class="row" style="margin:0!important;">
                <div class="col-md-12">
                    <br><br>
                    <?php include 'includes/header_menu.php'; ?>

                    <?php
                    // جلب بيانات المستخدم والشركة
                    $sql = "SELECT * FROM user, business_profile 
                            WHERE usr_id = bnsprof_uid AND usr_id = '{$uid}' AND status = '1'";
                    $res = mysqli_query($con, $sql);
                    $row = mysqli_fetch_object($res);
                    
                    if (!$row) {
                        echo "<div class='alert alert-danger'>لم يتم العثور على بيانات المستخدم</div>";
                        $row = new stdClass();
                    }
                    
                    // جلب آخر فاتورة
                    $billing_sql = "SELECT * FROM billing_history 
                                    WHERE bh_usr_id = '{$uid}' 
                                    ORDER BY bh_updated_date DESC LIMIT 1";
                    $billing_query = mysqli_query($con, $billing_sql);
                    $billing_detail = mysqli_fetch_object($billing_query);
                    
                    // جلب خطة العضوية
                    $sql = "SELECT * FROM user u, smembership_plan mp 
                            WHERE u.usr_id = '{$uid}' AND u.usr_mp_id = mp.mp_id LIMIT 1";
                    $query_01 = mysqli_query($con, $sql);
                    $plan_detail = mysqli_fetch_object($query_01);
                    $count = mysqli_num_rows($query_01);
                    
                    // جلب خطة العضوية من جدول membership_plan
                    $plan_key = $plan_detail->s_key ?? '';
                    $qq = 'SELECT * FROM membership_plan WHERE s_key = "' . mysqli_real_escape_string($con, $plan_key) . '"';
                    $ww = mysqli_query($con, $qq);
                    $membership_plan = mysqli_fetch_object($ww);
                    
                    // جلب تواريخ الخطة
                    $sql_dates = "SELECT * FROM plan_member_id, business_profile 
                                  WHERE b_id = bnsprof_id AND bnsprof_uid = '{$uid}'";
                    $query_dates = mysqli_query($con, $sql_dates);
                    $plan_dates = mysqli_fetch_object($query_dates);
                    
                    $start_date = $plan_dates->start_date ?? time();
                    $expiry_date = $plan_dates->expiry_date ?? time();
                    
                    $start_date_formatted = date("Y-m-d", (int)$start_date);
                    $expiry_date_formatted = date("Y-m-d", (int)$expiry_date);
                    
                    // تحديد الرابط بناءً على الخطة
                    $link = ($count == 0 || ($plan_detail->mst_name ?? '') == 'JUNIOR') 
                            ? 'membership_plans.php' 
                            : 'product-add.php';
                    
                    // إنشاء معرف فريد للشركة
                    $cid = rand(1000, 9999) . md5((string)($row->bnsprof_id ?? ''));
                    ?>

                    <!-- قسم خطة الاشتراك -->
                    <div class="f1 nd2 cnfl" style="width:37.4%">
                        <h2 style="border-bottom:1px solid #D2D2D2;" title="My Current Plan">خطط إشتراكى بالمنصة</h2>
                        <div class="p5 lh cfc">
                            <div class="oi oinpr" style="padding-bottom:80px;">
                                <style>
                                .oinpr li {
                                    padding-right: 0px;
                                }
                                </style>

                                <div class="fl" style="width:62%">
                                    <ul>
                                        <li class="fw f1">التاريخ</li>
                                        <li class="fw">:</li>
                                        <li><?php echo date('d-M-Y H:i T'); ?></li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">خطة عضويتى</li>
                                        <li class="fw">:</li>
                                        <li>
                                            <?php 
                                            if (isset($plan_detail->mst_name)) {
                                                echo htmlspecialchars($plan_detail->mst_name);
                                            } else {
                                                echo "غير متوفر";
                                            }
                                            ?>
                                        </li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">بداية الخطة</li>
                                        <li class="fw">:</li>
                                        <li><?php echo htmlspecialchars($start_date_formatted); ?></li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">نهاية الخطة</li>
                                        <li class="fw">:</li>
                                        <li><?php echo htmlspecialchars($expiry_date_formatted); ?></li>
                                    </ul>
                                </div>
                                
                                <div class="fl" style="text-align:right; width:38%">
                                    <ul>
                                        <li class="fw f1">القيمة السنوية</li>
                                        <li class="fw">:</li>
                                        <li><?php echo number_format((float)($membership_plan->mp_amount ?? 0), 2); ?></li>
                                    </ul>
                                    <ul>
                                        <li class="fw f1">القيمة المدفوعة</li>
                                        <li class="fw">:</li>
                                        <li>
                                            <?php 
                                            if (($membership_plan->s_key ?? 0) == 1 || ($membership_plan->s_key ?? 0) == 0) {
                                                echo "0.00";
                                            } else {
                                                echo number_format((float)($membership_plan->mp_amount ?? 0), 2); ?>
                                                <br><br>
                                                <a href="membership_plans.php" target="_blank" class="prur fw mt3" 
                                                   style="color:#ff0000;">شاهد جميع الخطط</a>
                                            <?php } ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قسم الموقع المصغر -->
                    <div class="f1 nd2 ebh3">
                        <div class="f1" style="width: 100%">
                            <div class="demw c3">
                                <h2 class="pro_tle">
                                    <a href="membership_plans.php" target="_blank" 
                                       title="My Company Mini-Site">
                                        الموقع المصغر لشركتى
                                        <span style="color:rgb(0, 0, 255);font-weight:normal;font-size:9px;">
                                            شاهد خطط الاشتراك
                                        </span>
                                    </a>
                                </h2>
                                <br>
                                
                                <?php if ($count == 0 || ($plan_detail->mst_name ?? '') == 'JUNIOR'): ?>
                                    <div class="in2 p5">
                                        <span class="inic dbg bnr f1"></span>
                                        <span style="display: block;" id="wap">
                                            <p>!لايوجد موقع مصغر للشركة تم إنشاؤه عن طريقك</p>
                                        </span>
                                        <div align="center" class="mt-5" style="margin-top:20px;">
                                            <a href="membership_plans.php" class="prur fw" onMouseOver="amp();">
                                                إنشىء موقعك المصغر للتجارة
                                            </a>
                                        </div>
                                        <div class="c3"></div>
                                    </div>
                                <?php endif; ?>

                                <?php if (($row->user_type ?? 0) > 1 && !empty($row->bnsprof_compname ?? '')): ?>
                                    <h2 style="border-bottom:1px solid #D2D2D2;">
                                        <a href="company/products.php?c=<?php echo urlencode($cid); ?>" target="_blank" 
                                           class="txt-yellow" style="color:#03bf00;font-weight:800;" 
                                           title="My Mini-Site">
                                            هنا موقع شركتك المصغر للمعاينة
                                        </a>
                                    </h2>
                                    <br>
                                    <a href="company/index.php?c=<?php echo urlencode($cid); ?>" 
                                       class="fwl txt-blue" style="line-height: 20px;padding-left: 10px;" target="_blank">
                                        <?php echo $_SERVER['HTTP_HOST'] ?? ''; ?>/company/index.php?c=<?php echo urlencode($cid); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <br><br>
                                <div class="c3"></div>
                            </div>
                            <div class="c3 mt5"></div>
                        </div>
                    </div>
                </div>
                
                <div class="clearfix"></div>

                <!-- القسم الرئيسي -->
                <div class="col-md-10 my_acc_main">
                    <!-- تفاصيل الاتصال -->
                    <div class="f1 nd2 cnfl">
                        <h2 style="border-bottom:1px solid #D2D2D2;">تفاصيل الإتصال بشركتى</h2>
                        <div class="p5 lh cfc">
                            <div>
                                <a href="my-contactdetails.php" class="f2 f11 ded" style="margin-left:5px">
                                    <span class="dbg bnr">عـدل</span>
                                </a>
                                <div class="c3"></div>
                            </div>

                            <strong class="txt-blue"><?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?></strong>
                            <br>
                            <?php 
                            echo htmlspecialchars(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? ''));
                            ?><br>
                            
                            <?php 
                            $address_parts = [];
                            if (!empty($row->address)) {
                                $address_parts[] = $row->address;
                            }
                            if (!empty($row->bnsprof_city) && $row->bnsprof_city != '0') {
                                $address_parts[] = get_city_name((int)$row->bnsprof_city);
                            }
                            if (!empty($row->bnsprof_state) && $row->bnsprof_state != '0') {
                                $address_parts[] = get_state_name((int)$row->bnsprof_state);
                            }
                            $address_parts[] = get_country_name((int)($row->country ?? 0));
                            echo htmlspecialchars(implode(', ', array_filter($address_parts)));
                            ?>
                            
                            <div class="mt12"></div>
                            
                            <div class="oi">
                                <ul>
                                    <?php
                                    $usr_oauth_reg = (int)($row->usr_oauth_reg ?? 0);
                                    if ($usr_oauth_reg == 0):
                                    ?>
                                        <li class="fw f1 lem">Email</li>
                                        <li class="fw">:</li>
                                        <li class="txt-blue"><?php echo htmlspecialchars($row->email ?? ''); ?>
                                            <?php if ((int)getEmailVerificationStatus() == 1): ?>
                                                <?php if (($row->usr_emailVerify ?? '') == '1'): ?>
                                                    <span class="ml8 f11 bnr dbg mo_ver">Verified</span>
                                                <?php else: ?>
                                                    &nbsp;<a href="sendVerifyLink.php" style="color: #F00">حقق بريدك الآن</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php elseif ($usr_oauth_reg == 1): ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/facebook_logo.jpg" alt="Facebook" /></li>
                                    <?php elseif ($usr_oauth_reg == 2): ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/gmail_.png" alt="Gmail" /></li>
                                    <?php elseif ($usr_oauth_reg == 3): ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/twtBrd.jpg" alt="Twitter" /></li>
                                    <?php elseif ($usr_oauth_reg == 4): ?>
                                        <li><strong>Logged In With</strong></li>
                                        <li><strong>:</strong></li>
                                        <li><img src="social_media_images/linkedinLog.png" alt="LinkedIn" /></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <?php if (!empty($row->mobile1 ?? '')): ?>
                                <div class="oi">
                                    <ul>
                                        <li class="fw f1 lem">Mobile</li>
                                        <li class="fw">:</li>
                                        <li><?php echo htmlspecialchars(($row->country_ph_code ?? '') . '-' . ($row->mobile1 ?? '')); ?></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_GET['verifySucces']) && $_GET['verifySucces'] == '1'): ?>
                                <div id="conf" class="emcr mt12 dbg bnr f11" style="display: block;">
                                    تم التحقق من بريدك الالكترونى بنجاح
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_GET['verifylinksend']) && $_GET['verifylinksend'] == '1'): ?>
                                <div style="display:block" id="alem">
                                    <div style="display:block" id="conf2" class="emcr mt12 dbg bnr f11">
                                        قمنا بإرسال رسالة تحقق الى بريدك .. رجاء الذهاب الى بريدك وفتح الرسالة للتحقق
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- المنتجات والخدمات -->
                    <div class="f1 nd2 ebh3">
                        <?php 
                        $prd_num = mysqli_num_rows(mysqli_query($con, 
                            "SELECT * FROM products, measurement_unit, country 
                             WHERE mu_id = pd_unit AND pd_currency = cn_id 
                             AND pd_status = '1' AND pd_uid = '{$uid}'"
                        ));
                        ?>
                        
                        <h2 class="pro_tle">
                            <a href="product-list.php">
                                منتجاتى وخدماتى المنشورة (<?php echo (int)$prd_num; ?>)
                                <span style="color:rgb(0, 0, 255);font-weight:normal;font-size:12px;">شاهد الجميع</span>
                            </a>
                        </h2>

                        <a href="<?php echo htmlspecialchars($link); ?>" class="f2" 
                           style="margin:-21px 8px 0px 0px;text-decoration:none; color:#ed0001; font-weight:bold">
                            أضف منتج
                        </a>

                        <?php 
                        $prd_res = mysqli_query($con, 
                            "SELECT * FROM products, measurement_unit, country 
                             WHERE mu_id = pd_unit AND pd_currency = cn_id AND pd_uid = '{$uid}' 
                             ORDER BY RAND() LIMIT 3"
                        );
                        
                        if (mysqli_num_rows($prd_res) > 0):
                            while ($prd_row = mysqli_fetch_object($prd_res)):
                        ?>
                                <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
                                    <p class="colr-n" style="margin-top:-5px;">
                                        <strong><?php echo htmlspecialchars(ucwords(stripslashes($prd_row->pd_title ?? ''))); ?></strong>
                                    </p>
                                    <div class="rej_pro">
                                        <span>
                                            <p style="display:inline-block; padding-left:24px;float:left;*min-width:150px !important;"
                                               <?php if (($prd_row->pd_status ?? '') == '2') echo 'class="dbg rej_pro1"'; ?>>
                                                <?php if (($prd_row->pd_status ?? '') == '0'): ?>
                                                    <img src="images/pend_clock.png" width="11" height="11" />
                                                <?php endif; ?>
                                                <strong>
                                                    <?php 
                                                    if (($prd_row->pd_status ?? '') == '0') {
                                                        echo '<font color="#496703">Pending:</font>';
                                                    } elseif (($prd_row->pd_status ?? '') == '2') {
                                                        echo '<font color="red">Rejected:</font>';
                                                    }
                                                    ?>
                                                </strong>
                                            </p>
                                        </span>
                                        
                                        <?php if (($prd_row->pd_status ?? '') == '0'): ?>
                                            <span class="f2">
                                                <a href="product-edit.php?token=<?php echo rand(1000, 9999) . md5((string)($prd_row->pd_id ?? '')); ?>">
                                                    Edit Product
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <div style="clear:both"></div>
                                        
                                        <?php if (($prd_row->pd_status ?? '') == '1'): ?>
                                            <p class="colr-n mtlf">
                                                <span>This product <label style="color:green">is visible</label> in
                                                <?php echo htmlspecialchars(getWebSiteName() ?? ''); ?> Search.</span>
                                            </p>
                                            <p class="dbg" style="background-position:7px -340px;background-repeat:no-repeat;margin-left:20px">
                                                <a href="product-edit.php?token=<?php echo rand(1000, 9999) . md5((string)($prd_row->pd_id ?? '')); ?>"
                                                   style="margin-left:20px">Edit Product</a>
                                            </p>
                                        <?php else: ?>
                                            <p class="colr-n mtlf">
                                                <span>This product <label style="color:red">will not be visible</label> in
                                                <?php echo htmlspecialchars(getWebSiteName() ?? ''); ?> Search.</span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <br />
                            <div class="c3 h5 bb1">&nbsp;</div>
                            <div align="center">
                                <a href='<?php echo htmlspecialchars($link); ?>' class="prur fw mt3" onMouseOver="amp();">
                                    أضف منتج جديد
                                </a>
                            </div>
                            <div class="c3 mt12">&nbsp;</div>
                            <div class="in2 p5">
                                <span class="inic dbg bnr f1"></span>
                                <span style="display: block;" id="wap">
                                    <strong>لماذا تضيف المزيد من المنتجات ؟</strong>
                                    <p>إضافة مزيد من المنتجات تزيد من فرص الحصول على مزيد من الاستفسارات وطلبات الشراء</p>
                                </span>
                                <div class="c3"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- طلبات الشراء -->
                    <div class="f1 nd2 ebh3 nd5" id="buylead_top_1">
                        <h2>آخر طلبات الشراء</h2>
                        <div id="buylead_1" class="">
                            <a href="post-buy-req.php" class="f2"
                               style="margin:-21px 8px 0px 0px;text-decoration:none; color:#ed0001; font-weight:bold">
                            </a>

                            <?php 
                            $bl_res = mysqli_query($con, 
                                "SELECT * FROM buy_requirement, measurement_unit 
                                 WHERE br_estimate_qty_unit = mu_id 
                                 AND br_approval_status = '1' 
                                 AND br_display_status = '1' 
                                 AND br_u_id = '{$uid}' 
                                 ORDER BY br_updated_date DESC LIMIT 3"
                            );
                            
                            if (mysqli_num_rows($bl_res) > 0):
                                while ($bl_row = mysqli_fetch_object($bl_res)):
                            ?>
                                    <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
                                        <table width="100%">
                                            <tr>
                                                <td width="80%">
                                                    <p class="colr-n" style="margin-top:-5px;">
                                                        <strong>
                                                            <a href="buyleads-details.php?id=<?php echo rand(1000, 9999) . md5((string)($bl_row->br_id ?? '')); ?>">
                                                                <?php echo htmlspecialchars(ucwords(stripslashes($bl_row->br_pd_name ?? ''))); ?>
                                                            </a>
                                                        </strong>
                                                    </p>
                                                </td>
                                                <td style="text-align:right; color:#999; font-size:10px">
                                                    <?php echo date('d M, Y', strtotime($bl_row->br_updated_date ?? 'now')); ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="rej_pro">
                                            <p class="colr-n mtlf">
                                                <span>
                                                    <?php 
                                                    $requirement = $bl_row->br_requirement ?? '';
                                                    echo htmlspecialchars(substr($requirement, 0, 100));
                                                    if (strlen($requirement) > 100):
                                                    ?>
                                                        <a href="buyleads-details.php?id=<?php echo rand(1000, 9999) . md5((string)($bl_row->br_id ?? '')); ?>">
                                                            more...
                                                        </a>
                                                    <?php endif; ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <div class="p5 bb1 txc mt12">لايوجد طلبات شراء</div>
                                <div class="in2 p5 mt12">
                                    <span class="inic dbg bnr f1"></span>
                                    <span><strong>!لايوجد طلبات شراء جديدة تم نشرها</strong></span>
                                    <ul>
                                        <li class="ltm">&nbsp;</li>
                                        <li class="ltm">&nbsp;</li>
                                        <li class="ltm">
                                            <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span>
                                            <a style="color:#19528E; font-weight:bold; text-decoration:none;" 
                                               href="subscription.php">شراء كريديت</a>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span>
                                            <a style="color:#ed0001; font-weight:bold; text-decoration:none;" 
                                               href="post-buy-req.php">أنشر طلب شراء</a>
                                        </li>
                                        <li class="ltm">
                                            <span style="color:#19528E; font-weight:bold;">&bull;&nbsp;&nbsp;</span>
                                            <a style="color:#19528E; font-weight:bold; text-decoration:none;" 
                                               href="buyleads.php">طلبات شراء</a>
                                        </li>
                                    </ul>
                                    <div class="c3 h5"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <script>
                        setTimeout("LatestBuyLeads_free('')", 100);
                        </script>
                    </div>

                    <!-- عروض البيع -->
                    <div class="f1 nd2 ebh3">
                        <h2>عروض بيعى الخاصة</h2>
                        
                        <?php 
                        $so_res = mysqli_query($con, 
                            "SELECT * FROM sale_offer 
                             WHERE so_usr_id = '{$uid}' 
                             ORDER BY so_updated_date DESC LIMIT 3"
                        );
                        
                        if (mysqli_num_rows($so_res) > 0):
                            while ($so_row = mysqli_fetch_object($so_res)):
                        ?>
                                <div class="dbg ppad pdsc" style="background-position:13px -339px;background-repeat:no-repeat;height:60px;">
                                    <table width="100%">
                                        <tr>
                                            <td width="80%">
                                                <p class="colr-n" style="margin-top:-5px;">
                                                    <strong>
                                                        <a href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5((string)($so_row->so_id ?? '')); ?>">
                                                            <?php echo htmlspecialchars(ucwords(stripslashes($so_row->so_service ?? ''))); ?>
                                                        </a>
                                                    </strong>
                                                </p>
                                            </td>
                                            <td style="text-align:right; color:#999; font-size:10px">
                                                <?php echo date('d M, Y', strtotime($so_row->so_updated_date ?? 'now')); ?>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="rej_pro">
                                        <span>
                                            <p style="display:inline-block; padding-left:24px;float:left;*min-width:150px !important;"
                                               <?php if (($so_row->so_approval_status ?? '') == '2') echo 'class="dbg rej_pro1"'; ?>>
                                                <?php if (($so_row->so_approval_status ?? '') == '0'): ?>
                                                    <img src="images/pend_clock.png" width="11" height="11" />
                                                <?php endif; ?>
                                                <strong>
                                                    <?php 
                                                    if (($so_row->so_approval_status ?? '') == '0') {
                                                        echo '<font color="#496703">Pending:</font>';
                                                    } elseif (($so_row->so_approval_status ?? '') == '2') {
                                                        echo '<font color="red">Rejected:</font>';
                                                    }
                                                    ?>
                                                </strong>
                                            </p>
                                        </span>
                                        <div style="clear:both"></div>
                                        <p class="colr-n mtlf">
                                            <span>
                                                <?php 
                                                $desc = $so_row->so_description ?? '';
                                                echo htmlspecialchars(substr($desc, 0, 100));
                                                if (strlen($desc) > 100):
                                                ?>
                                                    <a href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5((string)($so_row->so_id ?? '')); ?>">
                                                        more...
                                                    </a>
                                                <?php endif; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
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
                                <a href="post-sell-offer.php" class="prur f1 mt12 fw mt4">
                                    أنشر عروض بيع خاصة
                                </a>
                                <div class="c3"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- الرسائل -->
                    <div class="mt5"></div>
                    <div class="f1 nd2 ebh3">
                        <a name="enquiries"></a>
                        <?php
                        $sql_enq = "SELECT * FROM message, user 
                                    WHERE msg_to = '{$uid}' 
                                    AND msg_from = usr_id 
                                    AND msg_to_status = '1' 
                                    ORDER BY msg_date DESC LIMIT 4";
                        $res_enq = mysqli_query($con, $sql_enq);
                        $num_enq = mysqli_num_rows($res_enq);
                        ?>
                        <h2>رسائل بريدى (<?php echo (int)$num_enq; ?>)</h2>
                        
                        <?php while ($row_enq = mysqli_fetch_object($res_enq)): ?>
                            <div class="sms bnr">
                                <a href="my-enquiries.php?ii=<?php echo (int)($row_enq->msg_id ?? 0); ?>&tp=in"
                                   onClick="return track(this,'MyDashboard','EnqView','');">
                                    <strong>
                                        <?php 
                                        echo htmlspecialchars(
                                            ($row_enq->name_prefix ?? '') . ' ' . 
                                            ucfirst($row_enq->fname ?? '') . ' ' . 
                                            ucfirst($row_enq->lname ?? '')
                                        ); 
                                        ?>
                                    </strong><br>
                                    <?php 
                                    if (!empty($row->msg_subject ?? '')) {
                                        echo htmlspecialchars(stripslashes($row->msg_subject));
                                    } else {
                                        echo "No Subject";
                                    }
                                    ?>
                                </a>
                            </div>
                        <?php endwhile; ?>
                        
                        <a href="my-enquiries.php" class="sall f1 mt5">إظهار كل الرسائل</a>
                    </div>

                    <!-- سجل الدفوعات -->
                    <div class="f1 nd2 cnfl" style="padding-bottom:5px">
                        <a name="alerts"></a>
                        <h2 style="border-bottom:1px solid #D2D2D2;">
                            قائمة الدفوعات
                            <a href="transaction_history.php">
                                <span style="color:rgb(0, 0, 255);font-weight:normal;font-size:12px;">مشاهدة الجميع</span>
                            </a>
                        </h2>
                        
                        <table border="0" cellpadding="2" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="25%">Date</th>
                                    <th width="50%">Details</th>
                                    <th width="25%">Purchase/Use</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $bh_res = mysqli_query($con, 
                                    "SELECT * FROM billing_history 
                                     WHERE bh_status = '1' 
                                     AND bh_usr_id = '{$uid}' 
                                     ORDER BY bh_updated_date DESC LIMIT 5"
                                );
                                
                                if (mysqli_num_rows($bh_res) > 0):
                                    while ($bh_row = mysqli_fetch_object($bh_res)):
                                        $bh_type = (int)($bh_row->bh_type ?? 0);
                                        $details = '';
                                        if ($bh_type == 1) {
                                            $details = 'Credit Purchased';
                                        } elseif ($bh_type == 2) {
                                            $details = 'Credit Used For buy Leads';
                                        } elseif ($bh_type == 3) {
                                            $details = 'Credit Used For Tender';
                                        } elseif ($bh_type == 4) {
                                            $details = 'Credit Used For Auction';
                                        }
                                ?>
                                        <tr>
                                            <td style="vertical-align:middle">
                                                <?php echo date('d M, y', strtotime($bh_row->bh_updated_date ?? 'now')); ?>
                                            </td>
                                            <td style="vertical-align:middle"><?php echo htmlspecialchars($details); ?></td>
                                            <td style="vertical-align:middle; text-align:right">
                                                <?php 
                                                if ($bh_type == 1) {
                                                    echo (float)($bh_row->bh_credit_purchased ?? 0);
                                                } elseif (in_array($bh_type, [2, 3, 4])) {
                                                    echo (float)($bh_row->bh_credit_used ?? 0);
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                        <tr>
                                            <td colspan="3" style="text-align:center"><strong>!لايوجد مدفوعات</strong></td>
                                        </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

                <!-- الشريط الجانبي -->
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
                        #rnav { border-left: none; }
                        </style>

                        <div class="vem2 vem mt12 c3">
                            <h2>معلومات متحقق منها</h2>

                            <?php if (isset($_GET['verifySucces']) && $_GET['verifySucces'] == '1'): ?>
                                <div id="conf" class="emcr mt12 dbg bnr f11" style="display: block;">
                                    تم التحقق من بريدك الالكترونى بنجاح
                                </div>
                            <?php endif; ?>

                            <div id="veri_prim" style="display:block" class="mt12 f11">
                                <?php
                                $usr_oauth_reg = (int)($row->usr_oauth_reg ?? 0);
                                if ($usr_oauth_reg == 0):
                                    echo "<strong>Email:</strong><br>" . htmlspecialchars($row->email ?? '');
                                elseif ($usr_oauth_reg == 1):
                                    echo '<strong>Logged In With:</strong><br><img src="social_media_images/facebook_logo.jpg" alt="Facebook" />';
                                elseif ($usr_oauth_reg == 2):
                                    echo '<strong>Logged In With:</strong><br><img src="social_media_images/gmail_.png" alt="Gmail" />';
                                elseif ($usr_oauth_reg == 3):
                                    echo '<strong>Logged In With:</strong><br><img src="social_media_images/twtBrd.jpg" alt="Twitter" />';
                                elseif ($usr_oauth_reg == 4):
                                    echo '<strong>Logged In With:</strong><br><img src="social_media_images/linkedinLog.png" alt="LinkedIn" />';
                                endif;
                                ?>
                                <br>

                                <?php if ((int)getEmailVerificationStatus() == 1): ?>
                                    <?php if (($row->usr_emailVerify ?? '') == '1'): ?>
                                        <div class="f11 bnr dbg mo_ver mt5">Verified</div>
                                    <?php else: ?>
                                        <a href="sendVerifyLink.php">تحقق الآن</a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="c3"></div>
                            </div>

                            <?php if (!empty($row->mobile1 ?? '')): ?>
                                <div class="mt12 f11">
                                    <strong>Mobile:</strong><br>
                                    <?php echo htmlspecialchars(($row->country_ph_code ?? '') . '-' . ($row->mobile1 ?? '')); ?><br>
                                    <div class="c3"></div>
                                </div>
                            <?php endif; ?>

                            <div id="alh" class="pout mt12"></div>

                            <?php if (isset($_GET['verifylinksend']) && $_GET['verifylinksend'] == '1'): ?>
                                <div style="display:block" id="alem">
                                    <div style="display:block" id="conf2" class="emcr mt12 dbg bnr f11">
                                        قمنا بإرسال رسالة تحقق الى بريدك .. رجاء الذهاب الى بريدك وفتح الرسالة للتحقق
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="c3"></div>
                        </div>

                        <div class="c3">&nbsp;</div>
                        <div>&nbsp;</div>
                        <a href="post-buy-req.php" onClick="return track(this,'MyDashboard','BuyReqBannerRight','');">
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
</body>
</html>