<?php
declare(strict_types=1);
ob_start();
session_start();

// ==============================================
// وضع التصحيح الشامل
// ==============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
$debug_log = __DIR__ . '/enquiry_debug.log';

function writeDebug($msg) {
    global $debug_log;
    file_put_contents($debug_log, date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND);
}

writeDebug("========== بدء تنفيذ الملف ==========");
writeDebug("POST data: " . print_r($_POST, true));

require_once __DIR__ . '/../common.php';
writeDebug("common.php تم تحميله");

// ==============================================
// التحقق من البيانات
// ==============================================
if (!isset($_POST['msg_from']) || !is_numeric($_POST['msg_from']) ||
    !isset($_POST['msg_to']) || !is_numeric($_POST['msg_to']) ||
    !isset($_POST['msg_message']) || empty(trim($_POST['msg_message']))) {
    writeDebug("خطأ: بيانات غير مكتملة");
    http_response_code(400);
    echo "0|Invalid request data";
    exit;
}

// استقبال البيانات
$msg_from = (int)$_POST['msg_from'];
$msg_to = (int)$_POST['msg_to'];
$msg_message = trim($_POST['msg_message']);
$msg_pro_name = $_POST['msg_pro_name'] ?? '';
$msg_pro_unit = (int)($_POST['msg_pro_unit'] ?? 0);
$quantity_from = isset($_POST['quantity_from']) ? (int)$_POST['quantity_from'] : 0;
$quantity_to = isset($_POST['quantity_to']) ? (int)$_POST['quantity_to'] : 0;

writeDebug("البيانات المستلمة - from: $msg_from, to: $msg_to, qty: $quantity_from-$quantity_to");

// التحقق من الكمية
if ($quantity_from <= 0 || $quantity_to <= 0) {
    writeDebug("خطأ: الكمية غير صحيحة");
    echo "0|الرجاء إدخال الكمية التقريبية المطلوبة";
    exit;
}

if ($quantity_from > $quantity_to) {
    writeDebug("خطأ: الكمية 'من' أكبر من 'إلى'");
    echo "0|الكمية 'من' يجب أن تكون أقل من أو تساوي 'إلى'";
    exit;
}

global $con;

// جلب بيانات المشتري
writeDebug("جلب بيانات المشتري ID: $msg_from");
$sql_own = "SELECT u.*, bp.* FROM user u INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid WHERE u.usr_id = ? LIMIT 1";
$stmt_own = mysqli_prepare($con, $sql_own);
mysqli_stmt_bind_param($stmt_own, 'i', $msg_from);
mysqli_stmt_execute($stmt_own);
$result_own = mysqli_stmt_get_result($stmt_own);
$row_own = mysqli_fetch_object($result_own);
mysqli_stmt_close($stmt_own);

if (!$row_own) {
    writeDebug("خطأ: المشتري غير موجود");
    echo "0|Sender not found";
    exit;
}
writeDebug("تم جلب بيانات المشتري: " . ($row_own->email ?? 'no email'));

// جلب بيانات المورد
writeDebug("جلب بيانات المورد ID: $msg_to");
$sql_to = "SELECT u.*, bp.* FROM user u INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid WHERE u.usr_id = ? LIMIT 1";
$stmt_to = mysqli_prepare($con, $sql_to);
mysqli_stmt_bind_param($stmt_to, 'i', $msg_to);
mysqli_stmt_execute($stmt_to);
$result_to = mysqli_stmt_get_result($stmt_to);
$row_to = mysqli_fetch_object($result_to);
mysqli_stmt_close($stmt_to);

if (!$row_to) {
    writeDebug("خطأ: المورد غير موجود");
    echo "0|Recipient not found";
    exit;
}
writeDebug("تم جلب بيانات المورد: " . ($row_to->email ?? 'no email'));

// حفظ في جدول buy_enquiries
writeDebug("حفظ في جدول buy_enquiries");
$product_unit_name = get_measurement_unit($msg_pro_unit);
$sql_insert = "INSERT INTO buy_enquiries (buyer_id, supplier_id, product_name, product_unit, quantity_from, quantity_to, message, enquiry_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

$stmt_insert = mysqli_prepare($con, $sql_insert);
mysqli_stmt_bind_param($stmt_insert, 'iissiis', $msg_from, $msg_to, $msg_pro_name, $product_unit_name, $quantity_from, $quantity_to, $msg_message);

if (mysqli_stmt_execute($stmt_insert)) {
    $enquiry_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_insert);
    writeDebug("تم حفظ الاستفسار بنجاح، ID: $enquiry_id");
    
    // حفظ في جدول message (للمراسلة الداخلية)
    writeDebug("حفظ في جدول message");
    $msg_subject = "استفسار شراء: " . $msg_pro_name;
    $comment = "المنتج: $msg_pro_name\nالكمية: من $quantity_from إلى $quantity_to $product_unit_name\n\nالرسالة: $msg_message";
    
    $sql_msg = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date) VALUES (?, ?, ?, ?, NOW())";
    $stmt_msg = mysqli_prepare($con, $sql_msg);
    mysqli_stmt_bind_param($stmt_msg, 'iiss', $msg_from, $msg_to, $msg_subject, $comment);
    mysqli_stmt_execute($stmt_msg);
    mysqli_stmt_close($stmt_msg);
    writeDebug("تم حفظ في جدول message");
    
    // محاولة إرسال بريد إلكتروني (اختياري، لا نعتمد عليه للنجاح)
    $to_email = $row_to->email;
    if (!empty($to_email)) {
        $subject = $row_own->bnsprof_compname . ' - استفسار شراء';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: EgyptMART <noreply@egyptmart.shop>\r\n";
        
        $mail_body = "<h2>استفسار شراء جديد</h2>";
        $mail_body .= "<p><strong>من:</strong> " . ($row_own->bnsprof_compname ?: $row_own->fname . ' ' . $row_own->lname) . "</p>";
        $mail_body .= "<p><strong>المنتج:</strong> $msg_pro_name</p>";
        $mail_body .= "<p><strong>الكمية:</strong> من $quantity_from إلى $quantity_to $product_unit_name</p>";
        $mail_body .= "<p><strong>الرسالة:</strong> $msg_message</p>";
        $mail_body .= "<p><a href='https://egyptmart.shop/sign-in.php'>للرد اضغط هنا</a></p>";
        
        @mail($to_email, $subject, $mail_body, $headers);
        writeDebug("تم محاولة إرسال بريد إلى: $to_email");
    }
    
    echo "1";
    writeDebug("تم إرسال استجابة النجاح");
    
} else {
    $error = mysqli_error($con);
    writeDebug("خطأ في حفظ الاستفسار: " . $error);
    echo "0|فشل حفظ الاستفسار: " . $error;
}

writeDebug("========== انتهاء التنفيذ ==========");
?>