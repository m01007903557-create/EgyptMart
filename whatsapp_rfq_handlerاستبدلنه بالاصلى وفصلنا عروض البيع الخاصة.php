<?php
// ✅ منع أي إخراج قبل JSON
ob_clean();

// سجل أخطاء
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/whatsapp_error_log.txt');

session_start();
require_once __DIR__ . '/lib/connect.php';

header('Content-Type: application/json');

// اختبار بسيط
if (isset($_GET['test'])) {
    die(json_encode(['test' => 'OK', 'session' => $_SESSION['uid_indm'] ?? 'no session']));
}

// ✅ التحقق من الاتصال بقاعدة البيانات
if (!$con) {
    die(json_encode(['success' => false, 'error' => 'خطأ في الاتصال بقاعدة البيانات']));
}

// ✅ التحقق من تسجيل الدخول
$user_id = $_SESSION['uid_indm'] ?? 0;
if ($user_id == 0) {
    die(json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']));
}

// ✅ جلب البيانات من POST
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$product_name = isset($_POST['product_name']) ? mysqli_real_escape_string($con, trim($_POST['product_name'])) : '';
$qty_from = isset($_POST['qty_from']) ? (int)$_POST['qty_from'] : 1;
$qty_to = isset($_POST['qty_to']) ? (int)$_POST['qty_to'] : 1;
$requirement = isset($_POST['requirement_details']) ? mysqli_real_escape_string($con, trim($_POST['requirement_details'])) : '';

// ✅ بيانات saleoffer
$request_type = isset($_POST['request_type']) ? $_POST['request_type'] : '';
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
$delivery_days = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 7;

// ✅ سجل البيانات للتصحيح
error_log("=== Start Request ===");
error_log("user_id: $user_id, request_type: $request_type, offer_id: $offer_id, product_id: $product_id");

// ============================================================
// ✅ معالجة طلبات saleoffer
// ============================================================
if ($request_type == 'saleoffer') {
    error_log("Processing saleoffer request");
    
    if ($offer_id == 0) {
        die(json_encode(['success' => false, 'error' => 'معرف العرض غير صالح (offer_id = 0)']));
    }
    
    // ✅ جلب تفاصيل العرض
    $offer_sql = "SELECT so_service, usr_id FROM sale_offer WHERE so_id = $offer_id LIMIT 1";
    $offer_res = mysqli_query($con, $offer_sql);
    
    if (!$offer_res) {
        error_log("MySQL Error (offer): " . mysqli_error($con));
        die(json_encode(['success' => false, 'error' => 'خطأ في استعلام العرض: ' . mysqli_error($con)]));
    }
    
    $offer_data = mysqli_fetch_assoc($offer_res);
    if (!$offer_data) {
        die(json_encode(['success' => false, 'error' => "العرض غير موجود (ID: $offer_id)"]));
    }
    
    $product_name = $offer_data['so_service'] . ' (عرض بيع)';
    $product_id = $offer_id;
    $supplier_id = $offer_data['usr_id'];
    
    error_log("Offer found: product_id=$product_id, supplier_id=$supplier_id");
    
    // ✅ جلب بيانات المستخدم
    $user_sql = "SELECT mobile1, email, fname, lname FROM user WHERE usr_id = $user_id LIMIT 1";
    $user_res = mysqli_query($con, $user_sql);
    if (!$user_res) {
        die(json_encode(['success' => false, 'error' => 'خطأ في جلب بيانات المستخدم']));
    }
    $user = mysqli_fetch_assoc($user_res);
    
    // ✅ بناء وصف الطلب
    $qty_description = "الكمية: $qty_from | مدة التسليم: $delivery_days يوم";
    if (!empty($requirement)) {
        $qty_description .= " | ملاحظات: $requirement";
    }
    
    // ✅ استعلام INSERT (بدون supplier_id)
    $sql = "INSERT INTO buy_requirement (
        br_pc_id, br_u_id, br_pd_name, br_requirement, 
        br_estimate_qty, br_estimate_qty_unit, br_description,
        br_posting_date, communication_type, source_channel, 
        whatsapp_sent, whatsapp_sent_date
    ) VALUES (
        '$product_id', '$user_id', '$product_name', '$requirement',
        '$qty_to', 'piece', '$qty_description',
        NOW(), 'whatsapp', 'whatsapp_saleoffer', 
        1, NOW()
    )";
    
    error_log("SQL: " . $sql);
    
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        error_log("MySQL Error (insert): " . mysqli_error($con));
        die(json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]));
    }
    
    $insert_id = mysqli_insert_id($con);
    error_log("Inserted ID: $insert_id");
    
    // ✅ بناء رسالة واتساب
    $product_url = "https://" . $_SERVER['HTTP_HOST'] . "/saleoffer-details.php?id=$offer_id";
    $message = "مرحبا شركة مصر مارت\n";
    $message .= "أريد الحصول على أفضل سعر لـ: $product_name\n\n";
    $message .= "RFQ ID: $insert_id\n";
    $message .= "الكمية: $qty_from\n";
    $message .= "مدة التسليم: $delivery_days يوم\n";
    if (!empty($requirement)) {
        $message .= "الملاحظات: $requirement\n";
    }
    $message .= "الرابط: $product_url\n";
    $message .= "هاتفي: " . ($user['mobile1'] ?? '') . "\n";
    $message .= "بريدي: " . ($user['email'] ?? '');
    
    $wa_url = "https://api.whatsapp.com/send?phone=201104832811&text=" . urlencode($message);
    
    die(json_encode(['success' => true, 'whatsapp_url' => $wa_url]));
}

// ============================================================
// ✅ الكود الأصلي للمنتجات العادية
// ============================================================
if ($product_id == 0) {
    die(json_encode(['success' => false, 'error' => 'بيانات المنتج غير صالحة (product_id = 0)']));
}

// جلب بيانات المستخدم
$user_sql = "SELECT mobile1, email, fname, lname FROM user WHERE usr_id = $user_id LIMIT 1";
$user_res = mysqli_query($con, $user_sql);
$user = mysqli_fetch_assoc($user_res);

$user_fullname = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));

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

$result = mysqli_query($con, $sql);

if (!$result) {
    error_log("MySQL Error (regular): " . mysqli_error($con));
    die(json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]));
}

$insert_id = mysqli_insert_id($con);
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

die(json_encode(['success' => true, 'whatsapp_url' => $wa_url]));
?>