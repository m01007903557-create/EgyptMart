<?php
// fb_hook.php - استقبال رسائل فيسبوك وحفظها في قاعدة البيانات

$verify_token = 'EhabFawzy@64';

// سجل كل شيء للديباج
$logFile = __DIR__ . '/fb_log.txt';
$timestamp = date('c');
$input = file_get_contents('php://input');

// سجل الطلبات
file_put_contents($logFile, "$timestamp - METHOD: {$_SERVER['REQUEST_METHOD']} - GET: " . json_encode($_GET) . " - POST: $input\n", FILE_APPEND);

// 1. التحقق من طلب GET من فيسبوك (تأكيد الـ Webhook)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe') {
        if ($_GET['hub_verify_token'] === $verify_token) {
            http_response_code(200);
            header('Content-Type: text/plain');
            echo $_GET['hub_challenge'];
            exit;
        } else {
            http_response_code(403);
            exit('Forbidden');
        }
    }
    http_response_code(200);
    echo 'OK';
    exit;
}

// 2. معالجة طلب POST (استقبال الرسائل)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode($input, true);

    if (isset($data['entry'][0]['messaging'][0])) {
        $messaging = $data['entry'][0]['messaging'][0];
        $senderId = $messaging['sender']['id'] ?? '';
        $messageText = $messaging['message']['text'] ?? '';
        $timestamp = date('Y-m-d H:i:s');

        file_put_contents($logFile, "$timestamp - رسالة من $senderId: $messageText\n", FILE_APPEND);

        // =============================================
        // حفظ البيانات في قاعدة البيانات
        // =============================================
        require_once __DIR__ . '/lib/connect.php'; // ✅ المسار الصحيح

        // 1. التحقق من وجود المستخدم (أو إنشاؤه)
        $user_id = 1; // يمكنك تغيير هذا لاحقاً لاستخراج المعرف الفعلي
        // إذا كان لديك نظام مستخدمين، يمكنك البحث عن المستخدم باستخدام $senderId

        // 2. إعداد استعلام الإدراج
        $sql = "INSERT INTO buy_requirement (
            br_u_id, 
            br_pd_name, 
            br_requirement, 
            source_channel, 
            source_platform, 
            source_detail, 
            external_id, 
            raw_payload, 
            br_posting_date, 
            br_status, 
            communication_type
        ) VALUES (
            ?, ?, ?, 
            'facebook', 'facebook', ?, ?, 
            ?, 
            NOW(), 
            'new', 
            'facebook'
        )";

        $stmt = mysqli_prepare($con, $sql);
        $product_name = 'رسالة من فيسبوك';
        $source_detail = $senderId;
        $external_id = 'fb_' . $senderId . '_' . time();
        $raw_payload_json = json_encode($data);

        mysqli_stmt_bind_param($stmt, 'issss', 
            $user_id, 
            $product_name, 
            $messageText, 
            $source_detail, 
            $external_id, 
            $raw_payload_json
        );

        if (mysqli_stmt_execute($stmt)) {
            $br_id = mysqli_insert_id($con);
            file_put_contents($logFile, "$timestamp - ✅ تم حفظ الرسالة في قاعدة البيانات (br_id: $br_id)\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "$timestamp - ❌ خطأ في الحفظ: " . mysqli_error($con) . "\n", FILE_APPEND);
        }

        mysqli_stmt_close($stmt);
    }

    http_response_code(200);
    echo 'OK';
    exit;
}

// 3. أي طلب آخر
http_response_code(200);
echo 'OK';
?>