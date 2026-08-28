<?php
require_once dirname(__DIR__) . '/lib/connect.php';

session_start();

if (empty($_SESSION['ad_id_indm'])) {
    die('غير مصرح بالدخول');
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (!$rfq_id) {
    die('RFQ ID مطلوب');
}

// جلب بيانات الطلب والمورد
$sql = "SELECT 
            br.br_id,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            p.pd_title,
            u.mobile1 as supplier_mobile,
            u.fname,
            u.lname
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON p.pd_uid = u.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    die('الطلب غير موجود');
}

$supplier_phone = $rfq['supplier_mobile'] ?? '';

if (empty($supplier_phone)) {
    die('رقم جوال المورد غير موجود: ' . json_encode($rfq));
}

// تنظيف رقم الجوال (للصيغة الدولية)
$supplier_phone = preg_replace('/[^0-9]/', '', $supplier_phone);
if (substr($supplier_phone, 0, 1) == '0') {
    $supplier_phone = substr($supplier_phone, 1);
}
$supplier_phone = '20' . $supplier_phone;

// الـ Token الجديد
$access_token = 'EAASvKRi9KocBRhuSt6OqJEJ28xu3zjnuh4sP79uXG9pmA7wNJFMVZCaLSCzO2WTuY1wgtijKmtsk3ipXC26S0eqTumIj9yVBZBLlzE578IxUOZBu1Il0NN1VXDoSTv67Y00OXKHSFJ41X2LZBfsRuUjdtmciUxmGFge9nwhrY05bZCDBu28dkYcUk4OvCAmNXiXIOtWPSEkkORZA1bCdmK4e4HtRd6AYt1piOQKnfJ3D1YcaNMVNaVmsUYBXa6ZBkGNUTiCu7NHWqqSQJviWGZCx9oOc';
$phone_number_id = '1203497699502465';

// بناء رسالة واتساب
$message = "📦 *طلب شراء جديد عبر المنصة*\n\n";
$message .= "RFQ #: {$rfq['br_id']}\n";
$message .= "المنتج: {$rfq['pd_title']}\n";
$message .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
$message .= "التفاصيل: " . substr($rfq['br_requirement'], 0, 200) . "\n\n";
$message .= "يرجى تقديم عرض سعرك عبر المنصة.";

// إرسال عبر WhatsApp Cloud API
function sendWhatsAppMessage($to, $message, $access_token, $phone_number_id) {
    $url = "https://graph.facebook.com/v18.0/{$phone_number_id}/messages";
    
    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'text',
        'text' => ['body' => $message]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['success' => $http_code == 200, 'response' => json_decode($response, true), 'http_code' => $http_code];
}

$result = sendWhatsAppMessage($supplier_phone, $message, $access_token, $phone_number_id);

// عرض النتيجة
header('Content-Type: text/html; charset=utf-8');
echo "<pre>";
if ($result['success']) {
    echo "✅ تم إرسال الرسالة إلى المورد بنجاح!\n";
    echo "📱 الرقم: $supplier_phone\n";
    echo "📦 الطلب: RFQ #{$rfq['br_id']}\n";
} else {
    echo "❌ فشل الإرسال\n";
    echo "رمز الخطأ HTTP: " . $result['http_code'] . "\n";
    if (isset($result['response']['error'])) {
        echo "الخطأ: " . $result['response']['error']['message'] . "\n";
        echo "الكود: " . $result['response']['error']['code'] . "\n";
    } else {
        echo "الرد: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
    }
}
echo "</pre>";
?>