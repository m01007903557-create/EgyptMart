<?php
declare(strict_types=1);
// للتصحيح - سجل كل شيء
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("=== بدء تنفيذ sendQuotationMessage.php ===");
error_log("POST data: " . print_r($_POST, true));

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// ==============================================
// استقبال البيانات من نموذج الاستفسار
// ==============================================

// التحقق من البيانات المطلوبة
if (!isset($_POST['msg_from']) || !is_numeric($_POST['msg_from']) ||
    !isset($_POST['msg_to']) || !is_numeric($_POST['msg_to']) ||
    !isset($_POST['msg_message']) || empty(trim($_POST['msg_message']))) {
    http_response_code(400);
    echo "0|Invalid request data";
    exit;
}

// استقبال البيانات القديمة
$msg_from = (int)$_POST['msg_from'];
$msg_to = (int)$_POST['msg_to'];
$msg_img = $_POST['msg_img'] ?? '';
$msg_pro_name = $_POST['msg_pro_name'] ?? '';
$msg_pro_moq = $_POST['msg_pro_moq'] ?? '';
$msg_pro_unit = (int)($_POST['msg_pro_unit'] ?? 0);
$msg_subject = trim($_POST['msg_subject'] ?? '');
$msg_message = trim($_POST['msg_message'] ?? '');

// ========== الحقول الجديدة (الكمية من/إلى) ==========
$quantity_from = isset($_POST['quantity_from']) ? (int)$_POST['quantity_from'] : 0;
$quantity_to = isset($_POST['quantity_to']) ? (int)$_POST['quantity_to'] : 0;

// التحقق من صحة الكمية
if ($quantity_from <= 0 || $quantity_to <= 0) {
    http_response_code(400);
    echo "0|الرجاء إدخال الكمية التقريبية المطلوبة";
    exit;
}

if ($quantity_from > $quantity_to) {
    http_response_code(400);
    echo "0|الكمية 'من' يجب أن تكون أقل من أو تساوي 'إلى'";
    exit;
}

global $con;

// تنظيف النصوص
$msg_subject = mysqli_real_escape_string($con, $msg_subject);
$msg_message = mysqli_real_escape_string($con, $msg_message);
$msg_pro_name = mysqli_real_escape_string($con, $msg_pro_name);

// جلب بيانات المرسل (المشتري)
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

// جلب بيانات المستلم (المورد)
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

// ==============================================
// حفظ الاستفسار في قاعدة البيانات (جدول buy_enquiries)
// ==============================================

$buyer_id = $msg_from;
$supplier_id = $msg_to;
$product_name = $msg_pro_name;
$product_unit = get_measurement_unit($msg_pro_unit);
$message = $msg_message;

$sql_insert = "INSERT INTO buy_enquiries (
    buyer_id, supplier_id, product_name, product_unit, 
    quantity_from, quantity_to, message, enquiry_date, status
) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

$stmt_insert = mysqli_prepare($con, $sql_insert);
mysqli_stmt_bind_param($stmt_insert, 'iissiis', 
    $buyer_id, 
    $supplier_id, 
    $product_name, 
    $product_unit,
    $quantity_from, 
    $quantity_to, 
    $message
);

$enquiry_id = null;

