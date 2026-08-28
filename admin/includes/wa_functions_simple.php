<?php
// دوال مبسطة لـ WhatsApp RFQ (نسخة كاملة مع الصورة والدولة والمحافظة)

function get_whatsapp_rfq_list($start, $limit, $search = '', $status = 'all') {
    global $con;
    
    $sql = "SELECT 
                br.*,
                u.fname,
                u.lname,
                u.mobile1,
                u.email
            FROM buy_requirement br
            LEFT JOIN user u ON br.br_u_id = u.usr_id
            WHERE 1=1";
    
    // ✅ لا نفلتر على source_platform، نعرض كل الطلبات
    if (!empty($search)) {
        $search = mysqli_real_escape_string($con, $search);
        $sql .= " AND (br.br_id LIKE '%$search%' 
                    OR br.br_pd_name LIKE '%$search%' 
                    OR br.br_requirement LIKE '%$search%')";
    }
    
    if ($status != 'all') {
        $status = mysqli_real_escape_string($con, $status);
        $sql .= " AND br.wa_status = '$status'";
    }
    
    $sql .= " ORDER BY br.br_id DESC LIMIT $start, $limit";
    
    $result = mysqli_query($con, $sql);
    return $result;
}

function get_whatsapp_rfq_count($search, $status) {
    global $con;
    
    // ✅ استخدام source_channel بدلاً من communication_type
    $where = "source_channel = 'whatsapp_platform'";
    
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

function delete_whatsapp_rfq($id) {
    global $con;
    $sql = "DELETE FROM buy_requirement WHERE br_id = $id AND communication_type = 'whatsapp'";
    return mysqli_query($con, $sql);
}

function publish_to_public_rfq($id) {
    global $con;
    $sql = "UPDATE buy_requirement SET br_display_status = 1 WHERE br_id = $id";
    return mysqli_query($con, $sql);
}

?>