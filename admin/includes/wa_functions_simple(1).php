<?php
// دوال مبسطة لـ WhatsApp RFQ (نسخة كاملة مع الصورة والدولة والمحافظة)

function get_whatsapp_rfq_list($start, $limit, $search, $status) {
    global $con;
    
    // ✅ شرط التصفية الأساسي
    $where = "br.source_channel = 'whatsapp_platform'";
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($con, $search);
        $where .= " AND (br.br_pd_name LIKE '%$search%' OR br.br_id LIKE '%$search%')";
    }
    if (!empty($status) && $status != 'all') {
        $where .= " AND br.wa_status = '$status'";
    }
    
    // ✅ استعلام واحد فقط يجمع كل البيانات المطلوبة
    $sql = "SELECT br.*, 
                   u.fname, u.lname, u.mobile1, u.email,
                   c.cn_name as country_name,
                   st.state_name as city_name,
                   p.pd_title, p.pd_image,
                   bp.bnsprof_comp_url
            FROM buy_requirement br
            LEFT JOIN user u ON br.br_u_id = u.usr_id
            LEFT JOIN country c ON c.cn_id = u.country
            LEFT JOIN products p ON br.br_pc_id = p.pd_id
            LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
            LEFT JOIN states st ON st.state_id = bp.bnsprof_state
            WHERE $where
            ORDER BY br.br_posting_date DESC
            LIMIT $start, $limit";
    
    return mysqli_query($con, $sql);
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