<?php
// smsMail.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
include "../common.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo 0;
    exit;
}

$uid_indm = (int)$_SESSION['uid_indm'];

// جلب بيانات المرسل
$sql_own = "SELECT * FROM user, business_profile 
            WHERE usr_id = '{$uid_indm}' AND bnsprof_uid = usr_id LIMIT 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);

if (!$row_own) {
    echo 0;
    exit;
}

// جلب بيانات المستلم
$msg_to = isset($_POST['msg_to']) ? (int)$_POST['msg_to'] : 0;
$sql_to = "SELECT * FROM user, business_profile 
           WHERE usr_id = '{$msg_to}' AND bnsprof_uid = usr_id LIMIT 1";
$res_to = mysqli_query($con, $sql_to);
$row_to = mysqli_fetch_object($res_to);

if (!$row_to) {
    echo 0;
    exit;
}

// معالجة بيانات النموذج
$country = isset($_POST['country']) && !empty($_POST['country']) 
    ? (int)$_POST['country'] 
    : 98; // القيمة الافتراضية 98 (مصر)

$sn_email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : '';
$company = isset($_POST['company']) ? mysqli_real_escape_string($con, $_POST['company']) : '';
$msg_from = isset($_POST['name']) ? mysqli_real_escape_string($con, $_POST['name']) : '';
$country_code = isset($_POST['country_code']) ? mysqli_real_escape_string($con, $_POST['country_code']) : '';
$mobile = isset($_POST['mobile']) ? mysqli_real_escape_string($con, $_POST['mobile']) : '';
$msg = isset($_POST['description']) ? $_POST['description'] : '';

$description = wordwrap($msg, 150, "<br />\n");

// بناء نص الرسالة
$msg_message = '
    <strong>Company Name :</strong> ' . htmlspecialchars($msg_from) . '<br />
    <strong>Country :</strong> ' . htmlspecialchars(get_country_name($country)) . '<br />
    <strong>Mobile/ Cell Phone :</strong> ' . htmlspecialchars($country_code) . '-' . htmlspecialchars($mobile) . '<br />
    <strong>E-mail :</strong> <a href="mailto:' . htmlspecialchars($sn_email) . '" target="_blank">' . htmlspecialchars($sn_email) . '</a><br />
    <strong>Description :</strong> ' . $description . '<br />';

// إدراج الرسالة في قاعدة البيانات
$sql = "INSERT INTO message
        SET 
            msg_from = '" . mysqli_real_escape_string($con, $company) . "',
            msg_to = '{$msg_to}',
            msg_subject = 'SMS Enquiry',
            msg_message = '" . mysqli_real_escape_string($con, $msg_message) . "',
            msg_date = NOW()";

if (mysqli_query($con, $sql)) {
    
    // بناء قالب البريد الإلكتروني
    $comment = '<div class="b9_m2 b10_m2" id="detable">
        <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
            <tbody>
                <tr class="f5_m2">
                    <td class="sh_m2">
                        <span style="width:750px;word-wrap:break-word;" id="wbr">
                            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
                                <div style="height: 100px; width: 100%; float: left; ">
                                    <div style="height: 100px; width: 30%; float: left;">
                                        <img src="https://egyptmart.shop/images/logo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                                    </div>
                                    <div style="height:100px;width:43%;float:left;">
                                        <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;"> طلب اليوم <br> إستفسـار شـراء</h2>
                                    </div>
                                    <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                                        <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;"> إستفسار شراء </span>
                                        <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . date("Y/m/d") . '</span>
                                    </div>
                                </div>
                                
                                <div style="width:100%;color:#000000;">
                                    <p style="font-size:16px;text-align:right;color:#000000">
                                        <strong>' . htmlspecialchars(($row_to->name_prefix ?? '') . '' . ($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')) . ' : الســادة</strong>
                                    </p>
                                </div>
                                
                                <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                                    <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                                        <b>' . htmlspecialchars($row_own->bnsprof_compname ?? '') . ' : إستفسار شراء من</b>
                                    </p>
                                    
                                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">: تفاصيل إتصال الراسل</p>
                                    
                                    <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                                        إسم الشركة : ' . htmlspecialchars($msg_from) . '<br>
                                        البـلد : ' . htmlspecialchars(get_country_name($country)) . '<br>
                                        المحمول / التليفون: ' . htmlspecialchars($country_code) . '-' . htmlspecialchars($mobile) . '<br>
                                        البريد الالكترونى: <a href="mailto:' . htmlspecialchars($sn_email) . '" target="_blank">' . htmlspecialchars($sn_email) . '</a><br>
                                    </div>
                                    
                                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">: تفاصيل الإستفسار</p>
                                    
                                    <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:0.5em 0 0.9em 1em">
                                        <div style="width: 100%; float: left;">
                                            <span style="font-size:1.0em;font-weight:normal">' . $description . '</span>
                                        </div>
                                    </div>
                                    
                                    <div style="clear:both"></div>
                                    <br>
                                    
                                    <div style="clear:both">
                                        <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                                            يمكنك الرد على المشترى من هنا 
                                            <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($sn_email) . '&redirect=https://egyptmart.shop/my-enquiries.php" style="float: center">يمكنك الرد الآن</a>
                                        </p>
                                    </div>
                                    
                                    <table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="line-height:20px" valign="top">
                                                    <span style="color:blue">EgyptMART</span> الدعم الفنى
                                                    <br>
                                                    Call us on ' . htmlspecialchars(get_page_settings(21) ?? '') . '
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of ' . htmlspecialchars(getWebSiteName() ?? 'EgyptMART') . '.</span>
                                </div>
                                
                                <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
                                
                                <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                                    <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($sn_email) . '&redirect=https://www.egyptmart.shop/product-list.php" 
                                       style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">سجل منتجات جديدة</a> | 
                                    <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($sn_email) . '&redirect=https://www.egyptmart.shop/post-sell-offer.php" 
                                       style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر عروضك الخاصة</a> | 
                                    <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($sn_email) . '&redirect=https://www.egyptmart.shop/post-buy-req.php" 
                                       style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر طلب تسعير</a> | 
                                    <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($sn_email) . '&redirect=https://www.egyptmart.shop/post-tender.php" 
                                       style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر مناقصات مجانا</a>
                                </div>
                                
                                <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                                    <p style="margin:10px 0px 2px">You have received this mail virtue of your opt-in subscription for product Enquiry on <font style="color:blue;">EgyptMART</font>.</p>
                                    <p style="color:#808080; margin:0px 0px 20px;">
                                        <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($sn_email) . '&redirect=https://www.egyptmart.shop/manage-buylead-alert.php" 
                                           style="text-decoration:none;color:blue;">إضغط هنا</a> عندما تريد تغيير أصناف إشعارات طلبات الشراء الواردة الى بريدك
                                    </p>
                                </div>
                            </div>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    // إرسال البريد الإلكتروني
    $from_mail = get_adminemail();
    $to = user_info($msg_to, 'email');
    $from_name = get_page_settings(4);
    $subj = ($row_own->bnsprof_compname ?? '') . ' إستفسار شراء من';
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=UTF-8\n";
    $headers .= "From: " . $from_name . " <" . $from_mail . ">";
    
    if (mail($to, $subj, $comment, $headers)) {
        echo 1;
    } else {
        // حتى لو فشل البريد، الرسالة حفظت في قاعدة البيانات
        echo 1;
    }
    
} else {
    echo 0;
}
?>