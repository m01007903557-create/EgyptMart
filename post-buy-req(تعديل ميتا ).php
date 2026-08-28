<?php
declare(strict_types=1);
ob_start();
session_start();
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/includes/rfq_functions.php';

// =============================================
// 1. معالجة كل طلبات API: موقع + واتساب + سوشيال
// =============================================
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    
    $input_data = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($input_data)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'msg' => 'Invalid JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // توحيد أسماء الحقول اللي جاية من Make.com
    $rfq_data = [
        'br_u_id' => $input_data['user_id'] ?? $input_data['br_u_id'] ?? 1,
        'source_channel' => $input_data['source_channel'] ?? 'website', // ده المهم
        'source_detail' => $input_data['source_detail'] ?? $input_data['customer_phone'] ?? '',
        'br_pc_id' => $input_data['category_id'] ?? $input_data['br_pc_id'] ?? 0,
        'br_pd_name' => $input_data['product_name'] ?? $input_data['br_pd_name'] ?? '',
        'br_requirement' => $input_data['message'] ?? $input_data['br_requirement'] ?? '',
        'br_estimate_qty' => $input_data['quantity'] ?? $input_data['br_estimate_qty'] ?? 0,
        'br_estimate_qty_unit' => $input_data['unit_id'] ?? $input_data['br_estimate_qty_unit'] ?? 0,
        'send_whatsapp' => $input_data['send_whatsapp'] ?? false,
        'raw_payload' => $input_data // نخزن كل اللي جالنا
    ];
    
    $result = saveRFQ($rfq_data); // كل الـlogic في مكان واحد
    http_response_code($result['status'] === 'success' ? 200 : 500);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// =============================================
// 2. باقي الكود بتاع الفورم العادي يفضل زي ما هو
// =============================================
// ... كل الكلاس AddProduct والـ HTML زي ما هو بدون تغيير