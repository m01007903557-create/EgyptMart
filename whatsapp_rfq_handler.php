<?php
// سجل أخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/whatsapp_error_log.txt');

session_start();
require_once __DIR__ . '/lib/connect.php';

header('Content-Type: application/json');

// اختبار بسيط
if (isset($_GET['test'])) {
    echo json_encode(['test' => 'OK', 'session' => $_SESSION['uid_indm'] ?? 'no session']);
    exit;
}

global $con;
if (!$con) {
    echo json_encode(['success' => false, 'error' => 'خطأ في الاتصال بقاعدة البيانات']);
    exit;
}

$user_id = $_SESSION['uid_indm'] ?? 0;

if ($user_id == 0) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$product_name = isset($_POST['product_name']) ? mysqli_real_escape_string($con, $_POST['product_name']) : '';
$qty_from = isset($_POST['qty_from']) ? (int)$_POST['qty_from'] : 0;
$qty_to = isset($_POST['qty_to']) ? (int)$_POST['qty_to'] : 0;
$requirement = isset($_POST['requirement_details']) ? mysqli_real_escape_string($con, $_POST['requirement_details']) : '';

// سجل البيانات المستلمة
error_log("Received: product_id=$product_id, user_id=$user_id, qty_from=$qty_from, qty_to=$qty_to");

if ($product_id == 0) {
    echo json_encode(['success' => false, 'error' => 'بيانات المنتج غير صالحة']);
    exit;
}

// جلب بيانات المستخدم
$user_query = mysqli_query($con, "SELECT mobile1, email, fname, lname FROM user WHERE usr_id='$user_id'");
if (!$user_query) {
    echo json_encode(['success' => false, 'error' => 'خطأ في جلب بيانات المستخدم: ' . mysqli_error($con)]);
    exit;
}
$user = mysqli_fetch_assoc($user_query);

$user_fullname = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));

// حفظ في قاعدة البيانات
$sql = "INSERT INTO buy_requirement (
    br_pc_id, br_u_id, br_pd_name, br_requirement, 
    br_estimate_qty, br_estimate_qty_unit, br_description,
    br_posting_date, communication_type, source_channel, 
    whatsapp_sent, whatsapp_sent_date
) VALUES (
    '$product_id', '$user_id', '$product_name', '$requirement',
    '$qty_to', 'piece', 'Qty from $qty_from to $qty_to',
    NOW(), 'whatsapp', 'whatsapp_platform', 
    1, NOW()
)";

error_log("SQL: $sql");

$result = mysqli_query($con, $sql);

if ($result) {
    $insert_id = mysqli_insert_id($con);
    error_log("Inserted ID: $insert_id");
    
    $product_url = "https://" . $_SERVER['HTTP_HOST'] . "/product-details.php?id=$product_id";
    
    $message = "مرحبا شركة $user_fullname\n";
    $message .= "أريد الحصول على أفضل سعر لـ: $product_name\n\n";
    $message .= "RFQ ID: $insert_id\n";
    $message .= "الكمية: من $qty_from إلى $qty_to\n";
    $message .= "التفاصيل: $requirement\n";
    $message .= "الرابط: $product_url\n";
    $message .= "هاتفي: " . ($user['mobile1'] ?? '') . "\n";
    $message .= "بريدي: " . ($user['email'] ?? '');
    
    $wa_url = "https://api.whatsapp.com/send?phone=201104832811&text=" . urlencode($message);
    
    echo json_encode(['success' => true, 'whatsapp_url' => $wa_url]);
} else {
    error_log("Error: " . mysqli_error($con));
    echo json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]);
}
?>