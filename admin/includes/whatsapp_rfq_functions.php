<?php
// WhatsApp RFQ Functions - مستقل تماماً عن نظام الإيميل
// أضف هذا السطر في بداية ملف admin/whatsapp_rfq_functions.php داخل دالة get_whatsapp_rfq_list، قبل return
error_log("WhatsApp RFQ SQL Query: " . $sql);
function get_whatsapp_rfq_list($start = 0, $limit = 20, $search = '', $status = '') {
    global $con;
    $where = "br.communication_type = 'whatsapp'";
    if (!empty($search)) {
        $search = mysqli_real_escape_string($con, $search);
        $where .= " AND (br.br_pd_name LIKE '%$search%' OR br.br_id LIKE '%$search%' OR br.br_requirement LIKE '%$search%')";
    }
    if (!empty($status) && $status != 'all') {
        $where .= " AND br.wa_status = '$status'";
    }
    
    $sql = "SELECT br.*, 
                   u.fname, u.lname, u.mobile1, u.email,
                   c.cn_name as country_name,
                   s.state_name as city_name,
                   p.pd_title, p.pd_image,
                   p.pd_uid as supplier_id,
                   bp.bnsprof_compname as supplier_company,
                   bp.bnsprof_comp_url
            FROM buy_requirement br
            LEFT JOIN user u ON br.br_u_id = u.usr_id
            LEFT JOIN country c ON c.cn_id = u.country
            LEFT JOIN state s ON s.state_id = u.state
            LEFT JOIN products p ON br.br_pc_id = p.pd_id
            LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
            WHERE $where
            ORDER BY br.br_posting_date DESC
            LIMIT $start, $limit";
    
    return mysqli_query($con, $sql);
}

function get_whatsapp_rfq_count($search = '', $status = '') {
    global $con;
    $where = "communication_type = 'whatsapp'";
    if (!empty($search)) {
        $search = mysqli_real_escape_string($con, $search);
        $where .= " AND (br_pd_name LIKE '%$search%' OR br_id LIKE '%$search%')";
    }
    if (!empty($status) && $status != 'all') {
        $where .= " AND wa_status = '$status'";
    }
    $sql = "SELECT COUNT(*) as total FROM buy_requirement WHERE $where";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'];
}

function get_whatsapp_rfq_by_id($br_id) {
    global $con;
    $sql = "SELECT br.*, 
                   u.fname, u.lname, u.mobile1, u.email,
                   p.pd_title, p.pd_image, p.pd_desc, p.pd_uid as supplier_id,
                   bp.bnsprof_comp_url, bp.bnsprof_email, bp.bnsprof_mobile1
            FROM buy_requirement br
            LEFT JOIN user u ON br.br_u_id = u.usr_id
            LEFT JOIN products p ON br.br_pc_id = p.pd_id
            LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
            WHERE br.br_id = '$br_id' AND br.communication_type = 'whatsapp'";
    $res = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($res);
}

function update_whatsapp_rfq_status($br_id, $status, $notes = '') {
    global $con;
    $notes_esc = mysqli_real_escape_string($con, $notes);
    if (!empty($status)) {
        $sql = "UPDATE buy_requirement SET wa_status = '$status' WHERE br_id = '$br_id'";
        mysqli_query($con, $sql);
    }
    if (!empty($notes)) {
        $sql = "UPDATE buy_requirement SET wa_admin_notes = CONCAT(IFNULL(wa_admin_notes,''), '\n[" . date('Y-m-d H:i:s') . "] $notes_esc') WHERE br_id = '$br_id'";
        mysqli_query($con, $sql);
    }
    return true;
}

function delete_whatsapp_rfq($br_id) {
    global $con;
    $sql = "DELETE FROM buy_requirement WHERE br_id = '$br_id' AND communication_type = 'whatsapp'";
    return mysqli_query($con, $sql);
}

function generate_magic_link($supplier_id, $br_id) {
    global $con;
    $token = md5($supplier_id . $br_id . time() . rand(1000, 9999));
    $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
    $sql = "UPDATE buy_requirement SET wa_magic_token = '$token', wa_token_expiry = '$expiry' WHERE br_id = '$br_id'";
    mysqli_query($con, $sql);
    return "https://" . $_SERVER['HTTP_HOST'] . "/supplier/whatsapp_rfq_view.php?token=$token&id=$br_id";
}

