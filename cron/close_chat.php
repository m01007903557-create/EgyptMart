<?php
// تشغيل هذا الملف يومياً عبر Cron Job
require_once "../lib/connect.php";

// تحديث حالة العروض المقبولة منذ أكثر من 7 أيام
$sql = "UPDATE offers o 
        SET o.status = 'locked', o.locked_at = NOW() 
        WHERE o.status = 'accepted' 
        AND o.accepted_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
mysqli_query($con, $sql);

// تحديث حالة الشات
$chat_sql = "UPDATE chat_rooms c 
             SET c.is_locked = 1, c.locked_at = NOW() 
             WHERE c.rfq_id IN (
                 SELECT rfq_id FROM offers WHERE status = 'locked'
             )";
mysqli_query($con, $chat_sql);

echo "تم إغلاق " . mysqli_affected_rows($con) . " محادثة منتهية الصلاحية.";
?>