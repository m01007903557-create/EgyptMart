<?php
function get_whatsapp_rfq_messages($user_id, $user_type = 'supplier') {
    global $con;
    if ($user_type == 'supplier') {
        $sql = "SELECT w.*, p.pd_title as product_name 
                FROM whatsapp_rfq_messages w
                LEFT JOIN products p ON w.product_id = p.pd_id
                WHERE w.supplier_id = $user_id 
                ORDER BY w.created_date DESC";
    } else {
        $sql = "SELECT w.*, p.pd_title as product_name 
                FROM whatsapp_rfq_messages w
                LEFT JOIN products p ON w.product_id = p.pd_id
                WHERE w.buyer_id = $user_id 
                ORDER BY w.created_date DESC";
    }
    return mysqli_query($con, $sql);
}

function get_whatsapp_quote($rfq_id, $supplier_id) {
    global $con;
    $sql = "SELECT * FROM whatsapp_quotes WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id";
    $res = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($res);
}

function update_whatsapp_rfq_status($rfq_id, $status) {
    global $con;
    $sql = "UPDATE whatsapp_rfq_messages SET status = '$status' WHERE rfq_id = $rfq_id";
    return mysqli_query($con, $sql);
}

function send_whatsapp_notification($phone, $message) {
    $wa_url = "https://api.whatsapp.com/send?phone=" . ltrim($phone, '+') . "&text=" . urlencode($message);
    return $wa_url; // يفتح واتساب بالرسالة الجاهزة
}
?>