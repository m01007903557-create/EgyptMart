<?php
/**
 * File: admin/ajax-file/auction-view-response.php
 * Version: PHP 8.3
 * Description: معالجة طلبات AJAX لعرض المزادات في لوحة التحكم مع دعم البحث والترتيب والترقيم
 * 
 * هذا الملف يتعامل مع طلبات DataTable لعرض المزادات مع إمكانية
 * البحث والترتيب والترقيم وتحديد الحالة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// تشغيل عرض الأخطاء (للتصحيح فقط)
ini_set("display_errors", 1);

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    $json_data = array(
        "draw"            => 0,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => "Database connection error"
    );
    echo json_encode($json_data);
    exit();
}

// تهيئة المتغيرات
$params = $_REQUEST;
$columns = array(
    1 => 'auc_due_date',
    2 => 'auc_heading', 
    3 => 'pc_name',
    4 => 'auc_value',
    5 => 'bnsprof_compname',
    6 => 'cn_name',
    7 => 'mst_name',
    8 => 'expiry_date'
);

$where = "";
$sqlTot = "";
$sqlRec = "";

// التحقق من وجود قيمة بحث
if (!empty($params['search']['value'])) {
    $search_value = mysqli_real_escape_string($con, $params['search']['value']);
    $where .= " AND (";
    $where .= " auc_heading LIKE '%" . $search_value . "%' ";
    $where .= " OR pc_name LIKE '%" . $search_value . "%' ";
    $where .= " OR bnsprof_compname LIKE '%" . $search_value . "%' ";
    $where .= " OR cn_name LIKE '%" . $search_value . "%' ";
    $where .= " OR auc_due_date LIKE '%" . $search_value . "%' ";
    $where .= " OR auc_value LIKE '%" . $search_value . "%' )";
}

// استعلام جلب المزادات النشطة (غير منتهية)
$sql_base = "SELECT a.auc_id, a.auc_usr_id, a.auc_heading, a.auc_publish_date, 
                    a.auc_due_date, a.auc_approval_status, a.auc_value,
                    bf.bnsprof_compname, cn.cn_name, pc.pc_name,
                    sip.mst_name, pm.expiry_date
             FROM auction a
             JOIN product_category pc ON a.auc_pc_id = pc.pc_id
             JOIN user u ON u.usr_id = a.auc_usr_id
             JOIN business_profile bf ON bf.bnsprof_uid = a.auc_usr_id
             JOIN country cn ON cn.cn_id = u.country
             LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id
             LEFT JOIN smembership_icon_plan sip ON sip.mp_id = pm.p_id
             WHERE a.auc_status = '1' 
               AND a.auc_due_date >= CURDATE()";

// استعلامات العدد الكلي
$sqlTot = "SELECT COUNT(*) as count FROM auction a
           JOIN product_category pc ON a.auc_pc_id = pc.pc_id
           JOIN user u ON u.usr_id = a.auc_usr_id
           JOIN business_profile bf ON bf.bnsprof_uid = a.auc_usr_id
           JOIN country cn ON cn.cn_id = u.country
           WHERE a.auc_status = '1' 
             AND a.auc_due_date >= CURDATE()";

$sqlRec = $sql_base;

// إضافة شرط البحث إذا وجد
if (!empty($where)) {
    $sqlTot .= $where;
    $sqlRec .= $where;
}

// إعداد الترتيب
if ($params['order'][0]['column'] == 0) {
    $params['order'][0]['column'] = 1;
    $params['order'][0]['dir'] = 'desc';
}

// إضافة الترتيب والحدود
$order_column = isset($columns[$params['order'][0]['column']]) ? $columns[$params['order'][0]['column']] : 'auc_id';
$order_dir = ($params['order'][0]['dir'] == 'asc') ? 'ASC' : 'DESC';
$start = (int)$params['start'];
$length = (int)$params['length'];

$sqlRec .= " ORDER BY " . $order_column . " " . $order_dir . " 
             LIMIT " . $start . ", " . $length;

// تنفيذ استعلام العدد الكلي
$queryTot = mysqli_query($con, $sqlTot);
$totalRecords = 0;

if ($queryTot && mysqli_num_rows($queryTot) > 0) {
    $queryTotObj = mysqli_fetch_object($queryTot);
    $totalRecords = (int)$queryTotObj->count;
}

// تنفيذ استعلام جلب البيانات
$queryRecords = mysqli_query($con, $sqlRec);

if (!$queryRecords) {
    error_log("خطأ في استعلام المزادات: " . mysqli_error($con));
    $json_data = array(
        "draw"            => intval($params['draw']),
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => mysqli_error($con)
    );
    echo json_encode($json_data);
    exit();
}

// تجهيز البيانات
$data = array();

while ($row = mysqli_fetch_object($queryRecords)) {
    
    // إنشاء روابط الإجراءات
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    
    $plink = empty($query_string) 
        ? "?action=appr&id=" . $row->auc_id
        : "auction-view.php?" . $query_string . "&action=appr&id=" . $row->auc_id;
    
    $dlink = empty($query_string)
        ? "?action=disappr&id=" . $row->auc_id
        : "auction-view.php?" . $query_string . "&action=disappr&id=" . $row->auc_id;
    
    $dellink = empty($query_string)
        ? "?action=del&ad-id=" . $row->auc_id
        : $query_string . "&action=del&ad-id=" . $row->auc_id;
    
    // تحديد نص الحالة
    $status_html = '';
    if ($row->auc_approval_status == '0') {
        $status_html = '<a href="' . $plink . '" onclick="return confirm(\'Are you sure to approve this auction?\')" title="Approve">
                            <img alt="Approve" src="images/active.jpg">
                        </a>&nbsp;
                        <a href="' . $dlink . '" onclick="return confirm(\'Are you sure to disapprove this auction?\')" title="Disapprove">
                            <img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0">
                        </a>';
    } elseif ($row->auc_approval_status == '1') {
        $status_html = '<span style="color:#009933; font-weight:800;">Approved</span>';
    } elseif ($row->auc_approval_status == '2') {
        $status_html = '<span style="color:#CC0000; font-weight:800;">Rejected</span>';
    }
    
    // إنشاء صف البيانات
    $res = array();
    $res[0] = '<input name="cb[]" class="ace" type="checkbox" value="' . (int)$row->auc_id . '" /><span class="lbl"></span>';
    $res[1] = htmlspecialchars(ucwords(stripslashes($row->auc_heading ?? '')));
    $res[2] = htmlspecialchars(ucwords(stripslashes($row->bnsprof_compname ?? '')));
    $res[3] = htmlspecialchars(ucwords(stripslashes($row->cn_name ?? '')));
    $res[4] = !empty($row->auc_publish_date) ? date("d-M-Y", strtotime($row->auc_publish_date)) : '';
    $res[5] = !empty($row->auc_due_date) ? date("d-M-Y", strtotime($row->auc_due_date)) : '';
    $res[6] = '<a href="auction-details.php?token=' . rand(1000, 9000) . md5((string)$row->auc_id) . '" title="View Details">
                   <img src="images/details.png" alt="Details" />
               </a>';
    $res[7] = $status_html;
    $res[8] = '<a href="auction-edit.php?token=' . md5((string)$row->auc_id) . '" title="Edit">
                   <img src="images/edit.jpg" alt="Edit" />
               </a>';
    
    $data[] = $res;
}

// إنشاء مصفوفة النتائج النهائية
$json_data = array(
    "draw"            => intval($params['draw']),
    "recordsTotal"    => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data"            => $data
);

// إرسال البيانات بصيغة JSON
echo json_encode($json_data);

// إنهاء المخزن المؤقت
ob_end_flush();
?>