function send_whatsapp_notification_to_supplier($br_id, $type = 'email') {
    global $con;
    $rfq = get_whatsapp_rfq_by_id($br_id);
    if (!$rfq) return false;
    
    $supplier_id = $rfq['supplier_id'];
    $magic_link = generate_magic_link($supplier_id, $br_id);
    
    $product_image = !empty($rfq['pd_image']) ? explode(',', $rfq['pd_image'])[0] : 'noimage.jpg';
    $image_url = "https://" . $_SERVER['HTTP_HOST'] . "/upload/myproduct/$product_image";
    
    $log_entry = date('Y-m-d H:i:s') . " - Sent $type to supplier ID: $supplier_id\n";
    
    if ($type == 'email' && !empty($rfq['bnsprof_email'])) {
        $to = $rfq['bnsprof_email'];
        $subject = "طلب شراء جديد - RFQ #{$rfq['br_id']}";
        $body = build_email_template($rfq, $magic_link, $image_url);
        @mail($to, $subject, $body, "Content-Type: text/html; charset=UTF-8");
        $log_entry .= "Email sent to: $to\n";
    }
    
    if ($type == 'whatsapp' && !empty($rfq['bnsprof_mobile1'])) {
        $wa_msg = "مرحباً {$rfq['bnsprof_comp_url']}\n";
        $wa_msg .= "لديك طلب شراء جديد للمنتج: {$rfq['pd_title']}\n";
        $wa_msg .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
        $wa_msg .= "رقم الطلب: {$rfq['br_id']}\n";
        $wa_msg .= "رابط الطلب: $magic_link\n\n";
        $wa_msg .= "يرجى التواصل مع المشتري لكسب الثقة والترقي في المنصة";
        $wa_url = "https://api.whatsapp.com/send?phone=" . $rfq['bnsprof_mobile1'] . "&text=" . urlencode($wa_msg);
        $log_entry .= "WhatsApp link generated: $wa_url\n";
    }
    
    if ($type == 'dashboard') {
        // تخزين في جدول رسائل المورد (أنشئ الجدول إذا لم يكن موجوداً)
        $sql = "INSERT INTO supplier_messages (supplier_id, rfq_id, message, buyer_name, buyer_phone, buyer_email, product_name, quantity, requirements, created_at) 
                VALUES ('{$rfq['supplier_id']}', '{$rfq['br_id']}', 'طلب شراء جديد', '{$rfq['fname']} {$rfq['lname']}', '{$rfq['mobile1']}', '{$rfq['email']}', '{$rfq['pd_title']}', '{$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}', '{$rfq['br_requirement']}', NOW())";
        @mysqli_query($con, $sql);
        $log_entry .= "Dashboard message stored\n";
    }
    
    // تحديث سجل الإرسال
    $current_log = $rfq['wa_send_log'];
    $new_log = $current_log ? $current_log . "\n" . $log_entry : $log_entry;
    $new_count = ($rfq['wa_sent_count'] ?? 0) + 1;
    $sql = "UPDATE buy_requirement SET wa_send_log = '" . mysqli_real_escape_string($con, $new_log) . "', wa_sent_count = '$new_count', wa_last_sent_date = NOW() WHERE br_id = '$br_id'";
    mysqli_query($con, $sql);
    
    return true;
}

function build_email_template($rfq, $magic_link, $image_url) {
    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial; direction:rtl;">
        <div style="max-width:600px; margin:0 auto; border:1px solid #ddd; padding:20px;">
            <h2 style="color:#25D366;">طلب شراء جديد - RFQ #' . $rfq['br_id'] . '</h2>
            <div style="text-align:center; margin:15px 0;">
                <img src="' . $image_url . '" style="max-width:150px; border-radius:10px;">
            </div>
            <table width="100%">
                <tr><td width="30%"><strong>اسم المنتج:</strong></td><td>' . ($rfq['pd_title'] ?? $rfq['br_pd_name']) . '</td></tr>
                <tr><td><strong>الكمية:</strong></td><td>' . $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit'] . '</td></tr>
                <tr><td><strong>التفاصيل:</strong></td><td>' . nl2br($rfq['br_requirement']) . '</td></tr>
                <tr><td><strong>تاريخ الطلب:</strong></td><td>' . date('Y-m-d', strtotime($rfq['br_posting_date'])) . '</td></tr>
                <tr><td><strong>رابط الطلب:</strong></td><td><a href="' . $magic_link . '">اضغط هنا لعرض الطلب</a></td></tr>
            </table>
            <div style="margin-top:20px; padding:10px; background:#f5f5f5; text-align:center;">
                يرجى التواصل مع المشتري لكسب الثقة والترقي في المنصة
            </div>
        </div>
    </body>
    </html>';
}

function get_similar_suppliers($exclude_supplier_id, $limit = 10) {
    global $con;
    $sql = "SELECT bp.bnsprof_uid, bp.bnsprof_comp_url, bp.bnsprof_email, bp.bnsprof_mobile1
            FROM business_profile bp
            WHERE bp.bnsprof_uid != '$exclude_supplier_id'
            LIMIT $limit";
    return mysqli_query($con, $sql);
}

function resend_to_similar_suppliers($br_id, $supplier_ids = []) {
    global $con;
    $rfq = get_whatsapp_rfq_by_id($br_id);
    if (!$rfq) return false;
    
    $notified = $rfq['wa_notified_suppliers'] ? explode(',', $rfq['wa_notified_suppliers']) : [];
    $new_notified = array_merge($notified, $supplier_ids);
    $new_notified_str = implode(',', array_unique($new_notified));
    
    $sql = "UPDATE buy_requirement SET wa_notified_suppliers = '$new_notified_str' WHERE br_id = '$br_id'";
    return mysqli_query($con, $sql);
}

function publish_to_public_rfq($br_id) {
    global $con;
    $sql = "UPDATE buy_requirement SET br_display_status = '1', br_approval_status = 'approved' WHERE br_id = '$br_id' AND communication_type = 'whatsapp'";
    return mysqli_query($con, $sql);
}
?>