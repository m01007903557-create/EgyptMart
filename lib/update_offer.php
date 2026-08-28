<?php
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json');

// إيقاف عرض أي أخطاء
error_reporting(0);
ini_set('display_errors', 0);

$response = ['success' => false, 'message' => 'حدث خطأ غير معروف'];

try {
    // التحقق من تسجيل الدخول
    if (empty($_SESSION['uid_indm'])) {
        throw new Exception('غير مصرح. يرجى تسجيل الدخول.');
    }
    
    $supplier_id = (int)$_SESSION['uid_indm'];
    
    // التحقق من البيانات
    if (empty($_POST['offer_id']) || empty($_POST['price']) || empty($_POST['delivery_days'])) {
        throw new Exception('بيانات غير مكتملة');
    }
    
    $offer_id = (int)$_POST['offer_id'];
    $new_price = (float)$_POST['price'];
    $new_delivery = (int)$_POST['delivery_days'];
    $new_notes = isset($_POST['notes']) ? mysqli_real_escape_string($con, $_POST['notes']) : '';
    
    // 1. جلب بيانات العرض الحالي
    $sql = "SELECT o.*, br.br_u_id as buyer_id, br.br_id as rfq_id, u.mobile1 as buyer_phone 
            FROM offers o
            LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
            LEFT JOIN user u ON br.br_u_id = u.usr_id
            WHERE o.id = $offer_id AND o.supplier_id = $supplier_id";
    $result = mysqli_query($con, $sql);
    $offer = mysqli_fetch_assoc($result);
    
    if (!$offer) {
        throw new Exception('العرض غير موجود أو لا يخصك');
    }
    
    // 2. التحقق من عدد التعديلات
    $current_update_count = (int)$offer['update_count'];
    $new_update_count = $current_update_count + 1;
    
    $warning = '';
    if ($current_update_count == 0) {
        $warning = '⚠️ هذا أول تعديل. لا يزال لديك فرصة واحدة أخرى.';
    } elseif ($current_update_count == 1) {
        $warning = '🔴 تنبيه نهائي: هذا هو التعديل الأخير المسموح به! لن تتمكن من التعديل مرة أخرى.';
    } elseif ($current_update_count >= 2) {
        throw new Exception('لا يمكن تعديل السعر أكثر من مرتين. هذا العرض مغلق للتعديل.');
    }
    
    // 3. تحديث العرض (بدون updated_at)
    $update_sql = "UPDATE offers SET 
                    price = $new_price, 
                    delivery_days = $new_delivery, 
                    notes = '$new_notes',
                    update_count = $new_update_count,
                    status = 'negotiation'
                  WHERE id = $offer_id";
    
    if (!mysqli_query($con, $update_sql)) {
        throw new Exception('خطأ في التحديث: ' . mysqli_error($con));
    }
    
    // 4. تسجيل التعديل في offer_logs
    $log_sql = "INSERT INTO offer_logs (offer_id, rfq_id, old_price, new_price, old_delivery_days, new_delivery_days, old_notes, new_notes, updated_by, created_at) 
                VALUES ($offer_id, {$offer['rfq_id']}, {$offer['price']}, $new_price, {$offer['delivery_days']}, $new_delivery, '{$offer['notes']}', '$new_notes', 'supplier', NOW())";
    mysqli_query($con, $log_sql);
    
    // 5. إرسال إشعار للمشتري
   // 7. إرسال إشعار للمشتري
$buyer_id = (int)$offer['buyer_id'];
$rfq_id = (int)$offer['rfq_id'];
$subject = "تعديل عرض سعر لطلبك #$rfq_id";
$body = "قام المورد بتعديل عرض السعر.\n\nالسعر الجديد: $new_price USD\nمدة التوصيل: $new_delivery يوم\nملاحظات: $new_notes";

$msg_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
            VALUES ($supplier_id, $buyer_id, '$subject', '$body', NOW(), 1, 1, 'offer_update', $rfq_id)";
mysqli_query($con, $msg_sql);

    // 6. تجهيز رابط الواتساب
    $buyer_phone_raw = $offer['buyer_phone'] ?? '';
    $buyer_phone = preg_replace('/[^0-9]/', '', $buyer_phone_raw);
    if (substr($buyer_phone, 0, 2) != '20' && strlen($buyer_phone) > 9) {
        $buyer_phone = '20' . ltrim($buyer_phone, '0');
    }
    
    $site_url = "https://egyptmart.shop";
    // الرابط الصحيح - يفتح صفحة تفاصيل الرسالة مباشرة
    $magic_link = "https://egyptmart.shop/ajax-file/enq-details.php?id=" . $msg_id . "&type=inbox&offer_id=" . $offer_id;


    $whatsapp_msg = "🔄 تم تعديل عرض السعر لطلبك #$rfq_id\n\n";
    $whatsapp_msg .= "💰 السعر الجديد: $new_price USD\n";
    $whatsapp_msg .= "⏱ مدة التوصيل: $new_delivery يوم\n";
    $whatsapp_msg .= "📝 ملاحظات المورد: $new_notes\n\n";
    $whatsapp_msg .= "✨ رابط سحري للاطلاع:\n$magic_link\n\n";
    $whatsapp_msg .= "يمكنك قبول العرض أو رفضه من خلال الرابط أعلاه.";
    
    $whatsapp_url = "https://wa.me/" . $buyer_phone . "?text=" . urlencode($whatsapp_msg);
    
    // 7. الرد النهائي
    $response = [
        'success' => true,
        'message' => 'تم تحديث عرض السعر بنجاح',
        'warning' => $warning,
        'whatsapp_url' => $whatsapp_url,
        'magic_link' => $magic_link,
        'update_count' => $new_update_count,
        'is_last_update' => ($new_update_count == 2)
    ];
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>