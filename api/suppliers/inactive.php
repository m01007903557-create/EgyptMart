<?php
// api/suppliers/inactive.php
require_once __DIR__ . '/../includes/auth.php';
authenticate();

global $con;

// 1. تحديد فترة الخمول (مثلاً 30 يوم)
$inactive_days = 30; // يمكن جعلها قابلة للتغيير عبر الإعدادات

// 2. استعلام جلب الموردين الخاملين
$sql = "SELECT 
            u.usr_id,
            u.fname,
            u.lname,
            u.mobile1,
            u.email,
            MAX(a.created_at) as last_activity
        FROM user u
        LEFT JOIN activity_log a ON u.usr_id = a.user_id
        WHERE u.user_type = 'supplier' 
          AND u.status = 'active'
        GROUP BY u.usr_id
        HAVING MAX(a.created_at) < NOW() - INTERVAL ? DAY
           OR MAX(a.created_at) IS NULL";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $inactive_days);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$suppliers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $suppliers[] = [
        'id' => (int)$row['usr_id'],
        'name' => $row['fname'] . ' ' . $row['lname'],
        'phone' => $row['mobile1'],
        'email' => $row['email'],
        'last_activity' => $row['last_activity'] ?? null
    ];
}

echo json_encode([
    'status' => 'success',
    'suppliers' => $suppliers,
    'total' => count($suppliers)
]);
?>