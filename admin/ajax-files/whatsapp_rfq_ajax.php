<?php
include "../includes/header.php";
include "../includes/whatsapp_rfq_functions.php";

$action = $_REQUEST['action'] ?? '';

if($action == 'get_details') {
    $id = $_GET['id'];
    $rfq = get_whatsapp_rfq_by_id($id);
    echo json_encode($rfq);
}

if($action == 'update_status') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    update_whatsapp_rfq_status($id, $status, 'تحديث الحالة بواسطة الأدمن');
    echo 'ok';
}

if($action == 'delete') {
    $id = $_POST['id'];
    delete_whatsapp_rfq($id);
    echo 'ok';
}

if($action == 'save_notes') {
    $id = $_POST['id'];
    $notes = $_POST['notes'];
    update_whatsapp_rfq_status($id, null, 'ملاحظات: ' . $notes);
    echo 'ok';
}

if($action == 'send_to_supplier') {
    $id = $_POST['id'];
    send_whatsapp_notification_to_supplier($id, 'email');
    send_whatsapp_notification_to_supplier($id, 'whatsapp');
    send_whatsapp_notification_to_supplier($id, 'dashboard');
    echo 'تم إرسال الإشعار للمورد (إيميل، واتساب، لوحة التحكم)';
}

if($action == 'get_similar') {
    $id = $_GET['id'];
    $rfq = get_whatsapp_rfq_by_id($id);
    $suppliers = get_similar_suppliers($rfq['pd_cat_id'], $rfq['pd_subcat_id'], $rfq['supplier_id'], 10);
    $html = '<table class="table">';
    $html .= '<tr><th><input type="checkbox" id="simAll"></th><th>اسم الشركة</th><th>نوع العضوية</th><th>البلد</th></tr>';
    while($s = mysqli_fetch_assoc($suppliers)) {
        $html .= '<tr>';
        $html .= '<td><input type="checkbox" class="sim-supp" value="' . $s['bnsprof_uid'] . '"></td>';
        $html .= '<td>' . htmlspecialchars($s['bnsprof_comp_url']) . '</td>';
        $html .= '<td>' . $s['bnsprof_membership'] . '</td>';
        $html .= '<td>' . htmlspecialchars($s['bnsprof_city']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table><div class="alert alert-info">سيتم إرسال الطلب للموردين المحددين (بدون بيانات المشتري)</div>';
    echo $html;
    echo '<script>$("#simAll").click(function(){$(".sim-supp").prop("checked",this.checked);});</script>';
}

if($action == 'send_to_similar') {
    $rfq_id = $_POST['rfq_id'];
    $supplier_ids = $_POST['supplier_ids'];
    if(is_array($supplier_ids)) {
        foreach($supplier_ids as $sid) {
            // إرسال لكل مورد
            send_whatsapp_notification_to_supplier_specific($rfq_id, $sid);
        }
        echo 'تم إرسال الطلب لـ ' . count($supplier_ids) . ' موردين';
    } else {
        echo 'لم يتم تحديد موردين';
    }
}

function send_whatsapp_notification_to_supplier_specific($br_id, $supplier_id) {
    // نسخة مبسطة للإرسال لمورد معين
    global $con;
    $magic_link = generate_magic_link($supplier_id, $br_id);
    // تحديث سجل الإرسال
    $sql = "UPDATE buy_requirement SET wa_sent_count = wa_sent_count + 1, wa_last_sent_date = NOW() WHERE br_id = '$br_id'";
    mysqli_query($con, $sql);
    return true;
}
?>