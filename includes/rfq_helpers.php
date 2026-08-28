<?php
/**
 * ملف مساعد لإدارة أزرار RFQ
 * includes/rfq_helpers.php
 */

/**
 * دالة عرض أزرار RFQ بناءً على الحالة ودور المستخدم
 */
function render_rfq_buttons($status, $user_type, $rfq_id, $offer_id = 0, $update_count = 0) {
    $html = '';
    
    if ($status == 'pending_buyer' && $user_type == 'supplier') {
        $html .= '<button class="btn btn-success" onclick="sendOffer(' . $rfq_id . ')">
                    <i class="fa fa-money"></i> إرسال أول عرض سعر
                  </button>';
    }
    
    if ($status == 'quoted_once' && $user_type == 'admin') {
        $html .= '<button class="btn btn-primary send-notification" 
                        data-offer-id="' . $offer_id . '"
                        data-rfq-id="' . $rfq_id . '">
                    <i class="fa fa-send"></i> إرسال العرض للمشتري
                  </button>';
    }
    
    if ($status == 'in_negotiation') {
        if ($user_type == 'buyer') {
            $html .= '<button class="btn btn-success" onclick="acceptOffer(' . $offer_id . ', ' . $rfq_id . ')">
                        <i class="fa fa-check"></i> قبول العرض
                      </button>';
            $html .= '<button class="btn btn-danger" onclick="rejectOffer(' . $offer_id . ', ' . $rfq_id . ')">
                        <i class="fa fa-times"></i> رفض العرض
                      </button>';
        }
        if ($user_type == 'supplier') {
            $remaining = 2 - $update_count;
            if ($remaining > 0) {
                $html .= '<button class="btn btn-warning" onclick="updateOffer(' . $rfq_id . ', ' . $offer_id . ')">
                            <i class="fa fa-edit"></i> تحديث السعر (' . $remaining . ' فرص متبقية)
                          </button>';
            } else {
                $html .= '<span class="text-muted">تم استنفاذ فرص التحديث</span>';
            }
        }
    }
    
    if (in_array($status, ['accepted', 'rejected'])) {
        $html .= '<span class="text-muted">تم إغلاق الطلب - Read Only</span>';
    }
    
    // زر المحادثة
    if (!in_array($status, ['accepted', 'rejected'])) {
    $chat_code = get_chat_code($rfq_id);
    if ($chat_code) {
        $html .= '<a href="/chat/chat.php?chat_code=' . $chat_code . '" class="btn btn-info" target="_blank">
                    <i class="fa fa-comments"></i> بدء المحادثة
                  </a>';
        }
    }
    
    return $html;
}

/**
 * دالة مساعدة لجلب رمز المحادثة
 */
function get_chat_code($rfq_id) {
    global $con;
    $chat_code = '';
    $chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
    if ($chat_check && mysqli_num_rows($chat_check) > 0) {
        $chat_data = mysqli_fetch_assoc($chat_check);
        $chat_code = $chat_data['chat_code'];
    }
    return $chat_code;
}

/**
 * دالة تحديث حالة الطلب
 */
function update_rfq_status($rfq_id, $new_status) {
    global $con;
    $sql = "UPDATE buy_requirement SET rfq_status = '$new_status' WHERE br_id = $rfq_id";
    return mysqli_query($con, $sql);
}

/**
 * دالة جلب حالة الطلب الحالية
 */
function get_rfq_status($rfq_id) {
    global $con;
    $sql = "SELECT rfq_status FROM buy_requirement WHERE br_id = $rfq_id";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['rfq_status'] : 'pending_buyer';
}
?>