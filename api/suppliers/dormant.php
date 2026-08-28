<?php
/**
 * API: /api/suppliers/dormant
 * الهدف: جلب الموردين الخاملين (لم يسجلوا دخول من 30 يوم) + المنتجات الخاملة
 */

header('Content-Type: application/json');

// ✅ التحقق من مفتاح API
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
$valid_key = 'YOUR_SECRET_API_KEY'; // ✅ استبدل بالمفتاح الحقيقي

if ($api_key !== $valid_key) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// ✅ جلب المعاملات
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ✅ الاتصال بقاعدة البيانات
global $con;
if (!$con) {
    require_once __DIR__ . '/../lib/connect.php';
}

// ✅ 1. جلب الموردين الخاملين + عدد المنتجات الخاملة
$sql = "SELECT 
            u.usr_id as supplier_id,
            u.fname as supplier_name,
            u.lname,
            u.mobile1 as whatsapp,
            u.email,
            u.last_login_at,
            COUNT(p.pd_id) as dormant_products_count,
            MAX(a.created_at) as last_activity
        FROM user u
        LEFT JOIN products p ON u.usr_id = p.pd_uid AND p.pd_status = '0'
        LEFT JOIN activity_log a ON u.usr_id = a.user_id
        WHERE u.user_type = 'supplier' 
          AND u.status = 'active'
          AND (u.last_login_at < DATE_SUB(NOW(), INTERVAL ? DAY) OR u.last_login_at IS NULL)
        GROUP BY u.usr_id
        HAVING dormant_products_count > 0
        ORDER BY dormant_products_count DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $days, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$suppliers = [];
while ($row = mysqli_fetch_assoc($result)) {
    // ✅ تنظيف رقم الهاتف (بصيغة دولية)
    $phone = preg_replace('/[^0-9]/', '', $row['whatsapp']);
    if (substr($phone, 0, 1) == '0') {
        $phone = '20' . substr($phone, 1);
    }
    if (!substr($phone, 0, 2) == '20') {
        $phone = '20' . $phone;
    }
    
    // ✅ إنشاء رابط تحديث آمن
    $token = md5($row['supplier_id'] . $row['email'] . 'egyptmart_secret');
    $update_link = 'https://' . $_SERVER['HTTP_HOST'] . '/supplier/products?token=' . $token;
    
    $suppliers[] = [
        'supplier_id' => (int)$row['supplier_id'],
        'supplier_name' => trim($row['supplier_name'] . ' ' . $row['lname']),
        'whatsapp' => '+' . $phone,
        'email' => $row['email'],
        'dormant_products_count' => (int)$row['dormant_products_count'],
        'last_login_at' => $row['last_login_at'] ?? null,
        'last_activity' => $row['last_activity'] ?? null,
        'update_link' => $update_link
    ];
}

// ✅ 2. جلب العدد الإجمالي (للمساعدة في الترقيم)
$count_sql = "SELECT COUNT(DISTINCT u.usr_id) as total
              FROM user u
              LEFT JOIN products p ON u.usr_id = p.pd_uid AND p.pd_status = '0'
              WHERE u.user_type = 'supplier' 
                AND u.status = 'active'
                AND (u.last_login_at < DATE_SUB(NOW(), INTERVAL ? DAY) OR u.last_login_at IS NULL)
              HAVING COUNT(p.pd_id) > 0";

$count_stmt = mysqli_prepare($con, $count_sql);
mysqli_stmt_bind_param($count_stmt, 'i', $days);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);

// ✅ 3. إرجاع النتيجة
echo json_encode([
    'success' => true,
    'data' => $suppliers,
    'total' => (int)($count_row['total'] ?? 0),
    'page' => $page,
    'limit' => $limit,
    'days' => $days
]);
?>