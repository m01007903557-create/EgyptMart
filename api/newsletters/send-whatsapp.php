<?php
// api/newsletters/send-whatsapp.php
require_once __DIR__ . '/../includes/auth.php';
authenticate();

global $con;

$input = json_decode(file_get_contents('php://input'), true);

$supplier_id = (int)($input['supplier_id'] ?? 0);
$message = $input['message'] ?? '';
$channel = $input['channel'] ?? 'whatsapp';

if ($supplier_id <= 0 || empty($message)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// 1. حفظ الرسالة في جدول الإشعارات (إذا كان موجوداً)
// أو يمكن حفظها مباشرة في activity_log مع نوع النشاط

// 2. تسجيل النشاط في activity_log
$sql = "INSERT INTO activity_log (
            user_id, 
            action, 
            item_type, 
            item_id, 
            item_title, 
            ip_address, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($con, $sql);

$action = 'whatsapp_notification';
$item_type = 'supplier';
$item_id = $supplier_id;
$item_title = 'تم إرسال رسالة توعية عبر واتساب للمورد الخامل: ' . substr($message, 0, 50);
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

mysqli_stmt_bind_param($stmt, 'ississ', 
    $supplier_id, 
    $action, 
    $item_type, 
    $item_id, 
    $item_title, 
    $ip_address
);

if (mysqli_stmt_execute($stmt)) {
    $log_id = mysqli_insert_id($con);
    echo json_encode([
        'status' => 'success',
        'log_id' => $log_id,
        'message' => 'تم تسجيل النشاط بنجاح'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'فشل في تسجيل النشاط: ' . mysqli_error($con)
    ]);
}
?>