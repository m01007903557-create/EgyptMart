<?php
require_once __DIR__ . '/includes/rfq_functions.php';
require_once __DIR__ . '/common.php';

// التحقق من الطلب
$input_data = json_decode(file_get_contents('php://input'), true);
if (empty($input_data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'لا توجد بيانات']);
    exit;
}

// 1. تحديد المصدر
$source_channel = 'api';
$source_platform = null;

if (!empty($input_data['source_platform'])) {
    switch ($input_data['source_platform']) {
        case 'facebook':
            $source_channel = SOURCE_SOCIAL_FACEBOOK;
            break;
        case 'instagram':
            $source_channel = SOURCE_SOCIAL_INSTAGRAM;
            break;
        case 'telegram':
            $source_channel = SOURCE_SOCIAL_TELEGRAM;
            break;
        case 'whatsapp_business':
            $source_channel = SOURCE_SOCIAL_WHATSAPP;
            break;
        default:
            $source_channel = SOURCE_API;
    }
    $source_platform = $input_data['source_platform'];
} elseif (!empty($input_data['source_channel'])) {
    $source_channel = $input_data['source_channel'];
}

// 2. تحضير البيانات للدالة المركزية
$rfq_data = [
    'br_u_id' => $input_data['user_id'] ?? 1, // افتراضي
    'source_channel' => $source_channel,
    'source_platform' => $source_platform,
    'external_id' => $input_data['external_id'] ?? null,
    'br_pc_id' => $input_data['category_id'] ?? 0,
    'br_pd_name' => $input_data['product_name'] ?? $input_data['message'] ?? 'طلب غير محدد',
    'br_requirement' => $input_data['message'] ?? 'لا توجد تفاصيل',
    'br_estimate_qty' => $input_data['quantity'] ?? 0,
    'br_estimate_qty_unit' => $input_data['unit_id'] ?? 0,
    'br_preferred_supplier_location' => $input_data['location'] ?? 'any',
    'send_whatsapp' => $input_data['send_whatsapp'] ?? true,
    'supplier_phone' => $input_data['supplier_phone'] ?? null,
    'raw_payload' => $input_data
];

// 3. حفظ الطلب باستخدام الدالة المركزية
$result = saveRFQ($rfq_data);

// 4. إرجاع الرد
http_response_code($result['status'] === 'success' ? 200 : 500);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>