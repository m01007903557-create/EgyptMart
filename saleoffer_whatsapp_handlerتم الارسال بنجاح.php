<?php
/**
 * saleoffer_whatsapp_handler.php - معالج طلبات واتساب لعروض البيع
 */

session_start();
require_once __DIR__ . '/lib/connect.php';

header('Content-Type: application/json');

// ✅ اختبار بسيط
if (isset($_GET['test'])) {
    echo json_encode(['test' => 'OK', 'session' => $_SESSION['uid_indm'] ?? 'no session']);
    exit;
}

// ✅ التحقق من تسجيل الدخول
$user_id = $_SESSION['uid_indm'] ?? 0;
if ($user_id == 0) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

// ✅ جلب البيانات من POST
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
$product_name = isset($_POST['product_name']) ? mysqli_real_escape_string($con, trim($_POST['product_name'])) : '';
$requirement = isset($_POST['requirement_details']) ? mysqli_real_escape_string($con, trim($_POST['requirement_details'])) : '';

// ✅ التحقق من وجود offer_id
if ($offer_id == 0) {
    echo json_encode(['success' => false, 'error' => 'معرف العرض غير صالح']);
    exit;
}

// ✅ جلب بيانات المستخدم
$user_sql = "SELECT mobile1, email, fname, lname FROM user WHERE usr_id = $user_id LIMIT 1";
$user_res = mysqli_query($con, $user_sql);
$user = mysqli_fetch_assoc($user_res);

// ✅ ============================================================
// ✅ استعلام INSERT مبسط للاختبار (يستخدم فقط الأعمدة الأساسية)
// ✅ ============================================================
$sql = "INSERT INTO buy_requirement (
    br_u_id,
    br_pd_name,
    br_requirement,
    br_posting_date
) VALUES (
    '$user_id',
    '$product_name',
    '$requirement',
    NOW()
)";

// ✅ سجل الاستعلام
error_log("📝 SQL: " . $sql);

$result = mysqli_query($con, $sql);

if (!$result) {
    error_log("❌ MySQL Error: " . mysqli_error($con));
    echo json_encode([
        'success' => false,
        'error' => 'خطأ في الحفظ: ' . mysqli_error($con)
    ]);
    exit;
}

$insert_id = mysqli_insert_id($con);
error_log("✅ Inserted ID: $insert_id");

// ✅ بناء رسالة واتساب
$product_url = "https://" . $_SERVER['HTTP_HOST'] . "/saleoffer-details.php?id=$offer_id";

$message = "مرحبا شركة مصر مارت\n";
$message .= "أريد الحصول على أفضل سعر لـ: $product_name\n\n";
$message .= "RFQ ID: $insert_id\n";
if (!empty($requirement)) {
    $message .= "الملاحظات: $requirement\n";
}
$message .= "الرابط: $product_url\n";
$message .= "هاتفي: " . ($user['mobile1'] ?? '') . "\n";
$message .= "بريدي: " . ($user['email'] ?? '');

$wa_url = "https://api.whatsapp.com/send?phone=201104832811&text=" . urlencode($message);

echo json_encode(['success' => true, 'whatsapp_url' => $wa_url]);
exit;
?>