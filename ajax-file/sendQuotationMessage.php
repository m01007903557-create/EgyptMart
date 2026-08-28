<?php
declare(strict_types=1);
// تصحيح الأخطاء - إظهار كل الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// ملف سجل خاص
$debug_log = __DIR__ . '/send_quotation_debug.log';
file_put_contents($debug_log, date('Y-m-d H:i:s') . " - بدء التنفيذ\n", FILE_APPEND);


ob_start();
session_start();

require_once __DIR__ . '/../common.php';

/**
 * دالة بديلة لإرسال البريد الإلكتروني
 * (لأن sendSMTPMail غير موجودة في النظام)
 */
if (!function_exists('sendSMTPMail')) {
    function sendSMTPMail($to, $subject, $message, $headers = "") {
        // استخدام الدالة الأساسية mail()
        if (empty($headers)) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: EgyptMART <noreply@egyptmart.shop>\r\n";
        }
        
        // محاولة إرسال البريد
        $result = @mail($to, $subject, $message, $headers);
        
        // تسجيل النتيجة للتصحيح
        if (!$result) {
            error_log("فشل إرسال البريد إلى: " . $to);
        }
        
        return $result;
    }
}


// التحقق من البيانات المطلوبة
if (!isset($_POST['msg_from']) || !is_numeric($_POST['msg_from']) ||
    !isset($_POST['msg_to']) || !is_numeric($_POST['msg_to']) ||
    !isset($_POST['msg_message']) || empty(trim($_POST['msg_message']))) {
    http_response_code(400);
    echo "0|Invalid request data";
    exit;
}

$msg_from = (int)$_POST['msg_from'];
$msg_to = (int)$_POST['msg_to'];
$msg_img = $_POST['msg_img'] ?? '';
$msg_pro_name = $_POST['msg_pro'] ?? '';
$msg_pro_moq = $_POST['msg_pro_moq'] ?? '';
$msg_pro_unit = (int)($_POST['msg_pro_unit'] ?? 0);
$msg_subject = trim($_POST['msg_subject'] ?? '');
$msg = trim($_POST['msg_message'] ?? '');

global $con;

// جلب بيانات المرسل
$sql_own = "SELECT u.*, bp.* 
            FROM user u
            INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE u.usr_id = ? LIMIT 1";

$stmt_own = mysqli_prepare($con, $sql_own);
mysqli_stmt_bind_param($stmt_own, 'i', $msg_from);
mysqli_stmt_execute($stmt_own);
$result_own = mysqli_stmt_get_result($stmt_own);
$row_own = mysqli_fetch_object($result_own);
mysqli_stmt_close($stmt_own);

if (!$row_own) {
    echo "0|Sender not found";
    exit;
}

// جلب بيانات المستلم
$sql_to = "SELECT u.*, bp.* 
           FROM user u
           INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
           WHERE u.usr_id = ? LIMIT 1";

$stmt_to = mysqli_prepare($con, $sql_to);
mysqli_stmt_bind_param($stmt_to, 'i', $msg_to);
mysqli_stmt_execute($stmt_to);
$result_to = mysqli_stmt_get_result($stmt_to);
$row_to = mysqli_fetch_object($result_to);
mysqli_stmt_close($stmt_to);

if (!$row_to) {
    echo "0|Recipient not found";
    exit;
}

// تنظيف النصوص
$msg_subject = mysqli_real_escape_string($con, $msg_subject);
$msg_message = mysqli_real_escape_string($con, $msg);
$msg_message_formatted = wordwrap($msg, 90, "<br />\n");

// بناء قسم المنتج إذا وجد
$product_html = '';
if (!empty($msg_img)) {
    $msg_img_clean = basename($msg_img);
    $msg_img_clean = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $msg_img_clean);
    $pro_name_clean = htmlspecialchars($msg_pro_name, ENT_QUOTES, 'UTF-8');
    $pro_moq_clean = htmlspecialchars($msg_pro_moq, ENT_QUOTES, 'UTF-8');
    $unit_name = htmlspecialchars(get_measurement_unit($msg_pro_unit), ENT_QUOTES, 'UTF-8');
    
    $product_html = '<div style="width:35%; overflow: hidden; float:left; margin-bottom: 20px;">
        <div style="width: 50%; float: left; overflow: hidden;">
            <img height="100" width="150" src="https://' . $_SERVER['HTTP_HOST'] . '/upload/myproduct/' . rawurlencode($msg_img_clean) . '" alt="Product">
        </div>
        <div style="width:50%; float: left; overflow: hidden; font-size: 1.2em;">
            <div>
                <div style="color:rgb(70, 109, 160);">' . $pro_name_clean . '</div>
                <br>
                <div>MOQ : ' . $pro_moq_clean . ' ' . $unit_name . '</div>
            </div>
        </div>
    </div>';
}

