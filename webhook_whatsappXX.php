// ==========================================
// 3. webhook_whatsapp.php (جاهز للمستقبل - لا يؤثر على الاختبار الحالي)
// ==========================================
<?php
// هذا الملف سيتم تفعيله لاحقًا مع Meta API
// حاليًا يسجل الطلبات فقط ولا يؤثر على تجربة المستخدم

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// تسجيل للاختبار
$log = date('Y-m-d H:i:s') . " - Webhook received\n";
file_put_contents('whatsapp_webhook_log.txt', $log, FILE_APPEND);

// عندما تركب Meta API لاحقًا، استبدل هذا الكود بالرد الفعلي
// حاليًا لا نرسل أي رد لمنع الأخطاء

http_response_code(200);
echo json_encode(['status' => 'ok']);
?>