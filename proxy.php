<?php
// proxy.php - يتخطى مشاكل .htaccess و Hostinger Firewall

// 1. الهيدرز المطلوبة لفيسبوك
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
header('X-Accel-Buffering: no'); // مهم لـ Hostinger

// 2. سجل الطلب عشان نعرف فيسبوك وصل ولا لأ
file_put_contents(__DIR__. '/fb_log.txt', date('c'). ' PROXY: '. json_encode($_GET). "\n", FILE_APPEND);

// 3. رد التحقق مباشرة بدون ما نروح لأي ملف تاني
if (isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe') {
    if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === 'egyptmart_2024') {
        echo $_GET['hub_challenge'];
        exit;
    } else {
        echo 'Invalid Verify Token';
        exit;
    }
}

// 4. لو مش طلب تحقق، مرر الطلب لـ fb_hook.php الأصلي
if (file_exists(__DIR__ . '/fb_hook.php')) {
    include __DIR__ . '/fb_hook.php';
} else {
    echo 'OK';
}
?>