// بناء قالب البريد الإلكتروني
$company_name = htmlspecialchars($row_own->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$to_name = htmlspecialchars(trim(($row_to->name_prefix ?? '') . ' ' . ($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')), ENT_QUOTES, 'UTF-8');
$from_name = htmlspecialchars(trim(($row_own->name_prefix ?? '') . ' ' . ($row_own->fname ?? '') . ' ' . ($row_own->lname ?? '')), ENT_QUOTES, 'UTF-8');
$from_address = htmlspecialchars($row_own->bnsprof_address1 ?? '', ENT_QUOTES, 'UTF-8');
$from_city = htmlspecialchars(get_city_name((int)($row_own->bnsprof_city ?? 0)), ENT_QUOTES, 'UTF-8');
$from_country = htmlspecialchars(get_country_name((int)($row_own->country ?? 0)), ENT_QUOTES, 'UTF-8');
$from_phone = htmlspecialchars(($row_own->country_ph_code ?? '') . '-' . ($row_own->mobile1 ?? ''), ENT_QUOTES, 'UTF-8');
$from_email = htmlspecialchars($row_own->email ?? '', ENT_QUOTES, 'UTF-8');
$usr_email = $from_email; // للاستخدام في الروابط

$comment = '<div class="b9_m2 b10_m2" id="detable">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
        <tbody>
            <tr class="f5_m2">
                <td class="sh_m2">
                    <span style="width:600px;word-wrap:break-word;" id="wbr">
                        <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
                            <div style="height: 100px; width: 100%; float: left;">
                                <div style="height: 100px; width: 30%; float: left;">
                                    <img src="https://www.egyptmart.shop/images/Mlogo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                                </div>
                                <div style="height:100px;width:43%;float:left;">
                                    <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;"></h2>
                                </div>
                                <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                                    <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">إستفسار شــراء</span>
                                    <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . date("Y/m/d") . '</span>
                                </div>
                            </div>
                            <div style="width:100%;color:#000000;">
                                <p style="font-size:16px;text-align:right;color:#000000"><strong>' . $to_name . ' : الســادة</strong></p>
                            </div>
                            <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                                    <b>' . $company_name . ' <span style="color: blue;"> : إستفسار شــراء من</span></b>
                                </p>
                                <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">بيانات إتصال الراسل</p>
                                <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                                    ' . $from_name . '<br>
                                    ' . $from_address . '<br>
                                    ' . $from_city . ', ' . $from_country . '<br>
                                    Mobile/ Cell Phone: ' . $from_phone . '<br>
                                    E-mail: <a href="mailto:' . $from_email . '" target="_blank">' . $from_email . '</a><br>
                                </div>
                                <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">تفاصيـل الإستفسـار</p>
                                <div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:0.5em 0 0.9em 1em">
                                    ' . $product_html . '
                                    <div style="width: 90%; float: left;">
                                        <span style="font-size:1.0em;font-weight:normal">' . stripslashes($msg_message_formatted) . '</span>
                                    </div>
                                </div>
                                <div style="clear:both"></div>
                                <br>
                                <div style="clear:both">
                                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                                        يمكنك الرد على هذا الإستفسار من هنا <a href="https://egyptmart.shop/sign-in.php" style="margin-left: 50px;">... يمكنك الرد الآن</a>
                                    </p>
                                </div>
                                <br>
                                <table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="line-height:20px" valign="top">
                                                <span style="blue">EgyptMART</span> دعم العملاء <br> ' . htmlspecialchars(get_page_settings(21)) . ' : تواصل معنا على واتس
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of <span style="blue">EgyptMART</span>.</span>
                            </div>
                            <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
                            <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                                <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($usr_email) . '&redirect=https://www.egyptmart.shop/product-list.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">سجل منتجات جديدة</a> | 
                                <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($usr_email) . '&redirect=https://www.egyptmart.shop/post-sell-offer.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر عروضك الخاصة</a> | 
                                <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($usr_email) . '&redirect=https://www.egyptmart.shop/post-buy-req.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر طلب تسعير</a> | 
                                <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($usr_email) . '&redirect=https://www.egyptmart.shop/post-tender.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر مناقصات مجانا</a>
                            </div>
                            <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                                <p style="margin:10px 0px 2px">You have received this mail virtue of your opt-in subscription for product Enquiry on <font style="color:blue;">EgyptMART</font>.</p>
                                <p style="color:#808080; margin:0px 0px 20px;">
                                    <a href="https://www.egyptmart.shop/manage-buylead-alert.php" style="text-decoration:none;color:blue;">Click here</a> if you wish to modify your buy requirement alert categories.
                                </p>
                            </div>
                        </div>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>';

// حفظ الرسالة في قاعدة البيانات
$comment_escaped = mysqli_real_escape_string($con, $comment);
$sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date) 
        VALUES (?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iiss', $msg_from, $msg_to, $msg_subject, $comment_escaped);

if (mysqli_stmt_execute($stmt)) {
    $msg_id = (int)mysqli_insert_id($con);
    mysqli_stmt_close($stmt);
    
    // حفظ المرفق إذا وجد
    if (!empty($msg_img)) {
        $sql_ma = "INSERT INTO message_attachment (ma_msg_id, ma_file, ma_file_name, ma_file_quentity, ma_file_unit, ma_updated_date) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt_ma = mysqli_prepare($con, $sql_ma);
        mysqli_stmt_bind_param($stmt_ma, 'isssi', $msg_id, $msg_img, $msg_pro_name, $msg_pro_moq, $msg_pro_unit);
        mysqli_stmt_execute($stmt_ma);
        mysqli_stmt_close($stmt_ma);
    }
    
    // إرسال البريد الإلكتروني
    $from_mail = get_adminemail();
    $to_email = user_info($msg_to, 'email');
    $from_name = get_page_settings(4);
    $subject = $company_name . ' إستفسار شراء من';
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=UTF-8\n";
    $headers .= "From: " . $from_name . " <" . $from_mail . ">\n";
    
    if (!empty($to_email) && sendSMTPMail($to_email, $subject, $comment, $headers)) {
        echo "1";
    
        
        
        
        
        
    } else {
        echo "1|Message saved but email could not be sent";
    }
} else {
    error_log("Send Buy Enquiry Error: " . mysqli_error($con));
    echo "0|Failed to send message";
}
?>