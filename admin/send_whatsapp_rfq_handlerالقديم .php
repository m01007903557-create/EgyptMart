<?php
session_start();
require_once "../common.php";
require_once "../includes/whatsapp_enquiries_functions.php";
check_admin_login();
header('Content-Type: application/json');

$rfq_id = isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0;
if (!$rfq_id) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// جلب بيانات الطلب الأصلي من buy_requirement
$sql = "SELECT br.*, 
               p.pd_title, p.pd_uid as supplier_id,
               u.fname, u.lname, u.mobile1, u.email,
               bp.bnsprof_comp_url, bp.bnsprof_mobile1, bp.bnsprof_email
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
        WHERE br.br_id = $rfq_id AND br.communication_type = 'whatsapp'";
$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// حفظ في جدول whatsapp_rfq_messages
$supplier_id = $rfq['supplier_id'];
$buyer_id = $rfq['br_u_id'];
$buyer_phone = $rfq['mobile1'];
$product_id = $rfq['br_pc_id'];
$quantity = $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit'];
$notes = $rfq['br_requirement'];

// ========== أضف هذا الكود هنا ==========
// تنسيق رقم جوال المورد
$supplier_phone = $rfq['mobile1'] ?? '';
if (!empty($supplier_phone)) {
    // إذا كان الرقم يبدأ بـ 10، أضف 0 في البداية
    if (substr($supplier_phone, 0, 2) == '10') {
        $supplier_phone = '0' . $supplier_phone;
    }
}
// =======================================

// 3. إدراج في جدول whatsapp_rfq_messages
$insert_sql = "INSERT INTO whatsapp_rfq_messages (rfq_id, product_id, supplier_id, buyer_id, buyer_phone, quantity_required, notes, status, source, created_date) 
               VALUES ($rfq_id, $product_id, $supplier_id, $buyer_id, '$buyer_phone', '$quantity', '$notes', 'new', 'whatsapp_rfq', NOW())";
$insert_result = mysqli_query($con, $insert_sql);

// ========== كود التصحيح ==========
error_log("=== بدء إرسال RFQ #$rfq_id ===");
error_log("استعلام الإدراج: " . $insert_sql);
error_log("نتيجة الإدراج: " . ($insert_result ? 'نجاح' : 'فشل - ' . mysqli_error($con)));

if ($insert_result) {
    // تنسيق رقم جوال المورد
    $supplier_phone = $rfq['mobile1'] ?? '';
    error_log("رقم الجوال الخام: " . $supplier_phone);
    
    if (!empty($supplier_phone) && substr($supplier_phone, 0, 2) == '10') {
        $supplier_phone = '0' . $supplier_phone;
    }
    error_log("رقم الجوال بعد التنسيق: " . $supplier_phone);
    
    // إرسال إشعار واتساب للمورد
    if (!empty($supplier_phone)) {
        $message_text = "📦 طلب شراء جديد RFQ #$rfq_id\n";
        $message_text .= "المنتج: " . ($rfq['pd_title'] ?? '') . "\n";
        $message_text .= "الكمية: $quantity\n";
        $message_text .= "التاريخ: " . date('Y-m-d') . "\n";
        $message_text .= "رابط الطلب: https://" . $_SERVER['HTTP_HOST'] . "/my-enquiries.php";
        
        $whatsapp_url = "https://api.whatsapp.com/send?phone=" . $supplier_phone . "&text=" . urlencode($message_text);
        error_log("رابط واتساب: " . $whatsapp_url);
        
        echo json_encode(['success' => true, 'whatsapp_url' => $whatsapp_url]);
    } else {
        error_log("رقم الجوال فارغ - لم يتم إرسال واتساب");
        echo json_encode(['success' => true, 'whatsapp_url' => null, 'message' => 'تم الحفظ ولكن رقم الجوال غير متوفر']);
    }
} else {
    error_log("فشل الإدراج في قاعدة البيانات: " . mysqli_error($con));
    echo json_encode(['success' => false, 'error' => 'فشل الحفظ في قاعدة البيانات: ' . mysqli_error($con)]);
}

// =============================================


// إشعار للمورد (في dashboard)
$msg_sql = "INSERT INTO supplier_messages (supplier_id, rfq_id, message, created_at) 
            VALUES ($supplier_id, $rfq_id, 'طلب WhatsApp RFQ جديد - رقم: $rfq_id', NOW())";
mysqli_query($con, $msg_sql);

echo json_encode(['success' => true, 'message' => 'تم إرسال الطلب للمورد']);
?>