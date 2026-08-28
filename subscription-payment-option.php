<?php
/**
 * File: subscription-payment-option.php
 * Description: خيارات الدفع لخطة العضوية (تأكيد الطلب وبيانات الاتصال)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$id_param = isset($_GET['id']) ? $_GET['id'] : '';
$_SESSION['last_page'] = "subscription-payment-option.php?id=" . urlencode($id_param);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف خطة العضوية
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: membership_plans.php");
    exit;
}

$plan_hash = substr($_GET['id'], 5); // إزالة أول 5 أحرف

global $con;

// جلب بيانات خطة العضوية
$sql = "SELECT mp_id, mp_name, mp_credits, mp_amount FROM membership_plan WHERE MD5(mp_id) = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $plan_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header("Location: membership_plans.php");
    exit;
}

// حساب الضريبة والإجمالي
$tax_rate = 12.36;
$tax_amount = ($row->mp_amount * $tax_rate) / 100;
$total_amount = $row->mp_amount + $tax_amount;

// جلب بيانات المستخدم
$sql_usr = "SELECT u.*, bp.* 
            FROM user u
            INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE u.usr_id = ? AND bp.bnsprof_status = '1'
            LIMIT 1";

$stmt_usr = mysqli_prepare($con, $sql_usr);
mysqli_stmt_bind_param($stmt_usr, 'i', $uid);
mysqli_stmt_execute($stmt_usr);
$result_usr = mysqli_stmt_get_result($stmt_usr);
$row_usr = mysqli_fetch_object($result_usr);
mysqli_stmt_close($stmt_usr);

if (!$row_usr) {
    die("User profile not found");
}

// تنظيف البيانات للعرض
$user_name = ucfirst(user_info($uid, 'fname')) . ' ' . ucfirst(user_info($uid, 'lname'));
$user_email = user_info($uid, 'email');
$user_country_name = htmlspecialchars(get_country_name((int)($row_usr->country ?? 0)), ENT_QUOTES, 'UTF-8');
$user_city_name = htmlspecialchars(get_city_name((int)($row_usr->bnsprof_city ?? 0)), ENT_QUOTES, 'UTF-8');
$user_state_name = htmlspecialchars(get_state_name((int)($row_usr->bnsprof_state ?? 0)), ENT_QUOTES, 'UTF-8');
$user_address = trim(($row_usr->bnsprof_address1 ?? '') . ', ' . ($row_usr->bnsprof_address2 ?? ''), ', ');
$user_zipcode = htmlspecialchars($row_usr->bnsprof_zipcode ?? '', ENT_QUOTES, 'UTF-8');
$user_mobile = htmlspecialchars($row_usr->mobile1 ?? '', ENT_QUOTES, 'UTF-8');
$user_phone_code = htmlspecialchars(get_country_phn_code((int)($row_usr->country ?? 0)), ENT_QUOTES, 'UTF-8');
$user_prefix = user_info($uid, 'name_prefix') ?? 'Mr.';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
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
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
</head>
<body>
    <div id="imgtrailer" style="position:absolute; z-index:4; visibility:hidden;">
        <img src="images/loading.gif" height="32" width="32" alt="Loading">
    </div>
    
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        <!-- ملخص الطلب -->
        <div class="m_pkgdtl">
            <h2>Order Summary</h2>
            <div class="m_pkgdtl-inner">
                <table class="m_tmt" border="0">
                    <tbody>
                        <tr>
                            <td class="m_pkgdetails">
                                <?php echo htmlspecialchars($row->mp_name ?? '', ENT_QUOTES, 'UTF-8'); ?> - 
                                <?php echo (int)($row->mp_credits ?? 0); ?> Credits
                            </td>
                            <td class="m_pkgdetails" width="10">:</td>
                            <td class="m_pkgdetails m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol(), ENT_QUOTES, 'UTF-8'); ?></span> 
                                <?php echo number_format((float)($row->mp_amount ?? 0), 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="m_stax">Service Tax (12.36%)</td>
                            <td class="m_stax" width="10">:</td>
                            <td class="m_stax m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol(), ENT_QUOTES, 'UTF-8'); ?></span> 
                                <?php echo number_format($tax_amount, 2); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="m_pricefnl">Total Amount Payable</td>
                            <td class="m_pricefnl" width="10">:</td>
                            <td class="m_pricefnl m_tdpl">
                                <span class="WebRupee"><?php echo htmlspecialchars(getCurrencySymbol(), ENT_QUOTES, 'UTF-8'); ?>&nbsp;</span> 
                                <?php echo number_format($total_amount, 2); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <input type="hidden" id="usr" name="usr" value="<?php echo $uid; ?>" />
        <input type="hidden" id="mp" name="mp" value="<?php echo (int)$row->mp_id; ?>" />
        
        <!-- خطوات الدفع -->
        <div class="m_ca2ndstep cpa1">
            <div class="m_crtop">
                <div class="m_fst1s cpa1 m_crtophd">
                    <p class="m_spimg m_1nons cpa1"></p>
                    Select Subscription Plan
                </div>
                <p class="m_spimg m_arwmiddle cpa1"></p>
                <div class="m_fst2s cpa1 m_crtophds">
                    <p class="m_spimg m_2s cpa1"></p>
                    Choose Payment Option
                </div>
            </div>
            
            <div class="m_paymentgateway" style="text-align:center; padding-top:10px;">
                <?php include __DIR__ . "/paymentgateway/api.php"; ?>
            </div>
        </div>
        
        <!-- معلومات الاتصال -->
        <div class="m_contactdetail" id="kelly" style="display:block;">
            <h3>Contact Information:</h3>
            
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td class="m_formtxt" valign="top" width="75">Your Name:</td>
                                        <td>
                                            <select name="salute" size="1" style="width:43px; padding:3px 0px; height:25px; margin:0px;" 
                                                    class="m_forminput" tabindex="1" readonly="readonly">
                                                <option value="Mr." <?php echo ($user_prefix == "Mr.") ? 'selected' : ''; ?>>Mr.</option>
                                                <option value="Ms." <?php echo ($user_prefix == "Ms.") ? 'selected' : ''; ?>>Ms.</option>
                                                <option value="Mrs." <?php echo ($user_prefix == "Mrs.") ? 'selected' : ''; ?>>Mrs.</option>
                                                <option value="Dr." <?php echo ($user_prefix == "Dr.") ? 'selected' : ''; ?>>Dr.</option>
                                            </select>
                                            <input name="first_name" size="10" style="width:177px;" class="m_forminput" 
                                                   value="<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                                   tabindex="2" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="m_formtxt" valign="top">Country:</td>
                                        <td>
                                            <input name="country_name" id="country_name" 
                                                   value="<?php echo $user_country_name; ?>" 
                                                   size="40" style="width:223px;" class="m_forminput" 
                                                   tabindex="4" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="m_formtxt" valign="top">Address:</td>
                                        <td>
                                            <input name="add1" value="<?php echo htmlspecialchars($user_address, ENT_QUOTES, 'UTF-8'); ?>" 
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
                                        <td class="m_formtxt" valign="top">E-mail:</td>
                                        <td>
                                            <input name="email" value="<?php echo htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8'); ?>" 
                                                   size="40" style="width:243px;" class="m_forminput" 
                                                   tabindex="3" type="text" readonly="readonly"/>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="m_formtxt1" valign="top">Location:</td>
                                        <td>
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td class="cpcart-nn">City</td>
                                                        <td class="cpcart-nn">State</td>
                                                        <td class="cpcart-nn">Postal Code</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input name="txtCity" size="11" id="txtCity" 
                                                                   value="<?php echo $user_city_name; ?>" 
                                                                   tabindex="5" class="m_forminput" type="text" readonly="readonly"/>
                                                        </td>
                                                        <td>
                                                            <input name="txtState" size="11" id="txtState" 
                                                                   value="<?php echo $user_state_name; ?>" 
                                                                   style="width:90px; margin-right:3px;" 
                                                                   tabindex="6" class="m_forminput" type="text" readonly="readonly"/>
                                                        </td>
                                                        <td>
                                                            <input name="zip" size="12" style="width:51px;" 
                                                                   class="m_forminput" tabindex="7" type="text" 
                                                                   value="<?php echo $user_zipcode; ?>" readonly="readonly"/>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="m_formtxt" valign="top">Mobile:</td>
                                        <td>
                                            <input name="S_cmobile" id="S_cmobile" 
                                                   value="<?php echo $user_phone_code; ?>" 
                                                   size="4" style="width:36px; background-color:#f1f1f1;" 
                                                   class="m_forminput" readonly="READONLY" tabindex="9" type="text"/>
                                            <input style="width:198px;" id="mobile" name="mobile" 
                                                   value="<?php echo $user_mobile; ?>" size="25" maxlength="16" 
                                                   class="m_forminput" tabindex="10" type="text" readonly="readonly"/>
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
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
mysqli_stmt_close($stmt_usr);
?>