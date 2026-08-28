<?php
/**
 * API: جلب المستهدفين لإرسال النشرة
 * Endpoint: /api/newsletters/targets?newsletter_id=XXX
 */

// 1. تضمين ملفات الاتصال بقاعدة البيانات
require_once __DIR__ . '/../../lib/connect.php'; // تأكد من المسار الصحيح

// 2. التحقق من newsletter_id
$newsletter_id = isset($_GET['newsletter_id']) ? (int)$_GET['newsletter_id'] : 0;

if ($newsletter_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid newsletter_id']);
    exit;
}

// 3. جلب بيانات النشرة
$sql_newsletter = "SELECT nc_category, nc_country, nc_companies, nc_channel 
                   FROM newsletter_content 
                   WHERE nc_id = ?";
$stmt = mysqli_prepare($con, $sql_newsletter);
mysqli_stmt_bind_param($stmt, 'i', $newsletter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$newsletter = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$newsletter) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Newsletter not found']);
    exit;
}

// 4. بناء استعلام جلب المستهدفين
$where_conditions = [];
$params = [];
$types = '';

// 4.1 فلترة حسب البلد
if (!empty($newsletter['nc_country'])) {
    $countries = explode(',', $newsletter['nc_country']);
    $placeholders = implode(',', array_fill(0, count($countries), '?'));
    $where_conditions[] = "u.country IN ($placeholders)";
    foreach ($countries as $country) {
        $params[] = (int)$country;
        $types .= 'i';
    }
}

// 4.2 فلترة حسب التصنيفات
if (!empty($newsletter['nc_category'])) {
    $categories = explode(',', $newsletter['nc_category']);
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $where_conditions[] = "EXISTS (
        SELECT 1 FROM selloffer_alert_category sac 
        WHERE sac.sac_usr_id = u.usr_id 
        AND sac.sac_pc_id IN ($placeholders)
        AND sac.sac_status = 1
    )";
    foreach ($categories as $category) {
        $params[] = (int)$category;
        $types .= 'i';
    }
}

// 4.3 فلترة حسب الشركات المحددة
if (!empty($newsletter['nc_companies'])) {
    $companies = explode(',', $newsletter['nc_companies']);
    $placeholders = implode(',', array_fill(0, count($companies), '?'));
    $where_conditions[] = "u.usr_id IN ($placeholders)";
    foreach ($companies as $company) {
        $params[] = (int)$company;
        $types .= 'i';
    }
}


// 4.4 إضافة شرط المستخدمين النشطين من نوع مورد
// إذا لم تكن هناك معايير، جلب جميع الموردين النشطين
if (empty($where_conditions)) {
    $where_conditions[] = "u.user_type = 'supplier' AND u.status = 1";
} else {
    $where_conditions[] = "u.user_type = 'supplier' AND u.status = 1";
}

$where_clause = implode(' AND ', $where_conditions);

// 5. استعلام جلب المستهدفين
$sql_targets = "SELECT u.usr_id, u.fname, u.lname, u.mobile1, u.email 
                FROM user u
                WHERE $where_clause";

$stmt = mysqli_prepare($con, $sql_targets);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$targets = [];
while ($row = mysqli_fetch_assoc($result)) {
    // تنظيف رقم الهاتف
    $phone = preg_replace('/[^0-9]/', '', $row['mobile1'] ?? '');
    if (!empty($phone) && substr($phone, 0, 2) != '20') {
        $phone = '20' . ltrim($phone, '0');
    }
    
    $targets[] = [
        'target_id' => (int)$row['usr_id'],
        'name' => trim($row['fname'] . ' ' . $row['lname']),
        'phone' => $phone,
        'email' => $row['email'] ?? ''
    ];
}
mysqli_stmt_close($stmt);

// 6. إرجاع النتيجة
echo json_encode([
    'status' => 'success',
    'newsletter_id' => $newsletter_id,
    'targets' => $targets,
    'total' => count($targets)
]);
?>