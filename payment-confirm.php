<?php
// payment-confirm.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "common.php";
require_once __DIR__ . '/lib/function.php';// التحقق من وجود المعاملات المطلوبة
if (!isset($_GET['m']) || !isset($_GET['p'])) {
    $redirect_url = $_SESSION['last_page'] ?? 'membership_plans.php';
    header("Location: " . $redirect_url);
    exit;
}

// استخراج المعرفات المشفرة
$mp = substr($_GET['m'] ?? '', 5);
$pg = substr($_GET['p'] ?? '', 5);

if (empty($mp) || empty($pg)) {
    header("Location: membership_plans.php");
    exit;
}

$mp = mysqli_real_escape_string($con, $mp);
$pg = mysqli_real_escape_string($con, $pg);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// جلب بيانات الخطة
$sql = "SELECT * FROM membership_plan WHERE md5(mp_id) = '{$mp}'";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("Location: membership_plans.php");
    exit;
}

$row = mysqli_fetch_object($res);
$plan = $row->mp_name ?? '';
$amount = (float)($row->mp_amount ?? 0);
$credits = (int)($row->mp_credits ?? 0);
$plan_id = (int)($row->mp_id ?? 0);

// جلب بيانات المستخدم
$sql_usr = "SELECT * FROM user, business_profile 
            WHERE usr_id = bnsprof_uid AND usr_id = '{$uid}' AND bnsprof_status = '1'";
$res_usr = mysqli_query($con, $sql_usr);
$row_usr = mysqli_fetch_object($res_usr);
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/credit_subs01.css" type="text/css" rel="stylesheet">
    <link href="css/AutoSuggestBox.css" type="text/css" rel="stylesheet">
    
    <style>
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
    
    <script src="js/jquery-1.2.1.min.js"></script>
</head>