if (mysqli_stmt_execute($stmt_insert)) {
    $enquiry_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_insert);
    
    // ==============================================
    // حفظ الرسالة في جدول message (للتوافق مع النظام القديم)
    // ==============================================
    
    // تنظيف النصوص للقالب
    $msg_message_formatted = wordwrap($msg_message, 90, "<br />\n");
    
    // بناء قالب البريد الإلكتروني (نفس الكود القديم)
    $company_name = htmlspecialchars($row_own->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
    $to_name = htmlspecialchars(trim(($row_to->name_prefix ?? '') . ' ' . ($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')), ENT_QUOTES, 'UTF-8');
    $from_name = htmlspecialchars(trim(($row_own->name_prefix ?? '') . ' ' . ($row_own->fname ?? '') . ' ' . ($row_own->lname ?? '')), ENT_QUOTES, 'UTF-8');
    $from_address = htmlspecialchars($row_own->bnsprof_address1 ?? '', ENT_QUOTES, 'UTF-8');
    $from_city = htmlspecialchars(get_city_name((int)($row_own->bnsprof_city ?? 0)), ENT_QUOTES, 'UTF-8');
    $from_country = htmlspecialchars(get_country_name((int)($row_own->country ?? 0)), ENT_QUOTES, 'UTF-8');
    $from_phone = htmlspecialchars(($row_own->country_ph_code ?? '') . '-' . ($row_own->mobile1 ?? ''), ENT_QUOTES, 'UTF-8');
    $from_email = htmlspecialchars($row_own->email ?? '', ENT_QUOTES, 'UTF-8');
    $usr_email = $from_email;
    
    // إضافة الكمية إلى القالب
    $quantity_html = '
    <div style="width: 100%; float: left; margin: 10px 0; padding: 10px; background: #f0f8ff; border-right: 4px solid #466da0;">
        <strong>الكمية التقريبية المطلوبة:</strong><br>
        من ' . (int)$quantity_from . ' إلى ' . (int)$quantity_to . ' ' . htmlspecialchars($product_unit) . '
    </div>';
    
    // بناء قالب البريد (نفس الكود القديم مع إضافة الكمية)
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
                                ' . $quantity_html . '
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
                                         '</td>
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
    
    // حفظ الرسالة في جدول message (للمراسلة الداخلية)
    $comment_escaped = mysqli_real_escape_string($con, $comment);
    $sql_msg = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date) 
                VALUES (?, ?, ?, ?, NOW())";
    
    $stmt_msg = mysqli_prepare($con, $sql_msg);
    mysqli_stmt_bind_param($stmt_msg, 'iiss', $msg_from, $msg_to, $msg_subject, $comment_escaped);
    mysqli_stmt_execute($stmt_msg);
    $msg_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_msg);
    
    // ==============================================
    // إرسال البريد الإلكتروني (نفس الكود القديم)
    // ==============================================
    
    $from_mail = get_adminemail();
    $to_email = user_info($msg_to, 'email');
    $from_name = get_page_settings(4);
    $subject = $company_name . ' إستفسار شراء من';
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=UTF-8\n";
    $headers .= "From: " . $from_name . " <" . $from_mail . ">\n";
    
    $email_sent = false;
    
    if (!empty($to_email)) {
        $email_sent = mail($to_email, $subject, $comment, $headers);
        if (!$email_sent) {
            error_log("Failed to send email to: " . $to_email);
        }
    }
    
    // ==============================================
    // إنشاء رابط واتساب للمورد
    // ==============================================
    
    $supplier_phone = user_info($msg_to, 'mobile1');
    $whatsapp_link = null;
    
    if (!empty($supplier_phone)) {
        $clean_phone = ltrim($supplier_phone, '0');
        $country_code = user_info($msg_to, 'country_ph_code') ?: '20';
        
        $whatsapp_message = "🛒 *استفسار شراء جديد - EgyptMART* 🛒\n\n";
        $whatsapp_message .= "👤 *من:* " . $from_name . "\n";
        $whatsapp_message .= "🏢 *الشركة:* " . $company_name . "\n";
        $whatsapp_message .= "📦 *المنتج:* " . $msg_pro_name . "\n";
        $whatsapp_message .= "📊 *الكمية المطلوبة:* من " . $quantity_from . " إلى " . $quantity_to . " " . $product_unit . "\n";
        $whatsapp_message .= "💬 *الرسالة:* " . substr($msg_message, 0, 150);
        if (strlen($msg_message) > 150) $whatsapp_message .= "...";
        $whatsapp_message .= "\n\n";
        $whatsapp_message .= "📞 *للاتصال بالمشتري:* " . $from_phone . "\n";
        $whatsapp_message .= "🔗 *للرد:* https://egyptmart.shop/admin/message-view.php?id=" . $enquiry_id;
        
        $encoded_message = urlencode($whatsapp_message);
        $whatsapp_link = "https://wa.me/" . $country_code . $clean_phone . "?text=" . $encoded_message;
    }
    
    // إرجاع النتيجة مع رابط واتساب
    echo "1";
    
    if ($whatsapp_link) {
        echo "|whatsapp_link=" . $whatsapp_link;
    }
    
} else {
    error_log("Send Buy Enquiry Error: " . mysqli_error($con));
    echo "0|Failed to save enquiry";
}

mysqli_stmt_close($stmt_insert ?? null);
?>