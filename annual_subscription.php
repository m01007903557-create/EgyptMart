<?php
/**
 * File: annual_subscription.php

 * Version: PHP 8.3
 * Description: صفحة اشتراك العضوية - عرض تفاصيل الخطة وخيارات الدفع
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// تعيين الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "subscription.php";

// التحقق من وجود مستخدم مسجل دخوله
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit();
}

// تنظيف معرف المستخدم
$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// معالجة المعرفات من URL
$id = isset($_GET['id']) ? substr(trim($_GET['id']), 5) : '';
$term = isset($_GET['term']) ? substr(trim($_GET['term']), 5) : '';

// إذا لم يتم تحديد مدة، استخدم 12 شهراً كافتراضي
if (empty($term)) {
    $term = md5(12);
}

// جلب بيانات خطة العضوية
$sql = "SELECT * FROM smembership_plan_arabyos WHERE MD5(mp_id) = '" . mysqli_real_escape_string($con, $id) . "' LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die('خطأ: خطة العضوية غير موجودة');
}

$row = mysqli_fetch_object($res);
$plan_id = (int)$row->mp_id;

// حساب المبلغ حسب المدة المختارة
$term_str = 'Annual';
$amount = (float)$row->mp_amount;

if (md5(1) == $term) {
    $term_str = '1 Month';
    $amount = $row->mp_amount / 12;
} else if (md5(3) == $term) {
    $term_str = '3 Months';
    $amount = $row->mp_amount / 4;
} else if (md5(6) == $term) {
    $term_str = '6 Months';
    $amount = $row->mp_amount / 2;
}

// حساب الضريبة والإجمالي
$tax_rate = 10; // 10% ضريبة خدمة
$tax_amount = ($amount * $tax_rate) / 100;
$total_amount = $amount + $tax_amount;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <!-- css start -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/credit_subs01.css" type="text/css" rel="stylesheet">
    <link href="css/AutoSuggestBox.css" type="text/css" rel="stylesheet">

    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->

    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#subscription_term').change(function() {
            var term = $(this).val();
            if (document.URL.indexOf('&term') > 0) {
                var url = document.URL.split("&");
                window.location.href = url[0] + "&term=" + term;
            } else {
                window.location.href = document.URL + "&term=" + term;
            }
        });
    });
    </script>
    
    <style type="text/css">
        .ui-widget-content {
            background: none repeat-x scroll 50% 50% #FFFFFF;
            border: 1px solid #AAAAAA;
            color: #222222;
            padding: 0 0 2px;
        }
        .ui-menu {
            display: block;
            float: left;
            list-style: none outside none;
            margin: 0;
        }
        .ui-menu .ui-menu-item {
            background-color: #FFFFFF;
            cursor: pointer;
            list-style-type: none;
        }
        .ui-menu .ui-menu-item a {
            width: auto !important;
            color: #000000;
            cursor: pointer;
            display: block;
            font-family: arial;
            font-size: 14px;
            font-weight: normal;
            list-style-type: none;
            padding: 1px 4px;
            text-decoration: none;
            cursor: pointer;
        }
        .ui-menu .ui-menu-item a.ui-state-hover,
        .ui-menu .ui-menu-item a.ui-state-active {
            background: none repeat scroll 0 0 #0095F9;
            color: #FFFFFF;
        }
        .ui-menu .ui-placeholder-input {
            margin-left: 0px;
            margin-top: 0px;
            color: #cccccc;
        }
        .labelContdet {
            width: 100px;
            color: #313131;
            padding-top: 10px;
            font-weight: bold;
            font-size: 12px;
        }
        .m_tmt {
            margin: 15px 0 0;
        }
        .redirect {
            color: #0000ff;
            text-decoration: none;
        }
        .redirect:hover {
            color: #ff0000;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="imgtrailer" style="position:absolute; z-index:4; visibility:hidden;">
        <img src="images/loading.gif" height="32" width="32" alt="Loading...">
    </div>

    <!--main div:start-->
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header start Here::-->
        <?php 
        if (file_exists("includes/header_new.php")) {
            include "includes/header_new.php"; 
        }
        ?>
        
        <br>
        <div class="bt">
            <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1">
        </div>
        <!-- Header End Here::-->

        <?php
        // جلب معلومات المستخدم والشركة
        $sql_usr = "SELECT * FROM user, business_profile 
                    WHERE usr_id = bnsprof_uid 
                      AND usr_id = {$uid} 
                      AND bnsprof_status = '1' 
                    LIMIT 1";
        $res_usr = mysqli_query($con, $sql_usr);
        $row_usr = $res_usr ? mysqli_fetch_object($res_usr) : null;
        ?>

        <!-- ملخص الطلب -->
        <div class="m_pkgdtl">
            <h2>ملخص الطلب</h2>
            <div class="m_pkgdtl-inner">
                <table class="m_tmt" border="0">
                    <tbody>
                        <tr>
                            <td class="m_pkgdetails">
                                <?php echo htmlspecialchars($row->mst_name ?? ''); ?> - <?php echo $term_str; ?> اشتراك
                            </td>
                            <td class="m_pkgdetails" width="10">:</td>
                            <td class="m_pkgdetails m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol()); ?></span> 
                                <?php echo number_format($amount, 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="m_stax">ضريبة الخدمة (<?php echo $tax_rate; ?>%)</td>
                            <td class="m_stax" width="10">:</td>
                            <td class="m_stax m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol()); ?></span> 
                                <?php echo number_format($tax_amount, 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="m_pricefnl">الإجمالي المستحق</td>
                            <td class="m_pricefnl" width="10">:</td>
                            <td class="m_pricefnl m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol()); ?>&nbsp;</span> 
                                <?php echo number_format($total_amount, 2); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" id="usr" name="usr" value="<?php echo $uid; ?>" />
        <input type="hidden" id="mp" name="mp" value="<?php echo (int)$row->mp_id; ?>" />

        <!-- خطوات الاشتراك -->
        <div class="m_ca2ndstep cpa1">
            <div class="m_crtop">
                <div class="m_fst1s cpa1 m_crtophd">
                    <p class="m_spimg m_1nons cpa1"></p>
                    اختر خطة الاشتراك
                </div>
                <p class="m_spimg m_arwmiddle cpa1"></p>
                <div class="m_fst2s cpa1 m_crtophds">
                    <p class="m_spimg m_2s cpa1"></p>
                    اختر طريقة الدفع
                </div>
            </div>

            <div class="m_paymentgateway" style="text-align:center; padding-top:10px;">
                <?php 
                if (file_exists("paymentgateway/api_annual_subscription.php")) {
                    include "paymentgateway/api_annual_subscription.php";
                }
                ?>
            </div>
        </div>

        <!-- اختيار مدة الاشتراك -->
        <div class="m_contactdetail" style="display:block;">
            <h3>اختر مدة الاشتراك:</h3>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td>مدة الاشتراك:</td>
                        <td>
                            <select name="subscription_term" id="subscription_term">
                                <?php
                                $terms = [
                                    1 => '1 month - $' . number_format($row->mp_amount / 12, 2),
                                    3 => '3 months - $' . number_format($row->mp_amount / 4, 2),
                                    6 => '6 months - $' . number_format($row->mp_amount / 2, 2),
                                    12 => '12 months - $' . number_format($row->mp_amount, 2)
                                ];
                                
                                foreach ($terms as $months => $label) {
                                    $term_value = rand(10000, 99999) . md5($months);
                                    $selected = (md5($months) == $term) ? 'selected' : '';
                                    echo "<option value=\"{$term_value}\" {$selected}>{$label}</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:20px;"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- معلومات التحويل البنكي -->
        <div class="m_contactdetail" style="display:block;">
            <h3>اختر التحويل البنكي (موصى به) - تفاصيل حسابنا البنكي:</h3>
            <table border="0" cellpadding="0" cellspacing="0" width="70%">
                <tbody>
                    <tr>
                        <td>اسم البنك :</td>
                        <td><b>National Ahli Bank</b></td>
                    </tr>
                    <tr>
                        <td>رقم الحساب :</td>
                        <td><b>0000000 000000 000000</b></td>
                    </tr>
                    <tr>
                        <td>رمز Swift :</td>
                        <td><b>0000 0000 0000</b></td>
                    </tr>
                    <tr>
                        <td>اسم الشركة:</td>
                        <td><b>Under License According to Published New Procedures</b></td>
                    </tr>
                    <tr>
                        <td>العنوان:</td>
                        <td><b>Nasr City - Cairo - Egypt</b></td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:20px;"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- معلومات الاتصال -->
        <div class="m_contactdetail" id="kelly" style="display:block;">
            <h3>معلومات الاتصال:</h3>
            
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td class="m_formtxt" valign="top" width="75">
                                            الاسم:
                                        </td>
                                        <td>
                                            <select name="salute" size="1" style="width:43px; padding:3px 0px; height:25px; margin:0px;" class="m_forminput" tabindex="1" readonly="readonly">
                                                <option value="Mr." <?php echo (user_info($uid, 'name_prefix') == "Mr.") ? 'selected' : ''; ?>>Mr.</option>
                                                <option value="Ms." <?php echo (user_info($uid, 'name_prefix') == "Ms.") ? 'selected' : ''; ?>>Ms.</option>
                                                <option value="Mrs." <?php echo (user_info($uid, 'name_prefix') == "Mrs.") ? 'selected' : ''; ?>>Mrs.</option>
                                                <option value="Dr." <?php echo (user_info($uid, 'name_prefix') == "Dr.") ? 'selected' : ''; ?>>Dr.</option>
                                            </select>
                                            <input name="first_name" size="10" style="width:177px;" class="m_forminput" 
                                                   value="<?php echo htmlspecialchars(ucfirst(user_info($uid, 'fname')) . ' ' . ucfirst(user_info($uid, 'lname'))); ?>" 
                                                   tabindex="2" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="m_formtxt" valign="top">
                                            البلد:
                                        </td>
                                        <td>
                                            <input name="country_name" id="country_name" value="<?php echo htmlspecialchars(get_country_name($row_usr->country ?? 0)); ?>" 
                                                   size="40" style="width:223px;" class="m_forminput" tabindex="4" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="m_formtxt" valign="top">
                                            العنوان:
                                        </td>
                                        <td>
                                            <input name="add1" value="<?php 
                                                $address = '';
                                                if (!empty($row_usr->bnsprof_address1)) {
                                                    $address .= $row_usr->bnsprof_address1;
                                                }
                                                if (!empty($row_usr->bnsprof_address2)) {
                                                    $address .= (!empty($address) ? ', ' : '') . $row_usr->bnsprof_address2;
                                                }
                                                echo htmlspecialchars($address);
                                            ?>" size="40" style="width:223px;" class="m_forminput" tabindex="8" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td class="m_formtxt" valign="top">
                                            البريد الإلكتروني:
                                        </td>
                                        <td>
                                            <input name="email" value="<?php echo htmlspecialchars(user_info($uid, 'email')); ?>" 
                                                   size="40" style="width:243px;" class="m_forminput" tabindex="3" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="m_formtxt1" valign="top">
                                            الموقع:
                                        </td>
                                        <td>
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td class="cpcart-nn">المدينة</td>
                                                        <td class="cpcart-nn">الولاية</td>
                                                        <td class="cpcart-nn">الرمز البريدي</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input name="txtCity" size="11" id="txtCity" value="<?php echo htmlspecialchars(get_city_name($row_usr->bnsprof_city ?? 0)); ?>" 
                                                                   tabindex="5" class="m_forminput" type="text" readonly="readonly"/>
                                                        </td>
                                                        <td>
                                                            <input name="txtState" size="11" id="txtState" value="<?php echo htmlspecialchars(get_state_name($row_usr->bnsprof_state ?? 0)); ?>" 
                                                                   style="width:90px; margin-right:3px;" tabindex="6" class="m_forminput" type="text" readonly="readonly"/>
                                                        </td>
                                                        <td>
                                                            <input name="zip" size="12" style="width:51px;" class="m_forminput" 
                                                                   tabindex="7" type="text" value="<?php echo htmlspecialchars($row_usr->bnsprof_zipcode ?? ''); ?>" readonly="readonly"/>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="m_formtxt" valign="top">
                                            الجوال:
                                        </td>
                                        <td>
                                            <input name="S_cmobile" id="S_cmobile" value="<?php echo htmlspecialchars(get_country_phn_code($row_usr->country ?? 0)); ?>" 
                                                   size="4" style="width:36px; background-color:#f1f1f1;" class="m_forminput" readonly="readonly" tabindex="9" type="text"/>
                                            <input style="width:198px;" id="mobile" name="mobile" value="<?php echo htmlspecialchars($row_usr->mobile1 ?? ''); ?>" 
                                                   size="25" maxlength="16" class="m_forminput" tabindex="10" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="cpm2"><!-- clear:both --></div>
        <img src="images/z_002.gif" height="10" width="1" alt="">
        <div class="cpm2"><!-- clear:both --></div>
        <!-- payment page End here -->
    </div>
    
    <div class="c3">&nbsp;</div>
    
    <!--footer:start-->
    <?php 
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>