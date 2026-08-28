<?php
/**
 * File: /admin/ajax-file/send_offer_notification.php
 * Description: تحديث حالة العرض إلى 'notified' بعد إرسال الإشعار للمشتري
 */

session_start();
require_once __DIR__ . '/../../lib/connect.php';

header('Content-Type: application/json');

// التحقق من صلاحيات الأدمن
if (empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح بالدخول']);
    exit;
}

// التحقق من وجود offer_id
if (!isset($_POST['offer_id']) || empty($_POST['offer_id'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة: offer_id مطلوب']);
    exit;
}

$offer_id = (int)$_POST['offer_id'];

// تحديث حالة العرض إلى 'notified'
$update_sql = "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = ?";
$stmt = mysqli_prepare($con, $update_sql);
mysqli_stmt_bind_param($stmt, 'i', $offer_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'تم تحديث حالة العرض']);
} else {
    echo json_encode(['success' => false, 'error' => 'خطأ في تحديث قاعدة البيانات: ' . mysqli_error($con)]);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
?>