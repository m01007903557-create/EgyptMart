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

// ============================================================
// ✅ جلب جميع البيانات
// ============================================================
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$product_name = isset($_POST['product_name']) ? mysqli_real_escape_string($con, $_POST['product_name']) : '';
$qty_from = isset($_POST['qty_from']) ? (int)$_POST['qty_from'] : 0;
$qty_to = isset($_POST['qty_to']) ? (int)$_POST['qty_to'] : 0;
$requirement = isset($_POST['requirement_details']) ? mysqli_real_escape_string($con, $_POST['requirement_details']) : '';

// ✅ بيانات خاصة بـ saleoffer
$request_type = isset($_POST['request_type']) ? $_POST['request_type'] : '';
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
$delivery_days = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 7;
$supplier_id_from_post = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;

// ✅ سجل كامل للبيانات المستلمة
error_log("=== WhatsApp RFQ Handler ===");
error_log("request_type: " . $request_type);
error_log("offer_id: " . $offer_id);
error_log("product_id: " . $product_id);
error_log("user_id: " . $user_id);
error_log("POST data: " . print_r($_POST, true));

// ============================================================
// ✅ معالجة طلبات saleoffer-details (تأتي أولاً)
// ============================================================
if ($request_type == 'saleoffer') {
    error_log("✅ Processing SaleOffer Request");
    
    // ✅ التحقق من وجود offer_id
    if ($offer_id == 0) {
        error_log("❌ offer_id is zero. POST data: " . print_r($_POST, true));
        echo json_encode(['success' => false, 'error' => 'معرف العرض غير صالح - تأكد من إرسال offer_id']);
        exit;
    }
    
    // جلب تفاصيل العرض من قاعدة البيانات
    $offer_sql = "SELECT so_service, so_price, so_id, usr_id FROM sale_offers WHERE so_id = $offer_id";
    $offer_res = mysqli_query($con, $offer_sql);
    
    if (!$offer_res) {
        error_log("❌ Offer Query Error: " . mysqli_error($con));
        echo json_encode(['success' => false, 'error' => 'خطأ في جلب بيانات العرض: ' . mysqli_error($con)]);
        exit;
    }
    
    $offer_data = mysqli_fetch_assoc($offer_res);
    
    if (!$offer_data) {
        error_log("❌ Offer not found: $offer_id");
        echo json_encode(['success' => false, 'error' => 'العرض غير موجود في قاعدة البيانات']);
        exit;
    }
    
    error_log("✅ Offer found: " . print_r($offer_data, true));
    
    // ✅ بناء البيانات من العرض
    $product_name = $offer_data['so_service'] . ' (عرض بيع)';
    $product_id = $offer_id;
    $supplier_id = $offer_data['usr_id'];
    
    if ($supplier_id_from_post > 0) {
        $supplier_id = $supplier_id_from_post;
    }
    
    // ✅ التحقق من وجود كمية
    if ($qty_from == 0) $qty_from = 1;
    if ($qty_to == 0) $qty_to = 1;
    
    // ✅ جلب بيانات المستخدم
    $user_query = mysqli_query($con, "SELECT mobile1, email, fname, lname FROM user WHERE usr_id='$user_id'");
    if (!$user_query) {
        error_log("❌ User Query Error: " . mysqli_error($con));
        echo json_encode(['success' => false, 'error' => 'خطأ في جلب بيانات المستخدم']);
        exit;
    }
    $user = mysqli_fetch_assoc($user_query);
    
    if (!$user) {
        error_log("❌ User not found: $user_id");
        echo json_encode(['success' => false, 'error' => 'المستخدم غير موجود']);
        exit;
    }
    
    $user_fullname = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
    
    // ✅ حفظ في قاعدة البيانات مع supplier_id و source_channel مميز
    $qty_description = "Qty: $qty_from | Delivery: $delivery_days days";
    if (!empty($requirement)) {
        $qty_description .= " | Notes: $requirement";
    }
    
    $sql = "INSERT INTO buy_requirement (
        br_pc_id, br_u_id, br_pd_name, br_requirement, 
        br_estimate_qty, br_estimate_qty_unit, br_description,
        br_posting_date, communication_type, source_channel, 
        whatsapp_sent, whatsapp_sent_date, supplier_id
    ) VALUES (
        '$product_id', '$user_id', '$product_name', '$requirement',
        '$qty_to', 'piece', '$qty_description',
        NOW(), 'whatsapp', 'whatsapp_saleoffer', 
        1, NOW(), '$supplier_id'
    )";
    
    error_log("✅ SaleOffer SQL: $sql");
    $result = mysqli_query($con, $sql);
    
    if ($result) {
        $insert_id = mysqli_insert_id($con);
        error_log("✅ SaleOffer Inserted ID: $insert_id");
        
        // ✅ بناء رسالة الواتساب
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
        
        echo json_encode(['success' => true, 'whatsapp_url' => $wa_url]);
        exit;
    } else {
        error_log("❌ SaleOffer Insert Error: " . mysqli_error($con));
        echo json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]);
        exit;
    }
}

// ============================================================
// ✅ الكود الأصلي للمنتجات العادية
// ============================================================
if ($product_id == 0) {
    error_log("❌ product_id is zero");
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