<body>
    <div id="imgtrailer" style="position:absolute; z-index:4;visibility:hidden;">
        <img src="images/loading.gif" height="32" width="32">
    </div>

    <div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        
        <br><br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1"></div>

        <div class="m_pkgdtl">
            <h2>ملخص قيمة الإشتراك</h2>
            <div class="m_pkgdtl-inner">
                <table class="m_tmt" border="0">
                    <tbody>
                        <tr>
                            <td class="m_pkgdetails"><?php echo htmlspecialchars($plan); ?> - <?php echo (int)$credits; ?> Credits</td>
                            <td class="m_pkgdetails" width="10">:</td>
                            <td class="m_pkgdetails m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol() ?? ''); ?></span> 
                                <?php echo number_format($amount, 2); ?>
                            </td>
                        </tr>
                        
                        <?php 
                        $tax_rate = (float)(getServiceTaxRate() ?? 0);
                        if ($tax_rate > 0): 
                            $tax_amount = ($amount * $tax_rate) / 100;
                        ?>
                            <tr>
                                <td class="m_stax">Service Tax (<?php echo number_format($tax_rate, 2); ?>%)</td>
                                <td class="m_stax" width="10">:</td>
                                <td class="m_stax m_tdpl">
                                    <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol() ?? ''); ?></span> 
                                    <?php echo number_format($tax_amount, 2); ?>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="m_pricefnl">قيمة السداد الإجمالية</td>
                                <td class="m_pricefnl" width="10">:</td>
                                <td class="m_pricefnl m_tdpl">
                                    <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol() ?? ''); ?>&nbsp;</span> 
                                    <?php $total = $amount + $tax_amount; echo number_format($total, 2); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td class="m_pricefnl">قيمة السداد الإجمالية</td>
                                <td class="m_pricefnl" width="10">:</td>
                                <td class="m_pricefnl m_tdpl">
                                    <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol() ?? ''); ?>&nbsp;</span> 
                                    <?php $total = $amount; echo number_format($total, 2); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" id="usr" name="usr" value="<?php echo $uid; ?>" />
        <input type="hidden" id="mp" name="mp" value="<?php echo (int)$plan_id; ?>" />

        <div class="m_ca2ndstep cpa1">
            <div class="m_crtop">
                <div class="m_fst2s cpa1 m_crtophd">
                    <p class="m_2s cpa1"></p>
                    تأكيد الدفع
                </div>
            </div>

            <div class="m_paymentgateway" style="text-align:center;padding-top:10px;">
                <?php
                $m_prefix = substr($_GET['m'] ?? '', 0, 5);
                if (is_numeric($m_prefix)) {
                    include "paymentgateway/api.php";
                } else {
                    include "paymentgateway/api_annual_subscription.php";
                }
                ?>
            </div>
        </div>

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
                                            <select name="salute" size="1" style="width:43px; padding:3px 0px; height:25px; margin:0px;" 
                                                    class="m_forminput" tabindex="1" readonly="readonly">
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
                                            الدولة:
                                        </td>
                                        <td>
                                            <input name="country_name" id="country_name" autocomplete="off" 
                                                   value="<?php echo htmlspecialchars(get_country_name((int)($row_usr->country ?? 0))); ?>" 
                                                   size="40" style="width:223px;" class="m_forminput" tabindex="4" 
                                                   type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="m_formtxt" valign="top">
                                            العنوان:
                                        </td>
                                        <td>
                                            <?php
                                            $address = '';
                                            if (!empty($row_usr->bnsprof_address1)) {
                                                $address .= $row_usr->bnsprof_address1 . ', ';
                                            }
                                            if (!empty($row_usr->bnsprof_address2)) {
                                                $address .= $row_usr->bnsprof_address2;
                                            }
                                            ?>
                                            <input name="add1" value="<?php echo htmlspecialchars($address); ?>" 
                                                   size="40" style="width:223px;" class="m_forminput" 
                                                   tabindex="8" type="text" readonly="readonly"/>
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
                                            <input name="email" value="<?php echo htmlspecialchars(user_info($uid, 'email') ?? ''); ?>" 
                                                   size="40" style="width:243px;" class="m_forminput" 
                                                   tabindex="3" type="text" readonly="readonly"/>
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
                                                            <input name="txtCity" size="11" id="txtCity" autocomplete="off" 
                                                                   value="<?php echo htmlspecialchars(get_city_name((int)($row_usr->bnsprof_city ?? 0))); ?>" 
                                                                   tabindex="5" class="m_forminput" type="text" readonly="readonly"/>
                                                        </td>
                                                        <td>
                                                            <input name="txtState" size="11" id="txtState" autocomplete="off" 
                                                                   value="<?php echo htmlspecialchars(get_state_name((int)($row_usr->bnsprof_state ?? 0))); ?>" 
                                                                   style="width:90px; margin-right: 3px;" tabindex="6" 
                                                                   class="m_forminput" type="text" readonly="readonly"/>
                                                        </td>
                                                        <td>
                                                            <input name="zip" size="12" style="width:51px;" class="m_forminput" 
                                                                   tabindex="7" type="text" 
                                                                   value="<?php echo htmlspecialchars($row_usr->bnsprof_zipcode ?? ''); ?>" 
                                                                   readonly="readonly"/>
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
                                            <input name="S_cmobile" id="S_cmobile" 
                                                   value="<?php echo htmlspecialchars(get_country_phn_code((int)($row_usr->country ?? 0))); ?>" 
                                                   size="4" style="width:36px; background-color:#f1f1f1;" 
                                                   class="m_forminput" readonly="READONLY" tabindex="9" type="text"/>
                                            <input style="width:198px;" id="mobile" name="mobile" 
                                                   value="<?php echo htmlspecialchars($row_usr->mobile1 ?? ''); ?>" 
                                                   size="25" maxlength="16" class="m_forminput" 
                                                   tabindex="10" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="cpm2"></div>
        <img src="images/z_002.gif" height="10" width="1">
        <div class="cpm2"></div>
    </div>
    
    <div class="c3">&nbsp;</div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>